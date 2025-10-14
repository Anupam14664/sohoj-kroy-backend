<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['children' => function($query) {
            $query->withCount('products');
        }])
        ->withCount('products')
        ->whereNull('parent_id')
        ->get();

        $categories->transform(function ($category) {
            if ($category->image) {
                $category->image = $this->getFullImageUrl($category->image);
            }

            if ($category->children) {
                $category->children->transform(function ($child) {
                    if ($child->image) {
                        $child->image = $this->getFullImageUrl($child->image);
                    }
                    return $child;
                });
            }

            return $category;
        });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function show(Category $category)
    {
        $category->load('children', 'parent');

        // Add full URL to images
        if ($category->image) {
            $category->image = $this->getFullImageUrl($category->image);
        }

        if ($category->children) {
            $category->children->transform(function ($child) {
                if ($child->image) {
                    $child->image = $this->getFullImageUrl($child->image);
                }
                return $child;
            });
        }

        if ($category->parent && $category->parent->image) {
            $category->parent->image = $this->getFullImageUrl($category->parent->image);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function products(Category $category, Request $request)
    {
        $perPage = $request->input('per_page', 12);

        $products = $category->products()
            ->with(['category', 'variants.color', 'variants.options.size'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->paginate($perPage);

        // Add full URL to all images
        $this->addFullImageUrls($products);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category->image ? $category->only(['id', 'name', 'slug', 'image']) : $category->only(['id', 'name', 'slug']),
                'products' => $products
            ]
        ]);
    }

    protected function getFullImageUrl($path)
    {
        return $path ? url('storage/' . $path) : null;
    }

    protected function addFullImageUrls($products)
    {
        $products->getCollection()->transform(function ($product) {
            if ($product->main_image) {
                $product->main_image = $this->getFullImageUrl($product->main_image);
            }

            if ($product->category && $product->category->image) {
                $product->category->image = $this->getFullImageUrl($product->category->image);
            }

            if ($product->variants) {
                $product->variants->transform(function ($variant) {
                    if ($variant->image) {
                        $variant->image = $this->getFullImageUrl($variant->image);
                    }
                    return $variant;
                });
            }

            return $product;
        });
    }
}