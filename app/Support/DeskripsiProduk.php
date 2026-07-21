<?php

namespace App\Support;

/**
 * Pecah deskripsi produk/bundling menjadi paragraf pembuka, poin bercentang,
 * dan catatan tambahan.
 *
 * Admin menulis deskripsi sebagai satu blok teks dengan beberapa jenis penanda:
 *
 *   "Judul...\nIntro...
 *    ✅ Fitur A   ✅ Fitur B
 *    📌 Yang kamu dapat: ...
 *    🎯 Cocok untuk: ...
 *    ⚡ Combo lebih hemat!"
 *
 * Ditampilkan mentah, semuanya menyatu jadi teks panjang yang sulit dibaca —
 * dan bila hanya "✅" yang dipisah, teks setelah ✅ terakhir (📌/🎯/⚡)
 * "kebablasan" masuk ke poin terakhir. Di sini teks dipecah rapi tanpa
 * mengubah data yang tersimpan.
 */
class DeskripsiProduk
{
    /** Penanda poin FITUR → dirender sebagai centang hijau. */
    private const PENANDA_POIN = ['✅', '✔️', '✔', '☑️', '✓', '•', '●', '▪'];

    /** Penanda CATATAN → dirender sebagai baris tersendiri, ikonnya dipertahankan. */
    private const PENANDA_CATATAN = ['📌', '🎯', '⚡', '🎉', '🔥', '💡', '⭐', '👉'];

    /**
     * @return array{
     *   paragraf: array<int, string>,
     *   poin: array<int, string>,
     *   ekstra: array<int, array{ikon: string, teks: string}>
     * }
     */
    public static function pisah(?string $teks): array
    {
        $teks = trim((string) $teks);
        if ($teks === '') {
            return ['paragraf' => [], 'poin' => [], 'ekstra' => []];
        }

        $semua = array_merge(self::PENANDA_POIN, self::PENANDA_CATATAN);
        $pola = '/('.implode('|', array_map(fn ($m) => preg_quote($m, '/'), $semua)).')/u';

        // Hasil: [teks_awal, penanda1, teks1, penanda2, teks2, ...]
        $bagian = preg_split($pola, $teks, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$teks];

        // Bagian sebelum penanda pertama = teks biasa (paragraf). Baris baru di
        // dalamnya memisahkan paragraf, bukan membuat poin.
        $awal = trim((string) array_shift($bagian));
        $paragraf = $awal === ''
            ? []
            : array_values(array_filter(
                array_map('trim', preg_split('/\R+/u', $awal) ?: []),
                fn ($p) => $p !== ''
            ));

        $poin = [];
        $ekstra = [];

        // Sisanya berpasangan: [penanda, teks].
        for ($i = 0; $i + 1 < count($bagian); $i += 2) {
            $penanda = $bagian[$i];
            $isi = trim((string) preg_replace('/\s+/u', ' ', $bagian[$i + 1]));
            if ($isi === '') {
                continue;
            }

            if (in_array($penanda, self::PENANDA_CATATAN, true)) {
                $ekstra[] = ['ikon' => $penanda, 'teks' => $isi];
            } else {
                $poin[] = $isi;
            }
        }

        return ['paragraf' => $paragraf, 'poin' => $poin, 'ekstra' => $ekstra];
    }
}
