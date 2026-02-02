@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Edit Coupon</h1>

    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Coupon Code --}}
        <div class="form-group">
            <label>Coupon Code</label>
            <input type="text" name="code" class="form-control"
                   value="{{ old('code', $coupon->code) }}" required>
        </div>

        {{-- Type --}}
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="fixed" {{ $coupon->type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="percentage" {{ $coupon->type == 'percentage' ? 'selected' : '' }}>Percentage</option>
            </select>
        </div>

        {{-- Amount --}}
        <div class="form-group">
            <label>Amount</label>
            <input type="number" name="amount" class="form-control"
                   value="{{ old('amount', $coupon->amount) }}" required>
        </div>

        {{-- Dates --}}
        <div class="row">
            <div class="col-md-6">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control"
                       value="{{ $coupon->start_date->format('Y-m-d') }}">
            </div>
            <div class="col-md-6">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control"
                       value="{{ $coupon->end_date->format('Y-m-d') }}">
            </div>
        </div>

        {{-- Minimum Purchase --}}
        <div class="form-group mt-2">
            <label>Minimum Purchase</label>
            <input type="number" name="min_purchase" class="form-control"
                   value="{{ $coupon->min_purchase }}">
        </div>

        {{-- Active --}}
        <div class="form-check">
            <input type="checkbox" name="is_active" class="form-check-input"
                   {{ $coupon->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

        {{-- Apply to all --}}
        <div class="form-check mb-3">
            <input type="checkbox" name="apply_to_all" id="apply_to_all"
                   class="form-check-input"
                   {{ $coupon->apply_to_all ? 'checked' : '' }}>
            <label class="form-check-label">Apply to all products</label>
        </div>

        {{-- Products --}}
        <div id="products-section" style="{{ $coupon->apply_to_all ? 'display:none' : '' }}">
            <label>Select Products</label>

            {{-- Search --}}
            <div class="input-group mb-2">
                <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                <div class="input-group-append">
                    <button type="button" id="clear-search" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Card --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <span id="selected-count">{{ count($selectedProducts) }}</span> selected,
                        <span id="visible-count">0</span> matching
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="select-all-visible" class="form-check-input">
                        <label class="form-check-label"><small>Select visible</small></label>
                    </div>
                </div>

                <div class="card-body product-checkboxes-container" style="max-height:300px; overflow-y:auto">
                    <div id="product-list">
                        @foreach($products as $product)
                        @php
                            $checked = in_array($product->id, $selectedProducts);
                        @endphp
                        <div class="form-check product-item d-none"
                             data-name="{{ strtolower($product->name) }}"
                             data-category="{{ strtolower(optional($product->category)->name) }}"
                             data-sku="{{ strtolower($product->sku ?? '') }}"
                             data-selected="{{ $checked ? 'true' : 'false' }}"
                             style="display:flex; align-items:center; padding:5px;">

                            <input type="checkbox" name="products[]"
                                   value="{{ $product->id }}"
                                   class="form-check-input product-checkbox"
                                   style="margin-right:10px;"
                                   {{ $checked ? 'checked' : '' }}>

                            <label class="form-check-label"
                                   style="flex:1; display:flex; align-items:center; cursor:pointer;">

                                <img src="{{ $product->main_image
                                    ? asset('storage/'.$product->main_image)
                                    : asset('/images/no-image.png') }}"
                                     width="40" height="40"
                                     style="object-fit:cover; border-radius:4px; margin-right:8px;">

                                <div>
                                    <span class="product-name">{{ $product->name }}</span>
                                    @if($product->category)
                                        <span class="badge badge-info ml-2">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary mt-3">Update Coupon</button>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const applyToAllCheckbox = document.getElementById('apply_to_all');
    const productsSection = document.getElementById('products-section');
    const selectAllVisibleCheckbox = document.getElementById('select-all-visible');
    const productSearch = document.getElementById('product-search');
    const clearSearch = document.getElementById('clear-search');
    const productItems = document.querySelectorAll('.product-item');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const visibleCount = document.getElementById('visible-count');
    const selectedCount = document.getElementById('selected-count');

    // Initialize - show only selected products initially
    productItems.forEach(item => {
        if (item.dataset.selected === 'true') {
            item.classList.remove('d-none');
        }
    });
    updateSelectedCount();
    updateVisibleCount();

    // Toggle products section visibility
    applyToAllCheckbox.addEventListener('change', function() {
        productsSection.style.display = this.checked ? 'none' : 'block';
    });

    // Clear search field
    clearSearch.addEventListener('click', function() {
        productSearch.value = '';
        filterProducts();
        productSearch.focus();
    });

    // Select all VISIBLE products
    selectAllVisibleCheckbox.addEventListener('change', function() {
        const visibleCheckboxes = document.querySelectorAll('.product-item:not(.d-none) .product-checkbox');
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            const item = checkbox.closest('.product-item');
            item.dataset.selected = this.checked;
        });
        updateSelectedCount();
    });

    // Product search functionality with debounce
    let searchTimeout;
    productSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterProducts, 300);
    });

    // Allow pressing Enter to search
    productSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterProducts();
        }
    });

    // Filter products based on search terms
    function filterProducts() {
        const searchTerm = productSearch.value.toLowerCase().trim();
        let matchingItems = 0;

        productItems.forEach(item => {
            const isSelected = item.dataset.selected === 'true';
            const itemName = item.dataset.name;
            const itemCategory = item.dataset.category;
            const itemSku = item.dataset.sku;

            // Always show selected items
            if (isSelected) {
                item.classList.remove('d-none');
                highlightMatches(item, searchTerm);
                return;
            }

            // Show matching items only when there's a search term
            if (searchTerm === '') {
                item.classList.add('d-none');
                removeHighlights(item);
            } else if (itemName.includes(searchTerm) ||
                      itemCategory.includes(searchTerm) ||
                      itemSku.includes(searchTerm)) {
                item.classList.remove('d-none');
                matchingItems++;
                highlightMatches(item, searchTerm);
            } else {
                item.classList.add('d-none');
                removeHighlights(item);
            }
        });

        // Update counters
        updateVisibleCount();
        selectAllVisibleCheckbox.checked = false;
    }

    // Highlight matching text in product names
    function highlightMatches(item, term) {
        if (!term) return;

        const nameElement = item.querySelector('.product-name');
        const nameText = nameElement.textContent;
        const regex = new RegExp(term, 'gi');

        nameElement.innerHTML = nameText.replace(regex, match =>
            `<span class="bg-warning">${match}</span>`);
    }

    // Remove highlighting
    function removeHighlights(item) {
        const nameElement = item.querySelector('.product-name');
        if (nameElement) {
            nameElement.innerHTML = nameElement.textContent;
        }
    }

    // Update selected products count
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.product-checkbox:checked').length;
        selectedCount.textContent = selected;

        // Update data-selected attribute
        productCheckboxes.forEach(checkbox => {
            const item = checkbox.closest('.product-item');
            item.dataset.selected = checkbox.checked;
        });
    }

    // Update visible products count
    function updateVisibleCount() {
        const visible = document.querySelectorAll('.product-item:not(.d-none)').length;
        visibleCount.textContent = visible - selectedCount.textContent;
    }

    // Track checkbox changes
    document.getElementById('product-list').addEventListener('change', function(e) {
        if (e.target.classList.contains('product-checkbox')) {
            const item = e.target.closest('.product-item');
            item.dataset.selected = e.target.checked;

            updateSelectedCount();
            updateVisibleCount();

            if (!e.target.checked) {
                selectAllVisibleCheckbox.checked = false;
            } else {
                // Check if all VISIBLE products are selected
                const visibleCheckboxes = document.querySelectorAll('.product-item:not(.d-none) .product-checkbox');
                const allChecked = visibleCheckboxes.length > 0 &&
                    Array.from(visibleCheckboxes).every(cb => cb.checked);
                selectAllVisibleCheckbox.checked = allChecked;
            }
        }
    });
});
</script>

<style>
    .product-checkboxes-container {
        transition: all 0.3s ease;
    }
    .product-item {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .product-item:last-child {
        border-bottom: none;
    }
    .bg-warning {
        background-color: #ffc107;
        padding: 0 2px;
        border-radius: 3px;
    }
    #clear-search {
        cursor: pointer;
    }
    .card-header {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    .d-none {
        display: none !important;
    }
    .product-checkbox {
        margin-top: 0;
    }
</style>
