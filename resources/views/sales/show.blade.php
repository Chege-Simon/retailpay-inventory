@extends('layout')

@section('title', 'Sale Details - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Sale Details</h2>
        <p class="text-muted">{{ $sale->sale_number }}</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Sales
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Sale Items -->
        <div class="table-card mb-4">
            <div class="p-3 border-bottom">
                <h5 class="mb-0"><i class="bi bi-cart-check text-success"></i> Sale Items</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td><strong>{{ $item->product->name }}</strong></td>
                            <td><code>{{ $item->product->sku }}</code></td>
                            <td>{{ $item->quantity }}</td>
                            <td>KES {{ number_format($item->unit_price, 2) }}</td>
                            <td><strong>KES {{ number_format($item->subtotal, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total Amount:</td>
                            <td><strong class="text-success fs-5">KES {{ number_format($sale->total_amount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Sale Information -->
        <div class="table-card p-4 mb-3">
            <h5 class="mb-3"><i class="bi bi-info-circle text-primary"></i> Sale Information</h5>
            
            <div class="mb-3">
                <label class="text-muted small">Sale Number</label>
                <p class="fw-bold mb-0">{{ $sale->sale_number }}</p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Status</label>
                <p class="mb-0">
                    <span class="badge bg-{{ $sale->status === 'completed' ? 'success' : 'danger' }}">
                        {{ ucfirst($sale->status) }}
                    </span>
                </p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Store</label>
                <p class="fw-bold mb-0">{{ $sale->store->name }}</p>
                <small class="text-muted">{{ $sale->store->branch->name }}</small>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Processed By</label>
                <p class="mb-0">{{ $sale->user->name ?? 'System' }}</p>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Sale Date</label>
                <p class="mb-0">{{ $sale->sale_date->format('F d, Y') }}</p>
                <small class="text-muted">{{ $sale->sale_date->format('h:i A') }}</small>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small">Total Items</label>
                <p class="mb-0">{{ $sale->items->count() }} items</p>
            </div>
            
            <div>
                <label class="text-muted small">Total Quantity</label>
                <p class="mb-0">{{ $sale->items->sum('quantity') }} units</p>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-gear text-secondary"></i> Actions</h5>
            <div class="d-grid gap-2">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Export PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection