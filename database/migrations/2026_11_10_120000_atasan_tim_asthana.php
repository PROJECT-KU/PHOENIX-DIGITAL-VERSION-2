<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Struktur organisasi di halaman Tentang situs Asthana: tiap anggota tim bisa
 * punya atasan langsung. Murni tambahan; kosong = berada di puncak bagan.
 * Atasan yang dihapus → bawahannya naik ke puncak (nullOnDelete).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asthana_tim', function (Blueprint $t) {
            $t->foreignId('atasan_id')->nullable()->after('linkedin')
                ->constrained('asthana_tim')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asthana_tim', function (Blueprint $t) {
            $t->dropConstrainedForeignId('atasan_id');
        });
    }
};
