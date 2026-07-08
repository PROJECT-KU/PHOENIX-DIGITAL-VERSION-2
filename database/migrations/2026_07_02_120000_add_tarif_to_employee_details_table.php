<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            // Tarif bonus per karyawan (dipakai saat menghitung gaji dari data presensi)
            $table->decimal('tarif_presensi_offline', 15, 2)->default(0)->after('nomor_rekening');
            $table->decimal('tarif_presensi_online', 15, 2)->default(0)->after('tarif_presensi_offline');
            $table->decimal('tarif_lembur_per_jam', 15, 2)->default(0)->after('tarif_presensi_online');
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn([
                'tarif_presensi_offline',
                'tarif_presensi_online',
                'tarif_lembur_per_jam',
            ]);
        });
    }
};
