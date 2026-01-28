@extends('layout')

@section('title', 'Inventory Report - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Inventory Report</h2>
        <p class="text-muted">Comprehensive stock analysis across all locations</p>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
        <button class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </button>
    </div>
</div>

<!-- Summary by Store -->
<div class="row g-4 mb-4">
    @foreach($stockByStore as $storeId => $data)
    <div class="col-md-4">
        <div class="table-card p-4">
            <h5 class="mb-3">
                <i class="bi bi-shop text-primary"></i> {{ $data['store']->name }}
            </h5>
            <p class="text-muted mb-3">{{ $data['store']->branch->name }}</p>
            
            <div class="mb-3">
                <label class="text-muted small">Total Stock Value</label>
                <h4 class="text-success mb-0">KES {{ number_format($data['total_value'], 2) }}</h4>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Total Items</label>
                <p class="fw-bold mb-0">{{ $data['store']->inventory->count() }}</p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Total Quantity</label>
                <p class="fw-bold mb-0">{{ $data['store']->inventory->sum('quantity') }} units</p>
            </div>
            
            <div>
                <label class="text-muted small">Low Stock Items</label>
                <p class="mb-0">
                    @if($data['low_stock_count'] > 0)
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i> {{ $data['low_stock_count'] }}
                        </span>
                    @else
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> All Healthy
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Product Stock Levels Across All Stores -->
<div class="table-card mb-4">
    <div class="p-3 border-bottom">
        <h5 class="mb-0"><i class="bi bi-boxes text-info"></i> Stock Levels by Product</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    @foreach($stockByStore as $data)
                        <th class="text-center">{{ $data['store']->name }}</th>
                    @endforeach
                    <th class="text-center">Total</th>
                    <th class="text-end">Total Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><code>{{ $product->sku }}</code></td>
                    
                    @php $totalQty = 0; @endphp
                    @foreach($stockByStore as $storeId => $data)
                        @php
                            $inventory = $product->inventory->firstWhere('store_id', $storeId);
                            $qty = $inventory ? $inventory->quantity : 0;
                            $totalQty += $qty;
                            $isLow = $inventory && $inventory->isLowStock();
                        @endphp
                        <td class="text-center">
                            <span class="badge bg-{{ $isLow ? 'danger' : ($qty > 0 ? 'success' : 'secondary') }}">
                                {{ $qty }}
                            </span>
                        </td>
                    @endforeach
                    
                    <td class="text-center">
                        <strong>{{ $totalQty }}</strong>
                    </td>
                    <td class="text-end">
                        <strong>KES {{ number_format($totalQty * $product->unit_price, 2) }}</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="{{ count($stockByStore) + 2 }}" class="text-end fw-bold">Grand Total:</td>
                    <td class="text-center fw-bold">
                        {{ $products->sum(function($p) { return $p->inventory->sum('quantity'); }) }}
                    </td>
                    <td class="text-end fw-bold text-success">
                        KES {{ number_format($products->sum(function($p) { 
                            return $p->inventory->sum('quantity') * $p->unit_price; 
                        }), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Low Stock Alert Section -->
@php
    $lowStockItems = [];
    foreach($products as $product) {
        foreach($product->inventory as $inv) {
            if($inv->isLowStock()) {
                $lowStockItems[] = $inv;
            }
        }
    }
@endphp

@if(count($lowStockItems) > 0)
<div class="table-card">
    <div class="p-3 border-bottom bg-warning bg-opacity-10">
        <h5 class="mb-0 text-warning">
            <i class="bi bi-exclamation-triangle"></i> Low Stock Alert ({{ count($lowStockItems) }} items)
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Store</th>
                    <th>Current Stock</th>
                    <th>Minimum Stock</th>
                    <th>Shortage</th>
                    <th>Action Needed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small class="text-muted">{{ $item->product->sku }}</small>
                    </td>
                    <td>
                        {{ $item->store->name }}<br>
                        <small class="text-muted">{{ $item->store->branch->name }}</small>
                    </td>
                    <td>
                        <span class="badge bg-danger">{{ $item->quantity }}</span>
                    </td>
                    <td>{{ $item->minimum_stock }}</td>
                    <td>
                        <span class="text-danger fw-bold">
                            {{ $item->minimum_stock - $item->quantity }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-circle"></i> Restock Required
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@section('styles')
<style>
@media print {
    .sidebar, .navbar, .btn, .page-header .btn-group {
        display: none !important;
    }
    .col-md-10 {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .table-card {
        box-shadow: none !important;
        page-break-inside: avoid;
    }
}
</style>
@endsection