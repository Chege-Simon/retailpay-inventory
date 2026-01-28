<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Transfer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{ 
    public function index()
    {
        $stats = [
            'total_branches' => Branch::count(),
            'total_stores' => Store::count(),
            'total_products' => Product::count(),
            'today_sales' => Sale::whereDate('sale_date', today())->count(),
            'today_revenue' => Sale::whereDate('sale_date', today())->sum('total_amount'),
            'pending_transfers' => Transfer::where('status', 'pending')->count(),
        ];

        $lowStockItems = Inventory::with(['product', 'store'])
            ->whereRaw('quantity <= minimum_stock')
            ->orderBy('quantity', 'asc')
            ->limit(10)
            ->get();

        $recentSales = Sale::with(['store.branch', 'items.product'])
            ->latest('sale_date')
            ->limit(5)
            ->get();

        $recentTransfers = Transfer::with(['fromStore', 'toStore', 'product'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'lowStockItems', 'recentSales', 'recentTransfers'));
    }
}
