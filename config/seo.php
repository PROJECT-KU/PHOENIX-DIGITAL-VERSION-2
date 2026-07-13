<?php

/*
|--------------------------------------------------------------------------
| SEO — meta default & per-halaman
|--------------------------------------------------------------------------
| Dibaca oleh resources/views/partials/seo.blade.php berdasarkan nama route.
| Komponen dinamis (mis. detail produk) bisa menimpa via view()->share(
| 'seoTitle' / 'seoDescription' / 'seoImage' ).
*/

return [
    'site_name' => 'Phoenix Digital',

    // Kode verifikasi Google Search Console (untuk properti "Awalan URL" via meta tag).
    // Catatan: verifikasi properti "Domain" TETAP butuh TXT record di DNS, bukan ini.
    'google_verification' => 'JM7dDU4JCagjM8LcAFJH8fdB3RwdWA_92UAUXLIt-iM',

    // Path relatif (bukan URL) — URL dibangun dengan asset() di partial.
    'image' => 'storage/img/phoenix-mark.png',

    'default' => [
        'title' => 'Phoenix Digital — Akun Premium, Lisensi & Tools AI Bergaransi',
        'description' => 'Phoenix Digital: toko akun premium, lisensi software, & tools AI untuk riset dan produktivitas. Proses cepat, aman, bergaransi. Juga jasa pembuatan website & aplikasi.',
        'keywords' => 'akun premium, akun premium murah, akun premium bergaransi, tools AI, lisensi software, jual akun premium, toko akun premium, jasa pembuatan website, jasa aplikasi mobile, Phoenix Digital, akun premium Yogyakarta',
    ],

    // Halaman yang TIDAK boleh diindeks (privat / transaksi).
    'noindex_routes' => [
        'cart', 'checkout', 'payment', 'order.success', 'order.expired',
        'order.history', 'order.receipt', 'ebook.view', 'ebook.raw',
    ],

    'routes' => [
        'homepage' => [
            'title' => 'Phoenix Digital — Akun Premium, Lisensi & Tools AI Bergaransi',
            'description' => 'Belanja akun premium, lisensi software, & tools AI dengan harga hemat. Proses cepat, aman, dan bergaransi. Phoenix Digital juga melayani jasa website & aplikasi.',
        ],
        'shop.index' => [
            'title' => 'Shop — Jual Akun Premium & Tools AI Murah Bergaransi | Phoenix Digital',
            'description' => 'Jual akun premium & tools AI murah bergaransi: streaming, desain, produktivitas, & lisensi software. Proses cepat, aman, dan bergaransi di Phoenix Digital.',
            'keywords' => 'jual akun premium, akun premium murah, akun premium bergaransi, tools AI murah, lisensi software murah, langganan premium murah, akun streaming, akun desain, akun produktivitas',
        ],
        'bundling.index' => [
            'title' => 'Paket Bundling Hemat — Akun Premium & Tools AI Murah | Phoenix Digital',
            'description' => 'Paket bundling akun premium & tools AI dengan harga lebih hemat. Pilih kombinasi terbaik untuk kebutuhan digital Anda di Phoenix Digital.',
            'keywords' => 'paket bundling akun premium, bundling tools AI, paket hemat akun premium, akun premium murah, langganan premium hemat',
        ],
        'bundling.product-bundlings' => [
            'title' => 'Paket Bundling Produk — Hemat Akun & Tools AI Murah | Phoenix Digital',
            'description' => 'Kombinasi paket bundling produk premium dengan harga lebih hemat. Lebih lengkap, lebih murah — hanya di Phoenix Digital.',
            'keywords' => 'paket bundling produk, bundling akun premium, paket hemat tools AI, akun premium murah bergaransi',
        ],
        'services' => [
            'title' => 'Jasa Pembuatan Website & Aplikasi Mobile Murah Yogyakarta | Phoenix Digital',
            'description' => 'Jasa pembuatan website, aplikasi mobile, & konten sosial media di Yogyakarta. Harga mulai Rp 500rb, gratis domain & hosting 1 tahun, source code milik Anda. Konsultasi gratis.',
            'keywords' => 'jasa pembuatan website, jasa website murah, jasa website Yogyakarta, jasa website Jogja, jasa website UMKM, jasa pembuatan aplikasi mobile, jasa aplikasi Android, jasa toko online, jasa konten sosial media, developer website Jogja',
        ],
        'faq' => [
            'title' => 'FAQ — Pertanyaan Umum | Phoenix Digital',
            'description' => 'Jawaban seputar pemesanan, pembayaran, garansi, dan layanan Phoenix Digital.',
        ],
        'terms' => [
            'title' => 'Syarat & Ketentuan | Phoenix Digital',
            'description' => 'Ketentuan penggunaan layanan Phoenix Digital: pemesanan, pembayaran, garansi, refund, dan batas perangkat.',
        ],
        'privacy' => [
            'title' => 'Kebijakan Privasi | Phoenix Digital',
            'description' => 'Bagaimana Phoenix Digital mengumpulkan, menggunakan, dan melindungi data Anda.',
        ],
        'about' => [
            'title' => 'Tentang Kami | Phoenix Digital',
            'description' => 'Kenali Phoenix Digital — penyedia akun premium, tools AI, dan jasa teknologi yang terpercaya dan bergaransi.',
        ],
        'contact' => [
            'title' => 'Kontak | Phoenix Digital',
            'description' => 'Hubungi Phoenix Digital untuk pertanyaan produk, pemesanan, atau kerja sama. Respons cepat via WhatsApp.',
        ],
    ],

    // Data bisnis untuk structured data (JSON-LD LocalBusiness).
    'business' => [
        'telephone' => '+6289505967995',
        'email' => 'halo@phoenixdigital.id',
        'address' => 'Jl. Durmo, Ngemplak, Mlati, Sleman, Yogyakarta',
        'region' => 'Yogyakarta',
        'locality' => 'Sleman',
        'country' => 'ID',
        'same_as' => [
            'https://web.facebook.com/profile.php?id=61586376808425',
            'https://www.instagram.com/phoenixdigital.id/',
            'https://www.tiktok.com/@phoenix_digitalwarehouse',
        ],
    ],
];
