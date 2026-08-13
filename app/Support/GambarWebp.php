<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Simpan gambar unggahan sebagai WebP yang sudah dikecilkan.
 *
 * Admin tetap mengunggah PNG/JPG seperti biasa — perubahan terjadi di sini,
 * bukan di kebiasaan orangnya. Itu disengaja: aturan "perkecil dulu sebelum
 * unggah" pasti terlupakan cepat atau lambat, sedangkan kode tidak pernah lupa.
 *
 * Alasannya nyata dan terukur. Sebelum ini beranda berbobot 7,3 MB, dan 5,7 MB
 * di antaranya hanya tiga banner PNG berukuran 1254x1254 (~2 MB per berkas) —
 * 78% bobot halaman untuk tiga gambar. Bagi pembeli dengan kuota terbatas atau
 * sinyal lemah, halaman terasa lambat meski servernya menjawab dalam 0,25 detik.
 *
 * GAGAL-AMAN: bila konversi tidak bisa dilakukan (ekstensi GD tak lengkap,
 * berkas rusak, memori kurang), berkas asli tetap disimpan apa adanya dan nama
 * aslinya dikembalikan. Unggahan admin tidak boleh gagal hanya karena
 * penghematan ukuran tidak berhasil.
 */
class GambarWebp
{
    /** Lebar maksimum bawaan. Layar terlebar pun tidak butuh lebih dari ini. */
    public const LEBAR_BAWAAN = 1600;

    /** Mutu WebP. 82 = selisih dengan aslinya tak terlihat mata, ukuran turun jauh. */
    private const MUTU = 82;

    /**
     * Simpan sebagai WebP dan kembalikan NAMA BERKAS yang benar-benar tersimpan.
     *
     * Nama itu wajib dipakai pemanggil untuk mengisi kolom di database —
     * ekstensinya bisa `.webp` (konversi berhasil) atau ekstensi asli (gagal,
     * berkas asli yang disimpan).
     *
     * @param  string  $folder  relatif ke disk 'public', mis. 'img/banners'
     * @param  string  $namaDasar  tanpa ekstensi, mis. 'Banners_49846'
     */
    public static function simpan(
        UploadedFile $berkas,
        string $folder,
        string $namaDasar,
        int $maksLebar = self::LEBAR_BAWAAN,
    ): string {
        $asli = $namaDasar.'.'.$berkas->getClientOriginalExtension();

        try {
            $webp = self::ubah($berkas->getRealPath(), $maksLebar);

            if ($webp === null) {
                $berkas->storeAs($folder, $asli, 'public');

                return $asli;
            }

            $nama = $namaDasar.'.webp';
            Storage::disk('public')->put($folder.'/'.$nama, $webp);

            return $nama;
        } catch (\Throwable $e) {
            // Dicatat, tapi tidak dilempar: kegagalan menghemat ukuran tidak
            // boleh membuat admin gagal menyimpan produk atau banner.
            Log::warning('Konversi WebP gagal, memakai berkas asli: '.$e->getMessage());

            try {
                $berkas->storeAs($folder, $asli, 'public');
            } catch (\Throwable) {
                // Biarkan pemanggil yang menangani; nama tetap dikembalikan.
            }

            return $asli;
        }
    }

    /**
     * Ubah satu berkas gambar di disk menjadi data WebP, atau null bila tak bisa.
     *
     * Dipisah dari simpan() supaya bisa dipakai ulang oleh perintah konversi
     * gambar LAMA, yang bekerja pada berkas di storage — bukan pada unggahan.
     */
    public static function ubah(string $jalur, int $maksLebar = self::LEBAR_BAWAAN): ?string
    {
        if (! function_exists('imagewebp') || ! is_readable($jalur)) {
            return null;
        }

        $info = @getimagesize($jalur);

        if ($info === false) {
            return null;
        }

        [$lebar, $tinggi] = $info;

        $sumber = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($jalur),
            IMAGETYPE_PNG => @imagecreatefrompng($jalur),
            IMAGETYPE_WEBP => @imagecreatefromwebp($jalur),
            IMAGETYPE_GIF => @imagecreatefromgif($jalur),
            default => null,
        };

        if (! $sumber) {
            return null;
        }

        try {
            if ($lebar > $maksLebar) {
                $tinggiBaru = (int) round($tinggi * ($maksLebar / $lebar));
                $kecil = imagecreatetruecolor($maksLebar, $tinggiBaru);

                // PNG sering punya latar tembus pandang. Tanpa dua baris ini,
                // bagian transparan berubah jadi hitam pekat saat diperkecil.
                imagealphablending($kecil, false);
                imagesavealpha($kecil, true);

                imagecopyresampled($kecil, $sumber, 0, 0, 0, 0, $maksLebar, $tinggiBaru, $lebar, $tinggi);
                imagedestroy($sumber);
                $sumber = $kecil;
            } else {
                imagealphablending($sumber, false);
                imagesavealpha($sumber, true);
            }

            ob_start();
            $ok = imagewebp($sumber, null, self::MUTU);
            $data = ob_get_clean();

            return ($ok && $data !== '' && $data !== false) ? $data : null;
        } finally {
            if (is_resource($sumber) || $sumber instanceof \GdImage) {
                imagedestroy($sumber);
            }
        }
    }
}
