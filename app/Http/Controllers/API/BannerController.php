<?php

namespace App\Http\Controllers\API;

use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    public function homeBanners()
    {
        $banners = Banner::where('page_type', 'home')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }

    public function offerBanners()
    {
        $banners = Banner::where('page_type', 'offer')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->groupBy('position');

        return response()->json([
            'success' => true,
            'data' => [
                'left' => $banners['left'] ?? [],
                'right' => $banners['right'] ?? [],
            ]
        ]);
    }

    public function campaignBanners()
    {
        $banners = Banner::where('page_type', 'campaign')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->groupBy('position');

        return response()->json([
            'success' => true,
            'data' => [
                'left' => $banners['left'] ?? [],
                'right' => $banners['right'] ?? [],
            ]
        ]);
    }
}