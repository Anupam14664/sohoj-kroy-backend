<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return response()->json([
                'products' => [],
                'suggestions' => []
            ]);
        }


        $products = Product::active()
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('short_description', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhereJsonContains('keyword_tags', $query);
            })
            ->take(10)
            ->get(['id', 'name', 'slug', 'main_image', 'regular_price', 'discount_price']);


        $products = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'main_image' => asset('storage/' . $product->main_image),
                'regular_price' => $product->regular_price,
                'discount_price' => $product->discount_price
            ];
        });


        $allKeywords = Product::active()
            ->pluck('keyword_tags')
            ->flatten()
            ->unique()
            ->filter()
            ->toArray();

        $suggestions = array_filter($allKeywords, function($keyword) use ($query) {
            return stripos($keyword, $query) !== false;
        });


        $suggestions = array_slice($suggestions, 0, 5);

        return response()->json([
            'products' => $products,
            'suggestions' => array_values($suggestions)
        ]);
    }
}