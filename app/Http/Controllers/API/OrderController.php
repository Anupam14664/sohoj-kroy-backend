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

        $blocked = BlockedCustomer::where(function($query) use ($request) {
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
            $inventoryUpdates = ['items_updated' => 0, 'total_stock_reduced' => 0];

            foreach ($request->items as $itemData) {
                $product = Product::with(['variants.color', 'variants.options.size'])->findOrFail($itemData['product_id']);
                $hasVariants = $product->has_variants;

                $variant = null;
                $option = null;
                $colorName = null;
                $sizeName = null;
                $variantId = null;
                $optionId = null;
                $colorId = null;
                $sizeId = null;
                $colorCode = null;
                $itemPrice = 0;

                if ($hasVariants) {
                    $hasColor = $product->variants->contains(fn($v) => $v->color !== null);

                    if ($hasColor) {
                        if (empty($itemData['color_name'])) {
                            throw new \Exception("Color is required for product with variants: {$product->name}");
                        }

                        $variant = $product->variants->first(function ($v) use ($itemData) {
                            return $v->color && $v->color->name === $itemData['color_name'];
                        });

                        if (!$variant) {
                            $availableColors = $product->variants
                                ->filter(fn($v) => $v->color)
                                ->pluck('color.name')->unique()->implode(', ');
                            throw new \Exception("Color '{$itemData['color_name']}' not available for product: {$product->name}. Available colors: {$availableColors}");
                        }

                        $colorName = $variant->color->name ?? null;
                        $colorId = $variant->color->id ?? null;
                        $colorCode = $variant->color->code ?? null;
                    } else {
                        $variant = $product->variants->first();
                        if (!$variant) {
                            throw new \Exception("No variant available for product: {$product->name}");
                        }
                    }

                    if (empty($itemData['size_name'])) {
                        throw new \Exception("Size is required for product with variants: {$product->name}");
                    }

                    $option = $variant->options->first(fn($o) => $o->size && $o->size->name == $itemData['size_name']);

                    if (!$option) {
                        $availableSizes = $variant->options->pluck('size.name')->implode(', ');
                        throw new \Exception("Size '{$itemData['size_name']}' not available for product: {$product->name}. Available sizes: {$availableSizes}");
                    }

                    if ($option->stock < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$option->stock}, Requested: {$itemData['quantity']}");
                    }

                    $sizeName = $option->size->name;
                    $sizeId = $option->size->id;
                    $variantId = $variant->id;
                    $optionId = $option->id;
                    $itemPrice = $option->price ?? ($product->discount_price ?? $product->regular_price);
                } else {
                    if ($product->total_stock < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->total_stock}, Requested: {$itemData['quantity']}");
                    }

                    if ($product->size_id && !empty($itemData['size_name'])) {
                        $sizeName = $itemData['size_name'];
                        $sizeId = $product->size_id;
                    }

                    $itemPrice = $product->discount_price ?? $product->regular_price;
                }

                $itemTotal = $itemPrice * $itemData['quantity'];
                $subtotal += $itemTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => (float) $itemPrice,
                    'quantity' => (int) $itemData['quantity'],
                    'size_name' => $sizeName,
                    'color_name' => $colorName,
                    'size_id' => $sizeId,
                    'color_id' => $colorId,
                    'variant_id' => $variantId,
                    'option_id' => $optionId,
                    'variant_option_id' => $optionId,
                    'color_code' => $colorCode,
                    'total_price' => $itemTotal
                ];
            }

            $discount = 0;
            $couponCode = null;
            $couponDetails = null;

            if ($request->coupon_code) {
                $coupon = Coupon::with('products')->where('code', $request->coupon_code)->first();

                if (!$coupon) throw new \Exception('Coupon code not found');
                if (!$coupon->is_active) throw new \Exception('Coupon is inactive');

                $now = now();
                if (($coupon->start_date && $now->lt($coupon->start_date)) ||
                    ($coupon->end_date && $now->gt($coupon->end_date))) {
                    throw new \Exception('Coupon is not valid at this time');
                }

                $productIds = collect($request->items)->pluck('product_id')->toArray();

                if (!$coupon->apply_to_all && !$coupon->products()->whereIn('product_id', $productIds)->exists()) {
                    throw new \Exception('Coupon is not valid for any products in your cart');
                }

                $subtotalCheck = collect($items)->sum(fn($item) => $item['price'] * $item['quantity']);
                if ($subtotalCheck < $coupon->min_purchase) {
                    throw new \Exception('Coupon requires minimum purchase of ' . $coupon->min_purchase);
                }

                $discountResult = $coupon->calculateDiscountForProducts($items);
                $discount = $discountResult['total_discount'];

                $couponCode = $coupon->code;
                $couponDetails = [
                    'code' => $coupon->code,
                    'discount_type' => $coupon->type,
                    'discount_value' => (float) $coupon->amount,
                    'min_purchase' => (float) $coupon->min_purchase,
                    'apply_to_all' => $coupon->apply_to_all,
                    'applicable_products' => $coupon->apply_to_all ? null : $coupon->products->pluck('id')
                ];
            }

            $total = $subtotal + $deliveryOption->charge - $discount;
            $orderNumber = "H-" . str_pad(mt_rand(1, 99999), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'subtotal' => (float) $subtotal,
                'delivery_charge' => (float) $deliveryOption->charge,
                'discount' => (float) $discount,
                'total' => (float) $total,
                'coupon_code' => $couponCode,
                'status' => 'pending',
                'comment' => $request->comment,
                'delivery_option_id' => $request->delivery_option_id,
                'ip_address' => $ipAddress,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
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

                if ($item['option_id']) {
                    $rowsAffected = ProductVariantOption::where('id', $item['option_id'])
                        ->decrement('stock', $item['quantity']);
                } else {
                    $rowsAffected = Product::where('id', $item['product_id'])
                        ->decrement('total_stock', $item['quantity']);
                }

                if ($rowsAffected) {
                    $inventoryUpdates['items_updated']++;
                    $inventoryUpdates['total_stock_reduced'] += $item['quantity'];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => [
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'customer' => [
                            'name' => $order->name,
                            'phone' => $order->phone,
                            'address' => $order->address
                        ],
                        'delivery' => [
                            'method' => $deliveryOption->name,
                            'charge' => (float) $deliveryOption->charge,
                            'estimated_days' => $deliveryOption->estimated_days
                        ],
                        'payment' => [
                            'subtotal' => (float) $order->subtotal,
                            'discount' => (float) $order->discount,
                            'delivery_charge' => (float) $order->delivery_charge,
                            'total' => (float) $order->total,
                            'coupon_applied' => $order->coupon_code,
                            'coupon_details' => $couponDetails
                        ],
                        'items' => array_map(fn($item) => [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'variant' => [
                                'color' => $item['color_name'],
                                'color_code' => $item['color_code'],
                                'size' => $item['size_name']
                            ],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'total_price' => $item['price'] * $item['quantity']
                        ], $items),
                        'created_at' => $order->created_at->toIso8601String()
                    ],
                    'inventory_updates' => $inventoryUpdates
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'ip' => $ipAddress,
            ]);

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
                    $product = Product::with(['variants.color', 'variants.options.size'])->findOrFail($itemData['product_id']);

                    $hasVariants = $product->has_variants;
                    $variant = null;
                    $option = null;
                    $colorName = null;
                    $sizeName = null;
                    $variantId = null;
                    $optionId = null;
                    $colorId = null;
                    $sizeId = null;
                    $colorCode = null;
                    $itemPrice = $product->discount_price ?? $product->regular_price;

                    if ($hasVariants) {
                    $hasColor = $product->variants->contains(fn($v) => $v->color !== null);

                    if ($hasColor) {
                        if (empty($itemData['color_name'])) {
                            throw new \Exception("Color is required for product with variants: {$product->name}");
                        }

                        $variant = $product->variants->first(function ($v) use ($itemData) {
                            return $v->color && $v->color->name === $itemData['color_name'];
                        });

                        if (!$variant) {
                            $availableColors = $product->variants
                                ->filter(fn($v) => $v->color)
                                ->pluck('color.name')->unique()->implode(', ');
                            throw new \Exception("Color '{$itemData['color_name']}' not available for product: {$product->name}. Available colors: {$availableColors}");
                        }

                        $colorName = $variant->color->name ?? null;
                        $colorId = $variant->color->id ?? null;
                        $colorCode = $variant->color->code ?? null;
                    } else {
                        $variant = $product->variants->first();
                        if (!$variant) {
                            throw new \Exception("No variant available for product: {$product->name}");
                        }
                    }

                    if (empty($itemData['size_name'])) {
                        throw new \Exception("Size is required for product with variants: {$product->name}");
                    }

                    $option = $variant->options->first(fn($o) => $o->size && $o->size->name == $itemData['size_name']);

                    if (!$option) {
                        $availableSizes = $variant->options->pluck('size.name')->implode(', ');
                        throw new \Exception("Size '{$itemData['size_name']}' not available for product: {$product->name}. Available sizes: {$availableSizes}");
                    }

                    if ($option->stock < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$option->stock}, Requested: {$itemData['quantity']}");
                    }

                    $sizeName = $option->size->name;
                    $sizeId = $option->size->id;
                    $variantId = $variant->id;
                    $optionId = $option->id;
                    $itemPrice = $option->price ?? ($product->discount_price ?? $product->regular_price);
                }

                    $itemTotal = $itemPrice * $itemData['quantity'];
                    $subtotal += $itemTotal;

                    $items[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => (float) $itemPrice,
                        'quantity' => (int) $itemData['quantity'],
                        'size_name' => $sizeName,
                        'color_name' => $colorName,
                        'size_id' => $sizeId,
                        'color_id' => $colorId,
                        'variant_id' => $variantId,
                        'option_id' => $optionId,
                        'variant_option_id' => $optionId,
                        'color_code' => $colorCode,
                        'total_price' => $itemTotal
                    ];
                }
            }

            $orderNumber = "H-" . str_pad(mt_rand(1, 99999), 6, '0', STR_PAD_LEFT);

            // Create Order
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

            // Save Order Items to DB
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
                'data' => [
                    'order' => $order->load('items'),
                    'items' => $items
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Incomplete order failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'ip' => $ipAddress,
            ]);

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
