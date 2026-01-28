<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['store.branch', 'user']);
        if (Auth::user()->role != 'admin') {
            $query->whereHas('store', function ($q) {
                $q->where('branch_id', Auth::user()->branch_id);
            });
        }

        if ($request->has('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', Carbon::parse($request->date_from)->format('Y-m-d'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', Carbon::parse($request->date_to)->format('Y-m-d'));
        }


        $sales = $query->latest('sale_date')->paginate(10);
        $stores = Store::with('branch')->get();

        return view('sales.index', compact('sales', 'stores'));
    }

    public function create()
    {
        $stores_query = Store::with('branch')->where('is_active', true);
        
        if (Auth::user()->role != 'admin') {
            $stores_query->where('branch_id', Auth::user()->branch_id);
        }
        $stores = $stores_query->get();
        $products = Product::where('is_active', true)->get();
        
        return view('sales.create', compact('stores', 'products'));
        }
        
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $this->authorize('create', Store::where('id', $validated['store_id'])->firstOrFail());
        // if (! Gate::allows('create', Store::where('id', $validated['store_id'])->firstOrFail())) 
        // {
        //     abort(403); 
        // }

        DB::beginTransaction();

        try {
            $sale = Sale::create([
                'sale_number' => Sale::generateSaleNumber(),
                'store_id' => $validated['store_id'],
                'user_id' => Auth::id(),
                'total_amount' => 0,
                'status' => 'completed',
                'sale_date' => now(),
                'uuid' => Str::uuid()->toString()
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $inventory = Inventory::where('product_id', $product->id)
                    ->where('store_id', $validated['store_id'])
                    ->first();

                if (!$inventory || $inventory->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->unit_price,
                    'uuid' => Str::uuid()->toString()
                ]);

                // Record stock movement
                StockMovement::record(
                    $product,
                    $sale->store,
                    'sale',
                    -$item['quantity'],
                    $sale,
                    Auth::user(),
                    "Sale #{$sale->sale_number}"
                );
            }

            $sale->calculateTotal();

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Sale completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Sale failed: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['store.branch', 'items.product', 'user']);
        $this->authorize('view', $sale);
        return view('sales.show', compact('sale'));
    }

    public function getStoreInventory($storeId)
    {
        $inventory = Inventory::with('product')
            ->where('store_id', $storeId)
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'sku' => $item->product->sku,
                    'name' => $item->product->name,
                    'price' => $item->product->unit_price,
                    'available_quantity' => $item->quantity,
                ];
            });

        return response()->json($inventory);
    }
}