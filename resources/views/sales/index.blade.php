@extends('layout')

@section('title', 'Sales - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Sales Management</h2>
        <p class="text-muted">Track all sales transactions</p>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> New Sale
    </a>
</div>

<!-- Filters -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
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
            <label class="form-label">Date From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        
        <div class="col-md-3">
            <label class="form-label">Date To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        
        <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <div class="d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Sales Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sale #</th>
                    <th>Store</th>
                    <th>Branch</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" class="text-decoration-none fw-bold">
                            {{ $sale->sale_number }}
                        </a>
                    </td>
                    <td>{{ $sale->store->name }}</td>
                    <td>{{ $sale->store->branch->name }}</td>
                    <td>{{ $sale->items->count() }} items</td>
                    <td><strong>KES {{ number_format($sale->total_amount, 2) }}</strong></td>
                    <td>
                        <span class="badge bg-{{ $sale->status === 'completed' ? 'success' : 'danger' }}">
                            {{ ucfirst($sale->status) }}
                        </span>
                    </td>
                    <td>{{ $sale->sale_date->format('M d, Y H:i') }}</td>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                        No sales found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($sales->hasPages())
    <div class="p-3 border-top">
        {{ $sales->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection