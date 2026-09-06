<?php

namespace App\Support;

/**
 * Peminat yang menunggu kursi, dan hanya sebagiannya yang menuntut perbuatan.
 *
 * Yang dihitung: kursinya SUDAH terbuka, orangnya tanpa surel, dan belum ada
 * yang menghubunginya. Ketiga syarat itu perlu sekaligus.
 *
 * Versi pertama menghitung yang BELUM dikabari dan tanpa surel — dan itu salah
 * orang. Selama kursinya belum terbuka tidak ada apa pun yang bisa
 * dikabarkan, jadi ia tidak menuntut perbuatan siapa pun. Yang benar-benar
 * menunggu telepon justru kebalikannya: kursinya sudah terbuka, dan sistem
 * tidak bisa menjangkaunya sama sekali.
 *
 * Antrean yang panjang sendiri bukan pekerjaan — sistem mengabari mereka
 * sendiri begitu ada kursi. Penanda yang menghitung seluruhnya menyala terus
 * tanpa pernah bisa dinolkan, dan penanda yang tidak pernah padam berhenti
 * dibaca orang — lalu ikut membawa serta penanda lain yang sebenarnya
 * mendesak.
 */
class OrchaDaftarTungguPerhatian extends HitunganOrcha
{
    protected static function kunci(): string
    {
        return 'orcha.daftar-tunggu.perhatian';
    }

    protected static function jalur(): string
    {
        return '/daftar-tunggu/perhatian';
    }

    /** @return array<string, int> */
    protected static function bawaan(): array
    {
        return ['perlu_dihubungi' => 0, 'menunggu' => 0, 'dikabari' => 0];
    }

    /** Yang menuntut seseorang mengangkat telepon. */
    public static function jumlah(): int
    {
        return self::ambil()['perlu_dihubungi'];
    }
}
