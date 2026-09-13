<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bot Turnitin: pengecekan plagiasi dikerjakan skrip di Chrome admin lewat
 * submitin.id (paket Standard), lalu laporannya dikirim balik ke Phoenix.
 *
 * - exclude_kecocokan_kecil / ambang_kecocokan_kecil: pilihan "Exclude
 *   Matches" (kecocokan kurang dari N kata) yang kini bisa dipilih customer,
 *   karena submitin.id menyediakannya.
 * - dikerjakan_oleh: 'bot' | 'admin' — penanda di detail pesanan.
 * - bot_status: diambil → menunggu_hasil → selesai; atau gagal /
 *   perlu_dilengkapi / manual. NULL = belum pernah disentuh bot.
 * - bot_kode: kode order submitin (SC-…). Hasil hanya diterima bila kodenya
 *   cocok — inilah yang menjaga laporan tidak tertukar ke pesanan lain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->boolean('exclude_kecocokan_kecil')->default(false)->after('ambang_sumber_kecil');
            $table->unsignedSmallInteger('ambang_kecocokan_kecil')->nullable()->after('exclude_kecocokan_kecil');
            $table->string('dikerjakan_oleh', 10)->nullable()->after('status');
            $table->string('bot_status', 20)->nullable()->after('dikerjakan_oleh');
            $table->string('bot_kode', 40)->nullable()->after('bot_status');
            $table->string('bot_pesan', 500)->nullable()->after('bot_kode');
            $table->timestamp('bot_diambil_at')->nullable()->after('bot_pesan');
            $table->timestamp('bot_diperbarui_at')->nullable()->after('bot_diambil_at');
            $table->index('bot_status');
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropIndex(['bot_status']);
            $table->dropColumn([
                'exclude_kecocokan_kecil', 'ambang_kecocokan_kecil', 'dikerjakan_oleh',
                'bot_status', 'bot_kode', 'bot_pesan', 'bot_diambil_at', 'bot_diperbarui_at',
            ]);
        });
    }
};
