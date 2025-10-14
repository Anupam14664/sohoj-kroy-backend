<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('position')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('homepage_section_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Create default sections after tables are created
        DB::table('homepage_sections')->insert([
            ['name' => 'Home Section 1', 'position' => 1, 'is_active' => true],
            ['name' => 'Home Section 2', 'position' => 2, 'is_active' => true],
            ['name' => 'Home Section 3', 'position' => 3, 'is_active' => true],
            ['name' => 'Home Section 4', 'position' => 4, 'is_active' => true],
            ['name' => 'Home Section 5', 'position' => 5, 'is_active' => true],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('homepage_section_category');
        Schema::dropIfExists('homepage_sections');
    }
};