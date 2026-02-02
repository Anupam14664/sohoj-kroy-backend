<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Models\CourierService;

interface CourierInterface
{
    public function createOrder(
        Order $order,
        CourierService $courier,
        array $data
    ): array;
}
