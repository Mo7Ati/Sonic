<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('type'); // bop | palpay | jawwal_pay
            $table->string('beneficiary_name');
            $table->string('account_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_payment_methods');
    }
};
