<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat versi, pengalihan alamat lama, penguncian penyuntingan, berhenti
 * tayang terjadwal, dan kata kunci fokus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Artikel promo yang kedaluwarsa berhenti tayang sendiri.
            $table->timestamp('unpublish_at')->nullable()->after('published_at');

            // Kata kunci yang ingin dimenangkan artikel ini.
            $table->string('focus_keyword', 80)->nullable()->after('meta_description');

            // Dipakai pemangkas draf terbengkalai. Bawaannya TRUE supaya
            // artikel yang sudah ada sekarang tidak pernah ikut terpangkas;
            // hanya draf hasil simpan otomatis yang ditandai false.
            $table->boolean('disimpan_manual')->default(true)->after('is_featured');

            // Penanda "sedang dibuka" — supaya dua admin tidak saling menimpa.
            $table->unsignedBigInteger('dibuka_oleh')->nullable()->after('updated_by');
            $table->timestamp('dibuka_pada')->nullable()->after('dibuka_oleh');
        });

        // Alamat lama tetap hidup setelah slug diubah. Tanpa ini setiap
        // perubahan slug mematikan tautan yang sudah beredar.
        Schema::create('blog_post_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('slug_lama', 200)->unique();
            $table->unsignedBigInteger('blog_post_id');
            $table->timestamps();

            $table->index('blog_post_id');
        });

        // Riwayat isi. Disimpan SEBELUM perubahan ditulis, jadi baris terbaru
        // adalah keadaan sesaat sebelum penyimpanan terakhir.
        Schema::create('blog_post_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_post_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title', 200);
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->timestamps();

            $table->index(['blog_post_id', 'id']);
        });

        // SQLite (dipakai uji) tidak bisa menambah foreign key lewat ALTER.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('blog_post_redirects', function (Blueprint $table) {
                $table->foreign('blog_post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            });
            Schema::table('blog_post_revisions', function (Blueprint $table) {
                $table->foreign('blog_post_id')->references('id')->on('blog_posts')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_revisions');
        Schema::dropIfExists('blog_post_redirects');

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['unpublish_at', 'focus_keyword', 'disimpan_manual', 'dibuka_oleh', 'dibuka_pada']);
        });
    }
};
