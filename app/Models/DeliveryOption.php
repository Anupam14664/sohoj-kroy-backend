<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'charge',
        'is_active',
        'is_free_for_products',
    ];

    protected $casts = [
        'charge' => 'decimal:2',
        'is_active' => 'boolean',
        'is_free_for_products' => 'boolean',
    ];
    public function freeDeliveryProducts()
    {
        return $this->belongsToMany(Product::class, 'free_delivery_products')
                    ->withTimestamps();
    }
}