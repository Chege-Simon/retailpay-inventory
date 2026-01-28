@extends('layout')

@section('title', 'Inventory - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Inventory Management</h2>
        <p class="text-muted">Monitor stock levels across all stores</p>
    </div>
    <div>
        <!-- <a href="{{ route('inventory.add') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Restock Product
        </a> -->
        <a href="{{ route('transfers.add') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Request Transfers
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mt-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Items</p>
                    <h3 class="fw-bold mb-0">{{ $inventory->total() }}</h3>
                </div>
                <div class="icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Low Stock Items</p>
                    <h3 class="fw-bold mb-0">
                        {{ $inventory->filter(fn($i) => $i->isLowStock())->count() }}
                    </h3>
                </div>
                <div class="icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Quantity</p>
                    <h3 class="fw-bold mb-0">{{ $totalItems }}</h3>
                </div>
                <div class="icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-layers"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Total Value</p>
                    <h3 class="fw-bold mb-0">
                        KES {{ number_format($totalValue, 2) }}
                    </h3>
                </div>
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('inventory.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Store</label>
            <select name="store_id" class="form-select">
                <option value="">All Stores</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                        {{ $store->branch->name }} - {{ $store->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select">
                <option value="">All Products</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label">Stock Level</label>
            <select name="low_stock" class="form-select">
                <option value="">All Levels</option>
                <option value="1" {{ request('low_stock') ? 'selected' : '' }}>Low Stock Only</option>
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <div class="d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Inventory Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Store</th>
                    <th>Branch</th>
                    <th>Quantity</th>
                    <th>Min Stock</th>
                    <th>Status</th>
                    <th>Value</th>
                    <!-- <th>Action</th> -->
                </tr>
            </thead>
            <tbody>
                @forelse($inventory as $item)
                <tr>
                    <td><strong>{{ $item->product->name }}</strong></td>
                    <td><code>{{ $item->product->sku }}</code></td>
                    <td>{{ $item->store->name }}</td>
                    <td>{{ $item->store->branch->name }}</td>
                    <td>
                        <span class="badge {{ $item->isLowStock() ? 'bg-danger' : 'bg-success' }}">
                            {{ $item->quantity }}
                        </span>
                    </td>
                    <td>{{ $item->minimum_stock }}</td>
                    <td>
                        @if($item->isLowStock())
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-exclamation-triangle"></i> Low Stock
                            </span>
                        @else
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Healthy
                            </span>
                        @endif
                    </td>
                    <td>KES {{ number_format($item->quantity * $item->product->unit_price, 2) }}</td>
                    <!-- <td>
                        <form method="POST" action="{{ route('transfers.requesttransfer') }}">
                            @csrf
                            <input type="hidden" name="store_id" value="{{ $item->store->id }}">
                            <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                            <input type="hidden" name="quantity" value="10">
                            <input type="hidden" name="notes" value="">
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-patch-plus-fill"></i> Request Transafer
                            </button>
                        </form>
                    </td> -->
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No inventory records found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($inventory->hasPages())
        <div class="p-3 border-top">
            {{ $inventory->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@endsection