<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Models\Product;
use App\Models\PageSection;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index()
    {
        $pages = Page::latest()->paginate(15);
        $generalSettings = GeneralSetting::first();
        $domain = $generalSettings->domain_url ?? config('app.url');
        return view('admin.pages.single-pages.index', compact('pages', 'domain'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
{
    $sectionTypes = $this->getSectionTypes();

    // Include image_url manually
    $products = Product::all()->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'discount_price' => $p->discount_price,
            'image_url' => $p->main_image
                            ? asset('storage/' . $p->main_image)
                            : null
        ];
    });

    return view('admin.pages.single-pages.create', compact('sectionTypes', 'products'));
}


    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'nullable|string|unique:pages,slug',
            'product_id' => 'nullable|exists:products,id',
            'sections_json' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $slug = $request->slug ?: Str::slug($request->name) . '-' . rand(100, 999);

            $page = Page::create([
                'name' => $request->name,
                'slug' => $slug,
                'product_id' => $request->product_id,
                'status' => $request->status ?? 'draft',
                'meta' => $request->meta ? json_decode($request->meta, true) : null,
            ]);

            $sections = json_decode($request->sections_json ?? '[]', true);

            foreach ($sections as $pos => $s) {
                PageSection::create([
                    'page_id' => $page->id,
                    'type' => $s['type'],
                    'position' => $pos,
                    'settings' => $s['settings'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.pages.index')->with('success', 'Page created');
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        $sectionTypes = $this->getSectionTypes();
        $products = Product::all()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'discount_price' => $p->discount_price,
                'image_url' => $p->main_image
                                ? asset('storage/' . $p->main_image)
                                : null
            ];
        });

        // Load sections with proper ordering
        $page->load(['sections' => function($query) {
            $query->orderBy('position', 'asc');
        }]);

        // Transform sections to ensure settings is decoded
        $page->sections->transform(function($section) {
            if (is_string($section->settings)) {
                $section->settings = json_decode($section->settings, true);
            }
            return $section;
        });

        return view('admin.pages.single-pages.edit', compact('page', 'sectionTypes', 'products'));
    }


    /**
     * Update the specified page in storage.
     */
public function update(Request $request, Page $page)
{
    $request->validate([
        'name' => 'required|string',
        'slug' => 'nullable|string|unique:pages,slug,' . $page->id,
        'product_id' => 'nullable|exists:products,id',
        'sections_json' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $page) {
        // Update page details
        $page->update([
            'name' => $request->name,
            'slug' => $request->slug ?: $page->slug,
            'product_id' => $request->product_id,
            'status' => $request->status ?? $page->status,
        ]);

        // Delete old sections
        $page->sections()->delete();

        // Parse and create new sections
        $sections = json_decode($request->sections_json ?? '[]', true);

        foreach ($sections as $pos => $s) {
            PageSection::create([
                'page_id' => $page->id,
                'type' => $s['type'] ?? '',
                'position' => $pos,
                'settings' => $s['settings'] ?? null,
            ]);
        }
    });

    return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully!');
}

    /**
     * Upload media for a page section.
     */
public function uploadMedia(Request $request)
{
    $request->validate([
        'file' => 'required|file|max:153600',
    ]);

    $file = $request->file('file');

    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

    $destination = public_path('uploads/section_media');

    if (!file_exists($destination)) {
        mkdir($destination, 0775, true);
    }

    $file->move($destination, $filename);

    $url = asset('uploads/section_media/' . $filename);
    return response()->json([
        'success' => true,
        'url' => $url,
    ]);
}



    /**
     * Return page data as JSON via API.
     */
    public function apiGet(Page $page)
    {
        $page->load('sections.media');
        return response()->json($page);
    }

    /**
     * Return available section types.
     */
    private function getSectionTypes()
    {
        return [
            'hero_banner' => 'Hero Product Banner',
            'product_video_slide' => 'Product Video Slide',
            'product_feature' => 'Product Feature',
            'premium_promotion' => 'Premium Product Promotion',
            'why_choose_us' => 'Why Choose Us',
            'featured_product' => 'Featured Product',
            'product_highlight' => 'Product Highlight',
            'certification' => 'Certification',
            'customer_review' => 'Customer Review',
            'order_form' => 'Order Form',
            'faq' => 'FAQ',
        ];
    }
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $products = Product::where('name', 'like', "%{$query}%")
                    ->limit(10)
                    ->get(['id', 'name']);

        return response()->json($products);
    }

        /**
     * Delete a page by ID
     */
    public function destroy($id)
    {
        $page = Page::find($id);

        if (!$page) {
            return redirect()->route('admin.pages.index')->with('error', 'Page not found');
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully');
    }



}
