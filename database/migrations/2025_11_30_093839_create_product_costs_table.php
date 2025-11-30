<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('cost_type', ['Marketing Cost', 'Ads Cost', 'Others Cost'])->default('Others Cost');
            $table->decimal('amount', 10, 2);
            $table->decimal('product_buy_price', 10, 2)->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_costs');
    }
};
