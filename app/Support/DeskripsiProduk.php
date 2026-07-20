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
     * @return array{intro: string, poin: array<int, string>}
     */
    public static function pisah(?string $teks): array
    {
        $teks = trim((string) $teks);
        if ($teks === '') {
            return ['intro' => '', 'poin' => []];
        }

        // Samakan semua penanda jadi satu karakter agar mudah dipecah.
        $normal = str_replace(self::PENANDA, "\x00", $teks);

        // Baris baru juga dianggap pemisah bila barisnya memang berdiri sendiri.
        $normal = preg_replace('/\R+/u', "\x00", $normal) ?? $normal;

        $bagian = array_values(array_filter(
            array_map('trim', explode("\x00", $normal)),
            fn ($b) => $b !== ''
        ));

        if ($bagian === []) {
            return ['intro' => '', 'poin' => []];
        }

        // Bila tidak ada penanda sama sekali, kembalikan sebagai intro utuh —
        // jangan memaksa teks biasa menjadi daftar.
        if (count($bagian) === 1) {
            return ['intro' => $bagian[0], 'poin' => []];
        }

        // Bila teks langsung dibuka penanda (tanpa kalimat pembuka), seluruhnya
        // adalah poin — jangan promosikan poin pertama menjadi paragraf intro.
        if (str_starts_with($normal, "\x00")) {
            return ['intro' => '', 'poin' => $bagian];
        }

        return [
            'intro' => array_shift($bagian),
            'poin' => $bagian,
        ];
    }
}
