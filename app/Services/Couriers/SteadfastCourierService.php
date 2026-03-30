<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Models\CourierService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastCourierService implements CourierInterface
{
    /**
     * Create an order in Steadfast courier system
     *
     * @param Order $order
     * @param CourierService $courier
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function createOrder(Order $order, CourierService $courier, array $data): array
    {
        // Ensure credentials exist
        $apiKey = $courier->api_key ?? config('services.steadfast.api_key');
        $secretKey = $courier->secret_key ?? config('services.steadfast.secret_key');

        if (!$apiKey || !$secretKey) {
            throw new \Exception('Steadfast API credentials are missing.');
        }

        // Prepare payload
        $payload = [
            'invoice'           => $order->order_number,
            'recipient_name'    => $order->name,
            'recipient_phone'   => $order->phone,
            'recipient_address' => trim($order->address . ', ' . $order->thana . ', ' . $order->district),
            'cod_amount'        => (float) $order->total,
            'note'              => $data['delivery_note'] ?? 'Handle with care',
            'item_description'  => 'N/A',
            'delivery_type'     => 0,
        ];

        // Build URL
        $url = rtrim($courier->base_url, '/') . '/' . ltrim($courier->create_order_endpoint, '/');

        // Send request
        $response = Http::withHeaders([
            'Api-Key'     => $apiKey,
            'Secret-Key'  => $secretKey,
            'Content-Type'=> 'application/json',
        ])->post($url, $payload);

        $res = $response->json();

        // Log raw response for debugging
        Log::info('Steadfast API Response', [
            'order' => $order->order_number,
            'response' => $res,
        ]);

        // If HTTP request failed, throw exception
        if (!$response->successful()) {
            $message = $res['message'] ?? 'Unknown HTTP error';
            $errors = $res['errors'] ?? [];
            throw new \Exception('Steadfast API HTTP Error: ' . $message . ' ' . json_encode($errors));
        }

        /**
         * Proper handling:
         * Steadfast may return a success message like
         * "Consignment has been created successfully" even if tracking_code is not available yet.
         * We treat this as success.
         */
        $order->courier_service_id = $courier->id;
        $order->courier_response = json_encode($res);

        // Save tracking info if available
        if (isset($res['consignment'])) {
            $order->tracking_code = $res['consignment']['tracking_code'] ?? null;
            $order->consignment_id = $res['consignment']['consignment_id'] ?? null;
        }

        // Return data
        return [
            'tracking_code'  => $order->tracking_code,
            'consignment_id' => $order->consignment_id,
            'response'       => $res,
        ];
    }
}
