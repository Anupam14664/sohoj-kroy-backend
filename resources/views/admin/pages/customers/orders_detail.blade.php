@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Orders of {{ $customerName }} ({{ $phone }})</h4>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm float-right">Back to Customers</a>
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
                                    <td>&#2547;{{ number_format($item->price, 2) }}</td>
                                    <td>&#2547;{{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                         <div class="mt-3">
                            <p><strong>Delivery Method:</strong> {{ $order->deliveryOption->name ?? 'N/A' }}</p>
                            <p><strong>Delivery Charge:</strong> &#2547;{{ number_format($order->delivery_charge ?? 0, 2) }}</p>
                            <p><strong>Discount:</strong> &#2547;{{ number_format($order->discount ?? 0, 2) }}</p>
                            <p><strong>Coupon Amount:</strong> &#2547;{{ number_format($order->coupon_amount ?? 0, 2) }}</p>
                            <hr>
                            <p class="text-right"><strong>Grand Total: &#2547;{{ number_format($order->total + ($order->delivery_charge ?? 0) - ($order->discount ?? 0) - ($order->coupon_amount ?? 0), 2) }}</strong></p>
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
