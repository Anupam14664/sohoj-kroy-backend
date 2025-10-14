<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->longText('google_tag_manager')->nullable()->after('favicon');
            $table->longText('domain_verification')->nullable()->after('google_tag_manager');
            $table->longText('header_scripts')->nullable()->after('domain_verification');
            $table->longText('footer_scripts')->nullable()->after('header_scripts');
            $table->string('messenger_url')->nullable();
            $table->string('whatsapp_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_tag_manager',
                'domain_verification',
                'header_scripts',
                'footer_scripts',
            ]);
        });
    }
};
