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
        Schema::table('product_additions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['addition_id']);
            $table->dropPrimary(['product_id', 'addition_id']);
        });

        Schema::table('product_additions', function (Blueprint $table) {
            $table->id()->first();
            $table->unique(['product_id', 'addition_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('addition_id')->references('id')->on('additions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_additions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['addition_id']);
            $table->dropUnique(['product_id', 'addition_id']);
            $table->dropColumn('id');
        });

        Schema::table('product_additions', function (Blueprint $table) {
            $table->primary(['product_id', 'addition_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('addition_id')->references('id')->on('additions');
        });
    }
};
