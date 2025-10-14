<?php

namespace App\Helpers;

use App\Models\Product;

class ProductHelper
{
    public static function generateProductId()
    {
        $lastProduct = Product::orderBy('id', 'desc')->first();

        if (!$lastProduct) {
            return 'H-0101';
        }

        // Extract the numeric part
        $lastNumber = (int) substr($lastProduct->productId, 2);
        $newNumber = $lastNumber + 1;

        // Format with leading zeros
        return 'H-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}