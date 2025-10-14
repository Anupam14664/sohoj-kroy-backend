@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Edit Product</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" style="width: 100%;" id="product-form">
                @csrf
                @method('PUT')

                <div class="card mb-4 text-black">
                    <div class="card-header" style="font-size: 18px;">Basic Information</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror"
                                   value="{{ old('sku', $product->sku) }}">
                            @error('sku')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name) }}">
                            @error('name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug (URL)</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $product->slug ?? '') }}">
                            <small class="text-muted">Leave empty to auto-generate from product name</small>
                            @error('slug')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="short_description">Short Description</label>
                            <textarea name="short_description" id="short_description" class="form-control" rows="3">{{ old('short_description', isset($product) ? $product->short_description : '') }}</textarea>
                            @error('short_description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control ckeditor @error('description') is-invalid @enderror" rows="10">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="size_id">Product Size (Optional)</label>
                            <select name="size_id" id="size_id" class="form-control">
                                <option value="">-- No specific size --</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}"
                                        {{ old('size', $product->size) == $size->id ? 'selected' : '' }}>
                                        {{ $size->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Leave unselected if product doesn't have a default size</small>
                        </div>

                        <div class="form-group">
                            <label for="total_stock">Total Stock</label>
                            <input type="number" step="0.01" name="total_stock" id="total_stock"
                                   class="form-control @error('total_stock') is-invalid @enderror"
                                   value="{{ old('total_stock', $product->total_stock) }}">
                            @error('total_stock')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="Purchase_price">Buy Price</label>
                            <input type="number" step="0.01" name="Purchase_price" id="Purchase_price"
                                   class="form-control @error('Purchase_price') is-invalid @enderror"
                                   value="{{ old('Purchase_price', $product->buy_price) }}">
                            @error('Purchase_price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="regular_price">Regular Selling Price</label>
                            <input type="number" step="0.01" name="regular_price" id="regular_price"
                                   class="form-control @error('regular_price') is-invalid @enderror"
                                   value="{{ old('regular_price', $product->regular_price) }}">
                            @error('regular_price')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="discount_price">Discount Price</label>
                            <input type="number" step="0.01" name="discount_price" id="discount_price"
                                   class="form-control @error('discount_price') is-invalid @enderror"
                                   value="{{ old('discount_price', $product->discount_price) }}">
                            @error('discount_price')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="main_image">Main Product Image</label>
                            <input type="file" name="main_image" id="main_image" class="form-control-file @error('main_image') is-invalid @enderror">
                            @error('main_image')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                            @if($product->main_image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/'.$product->main_image) }}" style="max-height: 150px;">
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="card mb-4">
                                <div class="card-header">Gallery Images</div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Gallery Images</label>
                                        <input type="file" id="gallery_images" name="gallery_images[]" multiple style="visibility: hidden;">
                                        <button type="button" id="add-more-images" class="btn btn-secondary">
                                            <i class="fas fa-plus"></i> Add Images
                                        </button>
                                        <div class="row mt-2" id="gallery-preview"></div>
                                        @error('gallery_images')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Existing Images Preview -->
                                    <div class="row mt-3" id="existing-gallery-preview">
                                        @if($product->images && $product->images->count() > 0)
                                            @foreach($product->images as $image)
                                                <div class="col-md-3 mb-3 existing-image" data-id="{{ $image->id }}">
                                                    <div class="position-relative">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                                             class="img-thumbnail" style="height: 150px; width: 100%; object-fit: cover;">
                                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image"
                                                                onclick="removeExistingImage(this, {{ $image->id }})">
                                                            ×
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="existing_images" id="existing_images"
                                   value="{{ $product->images ? $product->images->pluck('id')->implode(',') : '' }}">
                            <input type="hidden" name="removed_images" id="removed_images" value="">
                        </div>

                        <div class="d-flex">
                            <div class="form-group form-check">
                                <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1"
                                    {{ old('featured', $product->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">Featured Product</label>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="Offer" id="offer" class="form-check-input" value="1"
                                    {{ old('Offer', $product->is_offer) ? 'checked' : '' }}>
                                <label class="form-check-label" for="offer">Offer Product</label>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="campaign" id="campaign" class="form-check-input" value="1"
                                    {{ old('campaign', $product->is_campaign) ? 'checked' : '' }}>
                                <label class="form-check-label" for="campaign">Campaign Product</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variants Section -->
                <div class="card mb-4">
                    <div class="card-header">Variants (Optional)</div>
                    <div class="card-body">
                        <div id="variants-container">
                            @foreach($product->variants as $variantIndex => $variant)
                                @include('admin.pages.products.partials.__variant', [
                                    'variantIndex' => $variantIndex,
                                    'variant' => $variant,
                                    'colors' => $colors,
                                    'sizes' => $sizes
                                ])

                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary mt-3" id="add-variant">Add Variant</button>
                    </div>
                </div>







                {{-- <div class="card mb-4">
                    <div class="form-group">
                        <label>Keyword Tags (comma-separated)</label>
                        <input type="text" name="keyword_tags" class="form-control"
                               placeholder="Add tags..."
                               value="{{ old('keyword_tags', is_array($product->keyword_tags ?? null) ? implode(',', $product->keyword_tags) : ($product->keyword_tags ?? '')) }}">
                    </div>
                </div> --}}

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="status" name="status" value="1" {{ old('status', isset($product) ? $product->status : true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="status">Active</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@include('admin.pages.products.partials.__additionalScript')

<style>
    .form-group label{
        color: black !important;
        font-weight: bold;
    }
</style>
