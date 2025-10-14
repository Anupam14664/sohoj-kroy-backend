<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('courier_service_id')->nullable()->constrained('courier_services')->nullOnDelete();
            $table->string('tracking_code')->nullable();
            $table->string('consignment_id')->nullable();
            $table->json('courier_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropForeign(['merchant_courier_id']);
            $table->dropColumn(['courier_id', 'merchant_courier_id', 'tracking_code', 'consignment_id']);
        });
    }
};
