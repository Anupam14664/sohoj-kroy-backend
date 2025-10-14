<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('page_type')->orderBy('order')->paginate(10);

        return view('admin.pages.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.pages.banners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'page_type' => 'required|in:home,offer,campaign',
            'position' => 'required_if:page_type,offer,campaign|in:left,right|nullable',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        // Debug: Check what's being validated
        Log::info('Validated data:', $validated);

        try {
            $imagePath = $request->file('image')->store('banners', 'public');

            $banner = Banner::create([
                'title' => $validated['title'],
                'image' => $imagePath,
                'page_type' => $validated['page_type'],
                'position' => $validated['position'] ?? null,
                'order' => $validated['order'] ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Banner created:', $banner->toArray());

            return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
        } catch (\Exception $e) {
            Log::error('Banner creation failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Banner creation failed: '.$e->getMessage());
        }
    }

    public function edit(Banner $banner)
    {
        return view('admin.pages.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'page_type' => 'required|in:home,offer,campaign',
            'position' => 'required_if:page_type,offer,campaign|in:left,right|nullable',
            'order' => 'nullable|integer',
        ]);

        $data = [
            'title' => $request->title,
            'page_type' => $request->page_type,
            'position' => $request->position,
            'order' => $request->order ?? 0,
        ];

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image);
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->image);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }
}