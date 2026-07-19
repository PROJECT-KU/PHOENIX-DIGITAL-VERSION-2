<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

/**
 * Pindahkan berkas SENSITIF dari disk publik ke disk privat.
 *
 * Bukti pembayaran, lampiran pengeluaran, dan CV pelamar sebelumnya tersimpan
 * di storage/app/public sehingga bisa diunduh siapa pun yang tahu URL-nya.
 * Path di database TIDAK berubah — hanya lokasi fisiknya — jadi tak ada data
 * yang perlu ditulis ulang dan tampilan admin tetap bekerja lewat route
 * ber-izin (BerkasPrivatController).
 *
 * Aman diulang: berkas yang sudah pindah dilewati, yang gagal disalin tidak
 * dihapus dari disk publik.
 */
return new class extends Migration
{
    /** Folder yang isinya dipindahkan. */
    private const FOLDER = ['bukti_pembayaran', 'spending', 'applications'];

    public function up(): void
    {
        $publik = Storage::disk('public');
        $privat = Storage::disk('local');

        foreach (self::FOLDER as $folder) {
            if (! $publik->exists($folder)) {
                continue;
            }

            foreach ($publik->allFiles($folder) as $path) {
                // Sudah ada di privat -> cukup bersihkan salinan publiknya.
                if ($privat->exists($path)) {
                    $publik->delete($path);

                    continue;
                }

                $isi = $publik->get($path);

                if ($isi === null) {
                    continue;
                }

                // Hapus dari publik HANYA bila salinan privat benar-benar jadi.
                if ($privat->put($path, $isi) && $privat->exists($path)) {
                    $publik->delete($path);
                }
            }
        }
    }

    public function down(): void
    {
        $publik = Storage::disk('public');
        $privat = Storage::disk('local');

        foreach (self::FOLDER as $folder) {
            if (! $privat->exists($folder)) {
                continue;
            }

            foreach ($privat->allFiles($folder) as $path) {
                if (! $publik->exists($path) && ($isi = $privat->get($path)) !== null) {
                    $publik->put($path, $isi);
                }
            }
        }
    }
};
