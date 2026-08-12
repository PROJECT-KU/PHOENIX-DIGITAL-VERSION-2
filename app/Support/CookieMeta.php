<?php

namespace App\Support;

/**
 * Pembacaan cookie milik Meta Pixel: _fbp dan _fbc.
 *
 * Keduanya ditulis oleh fbevents.js di browser, bukan oleh Laravel — jadi
 * dibaca langsung dari $_COOKIE lewat request(), BUKAN lewat Cookie::get().
 * Cookie::get() melewati EncryptCookies dan akan mengembalikan null untuk
 * cookie yang tidak dienkripsi Laravel.
 *
 * Gunanya: menghubungkan pembelian dengan klik iklan yang membawanya. _fbc
 * lahir dari parameter ?fbclid pada URL iklan; tanpanya Meta hanya bisa
 * menebak lewat pencocokan email/telepon.
 */
class CookieMeta
{
    /**
     * Panjang wajar cookie Meta. Penjaga terhadap nilai sampah — Meta menolak
     * seluruh peristiwa bila format fbp/fbc salah, bukan cuma mengabaikan
     * kolomnya, jadi satu nilai rusak bisa menghapus pembelian dari laporan.
     */
    private const MAKS = 255;

    /** ID browser, mis. "fb.1.1596403881668.1116446470". */
    public static function fbp(): ?string
    {
        return self::baca('_fbp');
    }

    /** ID klik iklan, mis. "fb.1.1554763741205.AbCdEfGh". Kosong bila bukan dari iklan. */
    public static function fbc(): ?string
    {
        return self::baca('_fbc');
    }

    private static function baca(string $nama): ?string
    {
        $nilai = request()?->cookie($nama);

        if (! is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        // Bentuk resmi Meta: fb.<subdomainIndex>.<waktu>.<nilai>
        if (! preg_match('/^fb\.\d+\.\d+\..+$/', $nilai)) {
            return null;
        }

        return strlen($nilai) > self::MAKS ? null : $nilai;
    }
}
