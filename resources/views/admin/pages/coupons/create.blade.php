@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Create New Coupon</h1>

    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="code">Coupon Code</label>
            <input type="text" name="code" id="code" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <select name="type" id="type" class="form-control" required>
                <option value="fixed">Fixed Amount</option>
                <option value="percentage">Percentage</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="min_purchase">Minimum Purchase Amount (0 for no minimum)</label>
            <input type="number" name="min_purchase" id="min_purchase" class="form-control" step="0.01" min="0" value="0">
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
            <label for="is_active" class="form-check-label">Active</label>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="apply_to_all" id="apply_to_all" class="form-check-input" value="1">
            <label for="apply_to_all" class="form-check-label">Apply to all products</label>
        </div>

        <div class="form-group" id="products-section">
            <label>Select Products (leave empty if applying to all)</label>

            <!-- Enhanced search box with clear button -->
            <div class="input-group mb-3">
                <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="clear-search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Product selection area with counters -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span id="selected-count">0</span> selected,
                        <span id="visible-count">0</span> matching
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="select-all-visible" class="form-check-input">
                        <label for="select-all-visible" class="form-check-label mb-0"><small>Select visible</small></label>
                    </div>
                </div>
                <div class="product-checkboxes-container card-body" style="max-height: 300px; overflow-y: auto;">
                    <div id="product-list">
                        @foreach($products as $product)
                        <div class="form-check product-item mb-2 d-none"
                             data-name="{{ strtolower($product->name) }}"
                             data-category="{{ $product->category ? strtolower($product->category->name) : '' }}"
                             data-sku="{{ strtolower($product->sku ?? '') }}"
                             data-selected="false">
                            <input type="checkbox" name="products[]" id="product-{{ $product->id }}"
                                   value="{{ $product->id }}" class="form-check-input product-checkbox">
                            <label for="product-{{ $product->id }}" class="form-check-label">
                                <span class="product-name">{{ $product->name }}</span>
                                @if($product->category)
                                <span class="badge badge-info ml-2">{{ $product->category->name }}</span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Create Coupon</button>
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

        // Initialize
        updateSelectedCount();

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
            visibleCount.textContent = matchingItems;
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

        // Track checkbox changes
        document.getElementById('product-list').addEventListener('change', function(e) {
            if (e.target.classList.contains('product-checkbox')) {
                const item = e.target.closest('.product-item');
                item.dataset.selected = e.target.checked;

                updateSelectedCount();

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
        }
        .d-none {
            display: none !important;
        }
    </style>

