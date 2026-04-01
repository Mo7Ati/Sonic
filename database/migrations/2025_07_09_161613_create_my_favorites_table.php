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
        Schema::create('my_favorites', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignId('customer_id')->constrained('customers');
            $table->unique(['model_type', 'model_id', 'customer_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_stores');
    }
};
