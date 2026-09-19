<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Testimoni: kendali tampil di beranda + jejak moderasi.
 *
 * Sebelumnya beranda memakai 9 testimoni TERBARU dan admin tidak punya cara
 * memilih; testimoni bagus yang lama otomatis tenggelam. `sorot`/`urutan`
 * memberi kendali itu. `ditinjau_*` & `alasan_tolak` mengembalikan jejak
 * moderasi yang sempat hilang saat kolom ditinjau_at dibuang (Jul 2026).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            if (! Schema::hasColumn('testimonis', 'sorot')) {
                $table->boolean('sorot')->default(false)->after('status');
            }
            if (! Schema::hasColumn('testimonis', 'urutan')) {
                $table->unsignedInteger('urutan')->default(0)->after('sorot');
            }
            if (! Schema::hasColumn('testimonis', 'ditinjau_at')) {
                $table->timestamp('ditinjau_at')->nullable()->after('urutan');
            }
            if (! Schema::hasColumn('testimonis', 'ditinjau_oleh')) {
                $table->uuid('ditinjau_oleh')->nullable()->after('ditinjau_at');
            }
            if (! Schema::hasColumn('testimonis', 'alasan_tolak')) {
                $table->string('alasan_tolak', 191)->nullable()->after('ditinjau_oleh');
            }
            if (! Schema::hasColumn('testimonis', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            foreach (['sorot', 'urutan', 'ditinjau_at', 'ditinjau_oleh', 'alasan_tolak', 'deleted_at'] as $kolom) {
                if (Schema::hasColumn('testimonis', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
