@extends('layout')

@section('title', 'Transfers - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Transfer Management</h2>
        <p class="text-muted">Manage inventory transfers between stores</p>
    </div>
    @if(Auth::user()->role === 'admin')
    <a href="{{ route('transfers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Transfer
    </a>
    @endif
</div>

<!-- Filters -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('transfers.index') }}" class="row g-3">
        <div class="col-md-4">
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
        
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        
        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <div class="d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Transfers Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Transfer #</th>
                    <th>Product</th>
                    <th>From Store</th>
                    <th>To Store</th>
                    <th>Quantity</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                <tr>
                    <td>
                        <a href="{{ route('transfers.show', $transfer) }}" class="text-decoration-none fw-bold">
                            {{ $transfer->transfer_number }}
                        </a>
                    </td>
                    <td>
                        <strong>{{ $transfer->product->name }}</strong><br>
                        <small class="text-muted">{{ $transfer->product->sku }}</small>
                    </td>
                    <td>
                        {{ $transfer->fromStore?->name }}<br>
                        <small class="text-muted">{{ $transfer->fromStore?->branch?->name }}</small>
                    </td>
                    <td>
                        {{ $transfer->toStore->name }}<br>
                        <small class="text-muted">{{ $transfer->toStore->branch->name }}</small>
                    </td>
                    <td><span class="badge bg-secondary">{{ $transfer->quantity }}</span></td>
                    <td>
                        @if($transfer->isInterBranch())
                            <span class="badge bg-info">Inter-Branch</span>
                        @else
                            <span class="badge bg-secondary">Inter-Store</span>
                        @endif
                    </td>
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
                            {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                        </span>
                    </td>
                    <td>{{ optional($transfer->created_at)->format('M d, Y H:i') ?? '-' }}</td>
                    <td>
                        <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-arrow-left-right fs-1 d-block mb-2"></i>
                        No transfers found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($transfers->hasPages())
    <div class="p-3 border-top">
        {{ $transfers->links() }}
    </div>
    @endif
</div>
@endsection