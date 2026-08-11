<?php

namespace App\Support;

/**
 * Pencocokan nomor HP saat MENCARI, tahan beda format penulisan.
 *
 * Masalahnya dua arah, dan keduanya harus dibereskan:
 *
 *  1. Yang DIKETIK admin sering disalin apa adanya dari WhatsApp:
 *     "+62 813-3913-9595" — ada plus, spasi, dan tanda hubung.
 *  2. Yang TERSIMPAN di database juga tidak seragam: "081339139595",
 *     "6281339139595", bahkan "+62 813-3913-9595".
 *
 * Membersihkan salah satu sisi saja tidak cukup. Karena itu kata kunci
 * diringkas jadi bentuk INTI (tanpa awalan 0/62/+62) sementara kolomnya
 * dibersihkan lewat SQL, lalu keduanya dicocokkan dengan LIKE. Bentuk inti
 * adalah potongan yang PASTI ada di semua varian penulisan.
 */
class CariNoHp
{
    /**
     * Sedikitnya berapa digit agar sebuah kata kunci dianggap "nomor".
     *
     * Penjaga ini penting: tanpa ambang, mencari "2" akan memicu pemindaian
     * REPLACE ke seluruh baris dan mencocokkan hampir semua nomor.
     */
    private const MIN_DIGIT = 6;

    /** Tanda baca yang lazim ditulis orang di dalam nomor telepon. */
    private const PEMISAH = [' ', '-', '(', ')', '.', '+'];

    /**
     * Kata kunci -> bentuk INTI, atau null bila jelas bukan nomor.
     *
     * "+62 813-3913-9595" -> "81339139595"
     * "0813 3913 9595"    -> "81339139595"
     * "budi"              -> null
     */
    public static function inti(?string $term): ?string
    {
        $digit = preg_replace('/\D/', '', (string) $term);

        if (strlen($digit) < self::MIN_DIGIT) {
            return null;
        }

        // Samakan awalan: 62… dan 0… diringkas ke bentuk tanpa awalan.
        $digit = preg_replace('/^62/', '', $digit);

        return preg_replace('/^0/', '', $digit);
    }

    /**
     * Ekspresi SQL untuk membaca kolom nomor TANPA tanda baca.
     *
     * Sengaja REPLACE bertingkat, bukan REGEXP_REPLACE: yang terakhir baru ada
     * di MySQL 8 dan proyek ini juga harus jalan di MariaDB shared hosting.
     *
     * @param  string  $kolom  nama kolom, mis. 'no_hp' atau 'customers.no_hp'
     */
    public static function kolomBersih(string $kolom): string
    {
        $sql = $kolom;

        foreach (self::PEMISAH as $tanda) {
            $sql = "REPLACE({$sql}, '{$tanda}', '')";
        }

        return $sql;
    }
}
