<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{ 
    public function index()
    {
        $stats = [
            'today_sales' => Sale::whereDate('sale_date', today())->whereHas('store', function ($q) {
                $q->where('branch_id', Auth::user()->branch_id);
                })->count(),
            'today_revenue' => Sale::whereDate('sale_date', today())->whereHas('store', function ($q) {
                $q->where('branch_id', Auth::user()->branch_id);
                })->sum('total_amount'),
            'pending_transfers' => Transfer::where('status', 'pending')->whereHas('fromStore', function ($q) {
                $q->where('branch_id', Auth::user()->branch_id);
                })
                ->orWhereHas('toStore', function ($q) {
                    $q->where('branch_id', Auth::user()->branch_id);
                })->count(),
        ];
        
        if(Auth::user()->role == 'admin') {
            $stats = array_merge($stats,[
                'total_branches' => Branch::count(),
                'total_stores' => Store::count(),
                'total_products' => Product::count(),
            ]);
        }

        $lowStockItems = Inventory::with(['product', 'store'])
            ->whereRaw('quantity <= minimum_stock')
            ->when(Auth::user()->role != 'admin', function ($query) {
                $query->whereHas('store', function ($q) {
                    $q->where('branch_id', Auth::user()->branch_id);
                });
            })
            ->orderBy('quantity', 'asc')
            ->limit(10)
            ->get();

        $recentSales = Sale::with(['store.branch', 'items.product'])
            ->when(Auth::user()->role != 'admin', function ($query) {
                $query->whereHas('store', function ($q) {
                    $q->where('branch_id', Auth::user()->branch_id);
                });
            })
            ->latest('sale_date')
            ->limit(5)
            ->get();

        $recentTransfers = Transfer::with(['fromStore', 'toStore', 'product'])
            ->when(Auth::user()->role != 'admin', function ($query) {
                $query->whereHas('fromStore', function ($q) {
                    $q->where('branch_id', Auth::user()->branch_id);
                    })
                    ->orWhereHas('toStore', function ($q) {
                        $q->where('branch_id', Auth::user()->branch_id);
                    });
            })
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'lowStockItems', 'recentSales', 'recentTransfers'));
    }
}
