<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'amount',
        'type',
        'start_date',
        'end_date',
        'min_purchase',
        'is_active',
        'apply_to_all',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'apply_to_all' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function isValidForProducts($productIds = []): bool
    {
        $now = now();

        $valid = $this->is_active && $now->between($this->start_date, $this->end_date);

        if (!$valid) return false;

        if ($this->apply_to_all) return true;

        if (!empty($productIds)) {
            return $this->products()->whereIn('product_id', $productIds)->exists();
        }

        return false;
    }

    public function calculateDiscountForProducts($cartItems)
    {
        $applicableProducts = [];
        $totalDiscount = 0;

        // Calculate total cart value for minimum purchase check
        $cartTotal = collect($cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);

        // Check minimum purchase requirement
        if ($cartTotal < $this->min_purchase) {
            return [
                'total_discount' => 0,
                'applicable_products' => []
            ];
        }

        $productIds = collect($cartItems)->pluck('product_id')->toArray();

        if (!$this->isValidForProducts($productIds)) {
            return [
                'total_discount' => 0,
                'applicable_products' => []
            ];
        }

        $applicableProductCount = 0;

        foreach ($cartItems as $item) {
            $productId = $item['product_id'];

            if ($this->apply_to_all || $this->products()->where('product_id', $productId)->exists()) {
                $applicableProductCount++;
            }
        }

        foreach ($cartItems as $item) {
            $productId = $item['product_id'];
            $price = $item['price'];
            $quantity = $item['quantity'];

            if ($this->apply_to_all || $this->products()->where('product_id', $productId)->exists()) {
                if ($this->type === 'fixed') {
                    $discount = $applicableProductCount > 0 ? ($this->amount / $applicableProductCount) : 0;
                } elseif ($this->type === 'percentage') {
                    $discount = ($price * $this->amount / 100) * $quantity;
                } else {
                    $discount = 0;
                }

                $totalDiscount += $discount;

                $applicableProducts[] = [
                    'product_id' => $productId,
                    'price' => $price,
                    'quantity' => $quantity,
                    'discount' => $discount
                ];
            }
        }

        return [
            'total_discount' => $totalDiscount,
            'applicable_products' => $applicableProducts
        ];
    }
}
