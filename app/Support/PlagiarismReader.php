<?php

namespace App\Support;

use Smalot\PdfParser\Parser;

/**
 * Pembaca persen kemiripan dari PDF hasil Turnitin. Bersifat "best effort":
 * kalau gagal/format tak dikenal, kembalikan null — admin isi manual.
 * Angka final SELALU dikonfirmasi admin, jadi heuristik ini cukup untuk pra-isi.
 */
class PlagiarismReader
{
    public static function persenDariPdf(string $absolutePath): ?int
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        // PDF besar berisiko menghabiskan memori saat di-parse — dan kehabisan
        // memori adalah FATAL error yang tak tertangkap try/catch, sehingga
        // request unggah ikut mati. Persen hanya fitur bantu (admin tetap bisa
        // mengisinya manual), jadi lebih aman dilewati untuk file besar.
        if (filesize($absolutePath) > 8 * 1024 * 1024) {
            return null;
        }

        try {
            $text = (new Parser)->parseFile($absolutePath)->getText();
        } catch (\Throwable $e) {
            return null;
        }

        if (! $text) {
            return null;
        }

        // Normalkan spasi agar regex stabil.
        $norm = preg_replace('/\s+/', ' ', $text);

        /*
         * Laporan Turnitin memuat BANYAK angka %: satu angka utama
         * ("14% Overall Similarity") plus rincian per sumber ("12% Internet
         * database", "3% Publications database", dst). Karena itu angka hanya
         * diambil bila menempel langsung pada frasa penanda angka UTAMA —
         * jarak antar keduanya dibatasi ketat (maks 15 karakter non-angka).
         * Urutan pola: dari paling spesifik ke paling longgar.
         */
        $pola = [
            '/(\d{1,3})\s*%\s*overall\s+similarity/i',              // "14% Overall Similarity"
            '/overall\s+similarity[^0-9%]{0,15}(\d{1,3})\s*%/i',    // "Overall Similarity: 14%"
            '/(\d{1,3})\s*%\s*similarity\s+index/i',                // "14% Similarity Index"
            '/similarity\s+index[^0-9%]{0,15}(\d{1,3})\s*%/i',      // "Similarity Index 14%"
            '/(\d{1,3})\s*%\s*kemiripan/i',
            '/kemiripan[^0-9%]{0,15}(\d{1,3})\s*%/i',
        ];

        foreach ($pola as $p) {
            if (preg_match($p, $norm, $m)) {
                return self::batasi((int) $m[1]);
            }
        }

        /*
         * Sengaja TIDAK ada fallback "ambil persen pertama". Menebak berisiko
         * menyambar angka rincian sumber dan menampilkan persen plagiasi yang
         * SALAH ke customer — jauh lebih berbahaya daripada kolom kosong.
         * Bila ragu, kembalikan null dan biarkan admin mengisinya manual.
         */
        return null;
    }

    private static function batasi(int $n): ?int
    {
        if ($n < 0 || $n > 100) {
            return null;
        }

        return $n;
    }
}
