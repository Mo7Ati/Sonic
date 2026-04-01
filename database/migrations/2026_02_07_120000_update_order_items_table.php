<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aligns order_items with options/additions, total_price, soft deletes and timestamps.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'options_amount')) {
                $table->decimal('options_amount', 10, 2)->default(0)->after('product_data');
            }
            if (!Schema::hasColumn('order_items', 'options_data')) {
                $table->json('options_data')->nullable()->after('options_amount');
            }
            if (!Schema::hasColumn('order_items', 'additions_amount')) {
                $table->decimal('additions_amount', 10, 2)->default(0)->after('options_data');
            }
            if (!Schema::hasColumn('order_items', 'additions_data')) {
                $table->json('additions_data')->nullable()->after('additions_amount');
            }
            if (!Schema::hasColumn('order_items', 'total_price')) {
                $table->decimal('total_price', 12, 2)->after('unit_price');
            }
            if (!Schema::hasColumn('order_items', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('order_items', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('order_items', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = [
            'options_amount', 'options_data', 'additions_amount', 'additions_data',
            'total_price', 'deleted_at', 'created_at', 'updated_at',
        ];
        Schema::table('order_items', function (Blueprint $table) use ($columnsToDrop) {
            $existing = array_filter($columnsToDrop, fn ($col) => Schema::hasColumn('order_items', $col));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
