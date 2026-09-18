<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batal per item pada pesanan yang SUDAH dibayar.
 *
 * Total pesanan (omzet) tidak diubah; uang yang dikembalikan dicatat sebagai
 * pengeluaran (spendings) → refund_spending_id. delivery_status diisi
 * 'cancelled' (nilai yang sudah ada di ENUM sejak awal).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->timestamp('dibatalkan_at')->nullable()->after('diperpanjang_oleh_item_id');
            $table->unsignedBigInteger('dibatalkan_oleh')->nullable()->after('dibatalkan_at');
            $table->string('alasan_batal', 500)->nullable()->after('dibatalkan_oleh');
            $table->decimal('refund_nominal', 15, 0)->nullable()->after('alasan_batal');
            $table->string('refund_spending_id', 36)->nullable()->after('refund_nominal');
            $table->boolean('batal_setelah_kirim')->default(false)->after('refund_spending_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['dibatalkan_at', 'dibatalkan_oleh', 'alasan_batal', 'refund_nominal', 'refund_spending_id', 'batal_setelah_kirim']);
        });
    }
};
