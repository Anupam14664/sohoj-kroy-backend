@extends('admin.layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">Add Product Cost</h3>

    <form action="{{ route('admin.product-costs.store') }}" method="POST">
        @csrf

        {{-- Product Search Box --}}
        <label class="fw-bold">Select Product*</label>
        <input type="text" id="productSearch" class="form-control"
               placeholder="Search by Product Name or SKU">

        {{-- Search Result Dropdown --}}
        <div id="productResults"
             class="list-group mt-2"
             style="max-height: 250px; overflow-y:auto;">
        </div>

        {{-- Hidden Values --}}
        <input type="hidden" name="product_id" id="product_id">
        <input type="hidden" name="product_buy_price" id="product_buy_price">

        <br>

        {{-- Cost Date --}}
        <label class="fw-bold">Cost Date*</label>
        <input type="date" name="cost_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        <br>

        {{-- Cost Type --}}
        <label class="fw-bold">Cost Type*</label>
        <select name="cost_type" class="form-control" required>
            <option value="" disabled selected>Select Cost Type</option>
            <option value="Marketing Cost">Marketing Cost</option>
            <option value="Others Cost">Others Cost</option>
        </select>

        <br>

        {{-- Amount --}}
        <label class="fw-bold">Amount*</label>
        <input type="number" name="amount" class="form-control" step="0.01" required>

        <br>

        {{-- Comment --}}
        <label class="fw-bold">Comment (Optional)</label>
        <textarea name="comment" class="form-control" rows="3"></textarea>

        <br>

        <button class="btn btn-primary w-100">Save Cost</button>

    </form>
</div>


<script>
    const products = @json($products);   // from controller
    const searchInput = document.getElementById('productSearch');
    const resultsBox = document.getElementById('productResults');
    const inputProductId = document.getElementById('product_id');
    const inputBuyPrice  = document.getElementById('product_buy_price');

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
                    ? `/storage/${p.main_image}`
                    : '/images/no-image.png';

                div.innerHTML = `
                    <img src="${img}"
                         style="width:45px; height:45px; object-fit:cover; border-radius:4px;">
                    <div>
                        <strong>${p.name}</strong><br>
                        <small class="text-muted">SKU: ${p.sku}</small><br>
                        <small class="text-muted">Buy: ${p.buy_price} ৳</small>
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

    // hide results when clicking outside
    document.addEventListener('click', (e) => {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.innerHTML = '';
        }
    });
</script>

@endsection
