@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Manage Free Delivery Products: {{ $deliveryOption->name }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.delivery-options.update-products', $deliveryOption->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-9">
                        <div class="form-group">
                            <input type="text" class="form-control" id="product-search"
                                   placeholder="Search by product name, SKU or variant SKU">
                        </div>
                    </div>
                    <div class="col-md-3 mt-2">
                        <button type="button" id="clear-search" class="btn btn-secondary">
                            X
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Products Eligible for Free Delivery</label>

                    <div class="products-list">
                        @foreach($allProducts as $product)
                        <div class="col-md-12 mb-3 product-item {{ in_array($product->id, $selectedProductIds) ? 'selected-product' : 'unselected-product' }}"
                             data-name="{{ strtolower($product->name) }}"
                             data-sku="{{ strtolower($product->sku) }}"
                             data-variant-skus="{{ $product->variants->flatMap->options->pluck('sku')->implode(',') }}"
                             style="{{ !in_array($product->id, $selectedProductIds) ? 'display: none;' : '' }}">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input product-checkbox"
                                       id="product-{{ $product->id }}"
                                       name="products[]"
                                       value="{{ $product->id }}"
                                       {{ in_array($product->id, $selectedProductIds) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="product-{{ $product->id }}">
                                    {{ $product->name }}
                                    <div class="small text-muted">
                                        SKU: {{ $product->sku }}
                                    </div>
                                    @if($product->has_variants)
                                        <div class="small text-muted mt-1">
                                            Variant SKUs: {{ $product->variants->flatMap->options->pluck('sku')->implode(', ') }}
                                        </div>
                                    @endif
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.delivery-options.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const clearSearch = document.getElementById('clear-search');
    const productItems = document.querySelectorAll('.product-item');

    // Show selected products by default
    document.querySelectorAll('.selected-product').forEach(item => {
        item.style.display = 'block';
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        if (searchTerm.length > 0) {
            // Show all products during search
            productItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const sku = item.getAttribute('data-sku');
                const variantSkus = item.getAttribute('data-variant-skus');

                if (name.includes(searchTerm) ||
                    sku.includes(searchTerm) ||
                    variantSkus.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        } else {
            // When search is cleared, show only selected products
            productItems.forEach(item => {
                if (item.classList.contains('selected-product')) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    });

    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        productItems.forEach(item => {
            if (item.classList.contains('selected-product')) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>