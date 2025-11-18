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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique(); // INV-20250112-0001
            $table->foreignUuid('customer_id')->constrained()->onDelete('cascade');

            // Order Info
            $table->decimal('subtotal', 15, 0);
            $table->decimal('total', 15, 0);
            $table->enum('status', ['pending', 'paid', 'processing', 'completed', 'cancelled'])->default('pending');

            // Payment Info
            $table->string('payment_method')->nullable(); // qris, bank_transfer, ewallet
            $table->string('payment_gateway')->nullable(); // midtrans, xendit, tripay
            $table->string('payment_reference')->nullable(); // transaction_id dari gateway
            $table->string('payment_url')->nullable(); // URL pembayaran
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable(); // Batas waktu pembayaran

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
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
