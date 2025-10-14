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


        <div class="card-body">
            <div class="mb-3 d-flex justify-content-end">
                <input type="text" id="search" class="form-control" placeholder="Search...." style="width: 30%;">
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-head-bg-primary mt-4" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Buy Price</th>
                            <th>Regular Price</th>
                            <th>Total Stock</th>
                            <th>Category</th>
                            <th>Variants</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="product-table">
                        @foreach($products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td>
                                @if($product->main_image)
                                    <img src="{{ asset('storage/'.$product->main_image) }}" width="30" class="img-thumbnail">
                                @else
                                    <span class="text-muted">No main image</span>
                                @endif
                            </td>
                            <td>&#2547;{{ number_format($product->buy_price, 2) }}</td>
                            <td>&#2547;{{ number_format($product->regular_price, 2) }}</td>
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


<script>
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
</script>
