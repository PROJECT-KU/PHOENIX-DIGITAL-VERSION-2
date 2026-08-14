<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penghubung promo <-> paket bundling, KHUSUS untuk ETALASE.
 *
 * Gunanya supaya paket seperti "Bundling Maulid Nabi" bisa ikut TAMPIL di
 * bagian flash sale beranda, berdampingan dengan produk yang didiskon.
 *
 * PENTING — ini BUKAN untuk memberi diskon. Harga paket sudah harga promo
 * (kolom `harga_bundling`), jadi memotongnya lagi dengan flash sale berarti
 * diskon dua kali. Pencocokan diskon di PromoService sengaja tetap hanya
 * mengenal `promo_product`, dan item bundling justru dikecualikan dari sana.
 *
 * Dibuat sebagai tabel TERPISAH, bukan menambah kolom di `promo_product`:
 * tabel itu punya kunci unik (promo_id, product_id) dengan foreign key ke
 * `products`, sehingga id bundling tidak mungkin dititipkan di sana tanpa
 * merusak keterkaitannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_product_bundling', function (Blueprint $table) {
            $table->foreignUuid('promo_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('product_bundling_id')
                ->constrained('product_bundlings')
                ->onDelete('cascade');
            $table->timestamps();

            // Satu paket tidak boleh dilampirkan dua kali ke promo yang sama.
            $table->unique(['promo_id', 'product_bundling_id'], 'promo_bundling_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_product_bundling');
    }
};
