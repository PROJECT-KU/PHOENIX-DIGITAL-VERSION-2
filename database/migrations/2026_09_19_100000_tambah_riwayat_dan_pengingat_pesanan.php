<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat pesanan toko + penanda pengingat perpanjangan.
 *
 * - order_riwayat: jejak siapa melakukan apa pada satu pesanan (dibuat,
 *   status berubah, akun dikirim, hasil jasa diunggah, …). Hanya ditambah,
 *   tidak pernah diubah.
 * - order_items.ingat_perpanjang_at: kapan pelanggan diingatkan bahwa
 *   akunnya segera habis (tab "Segera Habis"). Pasangan habis_notified_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_riwayat', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 36)->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('aksi', 40);
            $table->string('keterangan', 500)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->timestamp('ingat_perpanjang_at')->nullable()->after('habis_notified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_riwayat');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('ingat_perpanjang_at');
        });
    }
};
