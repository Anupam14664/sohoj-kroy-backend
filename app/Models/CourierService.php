<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierService extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'type',
        'store_id',
        'base_url',
        'create_order_endpoint',
        'api_key',
        'secret_key',
        'client_id',
        'client_secret',
        'username',
        'password',
        'auth_endpoint',
        'headers',
        'is_active',
    ];

    protected $casts = [
        'headers' => 'array',
        'is_active' => 'boolean',
    ];
}
