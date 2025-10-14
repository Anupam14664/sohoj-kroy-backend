<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'cart_items' => 'required|array',
            'cart_items.*.product_id' => 'required|exists:products,id',
            'cart_items.*.price' => 'required|numeric|min:0',
            'cart_items.*.quantity' => 'required|integer|min:1'
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found or inactive'
            ], 404);
        }

        // Calculate subtotal for minimum purchase check
        $subtotal = collect($request->cart_items)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Check minimum purchase requirement
        if ($subtotal < $coupon->min_purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum purchase requirement not met'
            ], 400);
        }

        // Calculate discount for applicable products
        $discountResult = $coupon->calculateDiscountForProducts($request->cart_items);

        if ($discountResult['total_discount'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon is not valid for any products in your cart'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully',
            'data' => [
                'coupon' => $coupon,
                'total_discount' => $discountResult['total_discount'],
                'applicable_products' => $discountResult['applicable_products'],
                'subtotal' => $subtotal,
                'discounted_total' => $subtotal - $discountResult['total_discount']
            ]
        ]);
    }
}