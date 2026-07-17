<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            // Nomor Induk Karyawan — otomatis, format "ACM-XXXXXX".
            $table->string('nik')->nullable()->unique()->after('user_id');
            // Tanggal bergabung — dasar perhitungan MASA KERJA.
            $table->date('tanggal_bergabung')->nullable()->after('nik');
        });

        // Backfill NIK untuk karyawan yang sudah ada agar semuanya punya nomor.
        $acak = function (): string {
            // Tanpa O/0/I/1 agar tak ambigu saat dibaca/diketik.
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $s = '';
            for ($i = 0; $i < 6; $i++) {
                $s .= $chars[random_int(0, strlen($chars) - 1)];
            }

            return 'ACM-'.$s;
        };

        $terpakai = [];
        foreach (DB::table('employee_details')->whereNull('nik')->pluck('id') as $id) {
            do {
                $nik = $acak();
            } while (in_array($nik, $terpakai, true) || DB::table('employee_details')->where('nik', $nik)->exists());

            $terpakai[] = $nik;
            DB::table('employee_details')->where('id', $id)->update(['nik' => $nik]);
        }
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn(['nik', 'tanggal_bergabung']);
        });
    }
};
