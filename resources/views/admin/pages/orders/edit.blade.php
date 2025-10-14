<!-- resources/views/admin/orders/show.blade.php -->

@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order #{{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
    </div>
    @include('admin.layouts.partials.__alerts')

    <div class="row">
        <div class="col-md-8">
            <form id="form-order-items" style="width: 100% !important" action="{{ route('admin.orders.update.items', $order->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card mb-12">
                    <div class="card-header"><h5>Order Items</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-head-bg-primary mt-4" id="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Product</th>
                                        <th>SKU / Variant SKU</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Size</th>
                                        <th>Color</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $index => $item)
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                @php
                                                    $img = $item->variantOption?->image ?? $item->product->main_image ?? null;
                                                @endphp
                                                @if($img)
                                                    <img src="{{ asset('storage/'.$img) }}" width="50" class="img-thumbnail" alt="{{ $item->product_name }}">
                                                @else
                                                    <span class="text-muted">No image</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->product_name }}
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                @if($item->variant_option_id)
                                                    <input type="hidden" name="items[{{ $index }}][variant_option_id]" value="{{ $item->variant_option_id }}">
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $sku = $item->product->sku ?? '';
                                                    if($item->variantOption){
                                                        $sku .= ' / '.$item->variantOption->sku;
                                                    }
                                                @endphp
                                                {{ $sku }}
                                            </td>
                                            <td>&#2547;{{ number_format($item->price,2) }}</td>
                                            <td><input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" class="form-control form-control-sm" style="width:60px;"></td>
                                            <td>
                                                {{ $item->variantOption?->size?->name ?? $item->product->size?->name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $item->variantOption?->variant?->color?->name ?? '-' }}
                                            </td>
                                            <td>&#2547;{{ number_format($item->price * $item->quantity,2) }}</td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        <hr>
                        <h6>Add Product by Name / SKU / Variant SKU:</h6>
                        <div class="input-group mb-3 position-relative">
                            <input type="text" id="search-product" class="form-control" placeholder="Type name, SKU or Variant SKU" autocomplete="off">
                            <input type="number" id="search-quantity" class="form-control" placeholder="Qty" value="1" style="max-width:100px;">
                            <div id="sku-suggestions" class="position-absolute bg-white border" style="z-index:1000;width:100%;display:none;"></div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Order Items</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Subtotal:</strong> &#2547;{{ number_format($order->subtotal, 2) }}
                    </div>

                    @if($order->discount > 0)
                    <div class="mb-3">
                        <strong>Discount:</strong> -&#2547;{{ number_format($order->discount, 2) }}
                        @if($order->coupon)
                            <br><small>Coupon: {{ $order->coupon->code }}</small>
                        @endif
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong>Delivery Method:</strong>
                        {{ optional($order->deliveryOption)->name ?? 'Not Set' }}
                    </div>


                    <form style="background: none; border: none; margin:0;" action="{{ route('admin.orders.update.delivery', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Delivery Method --}}
                        <div class="delivery mb-3">
                            <label for="delivery_option_id"><strong>Delivery Method:</strong></label>
                            <select name="delivery_option_id" id="delivery_option_id" class="form-control w-50 d-inline-block mx-1" required>
                                @foreach($deliveryOptions as $option)
                                    <option value="{{ $option->id }}"
                                        data-charge="{{ $option->charge }}"
                                        {{ $order->delivery_option_id == $option->id ? 'selected' : '' }}>
                                        {{ $option->name }} - {{ number_format($option->charge, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Delivery Charge --}}
                        <div class="delivery mb-3">
                            <label for="delivery_charge"><strong>Delivery Charge:</strong></label>
                            <input type="number" name="delivery_charge" id="delivery_charge"
                                   value="{{ $order->delivery_charge }}" step="0.01"
                                   class="form-control w-50 d-inline-block mx-1" style="max-width: 100px;" required>
                        </div>

                        {{-- Discount Field --}}
                        <div class="admin-discount mb-3">
                            <label for="admin_discount" class="ms-3"><strong>Discount:</strong></label>
                            <input type="number" name="admin_discount" id="admin_discount"
                                   value="{{ $order->admin_discount ?? 0 }}" step="0.01"
                                   class="form-control w-50 d-inline-block mx-1" style="max-width: 100px;" required>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    </form>

                    <div class="mb-3">
                        <strong>Total:</strong> &#2547;{{ number_format($order->total, 2) }}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Customer Details</h5>
                </div>
                <form style="width: 100% !important; background: none; border: none; margin:0;" action="{{ route('admin.orders.update', $order) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-2">
                        <label for="name"><strong>Name</strong></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $order->name) }}" required>
                    </div>

                    <div class="form-group mb-2">
                        <label for="phone"><strong>Phone</strong></label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $order->phone) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="address"><strong>Address</strong></label>
                        <textarea name="address" id="address" class="form-control" rows="2" required>{{ old('address', $order->address) }}</textarea>
                    </div>
                    @php
                    $districts = config('bd_location');
                    $selDist = old('district', $order->district);
                    $selThana = old('thana', $order->thana);
                    @endphp

                    <div class="form-group mb-3">
                      <label><strong>District</strong></label>
                      <select id="district" name="district" class="form-control" required>
                        <option value="">Select District</option>
                        @foreach($districts as $d => $thanas)
                          <option value="{{ $d }}" {{ $selDist == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group mb-3">
                      <label><strong>Thana</strong></label>
                      <select id="thana" name="thana" class="form-control" required>
                        <option value="">Select Thana</option>
                      </select>
                    </div>

                     <div class="form-group mb-3">
                        <label for="address"><strong>Admin Comment</strong></label>
                        <textarea name="admin_comment" id="admin_comment" class="form-control" rows="2">{{ old('admin_comment', $order->admin_comment) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary">Update Customer</button>
                </form>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Update Order Status</h5>
                </div>
                <div class="card-body">
                    <form id="order-status-form" action="{{ route('admin.orders.update-status', $order) }}" method="POST" style="width: auto;">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="status">Order Status</label>
                            @php
                                $hasIncomplete = \App\Models\Order::where('status', 'incomplete')->exists();
                            @endphp
                            <select name="status" id="status" class="form-control">
                                @if($hasIncomplete)
                                <option id="incompleteOption" value="incomplete" {{ $order->status == 'incomplete' ? 'selected' : '' }}>Incomplete</option>
                                @endif
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="hold" {{ $order->status == 'hold' ? 'selected' : '' }}>Hold</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Order Confirmed</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}
                                    {{ $order->status == 'shipped' ? 'disabled' : '' }}>Ready to Shipped</option>
                                <option value="courier_delivered" {{ $order->status == 'courier_delivered' ? 'selected' : '' }}>Courier Delivered</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>


                        <!-- Courier Fields -->
                        <div class="courier-field" style="display: {{ $order->status == 'shipped' ? 'block' : 'none' }};">
                            <div class="form-group">
                                <label for="courier_service_id">Select Courier</label>
                                <select name="courier_service_id" id="courier_service_id" class="form-control"
                                    {{ $order->status == 'shipped' ? 'disabled' : 'required' }}>
                                    <option value="">Select Courier</option>
                                    @foreach($couriers as $courier)
                                        <option value="{{ $courier->id }}" {{ $order->courier_service_id == $courier->id ? 'selected' : '' }}>
                                            {{ $courier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="delivery_note">Delivery Note</label>
                                <textarea name="delivery_note" id="delivery_note" class="form-control" rows="2"
                                    {{ $order->status == 'shipped' ? 'readonly' : '' }}>{{ $order->courier_note }}</textarea>
                            </div>
                        </div>
                        @if($order->status === 'courier_delivered' && !$order->courier_service_id)
                        <div class="form-group">
                            <label for="custom_link" class="form-label">Custom Link</label>
                            <input type="url" name="custom_link" id="custom_link" class="form-control"
                                value="{{ old('custom_link', $order->custom_link ?? '') }}"
                                placeholder="https://example.com">
                        </div>
                    @elseif($order->status === 'courier_delivered' && $order->courier_service_id)
                        <div class="form-group" hidden>
                            <label class="form-label">Custom Link</label>
                            <input type="url" class="form-control" value="{{ $order->custom_link }}" readonly>
                        </div>
                        @elseif($order->status === 'delivered' && $order->courier_service_id)
                        <div class="form-group" hidden>
                            <label class="form-label">Custom Link</label>
                            <input type="url" class="form-control" value="{{ $order->custom_link }}" readonly>
                        </div>
                    @elseif($order->status === 'delivered')
                        <div class="form-group">
                            <label class="form-label">Custom Link</label>
                            <input type="url" class="form-control" value="{{ $order->custom_link }}" readonly>
                        </div>
                    @endif


                        <div class="form-group">
                            <label for="comment">Comment (Optional)</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3">{{ old('comment', $order->comment) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>

                    @if($order->courier_service_id && in_array($order->status, ['shipped', 'courier_delivered', 'delivered']))
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6>Courier Information</h6>
                            <p><strong>Courier:</strong> {{ $order->courier->name ?? 'N/A' }}</p>
                            <p><strong>Tracking Code:</strong> {{ $order->tracking_code }}</p>
                            <p><strong>Consignment ID:</strong> {{ $order->consignment_id ?? 'N/A' }}</p>
                            <a href="https://steadfast.com.bd/t/{{ $order->tracking_code }}"
                            target="_blank"
                            class="btn btn-sm btn-info">
                            Track Order
                            </a>
                        </div>
                    @endif

                </div>
            </div>


        </div>
    </div>
</div>
@endsection



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  const bdLocations = @json(config('bd_location'));
  const selDist = "{{ $selDist }}";
  const selThana = "{{ $selThana }}";

  function loadThanas(district) {
    const list = bdLocations[district] || [];
    const $thana = $('#thana').empty().append('<option value="">Select Thana</option>');
    list.forEach(th => {
      const selected = th === selThana ? 'selected' : '';
      $thana.append(`<option value="${th}" ${selected}>${th}</option>`);
    });
  }

  $(function() {
    $('#district').on('change', function() {
      loadThanas(this.value);
    });

    if (selDist) {
      loadThanas(selDist);
    }
  });
</script>

<script>
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            const row = e.target.closest('tr');
            const idInput = row.querySelector('input[name*="[id]"]');

            if (idInput) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'removed_ids[]';
                hiddenInput.value = idInput.value;
                document.getElementById('form-order-items').appendChild(hiddenInput);
            }

            row.remove();
        }
    });

</script>

<script>
    $(document).ready(function () {
        const suggestionBox = $('#sku-suggestions');

        $('#new_sku_input').on('input', function () {
            const query = $(this).val();

            if (query.length < 2) {
                suggestionBox.hide();
                return;
            }

            $.get("{{ route('admin.orders.sku-search') }}", { query: query }, function (data) {
                suggestionBox.empty().show();
                data.forEach(item => {
                    suggestionBox.append(`
                        <a class="list-group-item list-group-item-action"
                        data-type="${item.type}"
                        data-id="${item.id}"
                        data-sku="${item.sku}"
                        data-name="${item.name}"
                        data-price="${item.price}"
                        data-product-id="${item.product_id ?? item.id}"
                        data-size="${item.size ?? ''}"
                        data-color="${item.color ?? ''}">
                            ${item.sku} - ${item.name}
                        </a>
                    `);
                });

                if (suggestionBox.children().length === 0) {
                    suggestionBox.hide();
                }
            });
        });

        suggestionBox.on('click', '.list-group-item', function () {
            const index = $('#order-items-table tbody tr').length;
            const type = $(this).data('type');
            const productId = $(this).data('product-id');
            const name = $(this).data('name');
            const price = parseFloat($(this).data('price')).toFixed(2);
            const size = $(this).data('size') || '-';
            const color = $(this).data('color') || '-';
            const qty = $('#search-quantity').val() || 1;

            $('#order-items-table tbody').append(`
                <tr>
                    <td>
                        ${name}
                        <input type="hidden" name="items[${index}][product_id]" value="${productId}">
                        <input type="hidden" name="items[${index}][product_name]" value="${name}">
                        <input type="hidden" name="items[${index}][price]" value="${price}">
                        <input type="hidden" name="items[${index}][size]" value="${size !== '-' ? size : ''}">
                        <input type="hidden" name="items[${index}][color]" value="${color !== '-' ? color : ''}">
                    </td>
                    <td>&#2547;${price}</td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" value="${qty}" class="form-control form-control-sm" style="width:60px;">
                    </td>
                    <td>${size}</td>
                    <td>${color}</td>
                    <td>&#2547;${(price * qty).toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                    </td>
                </tr>
            `);

            suggestionBox.hide();
            $('#search-product').val('');
            $('#search-quantity').val(1);
        });
    });

</script>


<style>
    .color-badge {
    display: inline-block;
    width: 15px;
    height: 15px;
    border-radius: 3px;
    vertical-align: middle;
    border: 1px solid #ddd;
}

#sku-suggestions {
    position: absolute;
    z-index: 1000;
    width: 100%;
    max-width: 500px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: none;
}

.list-group-item {
    cursor: pointer;
}
</style>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect = document.getElementById('status');
        const courierField = document.querySelector('.courier-field');
        const courierSelect = document.getElementById('courier_service_id');
        const form = document.getElementById('order-status-form');
        const isAlreadyShipped = {{ $order->status === 'shipped' ? 'true' : 'false' }};

        // Define allowed status transitions based on current status
        const allowedTransitions = {
            'incomplete': ['pending', 'hold','processing', 'cancelled'],
            'pending': ['hold', 'processing', 'cancelled'],
            'hold': ['processing', 'cancelled'],
            'processing': ['shipped', 'courier_delivered', 'cancelled'],
            'shipped': ['courier_delivered', 'cancelled'],
            'courier_delivered': ['delivered'],
            'delivered': [],
            'cancelled': []
        };

        // Current order status
        const currentStatus = '{{ $order->status }}';

        // Disable invalid options
        function updateStatusOptions() {
            Array.from(statusSelect.options).forEach(option => {
                option.disabled = !allowedTransitions[currentStatus].includes(option.value);
            });

            statusSelect.value = currentStatus;
        }

        function toggleCourierField() {
            if (statusSelect.value === 'shipped' && !isAlreadyShipped) {
                courierField.style.display = 'block';
                courierSelect.required = true;
            } else {
                courierField.style.display = 'none';
                courierSelect.required = false;
                courierSelect.value = "";
            }
        }

        updateStatusOptions();
        toggleCourierField();

        statusSelect.addEventListener('change', function() {
            if (isAlreadyShipped && statusSelect.value === 'shipped') {
                alert('This order has already been shipped and cannot be shipped again.');
                statusSelect.value = currentStatus;
            }
            toggleCourierField();
        });

        form.addEventListener('submit', function (e) {
            if (statusSelect.value === 'shipped' && !courierSelect.value && !isAlreadyShipped) {
                e.preventDefault();
                alert("Please select a courier service before marking as 'Shipped'.");
                courierSelect.focus();
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deliveryOptionSelect = document.getElementById('delivery_option_id');
        const deliveryChargeInput = document.getElementById('delivery_charge');

        deliveryOptionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const charge = selectedOption.getAttribute('data-charge');
            deliveryChargeInput.value = charge;
        });
    });
    </script>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusDropdown = document.getElementById('status');
        const incompleteOption = document.getElementById('incompleteOption');

        if(incompleteOption) {
            if(statusDropdown.value !== 'incomplete') {
                incompleteOption.style.display = 'none';
            }

            statusDropdown.addEventListener('change', function() {
                if(statusDropdown.value !== 'incomplete') {
                    incompleteOption.style.display = 'none';
                }
            });
        }
    });
    </script>


<script>
    $(document).ready(function(){
        let selectedProducts = {};

        function renderRow(product, variant = null, quantity = 1){
            let id = variant ? 'v'+variant.id : 'p'+product.id;
            if(selectedProducts[id]) return;
            selectedProducts[id] = true;

            let img = variant?.image ?? product.main_image ?? '';
            let sku = variant?.sku ?? product.sku ?? '';
            let name = product.name;
            let price = variant?.price ?? (product.discount_price ?? product.regular_price);
            let index = $('#order-items-table tbody tr').length;

            let row = `
                <tr data-item-id="">
                    <td>${img ? `<img src="/storage/${img}" width="50" class="img-thumbnail">` : '<span class="text-muted">No image</span>'}</td>
                    <td>
                        ${name}
                        <input type="hidden" name="items[${index}][product_id]" value="${product.id}">
                        ${variant ? `<input type="hidden" name="items[${index}][variant_option_id]" value="${variant.id}">` : ''}
                    </td>
                    <td>${product.sku}${variant ? ' / '+variant.sku : ''}</td>
                    <td>&#2547;${price.toFixed(2)}</td>
                    <td><input type="number" name="items[${index}][quantity]" value="${quantity}" class="form-control form-control-sm" style="width:60px;"></td>
                    <td>&#2547;${(price*quantity).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
            $('#order-items-table tbody').append(row);
        }

        // Remove row
        $(document).on('click','.remove-item',function(){
            $(this).closest('tr').remove();
        });

        // Client-side search without extra route
        let allProducts = @json(\App\Models\Product::with('variants.options')->get());
        let allVariants = @json(\App\Models\ProductVariantOption::with('variant.product')->get());

        $('#search-product').on('input',function(){
            let query = $(this).val().toLowerCase();
            if(query.length < 1){
                $('#sku-suggestions').hide();
                return;
            }

            let matches = [];

            allProducts.forEach(p=>{
                if(p.name.toLowerCase().includes(query) || p.sku.toLowerCase().includes(query)){
                    matches.push({product:p, variant:null});
                }
            });

            allVariants.forEach(v=>{
                let prodName = v.variant.product.name.toLowerCase();
                if(prodName.includes(query) || v.sku.toLowerCase().includes(query) || v.variant.product.sku.toLowerCase().includes(query)){
                    matches.push({product:v.variant.product, variant:v});
                }
            });

            let html = '';
            matches.forEach(m=>{
                let image = m.variant?.image ?? m.product.main_image ?? '';
                let sku = m.variant?.sku ?? m.product.sku ?? '';
                html += `<div class="p-2 border-bottom suggestion-item" data-product='${JSON.stringify(m.product)}' data-variant='${JSON.stringify(m.variant ?? null)}'>
                            <img src="/storage/${image}" width="40" class="me-2">${m.product.name} <small>(${sku})</small>
                        </div>`;
            });

            $('#sku-suggestions').html(html).show();
        });

        // Click suggestion to add
        $(document).on('click','.suggestion-item',function(){
            let product = JSON.parse($(this).attr('data-product'));
            let variant = $(this).attr('data-variant') ? JSON.parse($(this).attr('data-variant')) : null;
            let qty = parseInt($('#search-quantity').val() || 1);
            renderRow(product, variant, qty);
            $('#sku-suggestions').hide();
            $('#search-product').val('');
        });

        $(document).click(function(e){
            if(!$(e.target).closest('#sku-suggestions,#search-product').length){
                $('#sku-suggestions').hide();
            }
        });
    });
</script>
