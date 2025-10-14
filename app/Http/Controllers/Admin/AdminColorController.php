<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminColorController extends Controller
{
    public function index()
    {
        $colors = Color::withCount('products')->latest()->paginate(10);
        return view('admin.pages.colors.index', compact('colors'));
    }

    public function create()
    {
        return view('admin.pages.colors.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:colors,name',
            'code' => 'required|string|max:255|unique:colors,code',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Color::create($request->only(['name', 'code']));

        return redirect()->route('admin.colors.index')
            ->with('success', 'Color created successfully');
    }

    public function show(Color $color)
    {
        return view('admin.pages.colors.show', compact('color'));
    }

    public function edit(Color $color)
    {
        return view('admin.pages.colors.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:colors,name,'.$color->id,
            'code' => 'required|string|max:255|unique:colors,code,'.$color->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $color->update($request->only(['name', 'code']));

        return redirect()->route('admin.colors.index')
            ->with('success', 'Color updated successfully');
    }

    public function destroy(Color $color)
    {
        if ($color->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete color with associated products');
        }

        $color->delete();
        return redirect()->route('admin.colors.index')
            ->with('success', 'Color deleted successfully');
    }
}