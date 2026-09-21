<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perluasan modul artikel: tong sampah, tag, alt sampul, penanda unggulan,
 * jejak penyunting, deskripsi kategori, dan hitungan baca harian.
 *
 * Semua kolom nullable / bernilai bawaan supaya artikel lama tetap sah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Tong sampah: hapus artikel tidak lagi permanen.
            $table->softDeletes();

            // Satu artikel bisa masuk beberapa topik; kategori tetap satu
            // karena halaman publik mengelompokkan menurut kategori.
            $table->json('tags')->nullable()->after('category');

            // Teks pengganti gambar — dibaca pembaca layar dan mesin pencari.
            $table->string('cover_alt', 180)->nullable()->after('cover');

            // Artikel yang disematkan di atas daftar blog publik.
            $table->boolean('is_featured')->default(false)->after('views');

            // Siapa yang terakhir mengubah. BUKAN untuk ditampilkan publik —
            // kolom 'author' tetap statis 'admin' supaya nama karyawan tidak
            // pernah bocor ke halaman yang dibaca pengunjung.
            $table->unsignedBigInteger('updated_by')->nullable()->after('author');
        });

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->after('slug');
        });

        // Hitungan baca per hari. Tanpa ini "paling dibaca" hanya bisa
        // menjawab sepanjang masa, sehingga artikel lama selalu menang dan
        // tulisan yang sedang ramai tidak pernah kelihatan.
        Schema::create('blog_post_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_post_id');
            $table->date('tanggal');
            $table->unsignedInteger('jumlah')->default(0);
            $table->timestamps();

            $table->unique(['blog_post_id', 'tanggal']);
            $table->index('tanggal');
        });

        // SQLite (dipakai uji) tidak bisa menambah foreign key lewat ALTER.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('blog_post_reads', function (Blueprint $table) {
                $table->foreign('blog_post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_reads');

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['tags', 'cover_alt', 'is_featured', 'updated_by']);
        });
    }
};
