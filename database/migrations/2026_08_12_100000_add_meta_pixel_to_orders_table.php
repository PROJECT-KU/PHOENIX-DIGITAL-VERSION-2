<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom penunjang pengiriman Pembelian (Purchase) ke Meta Conversions API.
 *
 * Semuanya nullable dan tidak dibaca alur pesanan mana pun — pesanan lama tetap
 * berjalan apa adanya dengan ketiganya kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Penjaga kirim-sekali. Pesanan berpindah paid -> processing ->
            // completed, dan tanpa penanda ini tiap perpindahan akan mengirim
            // Pembelian lagi. Dedup event_id milik Meta hanya berlaku 48 jam,
            // jadi pesanan yang baru diselesaikan berhari-hari kemudian akan
            // terhitung dua kali di laporan iklan.
            $table->timestamp('meta_purchase_sent_at')->nullable()->after('paid_at');

            // Cookie Meta milik browser pembeli, direkam saat checkout.
            //
            // Harus disimpan karena pembelian dikirim dari SERVER: konfirmasi
            // pembayaran datang dari Midtrans/cron QRIS, yang tidak membawa
            // cookie pengunjung sama sekali. Tanpa keduanya, Meta kehilangan
            // jejak klik iklan yang membawa pembeli itu.
            $table->string('fbp')->nullable()->after('meta_purchase_sent_at');
            $table->string('fbc')->nullable()->after('fbp');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['meta_purchase_sent_at', 'fbp', 'fbc']);
        });
    }
};
