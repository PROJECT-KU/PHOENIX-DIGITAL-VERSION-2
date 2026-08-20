<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Memperkecil DOCX agar muat pada batas unggah penyedia pengecekan.
 *
 * Dasarnya sederhana: pemeriksaan kemiripan hanya membaca TEKS. Gambar tidak
 * ikut dianalisis sama sekali, padahal gambarlah yang membuat berkas
 * membengkak. DOCX sendiri adalah berkas ZIP — gambarnya tersimpan di
 * word/media/ dan bisa dimampatkan tanpa menyentuh satu huruf pun teksnya.
 *
 * Gambar TIDAK dihapus, hanya diperkecil dan dimampatkan, supaya tata letak
 * dokumen tetap utuh saat laporan dirender.
 */
class RingkasDokumen
{
    /**
     * Batas aman berkas masuk penyedia pengecekan (Groupy: di bawah 5 MB).
     * Disisakan margin supaya tidak mepet di angka penolakan.
     */
    public const BATAS_PENYEDIA = 4_800_000;

    /** Urutan percobaan: makin ke bawah makin agresif. */
    private const TAHAP = [
        ['lebar' => 1400, 'mutu' => 75],
        ['lebar' => 1000, 'mutu' => 65],
        ['lebar' => 700, 'mutu' => 55],
        ['lebar' => 500, 'mutu' => 45],
    ];

    /**
     * Hasilkan salinan DOCX yang lebih kecil dari $targetByte.
     *
     * @return string|null jalur berkas hasil, atau null bila tidak perlu/tidak bisa
     */
    public static function kecilkanDocx(string $jalurAsli, int $targetByte): ?string
    {
        if (! is_file($jalurAsli) || strtolower(pathinfo($jalurAsli, PATHINFO_EXTENSION)) !== 'docx') {
            return null;
        }

        // Sudah muat — tidak ada yang perlu dikerjakan.
        if (filesize($jalurAsli) <= $targetByte) {
            return null;
        }

        foreach (self::TAHAP as $tahap) {
            $hasil = self::coba($jalurAsli, $tahap['lebar'], $tahap['mutu']);

            if ($hasil && filesize($hasil) <= $targetByte) {
                return $hasil;
            }

            // Belum cukup kecil: buang dan coba tahap yang lebih agresif.
            if ($hasil) {
                @unlink($hasil);
            }
        }

        return null;
    }

    /** Apakah berkas ini DOCX yang melewati batas penyedia? */
    public static function perluDiringkas(string $jalurAsli): bool
    {
        return is_file($jalurAsli)
            && strtolower(pathinfo($jalurAsli, PATHINFO_EXTENSION)) === 'docx'
            && filesize($jalurAsli) > self::BATAS_PENYEDIA;
    }

    /** Nama unduhan yang jelas, agar admin tahu ini bukan berkas asli. */
    public static function namaRingkas(string $namaAsli): string
    {
        $dasar = pathinfo($namaAsli, PATHINFO_FILENAME);

        return trim($dasar).' (ringkas).docx';
    }

    /** Satu kali percobaan pemampatan pada ukuran & mutu tertentu. */
    private static function coba(string $jalurAsli, int $lebarMaks, int $mutu): ?string
    {
        $tujuan = tempnam(sys_get_temp_dir(), 'docx').'.docx';

        if (! copy($jalurAsli, $tujuan)) {
            return null;
        }

        $zip = new ZipArchive;

        if ($zip->open($tujuan) !== true) {
            @unlink($tujuan);

            return null;
        }

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $nama = $zip->getNameIndex($i);

                if (! str_starts_with($nama, 'word/media/')) {
                    continue;
                }

                $isi = $zip->getFromIndex($i);

                if ($isi === false) {
                    continue;
                }

                $kecil = self::mampatkanGambar($isi, $lebarMaks, $mutu);

                // Hanya ditulis bila memang menghemat.
                if ($kecil !== null && strlen($kecil) < strlen($isi)) {
                    $zip->addFromString($nama, $kecil);
                }
            }

            $zip->close();

            return $tujuan;
        } catch (\Throwable $e) {
            // Gagal di tengah tidak boleh menghasilkan DOCX rusak.
            @$zip->close();
            @unlink($tujuan);
            Log::warning('RingkasDokumen gagal: '.$e->getMessage());

            return null;
        }
    }

    /** Perkecil satu gambar. Mengembalikan null bila bukan gambar yang dikenali. */
    private static function mampatkanGambar(string $isi, int $lebarMaks, int $mutu): ?string
    {
        $gambar = @imagecreatefromstring($isi);

        if ($gambar === false) {
            return null;
        }

        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);

        if ($lebar > $lebarMaks) {
            $tinggiBaru = (int) round($tinggi * ($lebarMaks / $lebar));
            $kecil = imagescale($gambar, $lebarMaks, max(1, $tinggiBaru));

            if ($kecil !== false) {
                imagedestroy($gambar);
                $gambar = $kecil;
            }
        }

        // Latar putih: JPEG tidak mengenal transparansi, tanpa ini area
        // transparan menjadi hitam pekat.
        $datar = imagecreatetruecolor(imagesx($gambar), imagesy($gambar));
        imagefill($datar, 0, 0, imagecolorallocate($datar, 255, 255, 255));
        imagecopy($datar, $gambar, 0, 0, 0, 0, imagesx($gambar), imagesy($gambar));

        ob_start();
        imagejpeg($datar, null, $mutu);
        $keluaran = ob_get_clean();

        imagedestroy($gambar);
        imagedestroy($datar);

        return $keluaran ?: null;
    }
}
