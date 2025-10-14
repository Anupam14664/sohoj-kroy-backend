<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title> Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: solaimanlipi, sans-serif;
        }

        body {
            background: #fff;
        }

        .sticker-container {
            width: 3in;
            padding: 10px;
            border: 1px solid #ccc;
            background: #fff;
            margin: 0 auto;
            border-radius: 6px;
        }

        .sticker-header {
            width: 100%;
            margin-bottom: 8px;
        }

        /* Header using table */
        .sticker-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .sticker-header td {
            vertical-align: top;
        }

        .company-info h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-info p {
            font-size: 11px;
            margin-bottom: 2px;
            color: #333;
        }

        .parcel-info h5 {

        }

        .barcode {

        }

        .invoice-to {

        }

        .invoice-to h4 {
            font-size: 10px;
            margin-bottom: 4px;
            font-weight: bold;
        }

        .invoice-to p {
            font-size: 10px;
            margin: 2px 0;
            line-height: 1.3;
            color: #444;
        }

        /* Modern Table */
        table.items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 6px;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }

        table.items-table thead {
            background: #f1f1f1;
        }

        table.items-table th {
            font-size: 9px;
            text-transform: uppercase;
            padding: 5px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table.items-table td {
            font-size: 11px;
            padding: 5px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        table.items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .totals {
            margin-top: 8px;
            font-size: 12px;
            background: #f5f5f5;
            padding: 6px;
            border-radius: 4px;
        }

        .totals p {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            margin: 2px 0;
        }




        .order-note-container {
            margin-top: 8px;
        }

        .order-note-container p {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .order-note {
            border: 1px solid #bbb;
            height: 35px;
            font-size: 11px;
            padding: 2px 3px;
            border-radius: 4px;
            background: #fafafa;
        }
    </style>
</head>
<body>

<div class="sticker-container">
    <!-- Header -->
    <div class="sticker-header">
        <table>
            <tr>
                <td style="width:55%;">
                    <div class="company-info">
                        <h2>{{ $order->company_name }}</h2>
                        <p>Hotline: {{ $order->company_phone }}</p>
                        <p>Date: {{ \Carbon\Carbon::now()->format('d M, Y') }}</p>
                        <p>Courier: {{ $order->courier->name ?? 'N/A' }}</p>
                    </div>
                </td>
                <td style="width:45%; text-align: center;">
                    <div class="parcel-info">
                        <h5 style="font-size: 12px;background: #000;color: #fff; padding: 2px 5px; border-radius: 3px;margin-bottom: 10px !important;">Parcel Id: {{ $order->tracking_code ?? 'N/A' }}</h5>
                        <div class="barcode" style="width: 100%;text-align: center;margin: 5px 0;">
                            <img src="https://barcodeapi.org/api/128/{{ $order->order_number }}" alt="Barcode" style="width:120px;height:30px;">
                            {{-- <p style="font-size:11px;margin-top:3px;">R1</p> --}}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Invoice To -->
    <div class="invoice-to" style=" margin: 8px 0; padding: 6px; background: #f5f5f5; border-radius: 4px;">
        <h4>Invoice To:</h4>
        <p><strong>Name:</strong> {{ $order->name }}</p>
        <p><strong>Phone:</strong> {{ $order->phone }}</p>
        <p><strong>Address:</strong> {{ $order->address }}, {{ $order->thana }}, {{ $order->district }}</p>
    </div>

    <!-- Product Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Product (Size/Color)</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td style="text-align: left">{{ $item->product_name }}
                    @if($item->size_name || $item->color_name)
                        ({{ $item->size_name ?? '' }} {{ $item->color_name ? '/' .$item->color_name : '' }})
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <p><span>Sub Total:</span> <span>{{ number_format($order->subtotal, 2) }}</span></p>
        <p><span>Delivery Fee:</span> <span>{{ number_format($order->delivery_charge, 2) }}</span></p>
        <p><span>Discount:</span> <span>-{{ number_format($order->discount, 2) }}</span></p>
        <p style="font-weight: bold; font-size: 12px; color: #000;"><span>Total Amount:</span> <span>{{ number_format($order->total, 2) }}</span></p>
    </div>

    <!-- Order Note -->
    <div class="order-note-container">
        <p>Order Note:</p>
        <div class="order-note">{{ $order->admin_comment }}</div>
    </div>
</div>

</body>
</html>
