<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda "catatan pelanggan sudah ditangani".
 *
 * Catatan pelanggan adalah bagian data pesanan (mis. akun tujuan pesanan
 * kredit), jadi tidak dihapus — hanya ditandai supaya tidak lagi muncul
 * sebagai catatan yang perlu ditindaklanjuti di daftar pesanan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('catatan_ditangani_at')->nullable()->after('admin_notes');
            $table->unsignedBigInteger('catatan_ditangani_oleh')->nullable()->after('catatan_ditangani_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['catatan_ditangani_at', 'catatan_ditangani_oleh']);
        });
    }
};
