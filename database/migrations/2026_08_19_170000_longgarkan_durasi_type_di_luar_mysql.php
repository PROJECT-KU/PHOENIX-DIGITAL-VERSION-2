<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nilai 'kali' dan 'halaman' pada durasi_type hanya ditambahkan ke enum di
 * MySQL (lihat migrasi 2026_07_18_120000 dan 2026_07_18_160000). Di SQLite
 * enum menjadi CHECK constraint yang tetap ditegakkan, sehingga harga jasa
 * per pengecekan maupun per halaman tidak bisa dibuat saat pengujian.
 *
 * Kolom dilonggarkan menjadi string HANYA di luar MySQL. Skema produksi tidak
 * tersentuh: di MySQL migrasi ini tidak melakukan apa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        foreach (['product_prices', 'product_modal_prices'] as $tabel) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table) {
                $table->string('durasi_type')->change();
            });
        }
    }

    public function down(): void
    {
        // Sengaja dibiarkan: mengembalikan CHECK constraint akan menolak baris
        // ber-durasi_type 'kali'/'halaman' yang sudah ada.
    }
};
