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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->onDelete('cascade');

            $table->string('payment_gateway'); // midtrans, xendit, tripay
            $table->string('transaction_id')->unique(); // ID dari payment gateway
            $table->string('payment_method'); // qris, bank_transfer, gopay, ovo, dll
            $table->string('payment_channel')->nullable(); // Detail channel (BCA, BNI, dll)

            $table->decimal('amount', 15, 0);
            $table->enum('status', ['pending', 'settlement', 'expire', 'cancel', 'deny', 'refund'])->default('pending');

            // Payment Gateway Response
            $table->json('gateway_response')->nullable(); // Raw response dari gateway
            $table->string('payment_url')->nullable();
            $table->string('qr_url')->nullable(); // Untuk QRIS
            $table->string('va_number')->nullable(); // Untuk Virtual Account

            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
