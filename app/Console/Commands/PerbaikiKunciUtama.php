<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Mengembalikan PRIMARY KEY (dan AUTO_INCREMENT) yang hilang dari tabel MySQL.
 *
 * Ini alat perbaikan, bukan migrasi. Kerusakannya khas impor dump yang memuat
 * datanya saja tanpa indeksnya: tabelnya utuh, isinya benar, tapi setiap
 * migrasi baru yang menaruh foreign key ke tabel itu langsung gagal dengan
 *   "Missing index for constraint ... in the referenced table".
 *
 * Sengaja TIDAK dijalankan otomatis dan TIDAK menjadi migrasi: menyentuh skema
 * database yang sudah berisi data harus selalu keputusan sadar seseorang, dan
 * di server yang sehat perintah ini memang tidak ada gunanya.
 *
 * Bawaannya hanya melapor. Perubahan baru terjadi dengan --terapkan.
 */
class PerbaikiKunciUtama extends Command
{
    protected $signature = 'db:perbaiki-kunci
                            {--terapkan : Benar-benar ubah tabelnya (tanpa ini hanya melapor)}
                            {--tabel=* : Batasi ke tabel tertentu saja}';

    protected $description = 'Melaporkan (dan memperbaiki) tabel MySQL yang kehilangan PRIMARY KEY';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->error('Perintah ini hanya untuk MySQL. Koneksi sekarang: '.DB::connection()->getDriverName());

            return self::FAILURE;
        }

        $skema = DB::connection()->getDatabaseName();
        $terapkan = (bool) $this->option('terapkan');
        $batas = $this->option('tabel');

        $tanpaKunci = collect(DB::select(
            'SELECT t.TABLE_NAME AS nama
               FROM information_schema.TABLES t
              WHERE t.TABLE_SCHEMA = ?
                AND t.TABLE_TYPE = "BASE TABLE"
                AND NOT EXISTS (
                    SELECT 1 FROM information_schema.STATISTICS s
                     WHERE s.TABLE_SCHEMA = t.TABLE_SCHEMA
                       AND s.TABLE_NAME = t.TABLE_NAME
                       AND s.INDEX_NAME = "PRIMARY")
              ORDER BY t.TABLE_NAME',
            [$skema]
        ))->pluck('nama');

        if ($batas) {
            $tanpaKunci = $tanpaKunci->intersect($batas);
        }

        if ($tanpaKunci->isEmpty()) {
            $this->info('Semua tabel sudah punya PRIMARY KEY. Tidak ada yang perlu diperbaiki.');

            return self::SUCCESS;
        }

        $this->warn($tanpaKunci->count().' tabel tanpa PRIMARY KEY di skema "'.$skema.'".');
        $this->newLine();

        $baris = [];
        $perintah = [];

        foreach ($tanpaKunci as $tabel) {
            [$status, $sql] = $this->periksa($skema, $tabel);

            $baris[] = [$tabel, $status];

            foreach ($sql as $s) {
                $perintah[] = [$tabel, $s];
            }
        }

        $this->table(['Tabel', 'Keadaan'], $baris);

        if (! $perintah) {
            $this->newLine();
            $this->warn('Tidak ada yang bisa diperbaiki otomatis — lihat kolom Keadaan di atas.');

            return self::SUCCESS;
        }

        $this->newLine();

        if (! $terapkan) {
            $this->line('Rencana perubahan (belum dijalankan):');
            foreach ($perintah as [$tabel, $sql]) {
                $this->line('  '.$sql.';');
            }
            $this->newLine();
            $this->comment('Jalankan ulang dengan --terapkan untuk benar-benar mengubahnya.');
            $this->comment('Cadangkan dulu: mysqldump '.$skema.' > cadangan.sql');

            return self::SUCCESS;
        }

        $gagal = 0;

        foreach ($perintah as [$tabel, $sql]) {
            try {
                DB::statement($sql);
                $this->info('  ok  '.$sql);
            } catch (\Throwable $e) {
                $gagal++;
                $this->error('  GAGAL  '.$sql);
                $this->error('         '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->info(count($perintah) - $gagal.' perubahan berhasil, '.$gagal.' gagal.');

        return $gagal ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Menilai satu tabel: bisa diperbaiki otomatis atau tidak, dan dengan SQL apa.
     *
     * @return array{0: string, 1: array<string>}
     */
    private function periksa(string $skema, string $tabel): array
    {
        $kolom = collect(DB::select(
            'SELECT COLUMN_NAME nama, COLUMN_TYPE tipe, IS_NULLABLE boleh_null, EXTRA ekstra
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$skema, $tabel]
        ))->keyBy('nama');

        if (! $kolom->has('id')) {
            return ['tidak ada kolom "id" — perbaiki manual', []];
        }

        $id = $kolom->get('id');

        $jumlah = DB::selectOne('SELECT COUNT(*) n, COUNT(DISTINCT `id`) u, SUM(`id` IS NULL) nul FROM `'.$tabel.'`');

        if ($jumlah->nul > 0) {
            return ['ada '.$jumlah->nul.' baris ber-id kosong — perbaiki manual', []];
        }

        if ($jumlah->n != $jumlah->u) {
            return ['id kembar ('.$jumlah->n.' baris, '.$jumlah->u.' unik) — perbaiki manual', []];
        }

        $sql = ['ALTER TABLE `'.$tabel.'` ADD PRIMARY KEY (`id`)'];
        $catatan = 'tambah PRIMARY KEY';

        // Kolom id bertipe angka di Laravel selalu $table->id() alias
        // AUTO_INCREMENT. Kolom id bertipe char(36) adalah UUID dan justru
        // TIDAK boleh diberi AUTO_INCREMENT.
        $angka = (bool) preg_match('/^(big|medium|small|tiny)?int\b/i', $id->tipe);

        if ($angka && ! str_contains(strtolower($id->ekstra), 'auto_increment')) {
            $sql[] = 'ALTER TABLE `'.$tabel.'` MODIFY `id` '.$id->tipe.' NOT NULL AUTO_INCREMENT';
            $catatan .= ' + AUTO_INCREMENT';
        }

        return [$catatan.' ('.$jumlah->n.' baris)', $sql];
    }
}
