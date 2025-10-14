<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $settings = GeneralSetting::first();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Settings not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => $settings->app_name,
                'logo' => $settings->logo ? asset('storage/' . $settings->logo) : null,
                'favicon' => $settings->favicon ? asset('storage/' . $settings->favicon) : null,
                'social_links' => [
                    'facebook' => $settings->facebook_url,
                    'twitter' => $settings->twitter_url,
                    'instagram' => $settings->instagram_url,
                    'youtube' => $settings->youtube_url,
                    'linkedin' => $settings->linkedin_url,
                    'tiktok' => $settings->tiktok_url,
                    'messenger' => $settings->messenger_url,
                    'whatsapp' => $settings->whatsapp_url,
                ],
                'contact_numbers' => [
                    'primary' => $settings->contact_number_1,
                    'secondary' => $settings->contact_number_2,
                ],
                'google_tag_manager' => $settings->google_tag_manager,
                'domain_verification' => $settings->domain_verification,
                'header_scripts' => $settings->header_scripts,
                'footer_scripts' => $settings->footer_scripts,
                'footer_content' => $settings->footer_content,
                ]
        ]);
    }
}
