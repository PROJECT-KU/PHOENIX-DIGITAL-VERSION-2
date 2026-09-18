<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautan akun lama → item pesanan perpanjangannya.
 *
 * Diisi saat pesanan dibuat lewat tombol "Perpanjang". Akun yang sudah
 * diperpanjang tidak lagi muncul di tab Segera Habis / Akun Habis, supaya
 * pelanggan yang sudah membayar tidak diingatkan lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('diperpanjang_oleh_item_id', 36)->nullable()->after('ingat_perpanjang_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['diperpanjang_oleh_item_id']);
            $table->dropColumn('diperpanjang_oleh_item_id');
        });
    }
};
