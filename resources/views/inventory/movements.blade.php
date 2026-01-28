@extends('layout')

@section('title', 'Stock Movements - KK Wholesalers')

@section('content')
<div class="page-header">
    <h2 class="fw-bold">Stock Movement History</h2>
    <p class="text-muted">Complete audit trail of all stock changes</p>
</div>

<!-- Filters -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('inventory.movements') }}" class="row g-3">
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
        
        <div class="col-md-2">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select">
                <option value="">All Products</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->sku }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-2">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sale</option>
                <option value="transfer_out" {{ request('type') === 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                <option value="transfer_in" {{ request('type') === 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                <option value="initial" {{ request('type') === 'initial' ? 'selected' : '' }}>Initial</option>
            </select>
        </div>
        
        <div class="col-md-2">
            <label class="form-label">Date From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        
        <div class="col-md-2">
            <label class="form-label">Date To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        
        <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i>
            </button>
        </div>
    </form>
</div>

<!-- Movements Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>Product</th>
                    <th>Store</th>
                    <th>Type</th>
                    <th>Change</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>User</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td>
                        {{ $movement->created_at->format('M d, Y') }}<br>
                        <small class="text-muted">{{ $movement->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        <strong>{{ $movement->product->name }}</strong><br>
                        <small class="text-muted">{{ $movement->product->sku }}</small>
                    </td>
                    <td>
                        {{ $movement->store->name }}<br>
                        <small class="text-muted">{{ $movement->store->branch->name }}</small>
                    </td>
                    <td>
                        @php
                            $typeColors = [
                                'sale' => 'danger',
                                'transfer_out' => 'warning',
                                'transfer_in' => 'success',
                                'adjustment' => 'info',
                                'initial' => 'secondary'
                            ];
                            $typeIcons = [
                                'sale' => 'cart-check',
                                'transfer_out' => 'arrow-right-circle',
                                'transfer_in' => 'arrow-left-circle',
                                'adjustment' => 'gear',
                                'initial' => 'plus-circle'
                            ];
                        @endphp
                        <span class="badge bg-{{ $typeColors[$movement->type] }}">
                            <i class="bi bi-{{ $typeIcons[$movement->type] }}"></i>
                            {{ ucfirst(str_replace('_', ' ', $movement->type)) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $movement->quantity_change >= 0 ? 'success' : 'danger' }}">
                            {{ $movement->quantity_change >= 0 ? '+' : '' }}{{ $movement->quantity_change }}
                        </span>
                    </td>
                    <td>{{ $movement->quantity_before }}</td>
                    <td>{{ $movement->quantity_after }}</td>
                    <td>{{ $movement->user->name ?? 'System' }}</td>
                    <td>
                        @if($movement->reference_type === 'App\\Models\\Sale')
                            <a href="{{ route('sales.show', $movement->reference_id) }}" class="text-decoration-none">
                                Sale #{{ $movement->reference->sale_number ?? 'N/A' }}
                            </a>
                        @elseif($movement->reference_type === 'App\\Models\\Transfer')
                            <a href="{{ route('transfers.show', $movement->reference_id) }}" class="text-decoration-none">
                                Transfer #{{ $movement->reference->transfer_number ?? 'N/A' }}
                            </a>
                        @else
                            <small class="text-muted">{{ $movement->notes ?? 'N/A' }}</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                        No stock movements found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($movements->hasPages())
    <div class="p-3 border-top">
        {{ $movements->links() }}
    </div>
    @endif
</div>
@endsection