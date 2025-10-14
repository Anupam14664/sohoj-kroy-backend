<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOption;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminDeliveryOptionController extends Controller
{
    public function index()
    {
        $deliveryOptions = DeliveryOption::withCount('freeDeliveryProducts')->paginate(10);
        return view('admin.pages.delivery-options.index', compact('deliveryOptions'));
    }

    public function create()
    {
        return view('admin.pages.delivery-options.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_free_for_products' => 'boolean'
        ]);

        DeliveryOption::create($validated);

        return redirect()->route('admin.delivery-options.index')
            ->with('success', 'Delivery option created successfully.');
    }

    public function edit(DeliveryOption $deliveryOption)
    {
        return view('admin.pages.delivery-options.edit', compact('deliveryOption'));
    }

    public function update(Request $request, DeliveryOption $deliveryOption)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_free_for_products' => 'boolean'
        ]);

        $deliveryOption->update($validated);

        return redirect()->route('admin.delivery-options.index')
            ->with('success', 'Delivery option updated successfully.');
    }

    public function destroy(DeliveryOption $deliveryOption)
    {
        $deliveryOption->delete();

        return redirect()->route('admin.delivery-options.index')
            ->with('success', 'Delivery option deleted successfully.');
    }

    public function manageProducts(DeliveryOption $deliveryOption)
    {
        if (!$deliveryOption->is_free_for_products) {
            return redirect()->route('admin.delivery-options.index')
                ->with('error', 'This delivery option is not configured for product-specific free delivery.');
        }

        // Get all products with their variants and categories
        $allProducts = Product::with(['category', 'variants.options'])
            ->latest()
            ->get();

        $selectedProductIds = $deliveryOption->freeDeliveryProducts->pluck('id')->toArray();

        return view('admin.pages.delivery-options.manage-products', [
            'deliveryOption' => $deliveryOption,
            'allProducts' => $allProducts,
            'selectedProductIds' => $selectedProductIds
        ]);
    }

    public function updateProducts(Request $request, DeliveryOption $deliveryOption)
    {
        if (!$deliveryOption->is_free_for_products) {
            return redirect()->route('admin.delivery-options.index')
                ->with('error', 'This delivery option is not configured for product-specific free delivery.');
        }

        $request->validate([
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        $deliveryOption->freeDeliveryProducts()->sync($request->products ?? []);

        return redirect()->route('admin.delivery-options.index')
            ->with('success', 'Free delivery products updated successfully.');
    }
}