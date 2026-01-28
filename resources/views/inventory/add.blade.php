@extends('layout')

@section('title', isset($product) ? 'Edit Product - KK Wholesalers' : 'Add New Product - KK Wholesalers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold">{{ isset($product) ? 'Edit Product' : 'Add New Product' }}</h2>
        <p class="text-muted">{{ isset($product) ? 'Update product information' : 'Create a new product in the system' }}</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Products
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card p-4">
            <form action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data"
                  id="productForm">
                @csrf
                @if(isset($product))
                    @method('PUT')
                @endif

                <h5 class="mb-4 pb-2 border-bottom">
                    <i class="bi bi-box-seam text-primary"></i> Basic Information
                </h5>

                <!-- Product Name -->
                <div class="mb-4">
                    <label for="name" class="form-label fw-bold">
                        Product Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $product->name ?? '') }}"
                           placeholder="e.g., Coca Cola 500ml"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- SKU -->
                <div class="mb-4">
                    <label for="sku" class="form-label fw-bold">
                        SKU (Stock Keeping Unit) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="text" 
                               name="sku" 
                               id="sku" 
                               class="form-control @error('sku') is-invalid @enderror" 
                               value="{{ old('sku', $product->sku ?? '') }}"
                               placeholder="e.g., COCA-500ML-001"
                               {{ isset($product) ? 'readonly' : '' }}
                               required>
                        @if(!isset($product))
                        <button type="button" class="btn btn-outline-secondary" id="generateSku">
                            <i class="bi bi-shuffle"></i> Generate
                        </button>
                        @endif
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">
                        {{ isset($product) ? 'SKU cannot be changed after creation' : 'Unique identifier for the product' }}
                    </small>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="form-label fw-bold">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="4" 
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Enter product description, features, or specifications...">{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <h5 class="mb-4 pb-2 border-bottom mt-5">
                    <i class="bi bi-tag text-primary"></i> Pricing & Category
                </h5>

                <div class="row">
                    <!-- Category -->
                    <div class="col-md-6 mb-4">
                        <label for="category_id" class="form-label fw-bold">
                            Category <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" 
                                id="category_id" 
                                class="form-select @error('category_id') is-invalid @enderror" 
                                required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Unit of Measure -->
                    <div class="col-md-6 mb-4">
                        <label for="unit_of_measure" class="form-label fw-bold">
                            Unit of Measure <span class="text-danger">*</span>
                        </label>
                        <select name="unit_of_measure" 
                                id="unit_of_measure" 
                                class="form-select @error('unit_of_measure') is-invalid @enderror" 
                                required>
                            <option value="">-- Select Unit --</option>
                            <option value="piece" {{ old('unit_of_measure', $product->unit_of_measure ?? '') == 'piece' ? 'selected' : '' }}>Piece</option>
                            <option value="box" {{ old('unit_of_measure', $product->unit_of_measure ?? '') == 'box' ? 'selected' : '' }}>Box</option>
                            <option value="carton" {{ old('unit_of_measure', $product->unit_of_measure ?? '') == 'carton' ? 'selected' : '' }}>Carton</option>
                            <option value="kg" {{ old('unit_of_measure', $product->unit_of_measure ?? '') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                            <option value="liter" {{ old('unit_of_measure', $product->unit_of_measure ?? '') == 'liter' ? 'selected' : '' }}>Liter</option>
                            <option value="pack" {{ old('unit_of_measure', $product->unit_of_measure ?? '') == 'pack' ? 'selected' : '' }}>Pack</option>
                        </select>
                        @error('unit_of_measure')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <!-- Unit Price -->
                    <div class="col-md-6 mb-4">
                        <label for="unit_price" class="form-label fw-bold">
                            Unit Price (KES) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">KES</span>
                            <input type="number" 
                                   name="unit_price" 
                                   id="unit_price" 
                                   class="form-control @error('unit_price') is-invalid @enderror" 
                                   value="{{ old('unit_price', $product->unit_price ?? '') }}"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   required>
                            @error('unit_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Reorder Level -->
                    <div class="col-md-6 mb-4">
                        <label for="reorder_level" class="form-label fw-bold">
                            Reorder Level <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               name="reorder_level" 
                               id="reorder_level" 
                               class="form-control @error('reorder_level') is-invalid @enderror" 
                               value="{{ old('reorder_level', $product->reorder_level ?? '') }}"
                               min="0"
                               placeholder="Minimum stock level"
                               required>
                        @error('reorder_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Alert when stock falls below this level</small>
                    </div>
                </div>

                <h5 class="mb-4 pb-2 border-bottom mt-5">
                    <i class="bi bi-image text-primary"></i> Product Image
                </h5>

                <!-- Product Image -->
                <div class="mb-4">
                    <label for="image" class="form-label fw-bold">
                        Product Image
                    </label>
                    
                    @if(isset($product) && $product->image_url)
                    <div class="mb-3">
                        <img src="{{ $product->image_url }}" 
                             alt="{{ $product->name }}" 
                             class="img-thumbnail"
                             style="max-width: 200px;"
                             id="currentImage">
                        <p class="text-muted small mt-2">Current image</p>
                    </div>
                    @endif
                    
                    <input type="file" 
                           name="image" 
                           id="image" 
                           class="form-control @error('image') is-invalid @enderror"
                           accept="image/*">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Accepted formats: JPG, PNG, GIF (Max: 2MB)</small>
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-3 d-none">
                        <img src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                </div>

                <h5 class="mb-4 pb-2 border-bottom mt-5">
                    <i class="bi bi-toggles text-primary"></i> Additional Options
                </h5>

                <!-- Status -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               value="1"
                               {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <strong>Active Product</strong>
                            <br>
                            <small class="text-muted">Inactive products won't be available for sales or transfers</small>
                        </label>
                    </div>
                </div>

                <!-- Barcode (Optional) -->
                <div class="mb-4">
                    <label for="barcode" class="form-label fw-bold">
                        Barcode
                    </label>
                    <input type="text" 
                           name="barcode" 
                           id="barcode" 
                           class="form-control @error('barcode') is-invalid @enderror" 
                           value="{{ old('barcode', $product->barcode ?? '') }}"
                           placeholder="Enter product barcode">
                    @error('barcode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">For barcode scanning (optional)</small>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ isset($product) ? 'Update Product' : 'Create Product' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Sidebar -->
    <div class="col-md-4">
        <div class="table-card p-4 mb-3">
            <h5 class="mb-3"><i class="bi bi-info-circle text-info"></i> Quick Guide</h5>
            <ul class="small text-muted">
                <li class="mb-2"><strong>Product Name:</strong> Clear, descriptive name</li>
                <li class="mb-2"><strong>SKU:</strong> Unique code for tracking</li>
                <li class="mb-2"><strong>Category:</strong> Helps organize inventory</li>
                <li class="mb-2"><strong>Unit Price:</strong> Standard selling price</li>
                <li class="mb-2"><strong>Reorder Level:</strong> Low stock alert threshold</li>
            </ul>
        </div>

        @if(!isset($product))
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-lightbulb text-warning"></i> SKU Format Tips</h5>
            <p class="small text-muted">Recommended SKU format:</p>
            <ul class="small text-muted">
                <li>Use category prefix (e.g., BEVR, FOOD)</li>
                <li>Include product identifier</li>
                <li>Add variant (size, color, etc.)</li>
                <li>Sequential number</li>
            </ul>
            <p class="small mb-0"><strong>Example:</strong> BEVR-COCA-500ML-001</p>
        </div>
        @endif

        @if(isset($product))
        <div class="table-card p-4">
            <h5 class="mb-3"><i class="bi bi-graph-up text-success"></i> Product Stats</h5>
            <div class="mb-2">
                <small class="text-muted">Total Stock:</small>
                <p class="fw-bold mb-0">{{ $product->inventories->sum('quantity') }} units</p>
            </div>
            <div class="mb-2">
                <small class="text-muted">Total Stores:</small>
                <p class="fw-bold mb-0">{{ $product->inventories->count() }} stores</p>
            </div>
            <div>
                <small class="text-muted">Created:</small>
                <p class="mb-0">{{ $product->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // SKU Generator
    const generateSkuBtn = document.getElementById('generateSku');
    if (generateSkuBtn) {
        generateSkuBtn.addEventListener('click', function() {
            const name = document.getElementById('name').value;
            const category = document.getElementById('category_id').options[document.getElementById('category_id').selectedIndex].text;
            
            if (!name) {
                alert('Please enter product name first');
                return;
            }
            
            // Generate SKU from name
            const namePart = name.substring(0, 4).toUpperCase().replace(/[^A-Z]/g, '');
            const categoryPart = category.substring(0, 4).toUpperCase().replace(/[^A-Z]/g, '');
            const randomPart = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
            
            const sku = `${categoryPart}-${namePart}-${randomPart}`;
            document.getElementById('sku').value = sku;
        });
    }
    
    // Image Preview
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.querySelector('img').src = e.target.result;
                imagePreview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.classList.add('d-none');
        }
    });
    
    // Price formatting
    const priceInput = document.getElementById('unit_price');
    priceInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
    
    // Form validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const unitPrice = parseFloat(document.getElementById('unit_price').value);
        const reorderLevel = parseInt(document.getElementById('reorder_level').value);
        
        if (unitPrice < 0) {
            e.preventDefault();
            alert('Unit price cannot be negative');
            return false;
        }
        
        if (reorderLevel < 0) {
            e.preventDefault();
            alert('Reorder level cannot be negative');
            return false;
        }
    });
});
</script>
@endpush
@endsection