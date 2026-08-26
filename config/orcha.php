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
     | Panggilan ke Orcha dipaksa lewat IPv4.
     |
     | Orcha membatasi pemanggilnya dengan daftar IP. Server ini punya DUA
     | alamat keluar, dan keduanya tidak sederajat:
     |
     | - IPv4 46.202.186.249 — tetap, itu alamat akun hosting ini;
     | - IPv6 — BERGANTI sendiri. Tercatat 2a02:4780:6:1234::35 pada 24 Agu
     |   2026, lalu 2a02:4780:6:1509:0:1b9d:1c86:1 pada 26 Agu 2026, beda blok.
     |
     | Tanpa paksaan ini curl memilih IPv6 lebih dulu, sehingga Orcha menolak
     | dengan 403 dan dasbor menjadi kosong — bukan karena kuncinya salah,
     | melainkan karena alamatnya sudah bukan yang terdaftar. Menambahkan
     | alamat IPv6 yang baru hanya menunda kejadian yang sama.
     |
     | Diberi sakelar supaya bisa dimatikan bila hosting berpindah ke tempat
     | yang justru IPv4-nya tidak tetap.
     */
    'paksa_ipv4' => filter_var(env('ORCHA_API_PAKSA_IPV4', true), FILTER_VALIDATE_BOOLEAN),

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
