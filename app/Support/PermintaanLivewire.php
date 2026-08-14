<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjawab permintaan LATAR Livewire tanpa membuat browser berpindah halaman.
 *
 * Masalah yang diperbaiki: halaman admin memanggil server sendiri tiap 5 detik
 * lewat `wire:poll` di NotifPoller. Bila saat itu middleware menjawab dengan
 * pengalihan biasa (sesi habis, izin kurang, profil belum lengkap), Livewire
 * menanganinya begini — terlihat langsung di dist/livewire.js:
 *
 *     if (response.redirected) { window.location.href = response.url }
 *
 * Akibatnya admin yang sedang mengetik tiba-tiba terlempar ke halaman lain
 * tanpa menyentuh apa pun, kadang mendarat di /livewire/update yang hanya
 * menerima POST sehingga muncul "405 Method Not Allowed".
 *
 * Jawaban yang benar untuk permintaan latar adalah 419. Livewire mengenalinya
 * (`status === 419` -> handlePageExpiry) lalu memuat ulang halaman yang SEDANG
 * dibuka secara wajar — sehingga pengalihan berikutnya terjadi pada permintaan
 * halaman biasa, bukan pada permintaan latar.
 */
class PermintaanLivewire
{
    /**
     * Permintaan latar dari Livewire?
     *
     * Header X-Livewire dikirim pada setiap panggilan komponen (update/poll),
     * dan TIDAK ada pada kunjungan halaman biasa — itulah yang membedakan
     * keduanya.
     */
    public static function ya(Request $request): bool
    {
        return $request->hasHeader('X-Livewire');
    }

    /**
     * Jawaban "sesi/izin tidak lagi berlaku" yang aman untuk permintaan latar.
     *
     * Sengaja BUKAN redirect: redirect pada permintaan latar justru yang
     * membuat browser melompat sendiri.
     */
    public static function kedaluwarsa(): Response
    {
        return response('Sesi tidak lagi berlaku.', 419);
    }
}
