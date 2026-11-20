<?php

namespace App\Support;

/**
 * Nama, ikon, dan warna tiap GRUP IZIN.
 *
 * Satu daftar untuk semua layar yang menampilkan izin: Role → Permission,
 * Akses Akun per orang, dan borang pembuatan izin. Dulu daftarnya hidup di
 * dalam satu komponen saja, sehingga layar lain menampilkan nama grup
 * mentahnya — "Arsip_surat" dengan garis bawah — dan modul baru selalu
 * tertinggal di satu dari dua tempat.
 *
 * Urutannya SENGAJA mengikuti urutan bilah samping, bukan abjad: admin
 * mencari izin dengan mengingat letak menunya, bukan huruf depannya.
 */
class ModulIzin
{
    /** kunci grup => [label, ikon bootstrap, gradasi warna] */
    public const META = [
        // Dashboard
        'dashboard' => ['Dashboard', 'bi-grid-fill', '#4f46e5,#6366f1'],
        // Pesanan
        'pesananrsc' => ['Pesanan RSC', 'bi-cart', '#2563eb,#0ea5e9'],
        'pemesanantoko' => ['Pesanan Toko', 'bi-cart-check', '#2563eb,#0ea5e9'],
        'ebook' => ['Ebook Bonus', 'bi-book', '#2563eb,#0ea5e9'],
        // E-Commerce
        'banners' => ['Data Banner', 'bi-shop', '#0d9488,#14b8a6'],
        'productreview' => ['Moderasi Ulasan Produk', 'bi-star-half', '#0d9488,#14b8a6'],
        'blog' => ['Blog / Artikel', 'bi-journal-richtext', '#0d9488,#14b8a6'],
        'customer_message' => ['Pesan Masuk Pelanggan', 'bi-chat-dots', '#0d9488,#14b8a6'],
        // Promo
        'promo' => ['Promo', 'bi-tag', '#d97706,#f59e0b'],
        'testimoni' => ['Testimoni', 'bi-chat-quote', '#d97706,#f59e0b'],
        // Produk
        'dataakun' => ['Data Akun', 'bi-person-badge', '#7c3aed,#6d28d9'],
        'product' => ['Product', 'bi-box', '#7c3aed,#6d28d9'],
        'bundlings' => ['Product Bundling', 'bi-boxes', '#7c3aed,#6d28d9'],
        // Pelanggan
        'customer' => ['Pelanggan', 'bi-people', '#0891b2,#06b6d4'],
        // Keuangan
        'cashflow' => ['Cashflow', 'bi-cash-coin', '#059669,#10b981'],
        'spending' => ['Pengeluaran', 'bi-cash-stack', '#059669,#10b981'],
        'pemasukan' => ['Pemasukan', 'bi-graph-up-arrow', '#059669,#10b981'],
        'modal' => ['Modal', 'bi-piggy-bank', '#059669,#10b981'],
        'harga_modal' => ['Harga Modal', 'bi-tags', '#059669,#10b981'],
        'loan' => ['Peminjaman', 'bi-wallet2', '#059669,#10b981'],
        'gajikaryawan' => ['Gaji Karyawan', 'bi-coin', '#059669,#10b981'],
        // Akun
        'roles' => ['Pengaturan Role', 'bi-person-gear', '#e11d48,#f43f5e'],
        'permission' => ['Permission Akun', 'bi-shield-lock', '#e11d48,#f43f5e'],
        'users' => ['Data User', 'bi-person', '#e11d48,#f43f5e'],
        'activity_log' => ['Log Aktivitas', 'bi-clock-history', '#e11d48,#f43f5e'],
        // Task
        'task' => ['Task & KPI', 'bi-list-check', '#4338ca,#6366f1'],
        'jasa' => ['Berkas Jasa', 'bi-file-earmark-check', '#4338ca,#6366f1'],
        // Karyawan & Karir
        'karyawan' => ['Karyawan', 'bi-person-vcard', '#db2777,#ec4899'],
        'presensi' => ['Presensi', 'bi-calendar-check', '#db2777,#ec4899'],
        'kegiatan' => ['Kalender Kegiatan', 'bi-calendar-event', '#db2777,#ec4899'],
        'lowongan' => ['Lowongan Kerja', 'bi-briefcase', '#db2777,#ec4899'],
        'pelamar' => ['Pelamar', 'bi-file-earmark-person', '#db2777,#ec4899'],
        'message' => ['Pesan Masuk', 'bi-envelope', '#db2777,#ec4899'],
        // Aplikasi lain (mode tersendiri di bilah samping)
        'asthana' => ['Asthana — Situs, Karyawan & Karir', 'bi-buildings', '#2455e6,#17a974'],
        'orcha' => ['Orcha Journey', 'bi-water', '#0f2d4a,#1d6fa5'],
        'arsip_surat' => ['Arsip Surat', 'bi-archive', '#7a5c16,#a16207'],
    ];

    /** Label grup; grup yang belum terdaftar tetap terbaca sebagai kalimat. */
    public static function label(?string $grup): string
    {
        $grup = (string) $grup;

        if ($grup === '') {
            return 'Lainnya';
        }

        return self::META[$grup][0] ?? ucwords(str_replace('_', ' ', $grup));
    }

    /** Label + ikon + warna, dengan nilai cadangan yang tetap bisa dipakai layar. */
    public static function meta(?string $grup): array
    {
        return self::META[(string) $grup] ?? [self::label($grup), 'bi-grid-1x2-fill', '#64748b,#94a3b8'];
    }
}
