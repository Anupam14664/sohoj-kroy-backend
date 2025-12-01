@extends('admin.layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">Edit Product Cost</h3>

    @include('admin.layouts.partials.__alerts')

    <form action="{{ route('admin.product-costs.update', $cost->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Current Product Info --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6>Currently Selected Product:</h6>
                <div class="d-flex align-items-center gap-3">
                    @if($cost->product->main_image)
                        <img src="{{ filter_var($cost->product->main_image, FILTER_VALIDATE_URL) ? $cost->product->main_image : asset('storage/' . $cost->product->main_image) }}"
                             style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                    @endif
                    <div>
                        <strong>{{ $cost->product->name }}</strong><br>
                        <small class="text-muted">SKU: {{ $cost->product->sku }}</small><br>
                        <small class="text-muted">Current Buy Price: {{ number_format($cost->product->buy_price, 2) }}৳</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Search Box (For changing product) --}}
        <label class="fw-bold">Change Product (Optional)</label>
        <input type="text" id="productSearch" class="form-control"
               placeholder="Search by Product Name or SKU to change product">

        {{-- Search Result Dropdown --}}
        <div id="productResults"
             class="list-group mt-2"
             style="max-height: 250px; overflow-y:auto;">
        </div>

        {{-- Hidden Values --}}
        <input type="hidden" name="product_id" id="product_id" value="{{ $cost->product_id }}">
        <input type="hidden" name="product_buy_price" id="product_buy_price" value="{{ $cost->product_buy_price }}">

        <br>
        {{-- Cost Date --}}
        <label class="fw-bold">Cost Date*</label>
        <input type="date" name="cost_date" class="form-control"
            value="{{ old('cost_date', $cost->cost_date) }}" required>
        <br>


        {{-- Cost Type --}}
        <label class="fw-bold">Cost Type*</label>
        <select name="cost_type" class="form-control" required>
            <option value="" disabled>Select Cost Type</option>
            <option value="Marketing Cost" {{ $cost->cost_type == 'Marketing Cost' ? 'selected' : '' }}>Marketing Cost</option>
            <option value="Ads Cost" {{ $cost->cost_type == 'Ads Cost' ? 'selected' : '' }}>Ads Cost</option>
            <option value="Others Cost" {{ $cost->cost_type == 'Others Cost' ? 'selected' : '' }}>Others Cost</option>
        </select>

        <br>

        {{-- Amount --}}
        <label class="fw-bold">Amount*</label>
        <input type="number" name="amount" class="form-control" step="0.01"
               value="{{ old('amount', $cost->amount) }}" required>

        <br>

        {{-- Comment --}}
        <label class="fw-bold">Comment (Optional)</label>
        <textarea name="comment" class="form-control" rows="3">{{ old('comment', $cost->comment) }}</textarea>

        <br>

        <br>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
                <i class="fas fa-save"></i> Update Cost
            </button>
            <a href="{{ route('admin.product-costs.show', $cost->product_id) }}"
               class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>

    </form>

    {{-- Delete Form --}}
    <form id="deleteForm-{{ $cost->id }}"
          action="{{ route('admin.product-costs.destroy', $cost->id) }}"
          method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>


<script>
    const products = @json($products);
    const searchInput = document.getElementById('productSearch');
    const resultsBox = document.getElementById('productResults');
    const inputProductId = document.getElementById('product_id');
    const inputBuyPrice  = document.getElementById('product_buy_price');

    // Function to show product search results
    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        resultsBox.innerHTML = '';

        if (!q) return;

        products
            .filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.sku.toLowerCase().includes(q)
            )
            .forEach(p => {

                const div = document.createElement('div');
                div.className = 'list-group-item list-group-item-action d-flex gap-2 align-items-center';

                const img = p.main_image
                    ? (p.main_image.startsWith('http') ? p.main_image : `/storage/${p.main_image}`)
                    : '/images/no-image.png';

                div.innerHTML = `
                    <img src="${img}"
                         style="width:45px; height:45px; object-fit:cover; border-radius:4px;">
                    <div>
                        <strong>${p.name}</strong><br>
                        <small class="text-muted">SKU: ${p.sku}</small><br>
                        <small class="text-muted">Buy Price: ${p.buy_price}৳</small>
                    </div>
                `;

                div.addEventListener('click', () => {
                    searchInput.value = `${p.name} (SKU: ${p.sku})`;
                    inputProductId.value = p.id;
                    inputBuyPrice.value  = p.buy_price;
                    resultsBox.innerHTML = '';
                });

                resultsBox.appendChild(div);
            });
    });

    // Hide results when clicking outside
    document.addEventListener('click', (e) => {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.innerHTML = '';
        }
    });

    // Confirm delete function
    function confirmDelete(costId) {
        if (confirm('Are you sure you want to delete this cost entry? This action cannot be undone.')) {
            document.getElementById('deleteForm-' + costId).submit();
        }
    }

    // Pre-fill search box if coming from show page with product info
    document.addEventListener('DOMContentLoaded', function() {
        const currentProduct = @json($cost->product);
        if (currentProduct) {
            document.getElementById('productSearch').value = `${currentProduct.name} (SKU: ${currentProduct.sku})`;
        }
    });
</script>

@endsection
