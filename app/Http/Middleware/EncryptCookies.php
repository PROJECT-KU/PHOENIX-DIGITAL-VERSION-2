<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Ditulis oleh fbevents.js di browser, jadi tidak pernah terenkripsi
        // Laravel. Tanpa dikecualikan, middleware ini gagal mendekripsi
        // keduanya lalu MENIMPANYA JADI null — bukan membiarkannya apa adanya
        // — sehingga pembelian yang dilaporkan ke Meta kehilangan jejak klik
        // iklannya tanpa error apa pun yang terlihat.
        '_fbp',
        '_fbc',
    ];
}
