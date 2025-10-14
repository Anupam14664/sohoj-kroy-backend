<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Export Data</title>
<style>
    body {
        font-family: solaimanlipi, sans-serif;
        font-size: 10pt;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .header img {
        max-height: 180px;
        margin-bottom: 10px;
        justify-content:center;
    }

    .header h2 {
        margin: 0;
        font-size: 18px;
    }

    /* .order {
        border: 1px solid #000;
        margin-bottom: 15px;
        padding: 8px;
        border-radius: 6px;
    } */

    .order-header {
        background: #f0f0f0;
        font-weight: bold;
        padding: 6px;
        border-radius: 5px;
        justify-content: center;
        text-align: center;
        font-size: 14px;
    }

    /* Table based product card */
    .product-card {
        font-size: 16px;
        width: 100%;
        border: 1px solid #ddd;
        margin-top: 8px;
        border-radius: 6px;
        background-color: #fafafa;
        border-collapse: collapse;
    }

    .product-card td {
        padding: 8px;
        vertical-align: top;
    }

    .product-details p {
        margin: 4px 0;
        font-size: 11px;
    }

    .product-details p span {
        font-weight: bold;
        color: #000;
    }

    .product-image {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .variant-color {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 1px solid #000;
        margin-left: 5px;
    }

    .admin-comment {
        background-color: #ff6464;
        padding: 3px 6px;
        display: inline-block;
        border-radius: 4px;
        font-weight: bold;
        font-size: 16px;
        color: #000;
    }

    hr {
        margin: 10px 0;
        border: 0;
        height: 1px;
        background-color: #ddd;
    }

    /* Print Optimization */
    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .order {
            page-break-inside: avoid;
        }

        .product-card {
            page-break-inside: avoid;
        }
    }
</style>
</head>
<body>

@php
    $settings = \App\Models\GeneralSetting::first();
@endphp

<div class="header">
    @if($settings && $settings->logo)
        <img src="{{ public_path('storage/'.$settings->logo) }}" alt="Logo" style="width: 180px;">
    @else
        <h2>{{ $settings->app_name ?? 'Orders Report' }}</h2>
    @endif
    <p style="margin: 5px 0 0; font-size: 12px; color: #555;">
        Generated Date: {{ now()->format('d M, Y') }}
    </p>
</div>
<hr>

@foreach($orders as $order)
<div class="order">
    <div class="order-header">
        Order #{{ $order->order_number }} | Date: {{ $order->created_at->format('d M, Y') }}
    </div>

    @foreach($order->items as $item)
    <table class="product-card">
        <tr>

            <!-- Product Details -->
            <td class="product-details">
                <p><span>Product Name (SKU):</span> {{ $item->product?->name ?? 'N/A' }} ({{ $item->product?->sku ?? 'N/A' }})</p>

                <p><span>Size:</span>
                    @if($item->variantOption?->size)
                        {{ $item->variantOption->size->name ?? $item->variantOption->name ?? 'N/A' }}
                    @elseif($item->size_name)
                        {{  $item->size_name ?? 'N/A' }}
                    @else
                        N/A
                    @endif
                </p>

                <p><span>Color:</span>
                    @if($item->variantOption?->variant?->color)
                        {{ $item->variantOption->variant->color->name ?? 'N/A' }}
                    @elseif($item->color_name)
                        {{  $item->color_name ?? 'N/A' }}
                    @else
                        N/A
                    @endif
                </p>

                <p><span>Consignment ID:</span> {{ $order->tracking_code ?? 'N/A' }}</p>
                <p><span>Courier Name:</span> {{ $order->deliveryOption?->courier_name ?? 'N/A' }}</p>
                <p ><span class="admin-comment">Admin Comment:</span>
                    <span style="background: none;">{{ $order->admin_comment ?? 'N/A' }}</span>
                </p>
            </td>

             <!-- Product Image -->
            <td width="200">
                @if($item->product && $item->product->main_image)
                    <img src="{{ public_path('storage/'.$item->product->main_image) }}" class="product-image" alt="">
                @else
                    <img src="{{ public_path('images/no-image.png') }}" class="product-image" alt="No Image">
                @endif
            </td>

        </tr>
    </table>
    @endforeach
</div>

@if(!$loop->last)
    <div style="page-break-after: always;"></div>
@endif
@endforeach

</body>
</html>
