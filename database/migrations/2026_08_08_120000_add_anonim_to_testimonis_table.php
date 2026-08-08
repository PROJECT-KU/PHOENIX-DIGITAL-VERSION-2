<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Testimoni anonim: simpan NAMA ASLI, samarkan hanya saat ditampilkan publik.
 *
 * Sebelumnya penyamaran dilakukan saat KIRIM — yang tersimpan di kolom `nama`
 * sudah berupa "B•••", sehingga nama asli hilang permanen dan admin pun ikut
 * melihat "B•••" saat memoderasi. Kolom `anonim` memindahkan penyamaran ke sisi
 * tampilan: publik tetap melihat "B•••", admin melihat nama sebenarnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            $table->boolean('anonim')->default(false)->after('nama');
        });

        // ===== Backfill data lama =====
        // Baris lama yang namanya bertanda "•••" adalah kiriman anonim.
        $anonimLama = DB::table('testimonis')->where('nama', 'like', '%•••')->get(['id', 'customer_id']);

        foreach ($anonimLama as $row) {
            $data = ['anonim' => true];

            // Nama asli hanya bisa dipulihkan bila testimoninya tertaut pelanggan.
            // Kiriman TAMU anonim lama tidak bisa dipulihkan (nama aslinya memang
            // tidak pernah disimpan) — dibiarkan apa adanya, dan karena samarkan()
            // bersifat idempoten ("B•••" -> "B•••") tampilan publiknya tidak berubah.
            if ($row->customer_id) {
                $nama = DB::table('customers')->where('id', $row->customer_id)->value('nama');
                if (filled($nama)) {
                    $data['nama'] = $nama;
                }
            }

            DB::table('testimonis')->where('id', $row->id)->update($data);
        }
    }

    public function down(): void
    {
        // Kembalikan nama tersamar untuk baris anonim, supaya rollback tidak
        // membocorkan nama asli ke tampilan publik yang belum punya kolom ini.
        $anonim = DB::table('testimonis')->where('anonim', true)->get(['id', 'nama']);

        foreach ($anonim as $row) {
            $huruf = mb_substr(trim((string) $row->nama), 0, 1);
            DB::table('testimonis')->where('id', $row->id)->update([
                'nama' => $huruf === '' ? 'Anonim' : mb_strtoupper($huruf).'•••',
            ]);
        }

        Schema::table('testimonis', function (Blueprint $table) {
            $table->dropColumn('anonim');
        });
    }
};
