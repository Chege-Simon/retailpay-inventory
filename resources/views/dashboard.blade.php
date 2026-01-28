@extends('layout')

@section('title', 'Dashboard - KK Wholesalers')

@section('content')
<div class="page-header">
    <h2 class="fw-bold">Dashboard</h2>
    <p class="text-muted">Overview of your inventory management system</p>
</div>

<!-- Stats Cards -->
 @if (Auth()->user()->role == 'admin')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Branches</p>
                    <h3 class="fw-bold mb-0">{{ $stats['total_branches'] }}</h3>
                </div>
                <div class="icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Stores</p>
                    <h3 class="fw-bold mb-0">{{ $stats['total_stores'] }}</h3>
                </div>
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shop"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Products</p>
                    <h3 class="fw-bold mb-0">{{ $stats['total_products'] }}</h3>
                </div>
                <div class="icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Today's Sales</p>
                    <h3 class="fw-bold mb-0">{{ $stats['today_sales'] }}</h3>
                </div>
                <div class="icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-cart-check"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Today's Revenue</p>
                    <h3 class="fw-bold mb-0">KES {{ number_format($stats['today_revenue'], 2) }}</h3>
                </div>
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Pending Transfers</p>
                    <h3 class="fw-bold mb-0">{{ $stats['pending_transfers'] }}</h3>
                </div>
                <div class="icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Items -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="table-card">
            <div class="p-3 border-bottom">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> Low Stock Items</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Store</th>
                            <th>Quantity</th>
                            <th>Min Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product->name }}</strong><br>
                                <small class="text-muted">{{ $item->product->sku }}</small>
                            </td>
                            <td>{{ $item->store->name }}</td>
                            <td>
                                <span class="badge bg-danger">{{ $item->quantity }}</span>
                            </td>
                            <td>{{ $item->minimum_stock }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                                All stock levels are healthy
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="table-card">
            <div class="p-3 border-bottom">
                <h5 class="mb-0"><i class="bi bi-clock-history text-primary"></i> Recent Sales</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sale #</th>
                            <th>Store</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" class="text-decoration-none">
                                    {{ $sale->sale_number }}
                                </a>
                            </td>
                            <td>{{ $sale->store->name }}</td>
                            <td><strong>KES {{ number_format($sale->total_amount, 2) }}</strong></td>
                            <td>{{ $sale->sale_date->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No recent sales</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transfers -->
<div class="row">
    <div class="col-12">
        <div class="table-card">
            <div class="p-3 border-bottom">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right text-info"></i> Recent Transfers</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transfer #</th>
                            <th>Product</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Creation Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransfers as $transfer)
                        <tr>
                            <td>
                                <a href="{{ route('transfers.show', $transfer) }}" class="text-decoration-none">
                                    {{ $transfer->transfer_number }}
                                </a>
                            </td>
                            <td>{{ $transfer->product->name }}</td>
                            <td>{{ $transfer->fromStore?->name }}</td>
                            <td>{{ $transfer->toStore->name }}</td>
                            <td>{{ $transfer->quantity }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'requested' => 'warning',
                                        'pending_admin_approval' => 'warning',
                                        'approved' => 'info',
                                        'in_transit' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$transfer->status] }}">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </td>
                            <td>{{ optional($transfer->created_at)->format('M d, Y H:i') ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No recent transfers</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection