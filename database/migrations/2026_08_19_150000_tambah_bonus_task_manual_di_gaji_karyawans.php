<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda bahwa bonus penyelesaian task pada satu baris gaji diisi MANUAL oleh
 * admin, bukan hasil pembagian pool otomatis.
 *
 * Tanpa penanda ini, angka yang diketik admin akan tertimpa diam-diam begitu
 * tombol "Terapkan" di halaman Penyelesaian Task ditekan, karena aksi itu
 * menulis ulang semua baris gaji yang belum completed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->boolean('bonus_task_manual')->default(false)->after('bonus_penyelesaian_task');
        });
    }

    public function down(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->dropColumn('bonus_task_manual');
        });
    }
};
