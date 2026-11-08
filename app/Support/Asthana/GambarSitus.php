<?php

namespace App\Support\Asthana;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan gambar unggahan untuk situs Asthana.
 *
 * Gambar dikecilkan (sisi terpanjang $sisiMaks) dan disimpan sebagai WebP,
 * karena foto dari ponsel admin bisa 5–10 MB dan situs memuatnya apa adanya.
 * Transparansi dipertahankan — logo klien biasanya PNG berlatar bening.
 *
 * Bila GD tidak sanggup membaca berkasnya (format aneh, memori kurang),
 * berkas aslinya yang disimpan: lebih baik gambar besar daripada gambar hilang.
 */
class GambarSitus
{
    public static function simpan(UploadedFile $berkas, string $folder, int $sisiMaks = 1600): string
    {
        $folder = 'asthana/'.trim($folder, '/');
        $nama = Str::random(24);

        try {
            $webp = self::keWebp($berkas->getRealPath(), $sisiMaks);
            if ($webp !== null) {
                $jalur = "{$folder}/{$nama}.webp";
                Storage::disk('public')->put($jalur, $webp);

                return $jalur;
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengubah gambar situs Asthana ke WebP: '.$e->getMessage());
        }

        $ekstensi = strtolower($berkas->getClientOriginalExtension() ?: $berkas->extension() ?: 'jpg');

        return $berkas->storeAs($folder, "{$nama}.{$ekstensi}", 'public');
    }

    private static function keWebp(string $jalur, int $sisiMaks): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $isi = @file_get_contents($jalur);
        $asli = $isi !== false ? @imagecreatefromstring($isi) : false;
        if (! $asli) {
            return null;
        }

        $w = imagesx($asli);
        $h = imagesy($asli);
        $skala = min(1, $sisiMaks / max($w, $h));
        $lebar = max(1, (int) round($w * $skala));
        $tinggi = max(1, (int) round($h * $skala));

        $hasil = imagecreatetruecolor($lebar, $tinggi);
        imagealphablending($hasil, false);
        imagesavealpha($hasil, true);
        imagefill($hasil, 0, 0, imagecolorallocatealpha($hasil, 0, 0, 0, 127));
        imagecopyresampled($hasil, $asli, 0, 0, 0, 0, $lebar, $tinggi, $w, $h);

        ob_start();
        imagewebp($hasil, null, 80);
        $data = ob_get_clean();

        imagedestroy($asli);
        imagedestroy($hasil);

        return $data !== '' && $data !== false ? $data : null;
    }
}
