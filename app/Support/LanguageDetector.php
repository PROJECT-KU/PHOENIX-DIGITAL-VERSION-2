<?php

namespace App\Support;

use Smalot\PdfParser\Parser;

/**
 * Menebak apakah sebuah dokumen berbahasa Inggris.
 *
 * Dipakai untuk layanan deteksi AI, yang hanya andal pada teks Inggris.
 * Caranya membandingkan kemunculan kata paling umum tiap bahasa (stopword) —
 * cukup untuk memisahkan Indonesia vs Inggris tanpa pustaka tambahan.
 *
 * Bersifat hati-hati: bila teks tak bisa dibaca atau terlalu sedikit untuk
 * disimpulkan, hasilnya null (RAGU) dan pemanggil TIDAK boleh menolak
 * dokumen — lebih baik lolos lalu diperiksa admin daripada menolak
 * dokumen yang sebenarnya sah.
 */
class LanguageDetector
{
    /** Kata paling umum bahasa Inggris. */
    private const EN = [
        'the', 'and', 'of', 'to', 'in', 'is', 'that', 'for', 'with', 'this',
        'are', 'was', 'be', 'as', 'it', 'on', 'by', 'from', 'which', 'have',
        'has', 'were', 'been', 'their', 'these', 'they', 'not', 'can', 'we',
    ];

    /** Kata paling umum bahasa Indonesia. */
    private const ID = [
        'yang', 'dan', 'di', 'dari', 'untuk', 'pada', 'dengan', 'ini', 'itu',
        'adalah', 'dalam', 'tidak', 'akan', 'oleh', 'atau', 'juga', 'dapat',
        'telah', 'sebagai', 'karena', 'bahwa', 'kepada', 'para', 'terhadap',
        'penelitian', 'hasil', 'data', 'tersebut', 'antara',
    ];

    /** Minimal kata agar kesimpulan dianggap layak. */
    private const MIN_KATA = 60;

    /**
     * true = Inggris, false = BUKAN Inggris, null = tidak bisa disimpulkan.
     */
    public static function adalahInggris(string $absolutePath, ?string $ext = null): ?bool
    {
        $teks = self::teks($absolutePath, $ext);

        if ($teks === null) {
            return null;
        }

        $kata = preg_split('/[^a-z]+/u', mb_strtolower($teks), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($kata) < self::MIN_KATA) {
            return null; // terlalu pendek — jangan menyimpulkan
        }

        $hitung = array_count_values($kata);
        $skorEn = 0;
        $skorId = 0;

        foreach (self::EN as $w) {
            $skorEn += $hitung[$w] ?? 0;
        }
        foreach (self::ID as $w) {
            $skorId += $hitung[$w] ?? 0;
        }

        // Tak satu pun stopword ketemu (mis. hasil OCR berantakan) → ragu.
        if ($skorEn + $skorId === 0) {
            return null;
        }

        // Selisihnya harus meyakinkan; kalau nyaris seimbang (dokumen campuran)
        // lebih baik mengaku ragu daripada menolak dokumen yang sah.
        $total = $skorEn + $skorId;
        $rasioEn = $skorEn / $total;

        if ($rasioEn >= 0.65) {
            return true;
        }
        if ($rasioEn <= 0.35) {
            return false;
        }

        return null;
    }

    /** Ambil teks dokumen; null bila gagal / format tak didukung. */
    private static function teks(string $path, ?string $ext = null): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        // File besar dilewati: parsing bisa menghabiskan memori (fatal error
        // yang tak tertangkap try/catch dan mematikan request unggah).
        if (filesize($path) > 8 * 1024 * 1024) {
            return null;
        }

        $ext = strtolower($ext ?: pathinfo($path, PATHINFO_EXTENSION));

        try {
            if ($ext === 'pdf') {
                return (new Parser)->parseFile($path)->getText() ?: null;
            }

            if ($ext === 'docx') {
                return self::teksDocx($path);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /** Teks dari DOCX — baca word/document.xml lalu buang tag-nya. */
    private static function teksDocx(string $path): ?string
    {
        $zip = new \ZipArchive;

        if ($zip->open($path) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return null;
        }

        // <w:p> jadi batas paragraf agar kata tak menempel satu sama lain.
        $xml = preg_replace('/<\/w:p>/', ' ', $xml);

        return trim(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8')) ?: null;
    }
}
