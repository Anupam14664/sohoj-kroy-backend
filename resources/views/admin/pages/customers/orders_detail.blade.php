@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Orders of {{ $customer->name }} ({{ $phone }})</h4>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm float-right">Back to Customers</a>
        </div>

        <div>
            @if($customer)
            <div class="card mb-4 shadow-sm border-left-primary">
                <div class="card-body">
                    <h5 class="mb-3 text-primary">
                        <i class="fas fa-user"></i> Customer Information
                    </h5>

                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Name:</strong> {{ $customer->name }}</p>
                            <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                        </div>

                        <div class="col-md-4">
                            <p><strong>District:</strong> {{ $customer->district ?? 'N/A' }}</p>
                            <p><strong>Thana:</strong> {{ $customer->thana ?? 'N/A' }}</p>
                        </div>

                        <div class="col-md-4">
                            <p><strong>Address:</strong><br>
                                {{ $customer->address ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="card-body">
            @forelse($orders as $order)
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <strong>Order #{{ $order->order_number }}</strong>
                        <span class="float-right">{{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover table-head-bg-primary mt-4">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Variant</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($order->items->isNotEmpty() && $order->items[0]->product && $order->items[0]->product->main_image)
                                                <img src="{{ asset('storage/'.$order->items[0]->product->main_image) }}"
                                                    alt="{{ $order->items[0]->product->name }}"
                                                    width="50"
                                                    class="img-thumbnail">
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                         {{ $item->product->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        @if($item->variant_option_id)
                                            Color: {{ $item->color_name ?? 'N/A' }}<br>
                                            Size: {{ $item->size_name ?? 'N/A' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>&#2547;{{ number_format($item->price, 0) }}</td>
                                    <td>&#2547;{{ number_format($item->price * $item->quantity, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                         <div class="mt-3">
                            <p><strong>Delivery Method:</strong> {{ $order->deliveryOption->name ?? 'N/A' }}</p>
                            <p><strong>Delivery Charge:</strong> &#2547;{{ number_format($order->delivery_charge ?? 0, 0) }}</p>
                            <p><strong>Discount:</strong> &#2547;{{ number_format($order->discount ?? 0, 0) }}</p>
                            <p><strong>Coupon Amount:</strong> &#2547;{{ number_format($order->coupon_amount ?? 0, 0) }}</p>
                            <hr>
                            <p class="text-right"><strong>Grand Total: &#2547;{{ number_format($order->total + ($order->delivery_charge ?? 0) - ($order->discount ?? 0) - ($order->coupon_amount ?? 0), 0) }}</strong></p>


                            @php
                                $trackingLink = null;

                                if ($order->courier && $order->tracking_code) {

                                    switch ($order->courier->type) {

                                        case 'steadfast':
                                            $trackingLink = 'https://steadfast.com.bd/t/' . $order->tracking_code;
                                            break;

                                        case 'pathao':
                                            $trackingLink = 'https://merchant.pathao.com/tracking?tracking_number=' . $order->tracking_code;
                                            break;
                                    }
                                }

                                if (!$trackingLink && !empty($order->custom_link)) {
                                    $trackingLink = $order->custom_link;
                                }
                            @endphp

                            @if($trackingLink)
                                <p>
                                    <strong>Courier Tracking:</strong>

                                    <a href="{{ $trackingLink }}" target="_blank">
                                        {{ $trackingLink }}
                                    </a>

                                    <button
                                        type="button"
                                        class="btn btn-link btn-sm p-0 ms-2"
                                        onclick="copyTrackingLink('{{ $trackingLink }}', this)"
                                        title="Copy Link">
                                        <i class="far fa-copy"></i>
                                    </button>

                                    <span class="copy-msg text-success ms-2" style="display:none;">
                                        Copied!
                                    </span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center">No delivered orders found for this customer.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection


<script>
function copyTrackingLink(link, button) {
    navigator.clipboard.writeText(link).then(function () {
        const msg = button.parentElement.querySelector('.copy-msg');
        msg.style.display = 'inline';

        setTimeout(() => {
            msg.style.display = 'none';
        }, 2000);
    });
}
</script>
