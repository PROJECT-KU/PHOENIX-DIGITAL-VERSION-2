<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Web Push (VAPID) — notifikasi PWA di background & badge iPhone
    'webpush' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@lemon.app'),
    ],

    /*
     * Google Analytics 4.
     *
     * Nilai bawaannya properti yang sudah berjalan sejak sebelum pindah domain.
     * Dibuat lewat env supaya bisa dipindahkan ke properti Phoenix Digital
     * sendiri tanpa menyunting blade, atau dikosongkan untuk mematikan
     * pelacakan sepenuhnya.
     */
    'google_analytics' => [
        'id' => env('GA_MEASUREMENT_ID', 'G-YTEV4R4VHX'),
    ],

    /*
     * Meta Pixel + Conversions API.
     *
     * Pixel di browser mengirim ViewContent/AddToCart/InitiateCheckout; PEMBELIAN
     * dikirim dari server lewat Conversions API. Pembagian itu disengaja: pesanan
     * baru lunas setelah pembayaran dikonfirmasi Midtrans/QRIS — sering saat
     * browser pembeli sudah ditutup, jadi pixel tidak akan pernah sempat memicunya.
     *
     * capi_token kosong = pengiriman dimatikan diam-diam. Ini yang membuat mesin
     * lokal & CI tidak menembak API Facebook saat menguji checkout.
     */
    'meta' => [
        'pixel_id' => env('META_PIXEL_ID', '1364755135528892'),
        'capi_token' => env('META_CAPI_TOKEN'),
        // Diisi HANYA saat menguji di layar "Peristiwa Pengujian" Meta. Selama
        // terisi, event tidak masuk laporan iklan — jadi jangan tinggalkan
        // nilainya di server produksi.
        'test_event_code' => env('META_TEST_EVENT_CODE'),
        'api_version' => env('META_API_VERSION', 'v21.0'),
    ],

    // QRIS Dinamis (qris.online / OkeConnect)
    'qris' => [
        'base_url' => env('QRIS_BASE_URL', 'https://qris.interactive.co.id/restapi/qris'),
        'mid' => env('QRIS_MID'),
        'nmid' => env('QRIS_NMID'),
        'apikey' => env('QRIS_APIKEY'),
        // Masa berlaku QR (menit)
        'expiry_minutes' => (int) env('QRIS_EXPIRY_MINUTES', 30),
    ],

];
