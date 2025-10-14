<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort' => 'sometimes|in:latest,price_asc,price_desc,popular',
        ]);

        $perPage = $request->input('per_page', 12);
        $sort = $request->input('sort', 'latest');

        $categories = Category::withCount('products')
            ->whereNull('parent_id')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image_url,
                    'products_count' => $category->products_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function show($slug, Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort' => 'sometimes|in:latest,price_asc,price_desc,popular',
            'color' => 'sometimes|exists:colors,id',
            'size' => 'sometimes|exists:sizes,id',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
        ]);

        $perPage = $request->input('per_page', 12);
        $sort = $request->input('sort', 'latest');

        $category = Category::where('slug', $slug)
            ->with(['parent' => function($query) {
                $query->select('id', 'name', 'slug', 'image');
            }])
            ->firstOrFail();

        $query = Product::with([
                'category:id,name,slug,image',
                'variants.color:id,name,code',
                'variants.options.size:id,name'
            ])
            ->where('category_id', $category->id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('regular_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('regular_price', 'desc');
                break;
            case 'popular':
                $query->orderBy('reviews_avg_rating', 'desc')
                      ->orderBy('reviews_count', 'desc');
                break;
            default: // latest
                $query->latest();
                break;
        }

        // Apply filters
        if ($request->has('color')) {
            $query->whereHas('variants', function($q) use ($request) {
                $q->where('color_id', $request->color);
            });
        }

        if ($request->has('size')) {
            $query->whereHas('variants.options', function($q) use ($request) {
                $q->where('size_id', $request->size);
            });
        }

        if ($request->has('min_price')) {
            $query->where('regular_price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('regular_price', '<=', $request->max_price);
        }

        $products = $query->paginate($perPage);

        // Transform the data to include full image URLs
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'productId' => $product->productId,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'regular_price' => $product->regular_price,
                'main_image' => $product->main_image ? url('storage/' . $product->main_image) : null,
                'stock' => $product->stock,
                'reviews_count' => $product->reviews_count,
                'reviews_avg_rating' => (float) number_format($product->reviews_avg_rating, 1),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                    'image' => $product->category->image ? url('storage/' . $product->category->image) : null,
                ] : null,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'color' => $variant->color,
                        'image' => $variant->image ? url('storage/' . $variant->image) : null,
                        'options' => $variant->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'price' => $option->price,
                                'stock' => $option->stock,
                                'size' => $option->size,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image_url,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                        'slug' => $category->parent->slug,
                        'image' => $category->parent->image ? url('storage/' . $category->parent->image) : null,
                    ] : null,
                ],
                'products' => $products,
                'filters' => [
                    'sort_options' => [
                        ['value' => 'latest', 'label' => 'Latest'],
                        ['value' => 'price_asc', 'label' => 'Price: Low to High'],
                        ['value' => 'price_desc', 'label' => 'Price: High to Low'],
                        ['value' => 'popular', 'label' => 'Most Popular'],
                    ],
                    'current_sort' => $sort,
                ]
            ]
        ]);
    }
}