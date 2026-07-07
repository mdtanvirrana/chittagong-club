<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('successful_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->unique()->constrained('payment_transactions')->cascadeOnDelete();
            $table->string('transaction_id', 64)->unique();
            $table->string('member_id', 64)->index();
            $table->string('member_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('BDT');
            $table->string('validation_id', 128)->nullable()->index();
            $table->string('bank_transaction_id', 128)->nullable()->index();
            $table->string('card_type', 128)->nullable();
            $table->decimal('store_amount', 12, 2)->nullable();
            $table->text('note')->nullable();
            $table->longText('validation_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('successful_payment_transactions');
    }
};
