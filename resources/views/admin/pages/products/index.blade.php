@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Products Management</h3>
            <div class="card-tools">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
        </div>
            <div id="alert-container">
                @include('admin.layouts.partials.__alerts')
            </div>

        <div class="card-body">
            <div class="mb-3 d-flex justify-content-space-between gap-2">

            <!-- Status / Stock Filter -->
            <select id="product_filter" class="form-control" style="width: 20%;">
                <option value="">All Products</option>
                <option value="stock_out" {{ request('filter') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                <option value="active" {{ request('filter') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('filter') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="best_sale" {{ request('filter') === 'best_sale' ? 'selected' : '' }}>Best Sale</option>
                <option value="offer" {{ request('filter') === 'offer' ? 'selected' : '' }}>Offer</option>
                <option value="campaign" {{ request('filter') === 'campaign' ? 'selected' : '' }}>Campaign</option>
            </select>

            <!-- Category Filter -->
            <select id="category_filter" class="form-control" style="width: 25%;">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <input type="text" id="search" class="form-control" placeholder="Search..." style="width: 30%; justify-content: end;">
        </div>

            {{-- <div class="mb-3 d-flex justify-content-end">
                <input type="text" id="search" class="form-control" placeholder="Search...." style="width: 30%;">
            </div> --}}
            <div class="table-responsive">
                <table class="table table-striped table-hover table-head-bg-primary mt-4" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>SLUG</th>
                            <th>Image</th>
                            <th>Buy Price</th>
                            <th>Regular Price</th>
                            <th>Discount Price</th>
                            <th>Total Stock</th>
                            <th>Category</th>
                            <th>Variants</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="product-table">
                        @foreach($products as $index => $product)
                        <tr>
                            <td>{{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td>
                                @php
                                    $fullUrl = rtrim($domain ?? config('app.url'), '/') . '/product/' . $product->slug;
                                    $shortUrl = substr($fullUrl, 0, 20) . '....' . substr($fullUrl, -20);
                                @endphp

                                <span title="{{ $fullUrl }}">{{ $shortUrl }}</span>
                                <button class="btn btn-sm copy-btn" data-url="{{ $fullUrl }}" title="Copy URL" style="border:none;">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </td>
                            <td>
                                @if($product->main_image)
                                    <img src="{{ asset('storage/'.$product->main_image) }}" width="100" class="img-thumbnail" style="max-width: none;">
                                @else
                                    <span class="text-muted">No main image</span>
                                @endif
                            </td>
                            <td>&#2547;{{ number_format($product->buy_price, 0) }}</td>
                            <td>&#2547;{{ number_format($product->regular_price, 0) }}</td>
                            <td>&#2547;{{ number_format($product->discount_price, 0) }}</td>
                            <td>{{ number_format($product->total_stock) }} PCS</td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>
                                @foreach($product->variants as $variant)
                                    <span class="badge bg-secondary">{{ $variant->color->name ?? '' }}</span>
                                @endforeach
                            </td>
                            <td>
                                @include('admin.pages.products.partials.__actions')
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-3" id="no-results" style="display: none;">No products found.</div>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $products->appends(['search' => request('search')])->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection


{{-- <script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const tableBody = document.getElementById('product-table');
    const noResults = document.getElementById('no-results');

    searchInput.addEventListener('keyup', function() {
        let query = this.value;

        fetch(`{{ route('admin.products.index') }}?search=${query}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            tableBody.innerHTML = html;

            if(html.trim() === '') {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        })
        .catch(err => console.log(err));
    });
});
</script> --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('search');
    const productFilter = document.getElementById('product_filter');
    const categoryFilter = document.getElementById('category_filter');
    const tableBody = document.getElementById('product-table');
    const noResults = document.getElementById('no-results');

    function fetchProducts() {
        const params = new URLSearchParams({
            search: searchInput.value,
            filter: productFilter.value,
            category: categoryFilter.value,
        });

        fetch(`{{ route('admin.products.index') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            tableBody.innerHTML = html;
            noResults.style.display = html.trim() === '' ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('keyup', fetchProducts);
    productFilter.addEventListener('change', fetchProducts);
    categoryFilter.addEventListener('change', fetchProducts);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyButtons = document.querySelectorAll('.copy-btn');

    copyButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.dataset.url;
            const icon = this.querySelector('i');
            if (!url || !icon) return;

            // Copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                icon.classList.remove('fa-copy');
                icon.classList.add('fa-clipboard');
                icon.style.color = 'green';

                setTimeout(() => {
                    icon.classList.remove('fa-clipboard');
                    icon.classList.add('fa-copy');
                    icon.style.color = '';
                }, 1500);
            }).catch(() => {
                alert('Failed to copy URL.');
            });
        });
    });
});
</script>
