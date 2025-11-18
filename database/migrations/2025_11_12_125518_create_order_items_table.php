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
            $table->enum('duration_type', ['bulan', 'tahun']); // bulan atau tahun
            $table->integer('duration_value'); // 1, 5, 10 (bulan) atau 1 (tahun)
            $table->decimal('price', 15, 0); // Harga per item
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 15, 0); // price * quantity

            // Delivery Info (untuk produk digital)
            $table->boolean('is_delivered')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->text('credentials')->nullable(); // Encrypted username/password

            $table->timestamps();
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
