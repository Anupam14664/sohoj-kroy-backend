<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Models\CourierService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PathaoCourierService implements CourierInterface
{
    /**
     * Get Pathao OAuth Token (cached)
     */
    protected function getAccessToken(CourierService $courier): string
    {
        return Cache::remember(
            'pathao_token_' . $courier->id,
            3500,
            function () use ($courier) {

                $response = Http::post(
                    $courier->base_url . '/' . $courier->auth_endpoint,
                    [
                        'client_id'     => $courier->client_id,
                        'client_secret' => $courier->client_secret,
                        'username'      => $courier->username,
                        'password'      => $courier->password,
                        'grant_type'    => 'password',
                    ]
                );

                if (!$response->successful()) {
                    throw new \Exception(
                        'Pathao Auth Failed: ' . $response->body()
                    );
                }

                return $response->json('access_token');
            }
        );
    }

    /**
     * Create Pathao Order
     */
    public function createOrder(Order $order, CourierService $courier, array $data): array
    {
        $token = $this->getAccessToken($courier);

        $payload = [
            'merchant_order_id' => $order->order_number,
            'store_id'          => $courier->store_id,
            'recipient_name'    => $order->name,
            'recipient_phone'   => $order->phone,
            'recipient_address' => $order->address . ', ' . $order->thana . ', ' . $order->district,
            'amount_to_collect' => (float) $order->total,
            'note'              => $validated['delivery_note'] ?? 'Handle with care',
            'delivery_type'     => 48,
            'item_quantity'     => 1,
            'item_weight'       => 0.5,
            'item_type'         => 2,
        ];


        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->post(
            $courier->base_url . '/' . $courier->create_order_endpoint,
            $payload
        );

        if (!$response->successful()) {
            throw new \Exception(
                'Pathao Order Failed: ' . $response->body()
            );
        }

        $res = $response->json();

        return [
            'tracking_code'  => $res['data']['consignment_id'] ?? null,
            'consignment_id' => $res['data']['order_id'] ?? null,
            'response'       => $res,
        ];
    }
}
