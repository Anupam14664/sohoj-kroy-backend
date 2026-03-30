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
        Schema::table('courier_services', function (Blueprint $table) {
            $table->enum('type', ['pathao', 'steadfast'])->after('name');
            $table->unsignedBigInteger('store_id')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courier_services', function (Blueprint $table) {
            //
        });
    }
};
