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
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained();
            $table->foreignId('option_id')->constrained();

            $table->boolean('is_available')->default(true);
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('quantity')->nullable();

            $table->unique(['product_id', 'option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
