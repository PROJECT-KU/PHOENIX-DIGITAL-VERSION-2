<?php

namespace App\Support;

use Smalot\PdfParser\Parser;

/**
 * Hitung jumlah halaman PDF — dipakai menentukan harga jasa parafrase
 * (harga per halaman). Hanya PDF: format DOCX tidak menyimpan jumlah halaman
 * secara andal (pagination baru terbentuk saat dirender), sehingga tak bisa
 * dijadikan dasar harga.
 */
class PdfPageCounter
{
    /** Jumlah halaman, atau null bila gagal dibaca. */
    public static function hitung(string $absolutePath): ?int
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        try {
            $pages = (new Parser)->parseFile($absolutePath)->getPages();
            $n = count($pages);

            return $n > 0 ? $n : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
