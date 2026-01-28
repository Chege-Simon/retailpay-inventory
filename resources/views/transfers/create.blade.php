@extends('layout')

@section('title', 'Create Transfer - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">Create New Transfer</h2>
        <p class="text-muted">Transfer inventory between stores</p>
    </div>
    <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Transfers
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card p-4">
            <form action="{{ route('transfers.store') }}" method="POST" id="transferForm">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="from_store_id" class="form-label fw-bold">From Store</label>
                        <select class="form-select @error('from_store_id') is-invalid @enderror" 
                                id="from_store_id" name="from_store_id" required>
                            <option value="">Choose source store...</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('from_store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->branch->name }} - {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('from_store_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="to_store_id" class="form-label fw-bold">To Store</label>
                        <select class="form-select @error('to_store_id') is-invalid @enderror" 
                                id="to_store_id" name="to_store_id" required>
                            <option value="">Choose destination store...</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('to_store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->branch->name }} - {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_store_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div id="productSelection" style="display: none;">
                    <div class="mb-4">
                        <label for="product_id" class="form-label fw-bold">Select Product</label>
                        <select class="form-select @error('product_id') is-invalid @enderror" 
                                id="product_id" name="product_id" required>
                            <option value="">Choose a product...</option>
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="availableStock" class="form-text mt-2"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                        <input type="number" 
                               class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" 
                               name="quantity" 
                               min="1" 
                               value="{{ old('quantity', 1) }}"
                               required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-bold">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-left-right"></i> Initiate Transfer
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
        <div class="table-card p-4 mb-3">
            <h5 class="mb-3"><i class="bi bi-info-circle text-primary"></i> Transfer Information</h5>
            <p class="mb-2"><strong>Inter-Store Transfer:</strong> Within same branch</p>
            <p class="mb-0"><strong>Inter-Branch Transfer:</strong> Between different branches</p>
        </div>
        
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-list-check text-success"></i> Instructions</h5>
            <ol class="mb-0">
                <li class="mb-2">Select the source store (from)</li>
                <li class="mb-2">Select the destination store (to)</li>
                <li class="mb-2">Choose the product to transfer</li>
                <li class="mb-2">Enter the quantity</li>
                <li>Add any relevant notes</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let inventory = [];
    
    $('#from_store_id, #to_store_id').on('change', function() {
        const fromStoreId = $('#from_store_id').val();
        const toStoreId = $('#to_store_id').val();
        
        if (fromStoreId && toStoreId) {
            if (fromStoreId === toStoreId) {
                alert('Source and destination stores must be different');
                $(this).val('');
                return;
            }
            
            loadInventory(fromStoreId);
        }
    });
    
    function loadInventory(storeId) {
        $.ajax({
            url: `/transfers/store/${storeId}/inventory`,
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
    }
    
    function populateProductDropdown() {
        $('#product_id').empty().append('<option value="">Choose a product...</option>');
        
        inventory.forEach(function(item) {
            if (item.available_quantity > 0) {
                $('#product_id').append(`
                    <option value="${item.product_id}" 
                            data-available="${item.available_quantity}"
                            data-sku="${item.sku}">
                        ${item.name} (${item.sku})
                    </option>
                `);
            }
        });
    }
    
    $('#product_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const available = selectedOption.data('available');
        
        if (available) {
            $('#availableStock').html(`
                <i class="bi bi-info-circle text-info"></i> 
                <strong>${available}</strong> units available in source store
            `);
            $('#quantity').attr('max', available);
        } else {
            $('#availableStock').text('');
            $('#quantity').removeAttr('max');
        }
    });
    
    $('#quantity').on('input', function() {
        const max = parseInt($(this).attr('max'));
        const value = parseInt($(this).val());
        
        if (max && value > max) {
            alert(`Only ${max} units available`);
            $(this).val(max);
        }
    });
});
</script>
@endsection