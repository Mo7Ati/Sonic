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
        Schema::create('section_items', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('section_id')->constrained('sections');
            $table->json('data')->nullable();
            $table->integer('ordered')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('group_id')->nullable()->constrained('groups');
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->foreignId('store_category_id')->nullable()->constrained('store_categories');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_items');
    }
};
