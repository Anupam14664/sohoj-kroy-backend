<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title> Invoice</title>
    <style>

        @page {
            size: auto;
        }

        @media print {
            body {
                width: auto !important;
                height: auto !important;
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: solaimanlipi, sans-serif;
        }

        body {
            width: 75mm;
            background: #fff;
        }

        .sticker-container {
            width: 75mm;
            padding: 3px;
            background: #fff;
            margin: 0 auto;
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
            font-size: 9px !important;
        }

        .company-info p {
            font-size: 8px !important;
            margin-bottom: 1.2px;
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
            background: #000000;
        }

        table.items-table th {
            font-size: 9px;
            text-transform: uppercase;
            padding: 5px;
            text-align: center;
            border-bottom: 1px solid #000000;
        }

        table.items-table td {
            font-size: 11px;
            padding: 5px;
            text-align: center;
            border-bottom: 1px solid #000000;
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
        .totals-table {
            width: 100%;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-collapse: collapse;
            background: #f5f5f5;
            border-radius: 6px;
            overflow: hidden;
            font-size: 12px;
            line-height: 0.50px !important;
        }

        .totals-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e1e1e1;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table .amount {
            text-align: right;
            width: 80px;
        }

        .totals-table .grand-total td {
            font-weight: bold;
            font-size: 13px;
            color: #000;
        }

    </style>
</head>
<body>

<div class="sticker-container">
    <!-- Header -->
    <div class="sticker-header">
        <table>
            <tr>
                <td style="width:52%;">
                    <div class="company-info">
                        <h2 style="font-size: 14px;">{{ $order->company_name}}</h2>
                        <p style="font-size:11.8px; line-height: 1.2px; letter-spacing: 0.2px; margin-right: 0.6px;">Hotline: {{ $order->company_phone }}</p>
                        <p style="font-size:11.8px; line-height: 1.2px; letter-spacing: 0.2px; margin-right: 0.6px;">Date: {{ \Carbon\Carbon::now()->format('d M, Y') }}</p>
                        <p style="font-size:11.8px; line-height: 1.2px; letter-spacing: 0.2px; margin-right: 0.6px;">Courier: {{ $order->courier->name ?? 'N/A' }}</p>
                        <p style="font-size:11.8px; line-height: 1.2px; letter-spacing: 0.2px; margin-right: 0.6px;">Parcel ID: {{ $order->consignment_id ?? 'N/A' }}</p>
                        <p style="font-size:11.8px; line-height: 1.2px; letter-spacing: 0.2px; margin-right: 0.6px;">Order ID: {{ $order->order_number ?? 'N/A' }}</p>
                    </div>
                </td>
                <td style="width:48%;">

                        <!-- Invoice To -->
                    <div class="invoice-to" style=" ">
                        <h4 style="font-size:12px;">Invoice To:</h4>
                        <p style="font-size:11px; line-height: 1.2px; letter-spacing: 0.2px;"><strong>Name:</strong> {{ $order->name }}</p>
                        <p style="font-size:11px; line-height: 1.2px; letter-spacing: 0.2px;"><strong>Phone:</strong> {{ $order->phone }}</p>
                        <p style="font-size:11px; line-height: 1.2px; letter-spacing: 0.2px;"><strong>Address:</strong> {{ $order->address }}, {{ $order->thana }}, {{ $order->district }}</p>
                    </div>


                    {{-- <div class="parcel-info">
                        <h5 style="font-size: 12px;background: #000;color: #fff; padding: 2px 5px; border-radius: 3px;margin-bottom: 10px !important;">Parcel Id: {{ $order->consignment_id ?? 'N/A' }}</h5>
                        <div class="barcode" style="width: 100%;text-align: center;margin: 5px 0;">
                            <img src="https://barcodeapi.org/api/128/{{ $order->order_number }}" alt="Barcode" style="width:120px;height:30px;">
                            <p style="font-size:11px;margin-top:3px;">R1</p>
                        </div>
                    </div> --}}
                </td>
            </tr>
        </table>
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
                <td style="text-align: left; font-size: 9px; letter-spacing: 0.2px;">{{ $item->product_name }}
                    @if($item->size_name || $item->color_name)
                        ({{ $item->size_name ?? '' }} {{ $item->color_name ? '/' .$item->color_name : '' }})
                    @endif
                </td>
                <td style="font-size: 9px; letter-spacing: 0.2px;">{{ $item->quantity }}</td>
                <td style="font-size: 9px; letter-spacing: 0.2px;">৳ {{ number_format($item->price * $item->quantity, 0) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Totals -->
<table class="totals-table">
    <tr>
        <td style="letter-spacing: 0.2px;">Sub Total</td>
        <td class="amount" style="letter-spacing: 0.2px;">৳ {{ number_format($order->subtotal, 0) }}</td>
    </tr>
    <tr>
        <td style="letter-spacing: 0.2px;">Delivery Fee</td>
        <td class="amount" style="letter-spacing: 0.2px;">৳ {{ number_format($order->delivery_charge, 0) }}</td>
    </tr>
    @if((float)$order->admin_discount > 0)
    <tr>
        <td style="letter-spacing: 0.2px;">Discount</td>
        <td class="amount" style="letter-spacing: 0.2px;">৳ -{{ number_format($order->admin_discount, 0) }}</td>
    </tr>
    @endif
    <tr class="grand-total">
        <td style="letter-spacing: 0.2px;"><strong>Total Amount</strong></td>
        <td class="amount" style="letter-spacing: 0.2px;"><strong>৳ {{ number_format($order->total, 0) }}</strong></td>
    </tr>
</table>



    <!-- Order Note -->
    {{-- <div class="order-note-container">
        <p>Order Note:</p>
        <div class="order-note">{{ $order->admin_comment }}</div>
    </div> --}}
</div>

</body>
</html>
