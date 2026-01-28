@extends('layout')

@section('title', 'Create Sale - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Create New Sale</h2>
        <p class="text-muted">Process a new sale transaction</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Sales
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card p-4">
            <form id="saleForm" action="{{ route('sales.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="store_id" class="form-label fw-bold">Select Store</label>
                    <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id" required>
                        <option value="">Choose a store...</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}"
                                @if(Auth::check() && Auth::user()->store_id == $store->id)
                                    selected
                                @elseif(old('store_id') == $store->id)
                                    selected
                                @endif>
                                {{ $store->branch->name }} - {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div id="productSelection" style="display: none;">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Add Products</label>
                        <div class="input-group">
                            <select class="form-select" id="product_select">
                                <option value="">Select a product...</option>
                            </select>
                            <button type="button" class="btn btn-primary" id="addProductBtn">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th width="100px">Available</th>
                                    <th width="150px">Quantity</th>
                                    <th width="120px">Price</th>
                                    <th width="120px">Subtotal</th>
                                    <th width="80px">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="noItemsRow">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No items added yet. Select products from the dropdown above.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td colspan="2"><strong id="totalAmount">KES 0.00</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                            <i class="bi bi-check-circle"></i> Complete Sale
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-info-circle text-primary"></i> Instructions</h5>
            <ol class="mb-0">
                <li class="mb-2">Select the store where the sale is taking place</li>
                <li class="mb-2">Choose products from the dropdown menu</li>
                <li class="mb-2">Enter the quantity for each product</li>
                <li class="mb-2">Review the total amount</li>
                <li>Click "Complete Sale" to process the transaction</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let inventory = [];
    let items = [];
    let itemCounter = 0;
    
    // Load store inventory when store is selected
    $('#store_id').on('change', function() {
        const storeId = $(this).val();
        
        if (storeId) {
            $.ajax({
                url: `/sales/store/${storeId}/inventory`,
                method: 'GET',
                success: function(data) {
                    inventory = data;
                    populateProductDropdown();
                    $('#productSelection').slideDown();
                },
                error: function() {
                    alert('Error loading inventory');
                }
            });
        } else {
            $('#productSelection').slideUp();
            $('#product_select').empty().append('<option value="">Select a product...</option>');
            inventory = [];
        }
    });
    
    function populateProductDropdown() {
        $('#product_select').empty().append('<option value="">Select a product...</option>');
        
        inventory.forEach(function(item) {
            if (item.available_quantity > 0) {
                $('#product_select').append(`
                    <option value="${item.product_id}" 
                            data-sku="${item.sku}"
                            data-name="${item.name}"
                            data-price="${item.price}"
                            data-available="${item.available_quantity}">
                        ${item.name} (${item.sku}) - ${item.available_quantity} available
                    </option>
                `);
            }
        });
    }
    
    $('#addProductBtn').on('click', function() {
        const selectedOption = $('#product_select option:selected');
        const productId = selectedOption.val();
        
        if (!productId) {
            alert('Please select a product');
            return;
        }
        
        // Check if product already added
        if (items.find(item => item.productId == productId)) {
            alert('This product is already added. Update the quantity instead.');
            return;
        }
        
        const item = {
            id: itemCounter++,
            productId: productId,
            sku: selectedOption.data('sku'),
            name: selectedOption.data('name'),
            price: parseFloat(selectedOption.data('price')),
            available: parseInt(selectedOption.data('available')),
            quantity: 1
        };
        
        items.push(item);
        renderItems();
        $('#product_select').val('');
    });
    
    function renderItems() {
        const tbody = $('#itemsBody');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append(`
                <tr id="noItemsRow">
                    <td colspan="6" class="text-center text-muted py-4">
                        No items added yet. Select products from the dropdown above.
                    </td>
                </tr>
            `);
            $('#submitBtn').prop('disabled', true);
        } else {
            items.forEach(function(item, index) {
                const subtotal = item.quantity * item.price;
                tbody.append(`
                    <tr>
                        <td>
                            <strong>${item.name}</strong><br>
                            <small class="text-muted">${item.sku}</small>
                            <input type="hidden" name="items[${index}][product_id]" value="${item.productId}">
                        </td>
                        <td class="text-center">${item.available}</td>
                        <td>
                            <input type="number" 
                                   class="form-control quantity-input" 
                                   name="items[${index}][quantity]"
                                   value="${item.quantity}"
                                   min="1" 
                                   max="${item.available}"
                                   data-id="${item.id}"
                                   required>
                        </td>
                        <td>KES ${item.price.toFixed(2)}</td>
                        <td class="subtotal">KES ${subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-id="${item.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            $('#submitBtn').prop('disabled', false);
        }
        
        updateTotal();
    }
    
    $(document).on('input', '.quantity-input', function() {
        const id = $(this).data('id');
        const quantity = parseInt($(this).val());
        const item = items.find(i => i.id === id);
        
        if (item) {
            if (quantity > item.available) {
                alert(`Only ${item.available} units available`);
                $(this).val(item.available);
                item.quantity = item.available;
            } else {
                item.quantity = quantity;
            }
            
            const subtotal = item.quantity * item.price;
            $(this).closest('tr').find('.subtotal').text(`KES ${subtotal.toFixed(2)}`);
            updateTotal();
        }
    });
    
    $(document).on('click', '.remove-item', function() {
        const id = $(this).data('id');
        items = items.filter(item => item.id !== id);
        renderItems();
    });
    
    function updateTotal() {
        const total = items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
        $('#totalAmount').text(`KES ${total.toFixed(2)}`);
    }
    
    $('#saleForm').on('reset', function() {
        items = [];
        renderItems();
    });
});
</script>
@endsection