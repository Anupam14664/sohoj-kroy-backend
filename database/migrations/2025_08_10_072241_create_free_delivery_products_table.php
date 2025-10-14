<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('free_delivery_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_option_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['delivery_option_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('free_delivery_products');
    }
};