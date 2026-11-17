<?php

namespace App\Support;

/**
 * Memeriksa pembagian kursi & kamar yang sedang disusun DI LAYAR.
 *
 * Salinan aturan yang sama dengan App\Support\PembagianKelompok::periksa() di
 * Orcha, dan itu disengaja — bukan kelupaan. Yang di Orcha adalah yang
 * berwenang: ia memeriksa data yang TERSIMPAN, dan jawabannya yang masuk ke
 * balasan API. Yang di sini memeriksa yang BELUM tersimpan, yaitu yang sedang
 * dilihat admin saat ini.
 *
 * Tanpa salinan ini, peringatan selalu terlambat satu langkah: kamar campur
 * yang baru saja dibuat admin baru diberitahu setelah Simpan ditekan —
 * peringatan yang menyala setelah kesalahannya tersimpan. Dan menembak Orcha
 * pada setiap ketukan berarti satu panggilan jaringan per orang untuk
 * pekerjaan yang menyentuh seratus orang.
 *
 * Supaya keduanya tidak menyimpang: OrchaPembagianPeriksaTest di sini dan
 * PembagianKamarKursiTest di Orcha menguji kasus yang SAMA. Yang mengubah
 * aturan di satu sisi akan melihat sisi lain merah.
 */
class PembagianPeriksa
{
    public const LAKI = 'Laki-laki';

    public const PEREMPUAN = 'Perempuan';

    /**
     * @param  array<int, array<string, mixed>>  $peserta
     * @return array<int, array{jenis: string, pesan: string}>
     */
    public static function periksa(array $peserta): array
    {
        $peringatan = [];

        $perKamar = collect($peserta)
            ->filter(fn ($satu) => trim((string) ($satu['kamar'] ?? '')) !== '')
            ->groupBy(fn ($satu) => trim((string) $satu['kamar']));

        foreach ($perKamar as $nama => $penghuni) {
            $kelamin = $penghuni
                ->pluck('jenis_kelamin')
                ->map(fn ($satu) => trim((string) $satu))
                ->filter()
                ->unique()
                ->values();

            if ($kelamin->count() > 1) {
                $peringatan[] = [
                    'jenis' => 'kamar_campur',
                    'pesan' => 'Kamar '.$nama.' berisi '.$kelamin->implode(' dan ')
                        .'. Kalau ini pasangan, biarkan; kalau tidak, pindahkan salah satunya.',
                ];
            }

            $belum = $penghuni->filter(
                fn ($satu) => ! in_array(trim((string) ($satu['jenis_kelamin'] ?? '')), [self::LAKI, self::PEREMPUAN], true)
            );

            if ($belum->isNotEmpty()) {
                $peringatan[] = [
                    'jenis' => 'kamar_tanpa_kelamin',
                    'pesan' => 'Kamar '.$nama.' berisi '.$belum->count()
                        .' peserta yang jenis kelaminnya belum diisi.',
                ];
            }
        }

        /*
         | Kursi kembar selalu jadi peringatan, tanpa pengecualian.
         |
         | Tidak ada keadaan sah di mana dua orang menempati satu kursi, jadi
         | ini bukan hal yang perlu ditimbang admin — ini salah ketik yang baru
         | terasa akibatnya setelah bus berjalan.
         */
        $perKursi = collect($peserta)
            ->filter(fn ($satu) => trim((string) ($satu['kursi'] ?? '')) !== '')
            ->groupBy(fn ($satu) => trim((string) ($satu['bus'] ?? '')).'|'.trim((string) $satu['kursi']));

        foreach ($perKursi as $kunci => $penghuni) {
            if ($penghuni->count() < 2) {
                continue;
            }

            [$bus, $nomor] = array_pad(explode('|', (string) $kunci, 2), 2, '');

            $peringatan[] = [
                'jenis' => 'kursi_kembar',
                'pesan' => trim(($bus !== '' ? $bus.' ' : '').'kursi '.$nomor)
                    .' dipakai '.$penghuni->count().' orang: '
                    .$penghuni->pluck('nama')->implode(', ').'.',
            ];
        }

        return $peringatan;
    }
}
