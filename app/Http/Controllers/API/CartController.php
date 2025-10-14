<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $sessionId = Str::uuid();

        $cart = Cart::create([
            'session_id' => $sessionId,
            'user_id' => $request->user()->id ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart created successfully',
            'data' => [
                'session_id' => $sessionId,
                'cart' => $cart
            ]
        ]);
    }

    public function show($sessionId)
    {
        $cart = Cart::where('session_id', $sessionId)
            ->with(['items.product.images' => function($query) {
                $query->where('is_primary', true);
            }, 'items.size', 'items.color'])
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        $subtotal = $cart->subtotal;

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => $cart,
                'subtotal' => $subtotal
            ]
        ]);
    }

    public function addItem(Request $request, $sessionId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'size_id' => 'nullable|exists:sizes,id',
            'color_id' => 'nullable|exists:colors,id',
        ]);

        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        $product = Product::findOrFail($request->product_id);

        // Check if product is in stock
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock or has insufficient quantity'
            ], 400);
        }

        // Check if item already exists in cart
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->where('size_id', $request->size_id)
            ->where('color_id', $request->color_id)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $request->quantity;
            $existingItem->save();
            $item = $existingItem;
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'size_id' => $request->size_id,
                'color_id' => $request->color_id,
            ]);
        }

        $cart->load(['items.product.images' => function($query) {
            $query->where('is_primary', true);
        }, 'items.size', 'items.color']);

        $subtotal = $cart->subtotal;

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => [
                'item' => $item,
                'cart' => $cart,
                'subtotal' => $subtotal
            ]
        ]);
    }

    public function updateItem(Request $request, $sessionId, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        $item = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart'
            ], 404);
        }

        // Check if product has enough stock
        if ($item->product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock or has insufficient quantity'
            ], 400);
        }

        $item->quantity = $request->quantity;
        $item->save();

        $cart->load(['items.product.images' => function($query) {
            $query->where('is_primary', true);
        }, 'items.size', 'items.color']);

        $subtotal = $cart->subtotal;

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => [
                'item' => $item,
                'cart' => $cart,
                'subtotal' => $subtotal
            ]
        ]);
    }

    public function removeItem($sessionId, $itemId)
    {
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        $item = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart'
            ], 404);
        }

        $item->delete();

        $cart->load(['items.product.images' => function($query) {
            $query->where('is_primary', true);
        }, 'items.size', 'items.color']);

        $subtotal = $cart->subtotal;

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => [
                'cart' => $cart,
                'subtotal' => $subtotal
            ]
        ]);
    }
}