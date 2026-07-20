<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Menentukan halaman publik yang URL-nya memuat rahasia pelanggan.
 *
 * Pelanggan tidak login (audiens lansia), jadi token pada URL seperti
 * /cek/{token} adalah satu-satunya kunci akses ke dokumen & hasil mereka.
 * GA4 dan Meta Pixel mengirim URL lengkap ke server Google/Facebook, jadi
 * tanpa penyamaran token itu ikut tersimpan di laporan pihak ketiga.
 */
class JalurAnalitik
{
    /** Nama route => jalur samaran yang aman dikirim ke analitik. */
    private const PETA = [
        'jasa.cek' => '/cek/[token]',
        'jasa.cek.hasil' => '/cek/[token]/hasil',
        'jasa.cek.hasil-ai' => '/cek/[token]/hasil-ai',
        'jasa.cek.hasil-docx' => '/cek/[token]/hasil-docx',
        'payment' => '/payment/[order]',
        'order.success' => '/order/[order]/success',
        'order.expired' => '/order/expired/[order]',
        'qris.show' => '/qris/[token]',
        'order.receipt' => '/s/[token]',
        'ebook.view' => '/e/[token]',
        'ebook.raw' => '/e/[token]/raw',
    ];

    /**
     * Jalur samaran untuk route ini, atau null bila URL-nya memang tidak rahasia.
     *
     * @param  string|null  $namaRoute  Dikosongkan = pakai route yang sedang aktif.
     */
    public static function samaran(?string $namaRoute = null): ?string
    {
        $nama = $namaRoute ?? Route::currentRouteName();

        return $nama === null ? null : (self::PETA[$nama] ?? null);
    }

    /** Apakah URL halaman ini rahasia dan tidak boleh dikirim apa adanya. */
    public static function peka(?string $namaRoute = null): bool
    {
        return self::samaran($namaRoute) !== null;
    }
}
