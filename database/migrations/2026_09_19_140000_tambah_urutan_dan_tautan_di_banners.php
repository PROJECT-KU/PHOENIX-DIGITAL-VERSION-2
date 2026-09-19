<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - urutan: posisi slide di beranda (kecil = lebih dulu). Diisi sesuai urutan
 *   dibuat supaya beranda tampil PERSIS seperti sebelum migrasi.
 * - tautan: tujuan klik banner & tombol utama. Kosong = halaman Belanja
 *   (perilaku lama).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->default(0)->after('status');
            $table->string('tautan', 500)->nullable()->after('deskripsi');
        });

        $n = 0;
        foreach (DB::table('banners')->orderBy('created_at')->orderBy('id')->pluck('id') as $id) {
            DB::table('banners')->where('id', $id)->update(['urutan' => ++$n]);
        }
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['urutan', 'tautan']);
        });
    }
};
