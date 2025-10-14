<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'logo',
        'favicon',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'linkedin_url',
        'tiktok_url',
        'messenger_url',
        'whatsapp_url',
        'contact_number_1',
        'contact_number_2',
        'google_tag_manager',
        'domain_verification',
        'header_scripts',
        'footer_scripts',
        'footer_content',
    ];
}
