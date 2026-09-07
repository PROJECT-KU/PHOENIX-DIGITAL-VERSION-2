<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Menutup sementara satu-dua HALAMAN publik, bukan seluruh situs.
 *
 * Berbeda dari JedaLayanan yang menutup pemesanan satu produk atau satu jenis
 * layanan, di sini yang ditutup adalah fiturnya: halaman blog sedang dirapikan,
 * paket bundling sedang dihitung ulang, dan seterusnya. Sisa situs tetap jalan.
 *
 * DAFTAR di bawah adalah satu-satunya tempat pendaftaran. Menambah halaman
 * publik baru cukup menambah satu baris — middleware, menu, dan panel admin
 * membacanya dari sini, jadi tidak ada logika yang perlu disentuh lagi.
 *
 * Beberapa halaman SENGAJA tidak bisa ditutup karena menutupnya akan
 * menelantarkan pelanggan yang sudah membayar atau melanggar kewajiban:
 * pembayaran, struk, pembaca ebook, tautan /cek, syarat & kebijakan privasi,
 * sitemap, dan umpan katalog. Itu batas yang disengaja, bukan kelalaian.
 */
class FiturPublik
{
    /**
     * key => [label, keterangan, nama rute yang dicakup]
     *
     * Nama rute boleh berakhiran '.*' untuk mencakup seluruh keluarganya.
     */
    public const DAFTAR = [
        'shop' => [
            'label' => 'Toko',
            'ket' => 'Katalog produk & halaman detailnya',
            'rute' => ['shop.index', 'shop.detail-product'],
        ],
        'bundling' => [
            'label' => 'Paket Bundling',
            'ket' => 'Daftar & detail paket hemat',
            'rute' => ['bundling.*'],
        ],
        'layanan' => [
            'label' => 'Halaman Layanan',
            'ket' => 'Perkenalan jasa cek plagiasi, AI, parafrase',
            'rute' => ['services'],
        ],
        'blog' => [
            'label' => 'Blog',
            'ket' => 'Daftar artikel & isinya',
            'rute' => ['blog.*'],
        ],
        'keranjang' => [
            'label' => 'Keranjang & Checkout',
            'ket' => 'Menutup seluruh pembelian baru',
            'rute' => ['cart', 'checkout'],
        ],
        'wishlist' => [
            'label' => 'Wishlist',
            'ket' => 'Daftar simpanan pembeli',
            'rute' => ['wishlist'],
        ],
        'lacak' => [
            'label' => 'Lacak Pesanan',
            'ket' => 'Pencarian status pesanan',
            'rute' => ['track-order'],
        ],
        'riwayat' => [
            'label' => 'Riwayat Pesanan',
            'ket' => 'Daftar pesanan pengunjung',
            'rute' => ['order.history'],
        ],
        'kontak' => [
            'label' => 'Kontak',
            'ket' => 'Halaman & formulir hubungi kami',
            'rute' => ['contact'],
        ],
        'informasi' => [
            'label' => 'Halaman Informasi',
            'ket' => 'Tentang kami, FAQ, dan info member',
            'rute' => ['about', 'faq', 'member.info'],
        ],
    ];

    public const PESAN_BAWAAN = 'Halaman ini sedang kami perbaiki. Silakan kembali beberapa saat lagi, atau hubungi kami lewat WhatsApp bila butuh bantuan.';

    private static function kunci(string $fitur): string
    {
        return 'fitur_publik_'.$fitur.'_tutup';
    }

    private static function kunciPesan(string $fitur): string
    {
        return 'fitur_publik_'.$fitur.'_pesan';
    }

    public static function ada(string $fitur): bool
    {
        return array_key_exists($fitur, self::DAFTAR);
    }

    public static function ditutup(?string $fitur): bool
    {
        if (! $fitur || ! self::ada($fitur)) {
            return false;
        }

        return (string) Setting::get(self::kunci($fitur), '0') === '1';
    }

    public static function pesan(?string $fitur): string
    {
        if (! $fitur || ! self::ada($fitur)) {
            return self::PESAN_BAWAAN;
        }

        $pesan = trim((string) Setting::get(self::kunciPesan($fitur), ''));

        return $pesan !== '' ? $pesan : self::PESAN_BAWAAN;
    }

    public static function label(?string $fitur): string
    {
        return self::DAFTAR[$fitur]['label'] ?? 'Halaman ini';
    }

    public static function setel(string $fitur, bool $ditutup, ?string $pesan = null): void
    {
        if (! self::ada($fitur)) {
            return;
        }

        Setting::set(self::kunci($fitur), $ditutup ? '1' : '0');

        if ($pesan !== null) {
            Setting::set(self::kunciPesan($fitur), trim($pesan));
        }
    }

    /**
     * Fitur mana yang memiliki nama rute ini — null bila rutenya memang tidak
     * pernah bisa ditutup.
     */
    public static function dariRute(?string $namaRute): ?string
    {
        if (! $namaRute) {
            return null;
        }

        foreach (self::DAFTAR as $fitur => $info) {
            foreach ($info['rute'] as $pola) {
                if ($pola === $namaRute) {
                    return $fitur;
                }

                if (str_ends_with($pola, '.*')
                    && str_starts_with($namaRute, substr($pola, 0, -1))) {
                    return $fitur;
                }
            }
        }

        return null;
    }

    /** Apakah rute ini sedang ditutup? */
    public static function ruteDitutup(?string $namaRute): bool
    {
        return self::ditutup(self::dariRute($namaRute));
    }

    /**
     * Keadaan semua fitur — untuk panel admin.
     *
     * @return array<string, array{label:string, ket:string, ditutup:bool, pesan:string}>
     */
    public static function keadaan(): array
    {
        $out = [];

        foreach (self::DAFTAR as $fitur => $info) {
            $out[$fitur] = [
                'label' => $info['label'],
                'ket' => $info['ket'],
                'ditutup' => self::ditutup($fitur),
                'pesan' => trim((string) Setting::get(self::kunciPesan($fitur), '')),
            ];
        }

        return $out;
    }

    /** Daftar fitur yang sedang ditutup. */
    public static function yangDitutup(): array
    {
        return array_keys(array_filter(
            self::DAFTAR,
            fn ($info, $fitur) => self::ditutup($fitur),
            ARRAY_FILTER_USE_BOTH
        ));
    }
}
