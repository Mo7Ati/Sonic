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
        Schema::create('category_stores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('category_id')->constrained('store_categories');

            $table->unique(['store_id', 'category_id']);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_stores');
    }
};
