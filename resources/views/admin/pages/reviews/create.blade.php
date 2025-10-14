@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Review</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reviews.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Product Search Input -->
                        <div class="form-group">
                            <label for="product-search">Select Product</label>
                            <input type="text" id="product-search" class="form-control" placeholder="Search product by name, SKU..." autocomplete="off">
                            <input type="hidden" name="product_id" id="product-id">

                            <!-- Search Results -->
                            <div id="product-results" class="list-group mt-1" style="display:none; max-height: 150px; overflow-y: auto; border:1px solid #ccc; border-radius:4px;">

                            </div>
                        </div>

                        <!-- User Name -->
                        <div class="form-group">
                            <label for="name" class="col-md-3 col-form-label text-md-right">User Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                        </div>

                        <!-- Rating -->
                        <div class="form-group">
                            <label for="rating" class="col-md-3 col-form-label text-md-right">Rating</label>
                                <select class="form-control @error('rating') is-invalid @enderror" id="rating" name="rating" required>
                                    <option value="1">1 Star</option>
                                    <option value="2">2 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="5" selected>5 Stars</option>
                                </select>
                                @error('rating')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="col-md-3 col-form-label text-md-right">Review Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                        </div>

                        <!-- Approve Checkbox -->
                        <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_approved"
                                           name="is_approved" value="1"
                                           {{ old('is_approved', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_approved">
                                        Approve this review immediately
                                    </label>
                                </div>
                        </div>

                        <!-- Images -->
                        <div class="form-group">
                            <label for="images" class="col-md-3 col-form-label text-md-right">Review Images</label>
                                <input type="file" class="form-control-file @error('images.*') is-invalid @enderror"
                                       id="images" name="images[]" multiple accept="image/*">
                                <small class="form-text text-muted">
                                    You can upload up to 5 images
                                </small>
                                @error('images.*')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror

                                <!-- Preview Container -->
                                <div id="image-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>


                        <!-- Submit -->
                        <div class="form-group mb-0 justify-content-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Submit Review
                                </button>
                                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSearch = document.getElementById('product-search');
        const productResults = document.getElementById('product-results');
        const productIdField = document.getElementById('product-id');
        const preview = document.getElementById('selected-product-preview');
        const previewImg = document.getElementById('selected-product-image');
        const previewName = document.getElementById('selected-product-name');
        const previewSku = document.getElementById('selected-product-sku');

        let products = @json($products);

        productSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            productResults.innerHTML = '';
            if (term.length === 0) {
                productResults.style.display = 'none';
                return;
            }

            let filtered = products.filter(p =>
                p.name.toLowerCase().includes(term) ||
                (p.sku && p.sku.toLowerCase().includes(term))
            );

            if (filtered.length === 0) {
                productResults.innerHTML = '<div class="list-group-item text-muted">No products found</div>';
                productResults.style.display = 'block';
                return;
            }

            filtered.forEach(product => {
                let item = document.createElement('a');
                item.href = 'javascript:void(0)';
                item.classList.add('list-group-item', 'list-group-item-action');
                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div>
                            <strong>${product.name}</strong>
                            ${product.sku ? `<small class="d-block text-muted">SKU: ${product.sku}</small>` : ''}
                        </div>
                    </div>
                `;
                item.addEventListener('click', function() {
                    productSearch.value = product.name;
                    productIdField.value = product.id;

                    previewImg.src = product.image_url ?? '/images/no-image.png';
                    previewName.textContent = product.name;
                    previewSku.textContent = product.sku ?? '';
                    preview.style.display = 'block';

                    productResults.style.display = 'none';
                });

                productResults.appendChild(item);
            });

            productResults.style.display = 'block';
        });

        document.addEventListener('click', function(e) {
            if (!productResults.contains(e.target) && e.target !== productSearch) {
                productResults.style.display = 'none';
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imagesInput = document.getElementById('images');
        const previewContainer = document.getElementById('image-preview');

        imagesInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            const files = this.files;

            if (files.length > 5) {
                alert('You can upload up to 5 images only.');
                imagesInput.value = '';
                return;
            }

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '80px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    img.classList.add('border', 'rounded');
                    previewContainer.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });
    });
</script>
