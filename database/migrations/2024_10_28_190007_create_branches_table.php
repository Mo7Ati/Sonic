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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('address');
            $table->integer('delivery_time_from')->nullable();
            $table->integer('delivery_time_to')->nullable();

            $table->enum('status', ['busy', 'available', 'coming_soon', 'closed'])->default('available');
            $table->decimal('delivery_fee')->nullable();
            $table->boolean('is_active')->default(true);

            $table->json('range_of_area_polygon')->nullable();
            $table->json('location');

            $table->foreignId('store_id')->constrained('stores', 'id');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
