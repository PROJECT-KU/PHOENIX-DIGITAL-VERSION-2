<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_rsc', function (Blueprint $table) {
            // 'per_peserta' (harga x jumlah peserta) atau 'per_akun' (harga x jumlah akun).
            $table->string('metode_harga')->default('per_peserta')->after('jumlah_pemesanan');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_rsc', function (Blueprint $table) {
            $table->dropColumn('metode_harga');
        });
    }
};
