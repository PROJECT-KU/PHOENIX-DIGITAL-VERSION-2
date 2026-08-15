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

];
