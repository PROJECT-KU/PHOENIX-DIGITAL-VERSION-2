<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sambungan ke Orcha Journey
    |--------------------------------------------------------------------------
    |
    | Admin lemon memakai satu akun saja. Data Orcha tidak disalin ke sini —
    | basis datanya tetap di aplikasi Orcha, lemon hanya menggambar tampilannya
    | lewat API. Kuncinya rahasia bersama antar server; jangan sampai ikut ke
    | browser (jangan taruh di Blade yang tampil, jangan di JavaScript).
    |
    */

    'url' => rtrim((string) env('ORCHA_API_URL', ''), '/'),

    'kunci' => env('ORCHA_API_KEY'),

    // Detik. Orcha lambat lebih baik gagal cepat daripada menggantung halaman.
    'timeout' => (int) env('ORCHA_API_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Emoji pada kabar WhatsApp
    |--------------------------------------------------------------------------
    |
    | Emoji membuat kabar lebih mudah dipindai — mata menemukan baris nominal
    | tanpa membaca seluruhnya. Tapi emoji hanya berguna kalau tampil; sebagian
    | versi aplikasi WhatsApp salah membaca sandi persen pada tautan, dan tiap
    | emoji berubah jadi tanda tanya di layar pelanggan.
    |
    | Bila itu terjadi dan tidak kunjung beres, matikan saja lewat .env
    | (ORCHA_WA_EMOJI=false). Kabarnya tetap terbaca: strukturnya dijaga oleh
    | tebal bawaan WhatsApp dan baris baru, bukan oleh emojinya.
    |
    */

    'emoji_wa' => filter_var(env('ORCHA_WA_EMOJI', true), FILTER_VALIDATE_BOOL),

];
