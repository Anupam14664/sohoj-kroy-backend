<?php

use Illuminate\Support\Facades\Http;
use App\Models\Order;

if (!function_exists('getDeliveryStatus')) {
    function getDeliveryStatus(Order $order)
    {
        if (!$order->tracking_code || !$order->courier) {
            return [
                'code' => 'no_tracking',
                'description' => 'No tracking code or courier'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Api-Key' => $order->courier->api_key,
                'Secret-Key' => $order->courier->secret_key,
                'Content-Type' => 'application/json'
            ])->get($order->courier->base_url . '/status_by_trackingcode/' . $order->tracking_code);

            $data = $response->json();

            // যদি delivery_status nested থাকে
            $statusCode = $data['delivery_status'] ?? ($data['data']['delivery_status'] ?? 'unknown');
            $statusDescription = getDeliveryStatusDescription($statusCode);

            return [
                'code' => $statusCode,
                'description' => $statusDescription
            ];

        } catch (\Exception $e) {
            return [
                'code' => 'error',
                'description' => 'Error fetching status'
            ];
        }
    }
}

if (!function_exists('getDeliveryStatusDescription')) {
    function getDeliveryStatusDescription($code)
    {
        $statuses = [
            'pending' => 'Consignment is not delivered or cancelled yet.',
            'delivered_approval_pending' => 'Consignment is delivered but waiting for admin approval.',
            'partial_delivered_approval_pending' => 'Consignment is delivered partially and waiting for admin approval.',
            'cancelled_approval_pending' => 'Consignment is cancelled and waiting for admin approval.',
            'unknown_approval_pending' => 'Unknown Pending status. Need contact with the support team.',
            'delivered' => 'Consignment is delivered and balance added.',
            'partial_delivered' => 'Consignment is partially delivered and balance added.',
            'cancelled' => 'Consignment is cancelled and balance updated.',
            'hold' => 'Consignment is held.',
            'in_review' => 'Order is placed and waiting to be reviewed.',
            'unknown' => 'Unknown status. Need contact with the support team.',
            'no_tracking' => 'No tracking code or courier',
            'error' => 'Error fetching status'
        ];

        return $statuses[$code] ?? 'No description available';
    }
}
