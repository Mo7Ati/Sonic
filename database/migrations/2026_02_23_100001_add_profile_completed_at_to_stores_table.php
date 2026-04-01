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
        Schema::table('stores', function (Blueprint $table) {
            $table->timestamp('profile_completed_at')->nullable()->after('is_active');

            $table->json('address')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->integer('delivery_time')->default(30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('profile_completed_at');

            $table->json('address')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->integer('delivery_time')->nullable(false)->change();
        });
    }
};
