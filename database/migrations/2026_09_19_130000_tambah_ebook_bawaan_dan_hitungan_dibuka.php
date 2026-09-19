<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - products.ebook_bawaan_id: ebook yang otomatis tercentang saat memproses
 *   item produk ini (admin tetap bisa mengubahnya).
 * - ebooks.dibuka_count / terakhir_dibuka_at: berapa kali halaman baca
 *   dibuka pelanggan. Per ebook, bukan per pelanggan — satu ebook memakai
 *   satu tautan untuk semua penerima.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('ebook_bawaan_id', 36)->nullable()->index();
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->unsignedInteger('dibuka_count')->default(0);
            $table->timestamp('terakhir_dibuka_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['ebook_bawaan_id']);
            $table->dropColumn('ebook_bawaan_id');
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn(['dibuka_count', 'terakhir_dibuka_at']);
        });
    }
};
