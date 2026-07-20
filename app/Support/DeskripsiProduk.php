<?php

namespace App\Support;

/**
 * Pecah deskripsi produk menjadi paragraf pembuka + daftar poin.
 *
 * Admin menulis deskripsi sebagai satu blok teks dan menandai fiturnya dengan
 * emoji centang, contoh:
 *
 *   "Grammarly Premium ... bebas typo. ✅ Fitur lengkap ✅ Proses cepat"
 *
 * Ditampilkan apa adanya, semua itu menyatu jadi satu paragraf panjang yang
 * sulit dibaca. Di sini teksnya dipisah supaya bisa dirender sebagai daftar,
 * tanpa mengubah data yang tersimpan.
 */
class DeskripsiProduk
{
    /** Penanda awal poin yang lazim dipakai admin. */
    private const PENANDA = ['✅', '✔️', '✔', '☑️', '✓', '•', '●', '▪'];

    /**
     * Hanya teks yang DITANDAI admin yang menjadi poin bercentang.
     *
     * Baris baru sengaja TIDAK dianggap penanda: admin kerap menulis judul dan
     * paragraf pembuka di baris terpisah, dan sebelumnya paragraf itu ikut
     * mendapat centang — padahal ia kalimat biasa, bukan daftar fitur.
     *
     * @return array{paragraf: array<int, string>, poin: array<int, string>}
     */
    public static function pisah(?string $teks): array
    {
        $teks = trim((string) $teks);
        if ($teks === '') {
            return ['paragraf' => [], 'poin' => []];
        }

        // Samakan semua penanda jadi satu karakter agar mudah dipecah.
        $normal = str_replace(self::PENANDA, "\x00", $teks);
        $bagian = explode("\x00", $normal);

        // Bagian sebelum penanda pertama = teks biasa. Baris baru di dalamnya
        // memisahkan paragraf, bukan membuat poin.
        $awal = trim((string) array_shift($bagian));
        $paragraf = $awal === ''
            ? []
            : array_values(array_filter(
                array_map('trim', preg_split('/\R+/u', $awal) ?: []),
                fn ($p) => $p !== ''
            ));

        // Sisanya: masing-masing satu poin bercentang.
        $poin = [];
        foreach ($bagian as $b) {
            $b = trim((string) preg_replace('/\s+/u', ' ', $b));
            if ($b !== '') {
                $poin[] = $b;
            }
        }

        return ['paragraf' => $paragraf, 'poin' => $poin];
    }
}
