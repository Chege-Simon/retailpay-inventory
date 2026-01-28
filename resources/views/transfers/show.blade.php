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
                    <div class="p-3 border rounded {{ $transfer->from_store_id ? '' : 'bg-light' }}">
                        <h6 class="text-muted mb-2">FROM</h6>
                        @if($transfer->fromStore)
                            <h5 class="fw-bold mb-1">{{ $transfer->fromStore->name }}</h5>
                            <p class="text-muted mb-0">{{ $transfer->fromStore->branch->name }}</p>
                            <small class="text-muted">{{ $transfer->fromStore->location }}</small>
                        @else
                            <p class="text-muted fst-italic mb-0">Pending admin assignment</p>
                        @endif
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
                                    @if($transfer->fromStore && $transfer->isInterBranch())
                                        <span class="badge bg-info">Inter-Branch</span>
                                    @elseif($transfer->fromStore)
                                        <span class="badge bg-secondary">Inter-Store</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
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
        
        <!-- Actions Based on Status and Role -->
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-gear text-secondary"></i> Actions</h5>
            
            @if($transfer->status === 'requested')
                {{-- Branch Manager can forward to admin --}}
                @if(Auth()->user()->role === 'branch_manager' && $transfer->toStore && $transfer->toStore->branch_id === Auth()->user()->branch_id)
                <div class="d-flex gap-2">
                    <form action="{{ route('transfers.forward', $transfer) }}" method="POST" onsubmit="return confirm('Forward this restock request to administrator?')">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Forward to Administrator
                        </button>
                    </form>
                    
                    <form action="{{ route('transfers.cancel', $transfer) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this request?')">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Cancel Request
                        </button>
                    </form>
                </div>
                @else
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i> Waiting for branch manager to forward this request.
                </p>
                @endif
            @endif
            
            @if($transfer->status === 'pending_admin_approval')
                {{-- Administrator can approve and assign source --}}
                @if(Auth()->user()->role === 'admin')
                <form action="{{ route('transfers.approve', $transfer) }}" method="POST" onsubmit="return confirm('Approve and assign source store for this transfer?')">
                    @csrf
                    <div class="mb-3">
                        <label for="from_store_id" class="form-label">Select Source Store</label>
                        <select name="from_store_id" id="from_store_id" class="form-select" required>
                            <option value="">-- Select Store --</option>
                            @foreach(\App\Models\Store::where('id', '!=', $transfer->to_store_id)->get() as $store)
                                <option value="{{ $store->id }}">
                                    {{ $store->name }} ({{ $store->branch->name }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the store that will supply the items</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Approve & Assign Source
                        </button>
                        
                        <a href="{{ route('transfers.cancel', $transfer) }}" 
                           class="btn btn-danger"
                           onclick="event.preventDefault(); if(confirm('Cancel this transfer request?')) document.getElementById('cancel-form').submit();">
                            <i class="bi bi-x-circle"></i> Reject Request
                        </a>
                    </div>
                </form>
                
                <form id="cancel-form" action="{{ route('transfers.cancel', $transfer) }}" method="POST" class="d-none">
                    @csrf
                </form>
                @else
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i> Waiting for administrator approval and source assignment.
                </p>
                @endif
            @endif
            
            @if($transfer->status === 'approved')
                {{-- Source store manager can acknowledge shipment --}}
                @if(Auth()->user()->role === 'store_manager' && $transfer->fromStore && $transfer->fromStore->branch_id === Auth()->user()->branch_id)
                <div class="alert alert-info mb-3">
                    <i class="bi bi-exclamation-circle"></i> 
                    Please confirm that you have shipped the items to <strong>{{ $transfer->toStore->name }}</strong>
                </div>
                
                <form action="{{ route('transfers.ship', $transfer) }}" method="POST" onsubmit="return confirm('Confirm shipment? This will deduct items from your inventory.')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-truck"></i> Acknowledge Shipment
                    </button>
                </form>
                @else
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i> Waiting for {{ $transfer->fromStore->name }} to ship the items.
                </p>
                @endif
            @endif
            
            @if($transfer->status === 'in_transit')
                {{-- Destination store manager can acknowledge receipt --}}
                @if(Auth()->user()->role === 'branch_manager' && $transfer->toStore && $transfer->toStore->branch_id === Auth()->user()->branch_id)
                <div class="alert alert-success mb-3">
                    <i class="bi bi-box-seam"></i> 
                    Items have been shipped from <strong>{{ $transfer->fromStore->name }}</strong>. 
                    Please confirm receipt.
                </div>
                
                <form action="{{ route('transfers.receive', $transfer) }}" method="POST" onsubmit="return confirm('Confirm receipt? This will add items to your inventory.')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Acknowledge Receipt
                    </button>
                </form>
                @else
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i> In transit. Waiting for {{ $transfer->toStore->name }} to receive the items.
                </p>
                @endif
            @endif
            
            @if($transfer->status === 'completed')
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle"></i> Transfer completed successfully!
                </div>
            @endif
            
            @if($transfer->status === 'cancelled')
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-circle"></i> This transfer has been cancelled.
                </div>
            @endif
        </div>
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
                            'requested' => 'secondary',
                            'pending_admin_approval' => 'warning',
                            'approved' => 'info',
                            'in_transit' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $statusLabels = [
                            'requested' => 'Requested',
                            'pending_admin_approval' => 'Pending Admin Approval',
                            'approved' => 'Approved',
                            'in_transit' => 'In Transit',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$transfer->status] ?? 'secondary' }} fs-6">
                        {{ $statusLabels[$transfer->status] ?? ucfirst($transfer->status) }}
                    </span>
                </p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Transfer Number</label>
                <p class="fw-bold mb-0">{{ $transfer->transfer_number }}</p>
            </div>
            
            <hr>
            
            <!-- Timeline Events -->
            <div class="timeline">
                @if($transfer->requested_date)
                <div class="mb-3">
                    <label class="text-muted small">
                        <i class="bi bi-1-circle-fill text-secondary"></i> Requested By
                    </label>
                    <p class="mb-0">{{ $transfer->requestedByUser->name ?? 'N/A' }}</p>
                    <small class="text-muted">{{ $transfer->requested_date instanceof DateTimeInterface ? $transfer->requested_date->format('M d, Y h:i A') : date_format(new DateTime('@' . strtotime($transfer->requested_date)), 'M d, Y h:i A') }}</small>
                </div>
                @endif
                
                @if($transfer->forwarded_date)
                <div class="mb-3">
                    <label class="text-muted small">
                        <i class="bi bi-2-circle-fill text-warning"></i> Forwarded By
                    </label>
                    <p class="mb-0">{{ $transfer->forwardedByUser->name ?? 'N/A' }}</p>
                    <small class="text-muted">{{ date_format(date_create_from_format('Y-m-d H:i:s', $transfer->forwarded_date), 'M d, Y h:i A') }}</small>
                </div>
                @endif
                
                @if($transfer->approved_date)
                <div class="mb-3">
                    <label class="text-muted small">
                        <i class="bi bi-3-circle-fill text-info"></i> Approved By
                    </label>
                    <p class="mb-0">{{ $transfer->approvedByUser->name ?? 'N/A' }}</p>
                    <small class="text-muted">{{ date_format(date_create_from_format('Y-m-d H:i:s', $transfer->approved_date), 'M d, Y h:i A') }}</small>
                </div>
                @endif
                
                @if($transfer->shipped_date)
                <div class="mb-3">
                    <label class="text-muted small">
                        <i class="bi bi-4-circle-fill text-primary"></i> Shipped By
                    </label>
                    <p class="mb-0">{{ $transfer->shippedByUser->name ?? 'N/A' }}</p>
                    <small class="text-muted">{{ date_format(date_create_from_format('Y-m-d H:i:s', $transfer->shipped_date), 'M d, Y h:i A') }}</small>
                </div>
                @endif
                
                @if($transfer->received_date)
                <div class="mb-3">
                    <label class="text-muted small">
                        <i class="bi bi-5-circle-fill text-success"></i> Received By
                    </label>
                    <p class="mb-0">{{ $transfer->receivedByUser->name ?? 'N/A' }}</p>
                    <small class="text-muted">{{ $transfer->received_date->format('M d, Y h:i A') }}</small>
                </div>
                @endif
                
                @if($transfer->cancelled_date)
                <div class="mb-3">
                    <label class="text-muted small">
                        <i class="bi bi-x-circle-fill text-danger"></i> Cancelled By
                    </label>
                    <p class="mb-0">{{ $transfer->cancelledByUser->name ?? 'N/A' }}</p>
                    <small class="text-muted">{{ date_format(date_create_from_format('Y-m-d H:i:s', $transfer->cancelled_date), 'M d, Y h:i A') }}</small>
                </div>
                @endif
            </div>
            
            <hr>
            
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
        
        <!-- Progress Indicator -->
        <div class="table-card p-4 mt-3">
            <h6 class="mb-3">Transfer Progress</h6>
            <div class="progress" style="height: 25px;">
                @php
                    $progress = [
                        'requested' => 20,
                        'pending_admin_approval' => 40,
                        'approved' => 60,
                        'in_transit' => 80,
                        'completed' => 100,
                        'cancelled' => 0
                    ];
                    $progressValue = $progress[$transfer->status] ?? 0;
                    $progressColor = $transfer->status === 'cancelled' ? 'danger' : 'success';
                @endphp
                <div class="progress-bar bg-{{ $progressColor }}" 
                     role="progressbar" 
                     style="width: {{ $progressValue }}%"
                     aria-valuenow="{{ $progressValue }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    {{ $progressValue }}%
                </div>
            </div>
        </div>
    </div>
</div>
@endsection