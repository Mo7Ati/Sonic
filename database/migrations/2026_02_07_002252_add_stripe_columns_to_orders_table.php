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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_session_id')->nullable()->index()->after('notes');
            $table->uuid('checkout_group_id')->nullable()->index()->after('stripe_session_id');

            // Make address fields optional for checkout without address
            $table->foreignId('address_id')->nullable()->change();
            $table->json('address_data')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['stripe_session_id']);
            $table->dropIndex(['checkout_group_id']);
            $table->dropColumn(['stripe_session_id', 'checkout_group_id']);

            $table->foreignId('address_id')->nullable(false)->change();
            $table->json('address_data')->nullable(false)->change();
        });
    }
};
