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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('address');
            $table->json('description')->nullable();
            $table->json('keywords')->nullable();

            $table->json('social_media')->nullable();

            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->foreignId('category_id')->nullable()->constrained('store_categories');

            $table->integer('delivery_time');
            $table->json('delivery_area_polygon')->nullable();
            $table->boolean('is_active')->default(true);


            $table->text('two_factor_secret')
                ->nullable();
            $table->text('two_factor_recovery_codes')
                ->nullable();
            $table->timestamp('two_factor_confirmed_at')
                ->nullable();

            $table->rememberToken();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
