<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('delivery_options', function (Blueprint $table) {
            $table->boolean('is_free_for_products')->default(false)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('delivery_options', function (Blueprint $table) {
            $table->dropColumn('is_free_for_products');
        });
    }
};