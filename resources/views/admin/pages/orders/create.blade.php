@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h2>Create New Order</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
        @csrf

        <div class="row mb-3">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

                        @php
            $districts = config('bd_location');
            @endphp

            <div class="form-group mt-3">
                <label><strong>District</strong></label>
                <select id="district" name="district" class="form-control" required>
                    <option value="">Select District</option>
                    @foreach($districts as $d => $thanas)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mt-3">
                <label><strong>Thana</strong></label>
                <select id="thana" name="thana" class="form-control" required>
                    <option value="">Select Thana</option>
                </select>
            </div>

            <div class="form-group mt-3">
                <label>Area / Address</label>
                <textarea name="address" class="form-control"></textarea>
            </div>

            <div class="form-group mt-3">
                <label>Delivery Method</label>
                <select name="delivery_option_id" class="form-control" required>
                    <option value="">-- Select --</option>
                    @foreach($deliveryOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->name }} (৳{{ $option->charge }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr>
        <h5>Search & Add Products</h5>
        <div class="mb-3">

        <input type="text" id="productInput" class="form-control" placeholder="Search by SKU or Name" autocomplete="off">
        <div id="searchResults" class="border mt-1 p-2" style="max-height: 200px; overflow-y: auto;"></div>

        <div id="selectedProducts" class="mt-3"></div>

        <button type="submit" class="btn btn-success mt-3">Create Order</button>
    </form>
</div>
@endsection


{{-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('productInput');
        const results = document.getElementById('searchResults');
        const selected = document.getElementById('selectedProducts');
        let index = 0;

        input.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 2) {
                results.innerHTML = '';
                return;
            }

            fetch(`/admin/product/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(products => {
                if (products.length === 0) {
                    results.innerHTML = '<p class="text-muted">No products found.</p>';
                    return;
                }

                let html = '';
                products.forEach(product => {
                    const price = product.discount_price ?? product.regular_price;
                    html += `<div class="p-1 border mb-1" style="cursor:pointer;" onclick="addProduct(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${price}, '${product.sku}')">
                                <strong>${product.name}</strong> - ${product.sku} (৳${price})
                            </div>`;
                });
                results.innerHTML = html;
            })

            .catch(e => {
                console.error(e);
                results.innerHTML = '<p class="text-danger">Error fetching products.</p>';
            });
        });

        window.addProduct = function(id, name, price, sku) {
            const productHtml = `
                <div class="border p-2 mb-2 d-flex align-items-center">
                    <input type="hidden" name="products[${index}][product_id]" value="${id}">
                    <div class="flex-grow-1">
                        <strong>${name}</strong><br><small>SKU: ${sku}</small>
                    </div>
                    <div class="me-2">
                        <input type="number" name="products[${index}][quantity]" class="form-control" value="1" min="1" required style="width:80px;">
                    </div>
                    <div>
                        <input type="number" name="products[${index}][price]" class="form-control" value="${price}" readonly style="width:100px;">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm ms-2" onclick="this.parentElement.remove()">Remove</button>
                </div>
            `;
            selected.insertAdjacentHTML('beforeend', productHtml);
            index++;
            results.innerHTML = '';
            input.value = '';
        };

    });
</script> --}}



<script>
document.addEventListener("DOMContentLoaded", function () {

    const input    = document.getElementById('productInput');
    const results  = document.getElementById('searchResults');
    const selected = document.getElementById('selectedProducts');
    let index = 0;

    // 🔍 SEARCH
    input.addEventListener('input', function () {

        let q = this.value.trim();
        if (q.length < 2) {
            results.innerHTML = '';
            return;
        }

        fetch(`/admin/product/search?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(products => {

                if (!products.length) {
                    results.innerHTML = '<div class="text-muted">No products found</div>';
                    return;
                }

                let html = '';

                products.forEach(p => {

                    let price = p.discount_price ?? p.regular_price ?? p.price ?? 0;
                    let img   = p.main_image
                        ? `/storage/${p.main_image}`
                        : 'https://via.placeholder.com/40x40?text=N/A';

                    html += `
                    <div class="border p-2 mb-1 d-flex align-items-center"
                         style="cursor:pointer"
                         onclick="addProduct(
                             ${p.id},
                             '${p.name.replace(/'/g, "\\'")}',
                             '${p.sku}',
                             ${price},
                             '${p.main_image ?? ''}',
                             ${p.has_variants ? 1 : 0}
                         )">

                        <img src="${img}" width="40" height="40"
                             style="object-fit:cover;border-radius:4px;margin-right:10px"
                             onerror="this.src='https://via.placeholder.com/40x40?text=N/A'">

                        <div>
                            <strong>${p.name}</strong><br>
                            <small>${p.sku}</small> - ৳${price}
                        </div>
                    </div>`;
                });

                results.innerHTML = html;
            });
    });

    // ➕ ADD PRODUCT
    window.addProduct = function (id, name, sku, price, image, hasVariants) {

        if (hasVariants) {
            fetch(`/admin/product/${id}/variants`)
                .then(res => res.json())
                .then(variants => {

                    if (!variants.length) return;

                    let options = variants.map(v =>
                        `<option value="${v.id}"
                                 data-price="${v.price}"
                                 data-image="${v.image ?? image}">
                            ${v.size ?? ''} ${v.color ?? ''} (৳${v.price})
                        </option>`
                    ).join('');

                    let img = variants[0].image || image;

                    selected.insertAdjacentHTML('beforeend', `
                    <div class="border p-2 mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <img src="/storage/${img}" width="40"
                                 onerror="this.src='https://via.placeholder.com/40x40?text=N/A'"
                                 style="margin-right:10px">

                            <strong>${name}</strong>
                        </div>

                        <input type="hidden" name="products[${index}][product_id]" value="${id}">

                        <select name="products[${index}][variant_option_id]"
                                class="form-select variant-select mb-2"
                                data-index="${index}">
                            ${options}
                        </select>

                        <div class="d-flex">
                            <input type="number"
                                   name="products[${index}][quantity]"
                                   value="1"
                                   class="form-control me-2"
                                   style="width:70px">

                            <input type="number"
                                   name="products[${index}][price]"
                                   value="${variants[0].price}"
                                   class="form-control"
                                   readonly
                                   style="width:100px">

                            <button type="button"
                                    class="btn btn-danger btn-sm ms-2"
                                    onclick="this.closest('.border').remove()">
                                🗑
                            </button>
                        </div>
                    </div>`);

                    index++;
                });

        } else {

            let img = image
                ? `/storage/${image}`
                : 'https://via.placeholder.com/40x40?text=N/A';

            selected.insertAdjacentHTML('beforeend', `
            <div class="border p-2 mb-2 d-flex align-items-center">

                <img src="${img}" width="40"
                     onerror="this.src='https://via.placeholder.com/40x40?text=N/A'"
                     style="margin-right:10px">

                <input type="hidden"
                       name="products[${index}][product_id]"
                       value="${id}">

                <div class="flex-grow-1">
                    <strong>${name}</strong><br>
                    <small>${sku}</small>
                </div>

                <input type="number"
                       name="products[${index}][quantity]"
                       value="1"
                       class="form-control me-2"
                       style="width:70px">

                <input type="number"
                       name="products[${index}][price]"
                       value="${price}"
                       class="form-control"
                       readonly
                       style="width:90px">

                <button type="button"
                        class="btn btn-danger btn-sm ms-2"
                        onclick="this.parentElement.remove()">🗑</button>
            </div>`);

            index++;
        }

        results.innerHTML = '';
        input.value = '';
    };

    // 🔄 VARIANT CHANGE
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('variant-select')) {

            let price = e.target.selectedOptions[0].dataset.price;
            let img   = e.target.selectedOptions[0].dataset.image;
            let box   = e.target.closest('.border');

            box.querySelector('input[name$="[price]"]').value = price;
            box.querySelector('img').src = `/storage/${img}`;
        }
    });

});
</script>



{{-- <script>
    const bdLocations = @json(config('bd_location'));

    function loadThanas(district) {
        const list = bdLocations[district] || [];
        const $thana = $('#thana').empty().append('<option value="">Select Thana</option>');
        list.forEach(th => {
            $thana.append(`<option value="${th}">${th}</option>`);
        });
    }

    $(function () {
        $('#district').on('change', function () {
            loadThanas(this.value);
        });
    });
</script> --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const bdLocations = @json(config('bd_location'));

    function loadThanas(district) {
        const list = bdLocations[district] || [];
        const $thana = $('#thana');
        $thana.empty().append('<option value="">Select Thana</option>');

        list.forEach(function (thana) {
            $thana.append(`<option value="${thana}">${thana}</option>`);
        });
    }

    $(document).ready(function () {
        $('#district').on('change', function () {
            const selectedDistrict = $(this).val();
            loadThanas(selectedDistrict);
        });
    });
</script>
