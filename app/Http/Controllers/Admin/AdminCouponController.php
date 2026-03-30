<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AdminCouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(10);
        return view('admin.pages.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $products = Product::all();
        return view('admin.pages.coupons.create', compact('products'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'code' => 'required|unique:coupons|max:50',
        'type' => 'required|in:fixed,percentage',
        'amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'min_purchase' => 'nullable|numeric|min:0',
        'is_active' => 'sometimes|boolean',
        'apply_to_all' => 'sometimes|boolean',
        'products' => 'nullable|array',
        'products.*' => 'exists:products,id',
    ]);

    // Convert checkboxes to proper boolean values
    $validated['is_active'] = $request->has('is_active');
    $validated['apply_to_all'] = $request->has('apply_to_all');

    DB::beginTransaction();
    try {
        $coupon = Coupon::create($validated);

        if (!$validated['apply_to_all'] && isset($validated['products'])) {
            $coupon->products()->sync($validated['products']);
        }

        DB::commit();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Coupon creation failed: ' . $e->getMessage());
        return back()->withInput()
            ->with('error', 'Failed to create coupon. Please try again.');
    }
}

    public function edit(Coupon $coupon)
    {
        $products = Product::all();
        $selectedProducts = $coupon->products->pluck('id')->toArray();
        return view('admin.pages.coupons.edit', compact('coupon', 'products', 'selectedProducts'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code'          => 'required|max:50|unique:coupons,code,' . $coupon->id,
            'type'          => 'required|in:fixed,percentage',
            'amount'        => 'required|numeric|min:0',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'min_purchase'  => 'nullable|numeric|min:0',
            'products'      => 'nullable|array',
            'products.*'    => 'exists:products,id',
        ]);

        DB::beginTransaction();

        try {
            $coupon->code         = $validated['code'];
            $coupon->type         = $validated['type'];
            $coupon->amount       = $validated['amount'];
            $coupon->start_date   = $validated['start_date'];
            $coupon->end_date     = $validated['end_date'];
            $coupon->min_purchase = $validated['min_purchase'] ?? 0;
            $coupon->is_active    = $request->has('is_active');
            $coupon->apply_to_all = $request->boolean('apply_to_all');

            $coupon->save();

            if ($coupon->apply_to_all) {
                $coupon->products()->sync([]);
            } else {
                $coupon->products()->sync($request->input('products', []));
            }

            DB::commit();

            return redirect()
                ->route('admin.coupons.index')
                ->with('success', 'Coupon updated successfully');

        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('Coupon update failed', [
                'coupon_id' => $coupon->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update coupon. Please try again.');
        }
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->products()->detach();
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully');
    }
}
