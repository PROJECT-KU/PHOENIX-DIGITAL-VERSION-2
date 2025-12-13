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
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');

            // Product Snapshot (simpan data saat pembelian)
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->string('product_image')->nullable();

            // Pricing Details
            $table->enum('duration_type', ['bulan', 'tahun'])->default('bulan'); // bulan atau tahun
            $table->integer('duration_value'); // 1, 5, 10 (bulan) atau 1 (tahun)
            $table->decimal('price', 15, 0); // Harga per item
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 15, 0); // price * quantity

            // data akun
            $table->foreignUuid('data_akun_id')->nullable()->constrained('data_akuns')->onDelete('set null');
            $table->string('account_username')->nullable();
            $table->text('account_password')->nullable();
            $table->string('account_link')->nullable();
            $table->text('account_notes')->nullable();

            // durasi akun
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('remaining_days')->nullable();

            // subscription status
            $table->enum('subscription_status', [
                'baru',
                'perpanjang',
                'pengganti',
                'habis',
            ])->default('baru');

            // Delivery Info (untuk produk digital)
            $table->boolean('is_delivered')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->enum('delivery_status', [
                'pending',
                'processing',
                'delivered',
                'cancelled',
            ])->default('pending');

            // pic
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa
            $table->index('delivery_status');
            $table->index('subscription_status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
