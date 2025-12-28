<?php

namespace App\Http\Controllers\API;

use App\Models\Page;
use Illuminate\Http\Request;
use App\Models\DeliveryOption;
use App\Models\GeneralSetting;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    /**
     * Show all pages with sections, media, and product
     */
    public function index()
    {
        $pages = Page::select('id', 'name', 'slug', 'status')->where('status', 1)->get();
            return response()->json([
                'success' => true,
                'data' => $pages
            ]);
    }

    /**
     * Show single page details by ID or slug
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 1)
            ->with([
                'product',
                'sections' => function($q) {
                    $q->orderBy('position');
                }
            ])
            ->first();

        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        $generalSetting = GeneralSetting::first();
            $headerData = [
                'app_name' => $generalSetting->app_name ?? null,
                'logo' => $generalSetting && $generalSetting->logo ? asset('storage/' . $generalSetting->logo) : null,
                'contact_number_1' => $generalSetting->contact_number_1 ?? null,
                'contact_number_2' => $generalSetting->contact_number_2 ?? null,
                'whatsapp' => $generalSetting->whatsapp_url ?? null,
                'messenger' => $generalSetting->messenger_url ?? null,
                'facebook_url' => $generalSetting->messenger_url ?? null,
            ];

        $deliveryOptions = [];

        /* -----------------------------------------------------------
        | Load Full Product With Variants + Sizes + Colors + Gallery
        ------------------------------------------------------------*/
        if ($page->product) {

            $product = $page->product;

            $product->load([
                'category',
                'images',
                'variants.color',
                'variants.options.size',
                'variants' => function ($q) {
                    $q->withCount('options');
                },
                // 'freeDeliveryOptions',
                // 'reviews' => function ($q) {
                //     $q->where('is_approved', true);
                // }
            ]);

            // Remove internal fields
            $product->makeHidden(['buy_price']);

            // ---------- Fix Product Main Image ----------
            if ($product->main_image) {
                $product->main_image = asset('storage/' . $product->main_image);
            }

            // ---------- Fix Product Gallery ----------
            if ($product->images) {
                $product->images->transform(function ($image) {
                    $image->image_path = asset('storage/' . $image->image_path);
                    return $image;
                });
            }

            // ---------- Fix Category Image ----------
            // if ($product->category && $product->category->image) {
            //     $product->category->image = asset('storage/' . $product->category->image);
            // }

            // ---------- Fix Variant Image ----------
            if ($product->variants) {
                $product->variants->transform(function ($variant) {
                    if ($variant->image) {
                        $variant->image = asset('storage/' . $variant->image);
                    }

                    // Fix option data
                    if ($variant->options) {
                        $variant->options->transform(function ($opt) {
                            return $opt;
                        });
                    }

                    return $variant;
                });
            }

            // ---------- Rating Breakdown ----------
            // $ratingCount = $product->reviews->count();
            // $ratingAvg = round($product->reviews->avg('rating'), 2);

            // $ratingBreakdown = [
            //     5 => $product->reviews->where('rating', 5)->count(),
            //     4 => $product->reviews->where('rating', 4)->count(),
            //     3 => $product->reviews->where('rating', 3)->count(),
            //     2 => $product->reviews->where('rating', 2)->count(),
            //     1 => $product->reviews->where('rating', 1)->count(),
            // ];

            // ---------- Delivery Options ----------
            $freeDelivery = DeliveryOption::whereHas('freeDeliveryProducts', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->where('is_free_for_products', true)
                ->get();

            if ($freeDelivery->isNotEmpty()) {
                $deliveryOptions = $freeDelivery;
            } else {
                $deliveryOptions = DeliveryOption::where('is_active', true)
                    ->where('is_free_for_products', false)
                    ->get();
            }

            // Attach important data
            // $page->product->rating = [
            //     'avg' => $ratingAvg,
            //     'total' => $ratingCount,
            //     'breakdown' => $ratingBreakdown
            // ];

            // Inventory Summary
            $page->product->inventory = [
                'total_variants' => $product->variants->count(),
                'total_options' => $product->variants->sum('options_count'),
                'total_stock' => $product->variants->sum(fn($v) => $v->options->sum('stock')),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'header' => $headerData,
                'page' => $page,
                'delivery_options' => $deliveryOptions
            ]
        ]);
    }


}
