<?php

namespace App\Livewire\Pages\Admin\Orcha\Concerns;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Membaca daftar peserta dari tempelan maupun berkas Excel/CSV.
 *
 * Diangkat dari layar Lengkapi Peserta supaya layar Daftarkan Rombongan
 * memakai pengurai yang SAMA, bukan salinan kedua.
 *
 * Alasannya bukan kerapian: pengurai ini sudah memuat sejumlah keputusan yang
 * masing-masing lahir dari kejadian nyata — gelar di belakang nama yang tidak
 * boleh terbaca sebagai titik jemput, baris judul Excel yang tidak boleh jadi
 * peserta, penomoran WhatsApp yang harus dibuang. Salinan kedua akan
 * kehilangan sebagian keputusan itu diam-diam, dan bedanya baru ketahuan saat
 * satu layar menghasilkan daftar yang berbeda dari layar lain untuk berkas
 * yang sama.
 */
trait MembacaDaftarPeserta
{
    /**
     * Menguraikan baris mentah jadi peserta.
     *
     * @param  array<int, string>  $mentah
     * @return array<int, array{nama: string, titik_jemput: string, gantikan: ?string}>
     */
    protected function uraikanPeserta(array $mentah, bool $dariBerkas = false): array
    {
        return collect($mentah)
            ->map(function ($baris) use ($dariBerkas) {
                /*
                 | Tempelan dipisah tab, titik koma, atau koma — tiga bentuk yang
                 | sama-sama datang ke admin.
                 |
                 | Berkas TIDAK. Sel-selnya sudah terpisah sejak dibaca lalu
                 | disambung dengan tab di sini, jadi memisah ulang dengan koma
                 | hanya merusak isi selnya sendiri: "Budi Santoso, S.Pd" terbaca
                 | sebagai nama "Budi Santoso" dengan titik jemput " S.Pd", dan
                 | gelar di belakang nama bukan hal yang jarang di daftar peserta.
                 */
                $pemisah = $dariBerkas ? '/\t/' : '/\t|;|,/';

                $bagian = preg_split($pemisah, (string) $baris);
                $nama = trim((string) ($bagian[0] ?? ''));

                /*
                 | Penggantian boleh dinyatakan langsung di tempelan maupun berkas,
                 | memakai tanda panah: "Haha > Wiam".
                 |
                 | Panitia mengirim daftarnya sekaligus — sebagian nama baru,
                 | sebagian menggantikan yang berhalangan — dan memaksa admin
                 | memilah dua kelompok itu dengan tangan hanya memindahkan
                 | pekerjaan, tidak menghilangkannya.
                 */
                $gantikan = null;

                if (preg_match('/^(.+?)\s*(?:->|>|=>)\s*(.+)$/u', $nama, $cocok)) {
                    $gantikan = trim($cocok[1]);
                    $nama = trim($cocok[2]);
                }

                // Berkas boleh menyatakannya lewat kolom ketiga: "Menggantikan".
                if ($dariBerkas && filled($bagian[2] ?? null)) {
                    $gantikan = trim((string) $bagian[2]);
                }

                // Penomoran daftar WhatsApp ikut terbuang: "1." "2)" "3 -".
                $nama = trim(preg_replace('/^\s*\d+\s*[.)\-]?\s*/', '', $nama));

                return [
                    'nama' => $nama,
                    'titik_jemput' => trim((string) ($bagian[1] ?? '')),
                    'gantikan' => $gantikan,
                ];
            })
            ->filter(fn ($baris) => $baris['nama'] !== '')
            // Baris judul dari Excel ("Nama", "Nama Peserta") tidak ikut jadi peserta.
            ->reject(fn ($baris) => in_array(mb_strtolower($baris['nama']), ['nama', 'nama peserta', 'peserta'], true))
            ->values()
            ->all();
    }

    /**
     * Membaca berkas Excel/CSV jadi baris peserta.
     *
     * @return array<int, array<string, mixed>>|null null berarti berkasnya tidak
     *                                               terbaca; pemanggilnya yang
     *                                               mengabari admin
     */
    protected function bacaBerkasPeserta($berkas): ?array
    {
        try {
            $lembar = Excel::toArray(new class implements ToArray
            {
                public function array(array $baris) {}
            }, $berkas);
        } catch (\Throwable) {
            return null;
        }

        return $this->uraikanPeserta(
            collect($lembar[0] ?? [])
                ->map(fn ($kolom) => implode("\t", array_map(fn ($isi) => (string) $isi, (array) $kolom)))
                ->all(),
            dariBerkas: true,
        );
    }

    /**
     * Memecah tempelan jadi baris, lalu menguraikannya.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function uraikanTempelan(string $tempelan): array
    {
        return $this->uraikanPeserta(preg_split('/\r\n|\r|\n/', $tempelan) ?: []);
    }
}
