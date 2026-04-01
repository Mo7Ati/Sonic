<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid();

            $table->json('name');
            $table->string('slug');
            $table->json('description')->nullable();
            $table->json('keywords')->nullable();

            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->double('price');
            $table->double('compare_price')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_accepted')->default(false);

            $table->unsignedSmallInteger('quantity')->default(0);

            $table->unique(['store_id', 'slug']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
