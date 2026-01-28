@extends('layout')

@section('title', 'Transfer Details - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Transfer Details</h2>
        <p class="text-muted">{{ $transfer->transfer_number }}</p>
    </div>
    <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Transfers
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Transfer Details Card -->
        <div class="table-card p-4 mb-4">
            <h5 class="mb-4"><i class="bi bi-arrow-left-right text-primary"></i> Transfer Information</h5>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded">
                        <h6 class="text-muted mb-2">FROM</h6>
                        <h5 class="fw-bold mb-1">{{ $transfer->fromStore->name }}</h5>
                        <p class="text-muted mb-0">{{ $transfer->fromStore->branch->name }}</p>
                        <small class="text-muted">{{ $transfer->fromStore->location }}</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="p-3 border rounded">
                        <h6 class="text-muted mb-2">TO</h6>
                        <h5 class="fw-bold mb-1">{{ $transfer->toStore->name }}</h5>
                        <p class="text-muted mb-0">{{ $transfer->toStore->branch->name }}</p>
                        <small class="text-muted">{{ $transfer->toStore->location }}</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="text-muted small">Product</label>
                                <p class="fw-bold mb-1">{{ $transfer->product->name }}</p>
                                <small class="text-muted">{{ $transfer->product->sku }}</small>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Quantity</label>
                                <p class="fw-bold mb-0">
                                    <span class="badge bg-primary fs-5">{{ $transfer->quantity }}</span>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Transfer Type</label>
                                <p class="mb-0">
                                    @if($transfer->isInterBranch())
                                        <span class="badge bg-info">Inter-Branch</span>
                                    @else
                                        <span class="badge bg-secondary">Inter-Store</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($transfer->notes)
            <div class="mt-4">
                <label class="text-muted small">Notes</label>
                <p class="p-3 bg-light rounded">{{ $transfer->notes }}</p>
            </div>
            @endif
        </div>
        
        <!-- Actions -->
        @if($transfer->status === 'pending')
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-gear text-secondary"></i> Actions</h5>
            <div class="d-flex gap-2">
                <form action="{{ route('transfers.complete', $transfer) }}" method="POST" onsubmit="return confirm('Are you sure you want to complete this transfer?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Complete Transfer
                    </button>
                </form>
                
                <form action="{{ route('transfers.cancel', $transfer) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this transfer?')">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Cancel Transfer
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <!-- Status & Timeline -->
        <div class="table-card p-4 mb-3">
            <h5 class="mb-3"><i class="bi bi-clock-history text-info"></i> Status & Timeline</h5>
            
            <div class="mb-3">
                <label class="text-muted small">Current Status</label>
                <p class="mb-0">
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'in_transit' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$transfer->status] }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                    </span>
                </p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Transfer Number</label>
                <p class="fw-bold mb-0">{{ $transfer->transfer_number }}</p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Initiated By</label>
                <p class="mb-0">{{ $transfer->initiatedByUser->name ?? 'N/A' }}</p>
                <small class="text-muted">{{ $transfer->transfer_date->format('M d, Y h:i A') }}</small>
            </div>
            
            @if($transfer->status === 'completed')
            <div class="mb-3">
                <label class="text-muted small">Received By</label>
                <p class="mb-0">{{ $transfer->receivedByUser->name ?? 'N/A' }}</p>
                <small class="text-muted">{{ $transfer->received_date?->format('M d, Y h:i A') }}</small>
            </div>
            @endif
            
            <div>
                <label class="text-muted small">Created At</label>
                <p class="mb-0">{{ $transfer->created_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-box-seam text-primary"></i> Product Details</h5>
            
            <div class="mb-3">
                <label class="text-muted small">Product Name</label>
                <p class="fw-bold mb-0">{{ $transfer->product->name }}</p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">SKU</label>
                <p class="mb-0"><code>{{ $transfer->product->sku }}</code></p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Unit Price</label>
                <p class="mb-0">KES {{ number_format($transfer->product->unit_price, 2) }}</p>
            </div>
            
            <div>
                <label class="text-muted small">Transfer Value</label>
                <p class="fw-bold text-success mb-0">
                    KES {{ number_format($transfer->quantity * $transfer->product->unit_price, 2) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection