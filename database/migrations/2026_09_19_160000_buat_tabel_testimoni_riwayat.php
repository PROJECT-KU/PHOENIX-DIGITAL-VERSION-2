<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak moderasi testimoni — mengikuti pola order_riwayat.
 *
 * Kolom ditinjau_at/ditinjau_oleh hanya menyimpan keputusan TERAKHIR; kalau
 * sebuah testimoni disetujui lalu ditolak lalu disetujui lagi, dua keputusan
 * sebelumnya hilang tanpa bekas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimoni_riwayat', function (Blueprint $table) {
            $table->id();
            $table->uuid('testimoni_id')->index();
            $table->uuid('user_id')->nullable();
            $table->string('aksi', 40);
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimoni_riwayat');
    }
};
