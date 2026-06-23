<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite cannot drop a composite primary key or add an autoincrement
        // primary-key column via ALTER, so rebuild the table to the same final
        // shape. In a fresh migration run the table is empty, so no data copy
        // is needed; other drivers keep the original in-place alteration.
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::dropIfExists('product_additions');

            Schema::create('product_additions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained();
                $table->foreignId('addition_id')->constrained();
                $table->decimal('price', 10, 2);
                $table->unique(['product_id', 'addition_id']);
            });

            return;
        }

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
