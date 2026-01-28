@extends('layout')

@section('title', 'Request Restock - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Request Restock</h2>
        <p class="text-muted">Submit a request for product restock to your store</p>
    </div>
    <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Transfers
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card p-4">
            <form action="{{ route('transfers.requesttransfer') }}" method="POST" id="restockForm">
                @csrf
                
                <!-- Store Selection -->
                <div class="mb-4">
                    <label for="store_id" class="form-label fw-bold">
                        <i class="bi bi-shop text-primary"></i> Requesting Store
                    </label>
                    <select name="store_id" id="store_id" class="form-select @error('store_id') is-invalid @enderror" required>
                        <option value="">-- Select Your Store --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }} ({{ $store->branch->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select the store that needs the restock</small>
                </div>

                <!-- Product Selection -->
                <div class="mb-4">
                    <label for="product_id" class="form-label fw-bold">
                        <i class="bi bi-box-seam text-primary"></i> Product
                    </label>
                    <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">-- Select Product --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                    data-sku="{{ $product->sku }}"
                                    data-price="{{ $product->unit_price }}"
                                    data-stock="{{ $product->total_stock ?? 0 }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Product Info Display -->
                <div id="productInfo" class="mb-4 p-3 bg-light rounded d-none">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">SKU</small>
                            <p class="mb-0 fw-bold" id="displaySku">-</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Unit Price</small>
                            <p class="mb-0 fw-bold" id="displayPrice">-</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Available Stock</small>
                            <p class="mb-0 fw-bold" id="displayStock">-</p>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-4">
                    <label for="quantity" class="form-label fw-bold">
                        <i class="bi bi-123 text-primary"></i> Quantity Needed
                    </label>
                    <input type="number" 
                           name="quantity" 
                           id="quantity" 
                           class="form-control @error('quantity') is-invalid @enderror" 
                           min="1" 
                           value="{{ old('quantity') }}"
                           placeholder="Enter quantity needed"
                           required>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Estimated Value Display -->
                <div id="estimatedValue" class="mb-4 d-none">
                    <div class="alert alert-info">
                        <strong>Estimated Transfer Value:</strong> 
                        <span class="fs-5 fw-bold">KES <span id="totalValue">0.00</span></span>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label for="notes" class="form-label fw-bold">
                        <i class="bi bi-chat-left-text text-primary"></i> Notes (Optional)
                    </label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="4" 
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Add any additional information or reasons for this restock request...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Include reasons for urgency, special requirements, or any other relevant information</small>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Submit Restock Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Help & Information Sidebar -->
    <div class="col-md-4">
        <div class="table-card p-4 mb-3">
            <h5 class="mb-3"><i class="bi bi-info-circle text-info"></i> Restock Process</h5>
            <ol class="small">
                <li class="mb-2">
                    <strong>Submit Request</strong>
                    <p class="text-muted mb-0">Store manager submits restock request</p>
                </li>
                <li class="mb-2">
                    <strong>Branch Approval</strong>
                    <p class="text-muted mb-0">Branch manager forwards to administrator</p>
                </li>
                <li class="mb-2">
                    <strong>Admin Assignment</strong>
                    <p class="text-muted mb-0">Administrator assigns source store</p>
                </li>
                <li class="mb-2">
                    <strong>Shipment</strong>
                    <p class="text-muted mb-0">Source store ships the items</p>
                </li>
                <li class="mb-2">
                    <strong>Receipt</strong>
                    <p class="text-muted mb-0">Your store receives the items</p>
                </li>
            </ol>
        </div>

        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-lightbulb text-warning"></i> Tips</h5>
            <ul class="small text-muted">
                <li class="mb-2">Provide clear reasons in the notes section</li>
                <li class="mb-2">Check current stock levels before requesting</li>
                <li class="mb-2">Request reasonable quantities</li>
                <li class="mb-2">Mark urgent requests clearly in notes</li>
                <li class="mb-2">You'll be notified at each approval stage</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const productInfo = document.getElementById('productInfo');
    const estimatedValue = document.getElementById('estimatedValue');
    
    // Update product info when product is selected
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const sku = selectedOption.dataset.sku;
            const price = selectedOption.dataset.price;
            const stock = selectedOption.dataset.stock;
            
            document.getElementById('displaySku').textContent = sku;
            document.getElementById('displayPrice').textContent = 'KES ' + parseFloat(price).toLocaleString('en-KE', {minimumFractionDigits: 2});
            document.getElementById('displayStock').textContent = stock + ' units';
            
            productInfo.classList.remove('d-none');
            calculateValue();
        } else {
            productInfo.classList.add('d-none');
            estimatedValue.classList.add('d-none');
        }
    });
    
    // Calculate estimated value
    quantityInput.addEventListener('input', calculateValue);
    
    function calculateValue() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (productSelect.value && quantity > 0) {
            const price = parseFloat(selectedOption.dataset.price);
            const total = price * quantity;
            
            document.getElementById('totalValue').textContent = total.toLocaleString('en-KE', {minimumFractionDigits: 2});
            estimatedValue.classList.remove('d-none');
        } else {
            estimatedValue.classList.add('d-none');
        }
    }
    
    // Form validation
    document.getElementById('restockForm').addEventListener('submit', function(e) {
        const quantity = parseInt(quantityInput.value);
        if (quantity < 1) {
            e.preventDefault();
            alert('Quantity must be at least 1');
            return false;
        }
    });
});
</script>
@endpush
@endsection