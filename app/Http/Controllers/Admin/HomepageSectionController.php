<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\HomepageSection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomepageSectionController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('position')->get();
        return view('admin.pages.homepagesection.index', compact('sections'));
    }

    public function edit(HomepageSection $homepageSection)
    {
        $categories = Category::whereNull('parent_id')->get();
        $selectedCategories = $homepageSection->categories->pluck('id')->toArray();

        return view('admin.pages.homepagesection.edit', compact('homepageSection', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, HomepageSection $homepageSection)
{
    // Validate the request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'is_active' => 'sometimes|boolean',
        'categories' => 'required|array|min:1',
        'categories.*' => 'exists:categories,id',
    ]);

    // Convert checkbox value to boolean
    $isActive = $request->has('is_active') ? true : false;

    // Update the section
    $homepageSection->update([
        'name' => $validated['name'],
        'is_active' => $isActive,
    ]);

    // Sync categories with their order
    $syncData = [];
    foreach ($validated['categories'] as $index => $categoryId) {
        $syncData[$categoryId] = ['order' => $index + 1];
    }

    $homepageSection->categories()->sync($syncData);

    return redirect()->route('admin.homepage-sections.index')
                   ->with('success', 'Homepage section updated successfully');
}
}