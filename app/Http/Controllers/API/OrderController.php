<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DeliveryOption;
use App\Models\ProductVariant;
use App\Models\BlockedCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantOption;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function deliveryOptions()
    {
        $options = DeliveryOption::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                    'charge' => (float) $option->charge,
                    'estimated_days' => $option->estimated_days
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $ipAddress = $request->ip();

        $blocked = BlockedCustomer::where(function ($query) use ($request) {
            $query->where('phone', $request->phone)
                ->orWhere('ip_address', $request->ip());
        })->exists();

        if ($blocked) {
            return response()->json([
                'success' => false,
                'message' => 'You are blocked from placing orders. Please contact Admin.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'delivery_option_id' => 'required|exists:delivery_options,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size_name' => 'nullable|string',
            'items.*.color_name' => 'nullable|string',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $deliveryOption = DeliveryOption::findOrFail($request->delivery_option_id);
            $subtotal = 0;
            $items = [];

            foreach ($request->items as $itemData) {

                $product = Product::with(['variants.color', 'variants.options.size'])
                    ->findOrFail($itemData['product_id']);

                $quantity = (int) $itemData['quantity'];

                $variant = null;
                $option = null;

                $sizeName = null;
                $sizeId = null;
                $colorName = null;
                $colorId = null;
                $colorCode = null;
                $variantId = null;
                $optionId = null;

                // ===== VARIANT PRODUCT =====
                if ($product->has_variants) {

                    // ---------- COLOR ----------
                    if (!empty($itemData['color_name'])) {
                        $variant = $product->variants
                            ->first(fn($v) => $v->color && $v->color->name === $itemData['color_name']);

                        if (!$variant) {
                            throw new \Exception("Color '{$itemData['color_name']}' not available for {$product->name}");
                        }

                        $colorName = $variant->color->name;
                        $colorId   = $variant->color->id;
                        $colorCode = $variant->color->code;
                    } else {
                        // no color product
                        $variant = $product->variants->first();
                    }

                    if (!$variant) {
                        throw new \Exception("Variant not found for {$product->name}");
                    }

                    $variantId = $variant->id;

                    // ---------- SIZE (OPTIONAL) ----------
                    $hasSize = $variant->options->contains(fn($o) => $o->size !== null);

                    if ($hasSize && !empty($itemData['size_name'])) {

                        $option = $variant->options
                            ->first(fn($o) => $o->size && $o->size->name === $itemData['size_name']);

                        if (!$option) {
                            $availableSizes = $variant->options->pluck('size.name')->filter()->implode(', ');
                            throw new \Exception("Size '{$itemData['size_name']}' not available for {$product->name}. Available: {$availableSizes}");
                        }

                        if ($option->stock < $quantity) {
                            throw new \Exception("Insufficient stock for {$product->name}");
                        }

                        $sizeName = $option->size->name;
                        $sizeId   = $option->size->id;
                        $optionId = $option->id;
                        $price    = $option->price ?? $product->discount_price ?? $product->regular_price;

                    } else {
                        // size নাই → null যাবে
                        $price = $variant->price ?? $product->discount_price ?? $product->regular_price;
                    }

                }
                // ===== SIMPLE PRODUCT =====
                else {

                    if ($product->total_stock < $quantity) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }

                    $price = $product->discount_price ?? $product->regular_price;
                }

                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'size_name' => $sizeName,
                    'size_id' => $sizeId,
                    'color_name' => $colorName,
                    'color_id' => $colorId,
                    'color_code' => $colorCode,
                    'variant_id' => $variantId,
                    'variant_option_id' => $optionId,
                ];
            }

            $total = $subtotal + $deliveryOption->charge;
            $orderNumber = 'H-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'subtotal' => $subtotal,
                'delivery_charge' => $deliveryOption->charge,
                'total' => $total,
                'status' => 'pending',
                'comment' => $request->comment,
                'delivery_option_id' => $deliveryOption->id,
                'ip_address' => $ipAddress,
            ]);

            foreach ($items as $item) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'variant_option_id' => $item['variant_option_id'],
                    'size_id' => $item['size_id'],
                    'color_id' => $item['color_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'size_name' => $item['size_name'],
                    'color_name' => $item['color_name'],
                ]);

                if ($item['variant_option_id']) {
                    ProductVariantOption::where('id', $item['variant_option_id'])
                        ->decrement('stock', $item['quantity']);
                } else {
                    Product::where('id', $item['product_id'])
                        ->decrement('total_stock', $item['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_number' => $order->order_number
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function incomplete(Request $request)
    {
        $ipAddress = $request->ip();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'district' => 'nullable|string',
            'thana' => 'nullable|string',
            'delivery_option_id' => 'nullable|exists:delivery_options,id',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.color_name' => 'nullable|string',
            'items.*.size_name' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $deliveryOption = $request->delivery_option_id
                ? DeliveryOption::find($request->delivery_option_id)
                : null;

            $subtotal = 0;
            $items = [];

            if ($request->filled('items')) {

                foreach ($request->items as $itemData) {

                    $product = Product::with(['variants.color', 'variants.options.size'])
                        ->findOrFail($itemData['product_id']);

                    $quantity = (int) $itemData['quantity'];

                    $variant = null;
                    $option = null;

                    $colorName = null;
                    $colorId = null;
                    $colorCode = null;
                    $sizeName = null;
                    $sizeId = null;
                    $variantId = null;
                    $optionId = null;

                    // ===== VARIANT PRODUCT =====
                    if ($product->has_variants) {

                        // ---------- COLOR (OPTIONAL) ----------
                        if (!empty($itemData['color_name'])) {
                            $variant = $product->variants
                                ->first(fn($v) => $v->color && $v->color->name === $itemData['color_name']);

                            if (!$variant) {
                                throw new \Exception("Color '{$itemData['color_name']}' not available for {$product->name}");
                            }

                            $colorName = $variant->color->name;
                            $colorId   = $variant->color->id;
                            $colorCode = $variant->color->code;
                        } else {
                            $variant = $product->variants->first();
                        }

                        if (!$variant) {
                            throw new \Exception("Variant not found for {$product->name}");
                        }

                        $variantId = $variant->id;

                        // ---------- SIZE (OPTIONAL) ----------
                        $hasSize = $variant->options->contains(fn($o) => $o->size !== null);

                        if ($hasSize && !empty($itemData['size_name'])) {

                            $option = $variant->options
                                ->first(fn($o) => $o->size && $o->size->name === $itemData['size_name']);

                            if ($option) {
                                $sizeName = $option->size->name;
                                $sizeId   = $option->size->id;
                                $optionId = $option->id;
                                $price    = $option->price ?? $product->discount_price ?? $product->regular_price;
                            } else {
                                // incomplete order → invalid size হলেও fail না
                                $price = $variant->price ?? $product->discount_price ?? $product->regular_price;
                            }

                        } else {
                            // size নাই বা পাঠানো হয়নি
                            $price = $variant->price ?? $product->discount_price ?? $product->regular_price;
                        }

                    }
                    // ===== SIMPLE PRODUCT =====
                    else {
                        $price = $product->discount_price ?? $product->regular_price;
                    }

                    $itemTotal = $price * $quantity;
                    $subtotal += $itemTotal;

                    $items[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => (float) $price,
                        'quantity' => $quantity,
                        'size_name' => $sizeName,
                        'size_id' => $sizeId,
                        'color_name' => $colorName,
                        'color_id' => $colorId,
                        'variant_id' => $variantId,
                        'variant_option_id' => $optionId,
                        'color_code' => $colorCode,
                        'total_price' => $itemTotal
                    ];
                }
            }

            $orderNumber = "H-" . str_pad(mt_rand(1, 99999), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'subtotal' => (float) $subtotal,
                'delivery_charge' => $deliveryOption->charge ?? 0,
                'total' => (float) $subtotal + ($deliveryOption->charge ?? 0),
                'status' => 'incomplete',
                'comment' => $request->comment,
                'delivery_option_id' => $request->delivery_option_id,
                'ip_address' => $ipAddress,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_option_id' => $item['variant_option_id'],
                    'variant_id' => $item['variant_id'],
                    'size_id' => $item['size_id'],
                    'color_id' => $item['color_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'size_name' => $item['size_name'],
                    'color_name' => $item['color_name'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Incomplete order saved successfully',
                'data' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save incomplete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function incompleteOrders()
    {
        $orders = Order::where('status', 'incomplete')
                    ->latest()
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function showIncomplete($id)
    {
        $order = Order::where('status', 'incomplete')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
