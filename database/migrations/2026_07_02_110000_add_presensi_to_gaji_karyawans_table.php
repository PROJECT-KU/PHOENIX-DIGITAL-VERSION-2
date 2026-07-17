<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            // Komponen pendapatan dari kehadiran (ditarik dari fitur presensi)
            $table->unsignedInteger('jumlah_hadir_offline')->default(0)->after('jam_lembur');
            $table->decimal('uang_hadir_offline', 15, 2)->default(0)->after('jumlah_hadir_offline');
            $table->unsignedInteger('jumlah_hadir_online')->default(0)->after('uang_hadir_offline');
            $table->decimal('uang_hadir_online', 15, 2)->default(0)->after('jumlah_hadir_online');
        });

        // Default tarif global (dinamis, bisa diubah per entry & tersimpan sebagai default baru)
        $now = now();
        foreach (['tarif_presensi_offline' => '0', 'tarif_presensi_online' => '0'] as $k => $v) {
            DB::table('settings')->updateOrInsert(['key' => $k], ['value' => $v, 'updated_at' => $now, 'created_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_hadir_offline',
                'uang_hadir_offline',
                'jumlah_hadir_online',
                'uang_hadir_online',
            ]);
        });

        DB::table('settings')->whereIn('key', ['tarif_presensi_offline', 'tarif_presensi_online'])->delete();
    }
};
