<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            $price = $item->product->offer_price ?? $item->product->regular_price;

            // Add size price adjustment if applicable
            if ($item->size_id) {
                $productSize = $item->product->sizes()->where('size_id', $item->size_id)->first();
                if ($productSize) {
                    $price += $productSize->pivot->price_adjustment;
                }
            }

            return $price * $item->quantity;
        });
    }
}