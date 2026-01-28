<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['store.branch', 'user']);

        if ($request->has('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->latest('sale_date')->paginate(20);
        $stores = Store::with('branch')->get();

        return view('sales.index', compact('sales', 'stores'));
    }

    public function create()
    {
        $stores = Store::with('branch')->where('is_active', true)->get();
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

        DB::beginTransaction();

        try {
            $sale = Sale::create([
                'sale_number' => Sale::generateSaleNumber(),
                'store_id' => $validated['store_id'],
                'user_id' => Auth::id(),
                'total_amount' => 0,
                'status' => 'completed',
                'sale_date' => now(),
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