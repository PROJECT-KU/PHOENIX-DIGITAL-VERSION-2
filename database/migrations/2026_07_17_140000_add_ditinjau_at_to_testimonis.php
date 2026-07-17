<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            // Penanda "sudah ditinjau admin" — TERPISAH dari status aktif/tidak.
            //
            // Kenapa tidak cukup pakai status: 'non-active' punya dua arti yang
            // tertukar — (1) baru masuk, belum ditinjau, dan (2) sudah ditinjau
            // lalu sengaja ditolak. Kalau badge memakai status, testimoni yang
            // ditolak akan terhitung selamanya dan badge tak pernah bisa habis.
            $table->timestamp('ditinjau_at')->nullable()->after('source');
        });

        // Testimoni yang sudah ada dianggap SUDAH ditinjau, supaya badge mulai
        // dari nol dan hanya kiriman pelanggan yang baru yang memunculkannya.
        DB::table('testimonis')->update(['ditinjau_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            $table->dropColumn('ditinjau_at');
        });
    }
};
