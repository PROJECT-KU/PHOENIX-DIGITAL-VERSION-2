<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda "sudah dihubungi": tombol Balas WhatsApp tidak meninggalkan jejak,
 * jadi pengirim yang sama gampang diucapkan terima kasih dua kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            if (! Schema::hasColumn('testimonis', 'dihubungi_at')) {
                $table->timestamp('dihubungi_at')->nullable()->after('alasan_tolak');
            }
            if (! Schema::hasColumn('testimonis', 'dihubungi_oleh')) {
                $table->uuid('dihubungi_oleh')->nullable()->after('dihubungi_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            foreach (['dihubungi_at', 'dihubungi_oleh'] as $kolom) {
                if (Schema::hasColumn('testimonis', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
