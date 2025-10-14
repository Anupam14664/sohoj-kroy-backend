<?php

use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\SizeController;
use App\Http\Controllers\API\ColorController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductSearchController;
use App\Http\Controllers\API\DeliveryOptionController;
use App\Http\Controllers\API\GeneralSettingsController;
use App\Http\Controllers\API\HomepageSectionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes

Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/categories/products/{category:slug}', [ProductController::class, 'byCategory']);


// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('products/campaign', [ProductController::class, 'campaign']);
Route::get('products/offer', [ProductController::class, 'offer']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/related', [ProductController::class, 'related']);
Route::post('/coupon-check', [ProductController::class, 'couponsCheck']);
Route::get('/products/search', [ProductSearchController::class, 'search']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);

Route::get('/homepage-sections', [HomepageSectionController::class, 'index']);
Route::get('/homepage-sections/{position}', [HomepageSectionController::class, 'show']);

// GeneralSetting
Route::get('/general-settings', [GeneralSettingsController::class, 'index']);

// Search
Route::get('/search', [SearchController::class, 'search']);


// Cart
Route::post('/cart', [CartController::class, 'store']);
Route::get('/cart/{session_id}', [CartController::class, 'show']);
Route::post('/cart/{session_id}/items', [CartController::class, 'addItem']);
Route::put('/cart/{session_id}/items/{item_id}', [CartController::class, 'updateItem']);
Route::delete('/cart/{session_id}/items/{item_id}', [CartController::class, 'removeItem']);

// Order
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show']);

Route::post('/orders/incomplete', [OrderController::class, 'incomplete']);
Route::get('/orders/incomplete', [OrderController::class, 'incompleteOrders']);
// Route::get('/orders/incomplete/{order}', [OrderController::class, 'showIncomplete']);

// Coupons
Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

// Reviews

Route::get('/products/reviews/{product:slug}', [ReviewController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products/reviews/{product:slug}', [ReviewController::class, 'store']);
    // Route::post('/products/reviews/{product:slug}', [ReviewController::class, 'store']);
});

// Delivery Options
Route::get('/delivery-options', [DeliveryOptionController::class, 'index']);

// Colors
Route::get('/colors', [ColorController::class, 'index']);

// Sizes
Route::get('/sizes', [SizeController::class, 'index']);

// banner
Route::get('banners/home', [BannerController::class, 'homeBanners']);
Route::get('banners/offer', [BannerController::class, 'offerBanners']);
Route::get('banners/campaign', [BannerController::class, 'campaignBanners']);

});
