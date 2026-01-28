<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['product', 'store.branch']);

        if ($request->has('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->has('low_stock') && $request->low_stock) {
            $query->whereRaw('quantity <= minimum_stock');
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $inventory = $query->paginate(20);
        $stores = Store::with('branch')->get();
        $products = Product::all();

        return view('inventory.index', compact('inventory', 'stores', 'products'));
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
        
        $stockByStore = [];
        foreach ($stores as $store) {
            $stockByStore[$store->id] = [
                'store' => $store,
                'total_value' => $store->inventory->sum(function ($inv) {
                    return $inv->quantity * $inv->product->unit_price;
                }),
                'low_stock_count' => $store->inventory->filter(function ($inv) {
                    return $inv->isLowStock();
                })->count(),
            ];
        }

        $products = Product::with('inventory.store')->get();
        
        return view('inventory.report', compact('stockByStore', 'products'));
    }
}