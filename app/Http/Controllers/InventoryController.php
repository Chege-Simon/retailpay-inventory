<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['product', 'store.branch']);

        if (Auth::user()->role != 'admin') {
            $query->whereHas('store', function ($q) {
                $q->where('branch_id', Auth::user()->branch_id);
            });
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('low_stock') && $request->low_stock) {
            $query->whereRaw('quantity <= minimum_stock');
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $totalItems = (clone $query)->sum('quantity');
        $totalValue = (clone $query)->selectRaw('SUM(quantity * unit_price) as total')
                    ->join('products', 'inventories.product_id', '=', 'products.id')
                    ->value('total');
        $inventory = $query->paginate(10);
        $stores = Store::with('branch')->where('is_active', true)->where('branch_id', Auth::user()->branch_id)->get();
        $products = Product::all();

        return view('inventory.index', compact('inventory', 'stores', 'products', 'totalItems', 'totalValue'));
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'store.branch', 'user']);

        if ($request->has('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(50);
        $stores = Store::with('branch')->get();
        $products = Product::all();

        return view('inventory.movements', compact('movements', 'stores', 'products'));
    }

    public function report()
    {
        $stores = Store::with(['branch', 'inventory.product'])->get();
        $products = Product::with('inventory.store.branch')->get();

        $stockByStore = [];
        foreach ($stores as $store) {
            $stockByStore[$store->id] = [
                'store' => $store,
                'total_value' => $store->inventory->sum(fn($inv) => $inv->quantity * $inv->product->unit_price),
                'low_stock_count' => $store->inventory->where(fn($inv) => $inv->isLowStock())->count(),
            ];
        }

        // Precompute low stock items once
        $lowStockItems = $products->flatMap(
            fn($product) =>
            $product->inventory->filter(fn($inv) => $inv->isLowStock())
        );

        // Precompute totals once
        $grandTotalQty = $products->sum(fn($p) => $p->inventory->sum('quantity'));
        $grandTotalValue = $products->sum(fn($p) => $p->inventory->sum('quantity') * $p->unit_price);

        return view('inventory.report', compact('stockByStore', 'products', 'lowStockItems', 'grandTotalQty', 'grandTotalValue'));
    }
}
