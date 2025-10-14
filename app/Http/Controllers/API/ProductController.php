<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['images', 'category', 'variants.color', 'variants.options.size'])
            ->withCount(['reviews' => function ($q) {
                $q->where('is_approved', true);
            }])
            ->withAvg('reviews', 'rating')
            ->active()
            ->latest()
            ->paginate(12);

        // Add full URL to all images
        $this->addFullImageUrls($products);



        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'variants.color',
            'variants.options.size',
            'variants' => function($query) {
                $query->withCount('options');
            }
        ]);

        $product->makeHidden('Purchase_price');

        $product->loadCount(['reviews' => function ($q) {
            $q->where('is_approved', true);
        }]);

        $product->loadAvg('reviews', 'rating');

        $ratingProgress = \App\Models\Review::selectRaw('rating, COUNT(*) as total')
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratingBreakdown = [
            5 => $ratingProgress->get(5, 0),
            4 => $ratingProgress->get(4, 0),
            3 => $ratingProgress->get(3, 0),
            2 => $ratingProgress->get(2, 0),
            1 => $ratingProgress->get(1, 0),
        ];

        $totalReviews = array_sum($ratingBreakdown);
        $ratingPercentages = [];
        foreach ($ratingBreakdown as $star => $count) {
            $ratingPercentages[$star] = $totalReviews > 0
                ? round(($count / $totalReviews) * 100, 2)
                : 0;
        }

        // Add full URL to all images
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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

        // Calculate inventory summary
        $inventorySummary = [
            'total_variants' => $product->variants->count(),
            'total_options' => $product->variants->sum('options_count'),
            'total_stock' => $product->variants->sum(function($variant) {
                return $variant->options->sum('stock');
            }),
            'out_of_stock' => $product->variants->sum(function($variant) {
                return $variant->options->where('stock', '<=', 0)->count();
            })
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'inventory' => $inventorySummary
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

            if ($product->main_image_2) {
                $product->main_image_2 = $this->getFullImageUrl($product->main_image_2);
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

    public function byCategory(Category $category, Request $request)
    {
        try {
            $perPage = $request->input('per_page', 12);

            $products = Product::with(['category', 'variants.color', 'variants.options.size'])
                ->where('category_id', $category->id)
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->latest()
                ->paginate($perPage);

            $products->each(function ($product) {
                $product->makeHidden('Purchase_price');
            });

            // Add full URL to all images
            $this->addFullImageUrls($products);

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image ? $this->getFullImageUrl($category->image) : null
                    ],
                    'products' => $products
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function couponsCheck(Request $request)
{
    $couponCode = $request->input('coupon');

    $coupon = Coupon::where('code', $couponCode)->first();

    if (!$coupon) {
        return response()->json([
            'success' => false,
            'message' => 'Coupon not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'amount' => $coupon->amount,
            'minimum_purchase' => $coupon->min_purchase,
            'start_date' => $coupon->start_date,
            'end_date' => $coupon->end_date,
            'is_active' => $coupon->is_active,
            // You can add this if you want to show current validity (without subtotal check)
            'is_currently_valid' => $coupon->is_active &&
                                 now()->between($coupon->start_date, $coupon->end_date)
        ]
    ]);
}

public function featured()
{
    $products = Product::where('is_featured', true)
        ->with(['category', 'images', 'variants'])
        ->latest()
        ->get();

    // Add full URL to all images
    $products->transform(function ($product) {
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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

    return response()->json([
        'status' => true,
        'data' => $products
    ]);
}

public function offer()
{
    $products = Product::where('is_offer', true)
        ->with(['category', 'images', 'variants'])
        ->latest()
        ->get();

    // Add full URL to all images
    $products->transform(function ($product) {
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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

    return response()->json([
        'status' => true,
        'data' => $products
    ]);
}

public function campaign()
{
    $products = Product::where('is_campaign', true)
        ->with(['category', 'images', 'variants'])
        ->latest()
        ->get();

    // Add full URL to all images
    $products->transform(function ($product) {
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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

    return response()->json([
        'status' => true,
        'data' => $products
    ]);
}

}
