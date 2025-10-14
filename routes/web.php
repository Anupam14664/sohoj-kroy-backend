<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CKEditorController;
use App\Http\Controllers\Admin\AdminSizeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminColorController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\CourierServiceController;
use App\Http\Controllers\Admin\GeneralSettingsController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\AdminDeliveryOptionController;

Route::get('/', function () {
    return redirect('/signin');
});

// Login/Logout Routes
Route::get('signin', [AuthController::class, 'index'])->name('admin.login');
Route::post('signin', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

// Protect Admin Routes
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

      // For profile route
      Route::get('/profile', [AuthController::class, 'profileEdit'])->name('admin.profile');
      Route::post('/profile', [AuthController::class, 'profileUpdate'])->name('admin.profile.update');

    // Categories
    Route::resource('categories', AdminCategoryController::class)->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'show' => 'admin.categories.show',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy'
    ]);

    //Homepage section
    Route::resource('homepage-sections', HomepageSectionController::class)->names([
        'index' => 'admin.homepage-sections.index',
        'create' => 'admin.homepage-sections.create',
        'store' => 'admin.homepage-sections.store',
        'show' => 'admin.homepage-sections.show',
        'edit' => 'admin.homepage-sections.edit',
        'update' => 'admin.homepage-sections.update',
        'destroy' => 'admin.homepage-sections.destroy'
    ]);

    // Products
    Route::resource('products', AdminProductController::class)->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'show' => 'admin.products.show',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy'
    ]);
    Route::delete('products/images/{image}', [AdminProductController::class, 'deleteImage'])->name('admin.products.images.destroy');
    Route::get('admin/products/variant', [AdminProductController::class, 'variant'])->name('admin.products.variant');

    Route::get('admin/products/option', [AdminProductController::class, 'option'])->name('admin.products.option');




    // Orders

    Route::get('orders/incomplete', [AdminOrderController::class, 'incompleteOrders'])->name('admin.orders.incomplete');
    Route::get('/orders/couriers', [AdminOrderController::class, 'shippedOrders'])->name('admin.orders.shipped');
    Route::resource('orders', AdminOrderController::class)->names([
       'index' => 'admin.orders.index',
       'create' => 'admin.orders.create',
        'store' => 'admin.orders.store',
        'show' => 'admin.orders.show',
        'edit' => 'admin.orders.edit',
        'update' => 'admin.orders.update',
        'destroy' => 'admin.orders.destroy',
    ]);
    Route::put('orders/{order}/update-items', [AdminOrderController::class, 'updateItems'])->name('admin.orders.update.items');
    Route::put('orders/{order}/update-delivery', [AdminOrderController::class, 'updateDeliveryCharge'])->name('admin.orders.update.delivery');
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::get('/orders/download/{order}', [AdminOrderController::class, 'download'])->name('admin.orders.download');
    Route::get('/orders/sku-search', [AdminOrderController::class, 'skuSearch'])->name('admin.orders.sku-search');
    Route::get('/product/search', [AdminOrderController::class, 'search'])->name('admin.product.search');
    Route::get('/product/{product}/variants', [AdminOrderController::class, 'getVariants']);
    Route::post('/orders/export', [AdminOrderController::class, 'export'])->name('admin.orders.export');
    Route::post('orders/bulk-delete', [AdminOrderController::class, 'bulkDelete'])->name('admin.orders.bulk-delete');
    Route::post('/orders/export-order', [AdminOrderController::class, 'exportOrder'])->name('admin.orders.export.order');





    Route::get('/customers', [AdminOrderController::class, 'customerList'])->name('admin.customers.index');
    Route::get('customers/orders/{phone}', [AdminOrderController::class, 'customerOrdersDetail'])
    ->name('admin.customers.orders_detail');


    Route::post('/customers/block', [AdminOrderController::class, 'blockCustomer'])->name('admin.customers.block');
    Route::post('/customers/unblock', [AdminOrderController::class, 'unblockCustomer'])->name('admin.customers.unblock');
    Route::get('/customers/blocked', [AdminOrderController::class, 'blockedCustomers'])->name('admin.customers.blocked');
    Route::post('/customers/export', [AdminOrderController::class, 'exportCustomers'])->name('admin.customers.export');


    // Coupons
    Route::resource('coupons', AdminCouponController::class)->names([
        'index' => 'admin.coupons.index',
        'create' => 'admin.coupons.create',
        'store' => 'admin.coupons.store',
        'edit' => 'admin.coupons.edit',
        'update' => 'admin.coupons.update',
        'destroy' => 'admin.coupons.destroy'
    ]);
    Route::get('coupons/search-products', [AdminCouponController::class, 'searchProducts'])
    ->name('admin.coupons.search-products');

    // Delivery Options
    Route::resource('delivery-options', AdminDeliveryOptionController::class)->names([
        'index' => 'admin.delivery-options.index',
        'create' => 'admin.delivery-options.create',
        'store' => 'admin.delivery-options.store',
        'show' => 'admin.delivery-options.show',
        'edit' => 'admin.delivery-options.edit',
        'update' => 'admin.delivery-options.update',
        'destroy' => 'admin.delivery-options.destroy'
    ]);

    Route::prefix('delivery-options')->group(function () {
        Route::get('/{deliveryOption}/manage-products', [AdminDeliveryOptionController::class, 'manageProducts'])
            ->name('admin.delivery-options.manage-products');

        Route::put('/{deliveryOption}/update-products', [AdminDeliveryOptionController::class, 'updateProducts'])
            ->name('admin.delivery-options.update-products');
    });

    // Colors
    Route::resource('colors', AdminColorController::class)->names([
        'index' => 'admin.colors.index',
        'create' => 'admin.colors.create',
        'store' => 'admin.colors.store',
        'show' => 'admin.colors.show',
        'edit' => 'admin.colors.edit',
        'update' => 'admin.colors.update',
        'destroy' => 'admin.colors.destroy'
    ]);

    // Sizes
    Route::resource('sizes', AdminSizeController::class)->names([
        'index' => 'admin.sizes.index',
        'create' => 'admin.sizes.create',
        'store' => 'admin.sizes.store',
        'show' => 'admin.sizes.show',
        'edit' => 'admin.sizes.edit',
        'update' => 'admin.sizes.update',
        'destroy' => 'admin.sizes.destroy'
    ]);

    // Reviews
    Route::resource('reviews', AdminReviewController::class)->names([
        'index' => 'admin.reviews.index',
        'create' => 'admin.reviews.create',
        'store' => 'admin.reviews.store',
        'show' => 'admin.reviews.show',
        'edit' => 'admin.reviews.edit',
        'update' => 'admin.reviews.update',
        'destroy' => 'admin.reviews.destroy'
    ]);
    Route::post('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('admin.reviews.approve');


    // General Settings
    Route::get('general-settings', [GeneralSettingsController::class, 'index'])->name('admin.general.settings');
    Route::post('general-settings', [GeneralSettingsController::class, 'update'])->name('admin.general.settings.update');
    Route::get('social-links', [GeneralSettingsController::class, 'socialLinks'])->name('admin.socials.links');


    // CKE Editor
    Route::post('ckeditor/upload', [CKEditorController::class, 'upload'])->name('ckeditor.upload');


    // couriers

    Route::resource('couriers', CourierServiceController::class)->names([
        'index' => 'admin.couriers.index',
        'create' => 'admin.couriers.create',
        'store' => 'admin.couriers.store',
        'show' => 'admin.couriers.show',
        'edit' => 'admin.couriers.edit',
        'update' => 'admin.couriers.update',
        'destroy' => 'admin.couriers.destroy'
    ]);;

    // banners
    Route::resource('banners', BannerController::class)->names([
        'index' => 'admin.banners.index',
        'create' => 'admin.banners.create',
         'store' => 'admin.banners.store',
         'show' => 'admin.banners.show',
         'edit' => 'admin.banners.edit',
         'update' => 'admin.banners.update',
         'destroy' => 'admin.banners.destroy',
     ]);



});
