<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AdminCategoryController extends Controller
{
    // Display paginated categories
    public function index()
    {
        $categories = Category::with(['parent', 'children'])
                            ->withCount('products')
                            ->paginate(10);

        return view('admin.pages.categories.index', compact('categories'));
    }

    // Show create form
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('admin.pages.categories.create', compact('parentCategories'));
    }

    // Store new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'public')
            : null;

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'],
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category created successfully');
    }

    // Show category details
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products']);
        return view('admin.pages.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
                                    ->where('id', '!=', $category->id)
                                    ->get();

        return view('admin.pages.categories.edit', compact('category', 'parentCategories'));
    }

    // Update category
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',
        ]);

        // Prevent self-parenting
        if ($validated['parent_id'] == $category->id) {
            return back()->with('error', 'Category cannot be its own parent');
        }

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'],
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
    }

    // Delete category
    public function destroy(Category $category)
    {
        // Check for products
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete category with associated products');
        }

        // Check for children
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete category with subcategories');
        }

        // Delete image
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category deleted successfully');
    }
}
