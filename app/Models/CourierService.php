<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierService extends Model
{
    protected $fillable = [
        'name', 'base_url', 'create_order_endpoint', 'api_key', 'secret_key', 'headers', 'is_active'
    ];

    protected $casts = [
        'headers' => 'array',
        'is_active' => 'boolean',
    ];
}
