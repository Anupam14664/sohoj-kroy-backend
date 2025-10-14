@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Product Details: {{ $product->name }}</h3>
            <div>
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary mr-2">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Basic Information Column -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tbody>
                                        <tr>
                                            <th width="30%">SKU</th>
                                            <td>{{ $product->sku }}</td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ $product->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Slug</th>
                                            <td>{{ $product->slug }}</td>
                                        </tr>
                                        <tr>
                                            <th>Short Description</th>
                                            <td>{!! $product->short_description !!}</td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>{!! $product->description !!}</td>
                                        </tr>
                                        <tr>
                                            <th>Category</th>
                                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Default Size</th>
                                            <td>{{ $product->size_id ? $product->size->name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Buy Price</th>
                                            <td>{{ number_format($product->buy_price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Regular Price</th>
                                            <td>{{ number_format($product->regular_price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Discount Price</th>
                                            <td>{{ $product->discount_price ? number_format($product->discount_price, 2) : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Keyword Tags</th>
                                            <td>{{ is_array($product->keyword_tags) ? implode(', ', $product->keyword_tags) : $product->keyword_tags }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($product->is_featured)
                                                    <span class="badge badge-success">Featured</span>
                                                @endif
                                                @if($product->is_offer)
                                                    <span class="badge badge-danger">Offer</span>
                                                @endif
                                                @if($product->is_campaign)
                                                    <span class="badge badge-warning">Campaign</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ $product->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images Column -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Product Images</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <h6>Main Image</h6>
                                    @if($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}"
                                             class="img-fluid rounded border"
                                             style="max-height: 250px; width: auto;">
                                    @else
                                        <div class="alert alert-warning">No main image uploaded</div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <h6>Gallery Images</h6>
                                    @if($product->images->count() > 0)
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <h4>Gallery Images</h4>
                                            </div>
                                            @foreach($product->images as $image)
                                            <div class="col-md-3 mb-4">
                                                <div class="card h-100">
                                                    <img src="{{ asset('storage/'.$image->image_path) }}"
                                                        class="card-img-top img-fluid"
                                                        style="height: 200px; object-fit: cover;"
                                                        alt="Gallery Image">
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">No gallery images uploaded</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Variants Section -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Product Variants</h5>
                    <span class="badge badge-primary">
                        {{ $product->variants->count() }} Variants
                    </span>
                </div>
                <div class="card-body">
                    @if($product->variants->count() > 0)
                        <div class="row">
                            @foreach($product->variants as $variant)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="color-badge" style="background-color: {{ $variant->color->code ?? '#ccc' }}"></span>
                                                {{ $variant->color->name ?? 'N/A' }}
                                            </div>
                                            <span class="badge badge-info">
                                                {{ $variant->options->count() }} Options
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    @if($variant->image)
                                                        <img src="{{ asset('storage/' . $variant->image) }}"
                                                             class="img-fluid rounded border"
                                                             style="max-height: 150px;">
                                                    @else
                                                        <div class="alert alert-warning">No variant image</div>
                                                    @endif
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Size</th>
                                                                    <th>Price</th>
                                                                    <th>Stock</th>
                                                                    <th>SKU</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($variant->options as $option)
                                                                    <tr class="{{ $option->stock <= 0 ? 'table-danger' : '' }}">
                                                                        <td>{{ $option->size->name ?? 'N/A' }}</td>
                                                                        <td>{{ number_format($option->price, 2) }}</td>
                                                                        <td>{{ $option->stock }}</td>
                                                                        <td>{{ $option->sku }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This product has no variants
                        </div>
                    @endif
                </div>
            </div>

            <!-- Inventory Summary -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Inventory Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="50%">Total Variants</th>
                                    <td>{{ $inventorySummary['total_variants'] }}</td>
                                </tr>
                                <tr>
                                    <th>Total Options</th>
                                    <td>{{ $inventorySummary['total_options'] }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="50%">Total Stock</th>
                                    <td>{{ number_format($product->total_stock) }}</td>
                                </tr>
                                <tr>
                                    <th>Out of Stock Options</th>
                                    <td>{{ $inventorySummary['out_of_stock'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


<style>
    .card-header {
        font-weight: 600;
    }
    th {
        background-color: #f8f9fa;
    }
    .img-thumbnail {
        max-width: 100%;
        height: auto;
        transition: transform 0.3s;
    }
    .img-thumbnail:hover {
        transform: scale(1.05);
    }
    .color-badge {
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
</style>