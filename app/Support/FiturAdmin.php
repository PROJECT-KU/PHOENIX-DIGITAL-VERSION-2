<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Menutup sementara satu MODUL ADMIN, tanpa mencabut izin siapa pun.
 *
 * Bedanya dengan izin: izin menentukan siapa yang BOLEH, sedangkan ini
 * menentukan apakah modulnya sedang BISA dipakai sama sekali. Dipakai saat satu
 * modul sedang diperbaiki atau datanya sedang dihitung ulang — mis. gaji satu
 * periode sedang dirapikan dan tidak boleh disunting siapa pun dulu. Mencabut
 * izin karyawan satu per satu lalu mengembalikannya jauh lebih berisiko.
 *
 * TIGA PENGAMAN yang tidak bisa ditawar:
 *
 *  1. Halaman Jeda Layanan sendiri tidak pernah bisa ditutup. Kalau bisa,
 *     admin mengunci dirinya keluar dari satu-satunya tempat untuk membuka lagi.
 *  2. Dasbor dan Akun Profil juga tidak — karyawan yang baru masuk harus punya
 *     tempat mendarat, dan harus tetap bisa mengganti sandinya.
 *  3. Pemegang izin "Kelola Jeda Layanan" selalu bisa menembus. Dialah yang
 *     mengerjakan perbaikannya; kalau ia ikut terkunci, tak ada yang bisa
 *     memastikan modulnya sudah benar sebelum dibuka untuk yang lain.
 */
class FiturAdmin
{
    /** key => [label, keterangan, awalan nama rute yang dicakup] */
    public const DAFTAR = [
        'pesanan' => [
            'label' => 'Pesanan',
            'ket' => 'Pesanan toko, RSC, dan ebook bonus',
            'rute' => ['admin.pesanantoko.', 'admin.pesananrsc.', 'admin.ebook.'],
        ],
        'produk' => [
            'label' => 'Produk & Katalog',
            'ket' => 'Produk, bundling, data akun, banner',
            'rute' => ['admin.product.', 'admin.Bundlings.', 'admin.DataAkun.', 'admin.Banners.'],
        ],
        'promo' => [
            'label' => 'Promo & Ulasan',
            'ket' => 'Promo, testimoni, ulasan produk',
            'rute' => ['admin.promo.', 'admin.testimoni.', 'admin.reviews.'],
        ],
        'blog' => [
            'label' => 'Blog',
            'ket' => 'Artikel dan kategorinya',
            'rute' => ['admin.blog.'],
        ],
        'pelanggan' => [
            'label' => 'Pelanggan & Pesan',
            'ket' => 'Data pelanggan dan pesan masuk',
            'rute' => ['admin.customer.', 'admin.customer-message.', 'admin.message.'],
        ],
        'keuangan' => [
            'label' => 'Keuangan',
            'ket' => 'Arus kas, pengeluaran, modal, pemasukan',
            'rute' => ['admin.cashflow.', 'admin.spending.', 'admin.modal.', 'admin.pemasukan.', 'admin.hargamodal.'],
        ],
        'peminjaman' => [
            'label' => 'Peminjaman',
            'ket' => 'Pinjaman dan pengembaliannya',
            'rute' => ['admin.loan.', 'admin.pengembalian.'],
        ],
        'kepegawaian' => [
            'label' => 'Kepegawaian',
            'ket' => 'Karyawan, presensi, gaji',
            'rute' => ['admin.karyawan.', 'admin.presensi.', 'admin.gajikaryawan.'],
        ],
        'task' => [
            'label' => 'Task & KPI',
            'ket' => 'Task saya dan penyelesaian task',
            'rute' => ['admin.task-saya.', 'admin.penyelesaian-task.'],
        ],
        'karir' => [
            'label' => 'Lowongan & Pelamar',
            'ket' => 'Lowongan kerja dan lamaran masuk',
            'rute' => ['admin.lowongan.', 'admin.pelamar.'],
        ],
        'jasa' => [
            'label' => 'Berkas Jasa',
            'ket' => 'Unduhan berkas pengecekan',
            'rute' => ['admin.jasa.'],
        ],
        'orcha' => [
            'label' => 'Orcha Journey',
            'ket' => 'Seluruh modul Orcha di dalam lemon',
            'rute' => ['admin.orcha.'],
        ],
    ];

    public const PESAN_BAWAAN = 'Modul ini sedang diperbaiki. Silakan coba lagi nanti, atau tanyakan ke admin bila mendesak.';

    private static function kunci(string $fitur): string
    {
        return 'fitur_admin_'.$fitur.'_tutup';
    }

    private static function kunciPesan(string $fitur): string
    {
        return 'fitur_admin_'.$fitur.'_pesan';
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
        return self::DAFTAR[$fitur]['label'] ?? 'Modul ini';
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
     * Modul mana yang memiliki nama rute ini — null bila rutenya memang tidak
     * pernah bisa ditutup (dasbor, profil, dan halaman Jeda Layanan sendiri).
     */
    public static function dariRute(?string $namaRute): ?string
    {
        if (! $namaRute) {
            return null;
        }

        foreach (self::DAFTAR as $fitur => $info) {
            foreach ($info['rute'] as $awalan) {
                if (str_starts_with($namaRute, $awalan)) {
                    return $fitur;
                }
            }
        }

        return null;
    }

    /**
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
}
