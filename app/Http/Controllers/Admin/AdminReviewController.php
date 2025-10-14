<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product', 'images']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $reviews = $query->orderBy('is_approved')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        return view('admin.pages.reviews.index', compact('reviews'));
    }


    public function create()
    {
        $products = Product::all();
        return view('admin.pages.reviews.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'required|string|max:1000',
            'is_approved' => 'sometimes|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:6144',
            'images' => 'max:5'
        ]);

        try {
            $user = User::firstOrCreate(
                ['name' => $request->name],
                [
                    'mobile' => '01' . rand(3, 9) . rand(10000000, 99999999),
                    'password' => bcrypt('12345678')
                ]
            );

            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'rating' => $request->rating,
                'description' => $request->description,
                'is_approved' => $request->has('is_approved') ? $request->is_approved : false
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('review_images', 'public');
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image_path' => $path,
                    ]);
                }
            }

            return redirect()->route('admin.reviews.index')
                ->with('success', 'Review created successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create review: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function approve(Request $request, Review $review)
    {
        try {
            $review->update(['is_approved' => true]);

            return redirect()->route('admin.reviews.index')
                ->with('success', 'Review approved successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Failed to approve review: ' . $e->getMessage());
        }
    }

    public function destroy(Review $review)
    {
        try {
            // Delete associated images from storage
            foreach ($review->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            $review->delete();

            return redirect()->route('admin.reviews.index')
                ->with('success', 'Review deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Failed to delete review: ' . $e->getMessage());
        }
    }

}