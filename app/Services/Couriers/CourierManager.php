<?php

namespace App\Services\Couriers;

use App\Models\CourierService;

class CourierManager
{
    public static function make(CourierService $courier): CourierInterface
    {
        return match ($courier->type) {
            'pathao'    => new PathaoCourierService(),
            'steadfast' => new SteadfastCourierService(),
            default     => throw new \Exception('Unsupported courier type'),
        };
    }
}
