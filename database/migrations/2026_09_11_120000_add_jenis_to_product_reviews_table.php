<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ulasan kini bisa untuk produk satuan ATAU paket bundling.
 *
 * Aditif: kolom product_id tetap menyimpan id targetnya, dan `jenis`
 * membedakan tabelnya ('produk' → products, 'paket' → product_bundlings).
 * Nilai bawaan 'produk' membuat SEMUA ulasan lama tetap ulasan produk tanpa
 * satu baris data pun disentuh.
 *
 * SQL setara untuk server (hPanel, dijalankan manual):
 *   ALTER TABLE product_reviews ADD jenis VARCHAR(10) NOT NULL DEFAULT 'produk' AFTER product_id;
 *   CREATE INDEX product_reviews_jenis_product_id_status_index ON product_reviews (jenis, product_id, status);
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->string('jenis', 10)->default('produk')->after('product_id');
            $table->index(['jenis', 'product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['jenis', 'product_id', 'status']);
            $table->dropColumn('jenis');
        });
    }
};
