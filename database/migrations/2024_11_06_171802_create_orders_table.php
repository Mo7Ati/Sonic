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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pending', 'preparing', 'on_the_way', 'completed', 'cancelled', 'rejected'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->string('cancelled_reason')->nullable();

            $table->foreignId('customer_id')->constrained('customers');
            $table->json('customer_data');

            $table->foreignId('address_id')->constrained('addresses');
            $table->json('address_data');

            $table->foreignId('store_id')->constrained('stores');

            $table->double('total');
            $table->double('total_items_amount');
            $table->double('delivery_amount')->default(0);
            $table->double('tax_amount')->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
