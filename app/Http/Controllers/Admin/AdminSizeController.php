<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminSizeController extends Controller
{
    public function index()
    {
        $sizes = Size::withCount('products')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10);

        return view('admin.pages.sizes.index', compact('sizes'));
    }

    public function create()
    {
        $sizeTypes = [
            'numeric' => 'Numeric (e.g., 36, 38, 40)',
            'text' => 'Text (e.g., S, M, L, XL)'
        ];

        return view('admin.pages.sizes.create', compact('sizeTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sizes,name',
            'type' => 'required|in:numeric,text',
            'display_name' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // For numeric sizes, validate the name is numeric
        if ($request->type === 'numeric' && !is_numeric($request->name)) {
            return redirect()->back()
                ->with('error', 'Numeric sizes must contain only numbers')
                ->withInput();
        }

        Size::create([
            'name' => $request->name,
            'type' => $request->type,
            'display_name' => $request->display_name,
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size created successfully');
    }

    public function show(Size $size)
    {
        $size->load('products');
        return view('admin.pages.sizes.show', compact('size'));
    }

    public function edit(Size $size)
    {
        $sizeTypes = [
            'numeric' => 'Numeric (e.g., 36, 38, 40)',
            'text' => 'Text (e.g., S, M, L, XL)'
        ];

        return view('admin.pages.sizes.edit', compact('size', 'sizeTypes'));
    }

    public function update(Request $request, Size $size)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sizes,name,'.$size->id,
            'type' => 'required|in:numeric,text',
            'display_name' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // For numeric sizes, validate the name is numeric
        if ($request->type === 'numeric' && !is_numeric($request->name)) {
            return redirect()->back()
                ->with('error', 'Numeric sizes must contain only numbers')
                ->withInput();
        }

        $size->update([
            'name' => $request->name,
            'type' => $request->type,
            'display_name' => $request->display_name,
            'sort_order' => $request->sort_order ?? $size->sort_order
        ]);

        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size updated successfully');
    }

    public function destroy(Size $size)
    {
        if ($size->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete size with associated products');
        }

        $size->delete();
        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size deleted successfully');
    }
}