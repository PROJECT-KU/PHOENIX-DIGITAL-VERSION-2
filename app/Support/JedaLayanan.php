<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Setting;

/**
 * Menjeda PEMBELIAN BARU satu jenis layanan jasa, tanpa menyembunyikan produknya.
 *
 * Penyedia pengecekan di luar kendali kami kadang sedang perbaikan, dan pesanan
 * yang telanjur masuk saat itu hanya menumpuk tak bisa dikerjakan. Yang
 * dibutuhkan bukan menghapus produk — halamannya tetap perlu terlihat agar
 * calon pembeli tahu layanan itu ada, dan agar peringkat pencariannya tidak
 * hilang — melainkan menutup pintu checkout-nya saja.
 *
 * Tiap jenis berdiri sendiri: menjeda 'plagiasi' TIDAK menyentuh 'ai' maupun
 * 'parafrase', karena penyebab gangguannya kerap hanya mengenai salah satunya.
 *
 * Disimpan di tabel `settings` supaya admin bisa membuka-tutup sendiri kapan
 * saja tanpa menunggu deploy — sama seperti setelan presensi dan pool bonus.
 */
class JedaLayanan
{
    /** Jenis yang bisa dijeda, mengikuti Product::jenisLayanan(). */
    public const JENIS = [
        'plagiasi' => 'Cek Plagiasi',
        'ai' => 'Cek AI',
        'parafrase' => 'Parafrase',
    ];

    /**
     * Kalimat yang dibaca PEMBELI bila admin tidak menulis keterangan sendiri.
     *
     * Ditulis per jenis karena sebabnya memang berbeda: plagiasi dan cek AI
     * berhenti ketika sistem pemeriksaan sedang diperbaiki, sedangkan parafrase
     * dikerjakan tim sendiri sehingga yang penuh adalah antreannya. Menyamakan
     * ketiganya akan membuat salah satunya terdengar mengarang.
     *
     * Nadanya sengaja singkat, tanpa istilah teknis, dan selalu menawarkan
     * jalan keluar — pembeli yang mendesak tetap punya langkah berikutnya
     * alih-alih hanya disuruh menunggu.
     */
    private const PESAN_BAWAAN = [
        'plagiasi' => 'Sistem pemeriksaan sedang dalam perbaikan, jadi pemesanan kami tutup sementara. Silakan coba lagi nanti, atau hubungi kami lewat WhatsApp bila butuh cepat.',
        'ai' => 'Sistem pemeriksaan sedang dalam perbaikan, jadi pemesanan kami tutup sementara. Silakan coba lagi nanti, atau hubungi kami lewat WhatsApp bila butuh cepat.',
        'parafrase' => 'Antrean pengerjaan sedang penuh, jadi pemesanan kami tutup sementara. Silakan coba lagi nanti, atau hubungi kami lewat WhatsApp bila butuh cepat.',
    ];

    public const PESAN_UMUM = 'Pemesanan layanan ini kami tutup sementara. Silakan coba lagi nanti, atau hubungi kami lewat WhatsApp bila butuh cepat.';

    private static function kunci(string $jenis): string
    {
        return 'jeda_layanan_'.$jenis;
    }

    private static function kunciPesan(string $jenis): string
    {
        return 'jeda_layanan_'.$jenis.'_pesan';
    }

    /** Apakah jenis layanan ini sedang dijeda? */
    public static function dijeda(?string $jenis): bool
    {
        if (! $jenis || ! array_key_exists($jenis, self::JENIS)) {
            return false;
        }

        return (string) Setting::get(self::kunci($jenis), '0') === '1';
    }

    /**
     * Apakah produk ini sedang dijeda?
     *
     * Produk non-jasa tidak pernah dijeda — jenisLayanan()-nya null. Fitur ini
     * sengaja tidak menyentuh alur produk biasa sama sekali.
     */
    public static function produkDijeda(?Product $produk): bool
    {
        return $produk instanceof Product && self::dijeda($produk->jenisLayanan());
    }

    /**
     * Kalimat bawaan untuk satu jenis — yang dipakai bila admin membiarkan
     * kolom keterangannya kosong.
     */
    public static function pesanBawaan(?string $jenis): string
    {
        return self::PESAN_BAWAAN[$jenis] ?? self::PESAN_UMUM;
    }

    /**
     * Keterangan yang ditampilkan ke pembeli.
     *
     * Keterangan tulisan admin selalu menang. Bila dikosongkan, dipakai kalimat
     * bawaan — sengaja tidak disalin ke database, supaya perbaikan katanya kelak
     * langsung berlaku pada layanan yang sudah pernah dijeda.
     */
    public static function pesan(?string $jenis): string
    {
        if (! $jenis) {
            return self::PESAN_UMUM;
        }

        $pesan = trim((string) Setting::get(self::kunciPesan($jenis), ''));

        return $pesan !== '' ? $pesan : self::pesanBawaan($jenis);
    }

    /** Keterangan untuk satu produk. */
    public static function pesanProduk(?Product $produk): string
    {
        return self::pesan($produk?->jenisLayanan());
    }

    /** Buka atau tutup satu jenis layanan. */
    public static function setel(string $jenis, bool $dijeda, ?string $pesan = null): void
    {
        if (! array_key_exists($jenis, self::JENIS)) {
            return;
        }

        Setting::set(self::kunci($jenis), $dijeda ? '1' : '0');

        if ($pesan !== null) {
            Setting::set(self::kunciPesan($jenis), trim($pesan));
        }
    }

    /**
     * Keadaan semua jenis — untuk panel admin.
     *
     * @return array<string, array{label:string, dijeda:bool, pesan:string}>
     */
    public static function daftar(): array
    {
        $out = [];

        foreach (self::JENIS as $jenis => $label) {
            $out[$jenis] = [
                'label' => $label,
                'dijeda' => self::dijeda($jenis),
                'pesan' => trim((string) Setting::get(self::kunciPesan($jenis), '')),
            ];
        }

        return $out;
    }

    /**
     * Produk jasa yang tercakup tiap jenis, untuk ditampilkan di halaman admin.
     *
     * Dibaca dari data, bukan didaftar manual, sehingga produk jasa baru
     * langsung muncul di bawah sakelar yang sesuai tanpa perlu diubah di sini.
     *
     * @return array<string, array<int, string>>
     */
    public static function produkPerJenis(): array
    {
        $out = array_fill_keys(array_keys(self::JENIS), []);

        foreach (Product::where('butuh_file', true)->orderBy('nama_akun')->get() as $produk) {
            $jenis = $produk->jenisLayanan();

            if ($jenis !== null && array_key_exists($jenis, $out)) {
                $out[$jenis][] = $produk->nama_akun;
            }
        }

        return $out;
    }

    /** Jenis yang sedang dijeda saat ini. */
    public static function yangDijeda(): array
    {
        return array_keys(array_filter(self::JENIS, fn ($l, $j) => self::dijeda($j), ARRAY_FILTER_USE_BOTH));
    }
}
