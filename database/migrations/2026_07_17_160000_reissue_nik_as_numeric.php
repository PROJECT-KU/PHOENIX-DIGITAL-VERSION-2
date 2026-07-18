<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Terbitkan ulang semua NIK ke format "ACM-NNNNNN" (6 digit angka ACAK).
        // Format lama (huruf+angka) diganti total. NIK dipakai untuk login, jadi
        // setelah migrasi ini karyawan memakai NIK barunya.
        $baru = [];

        foreach (DB::table('employee_details')->whereNotNull('nik')->orderBy('id')->pluck('id') as $id) {
            do {
                $nik = 'ACM-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            } while (in_array($nik, $baru, true) || DB::table('employee_details')->where('nik', $nik)->exists());

            $baru[] = $nik;
            DB::table('employee_details')->where('id', $id)->update(['nik' => $nik]);
        }
    }

    public function down(): void
    {
        // Perubahan format satu arah: NIK dibuat otomatis & tak mengandung makna
        // eksternal, jadi nilai lama tidak dipulihkan. Rollback dibiarkan no-op.
    }
};
