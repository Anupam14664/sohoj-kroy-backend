<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
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

        // Search in product names, descriptions, and keyword tags
        $products = Product::active()
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('short_description', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhereJsonContains('keyword_tags', $query);
            })
            ->take(10)
            ->get(['id', 'name', 'slug', 'main_image']);

        // Get keyword suggestions from all products
        $allKeywords = Product::active()
            ->pluck('keyword_tags')
            ->flatten()
            ->unique()
            ->filter()
            ->toArray();

        $suggestions = array_filter($allKeywords, function($keyword) use ($query) {
            return stripos($keyword, $query) !== false;
        });

        // Limit suggestions and sort by relevance
        $suggestions = array_slice($suggestions, 0, 5);

        return response()->json([
            'products' => $products,
            'suggestions' => array_values($suggestions)
        ]);
    }
}