@extends('layout')

@section('title', 'Sale Receipt - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center no-print">
    <div>
        <h2 class="fw-bold">Sale Details</h2>
        <p class="text-muted">{{ $sale->sale_number }}</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Sales
    </a>
</div>

<!-- Receipt Area -->
<div id="receipt" class="mx-auto my-4 p-4 border rounded bg-white" style="max-width: 400px; font-family: monospace;">
    <h4 class="text-center fw-bold mb-2">KK WHOLESALERS</h4>
    <p class="text-center mb-1">{{ $sale->store->branch->name }} - {{ $sale->store->name }}</p>
    <p class="text-center mb-3">Sale #: {{ $sale->sale_number }}</p>

    <hr>

    <table class="table table-sm mb-3">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Sub</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-end">{{ $item->quantity }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <p class="text-end fw-bold">TOTAL: KES {{ number_format($sale->total_amount, 2) }}</p>

    <hr>

    <p class="mb-0">Processed By: {{ $sale->user->name ?? 'System' }}</p>
    <p class="mb-0">Date: {{ $sale->sale_date->format('F d, Y h:i A') }}</p>
    <p class="mb-0">Items: {{ $sale->items->count() }} | Qty: {{ $sale->items->sum('quantity') }}</p>

    <hr>

    <p class="text-center small">Thank you for shopping with us!</p>
</div>

<!-- Actions -->
<div class="d-grid gap-2 no-print">
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="bi bi-printer"></i> Print Receipt
    </button>

</div>

<style>
    /* Hide everything except receipt when printing */
    @media print {
        body * {
            visibility: hidden;
        }

        #receipt,
        #receipt * {
            visibility: visible;
        }

        #receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .no-print {
            display: none !important;
        }
    }
</style>
@endsection