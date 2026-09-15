<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satuan harga 'kredit' (mis. kredit Gamma AI).
 *
 * Di MySQL kolom satuan ini ENUM, jadi menyimpan 'kredit' tanpa migrasi ini
 * GAGAL — dan di server pesanannya batal diam-diam: pembeli tidak pernah
 * sampai ke halaman QRIS, persis seperti kasus paket bundling dulu
 * (2026_08_19_140000). Pengujian tidak menangkapnya karena memakai SQLite,
 * yang kolomnya sudah dilonggarkan jadi string (2026_08_19_170000).
 *
 * Tiga kolom yang menyimpan satuan:
 *  - product_prices.durasi_type        : harga jual paket kredit
 *  - product_modal_prices.durasi_type  : modal per paket kredit
 *  - order_items.duration_type         : satuan yang dibeli (NULL utk bundling)
 *
 * bonus_duration_type sengaja dilewati: bonus hanya berupa masa aktif
 * (bulan/tahun), dan kredit tidak punya masa aktif.
 */
return new class extends Migration
{
    private const LAMA = "'bulan','tahun','sekali','kali','halaman'";

    private const BARU = "'bulan','tahun','sekali','kali','halaman','kredit'";

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            // Di luar MySQL kolomnya sudah string (2026_08_19_170000).
            return;
        }

        $this->ubah(self::BARU);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Baris kredit dihapus dulu: tanpa itu ALTER-nya memangsa datanya
        // menjadi string kosong tanpa galat apa pun.
        DB::table('product_prices')->where('durasi_type', 'kredit')->delete();
        DB::table('product_modal_prices')->where('durasi_type', 'kredit')->delete();
        DB::table('order_items')->where('duration_type', 'kredit')->update(['duration_type' => null]);

        $this->ubah(self::LAMA);
    }

    private function ubah(string $nilai): void
    {
        foreach (['product_prices', 'product_modal_prices'] as $tabel) {
            if (Schema::hasTable($tabel)) {
                DB::statement("ALTER TABLE {$tabel} MODIFY durasi_type ENUM({$nilai}) NOT NULL");
            }
        }

        if (Schema::hasTable('order_items')) {
            DB::statement("ALTER TABLE order_items MODIFY duration_type ENUM({$nilai}) NULL DEFAULT NULL");
        }
    }
};
