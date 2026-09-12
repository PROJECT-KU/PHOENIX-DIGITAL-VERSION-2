<?php

namespace App\Livewire\Pages\Public\About;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman /about — profil Phoenix Digital.
 *
 * Isinya statis. Datanya disusun di sini, bukan di view, supaya view hanya
 * urusan tampilan dan angkanya bisa diuji. Tiap kartu membawa WARNANYA
 * sendiri (--c di view) — bahasa visual yang sama dengan Shop, Bundling,
 * dan Layanan.
 */
class AboutPage extends Component
{
    public const WA = '6289505967995';

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.about.about-page', [
            'janji' => self::janji(),
            'nilai' => self::nilai(),
            'angka' => self::angka(),
            'waTanya' => self::wa('Halo Phoenix Digital, saya ingin bertanya.'),
            'waKampus' => self::wa('Halo Phoenix Digital, saya ingin booking untuk kampus/instansi.'),
        ]);
    }

    /** Tautan WhatsApp admin dengan pesan pembuka yang sudah terisi. */
    public static function wa(string $pesan): string
    {
        return 'https://wa.me/'.self::WA.'?text='.rawurlencode($pesan);
    }

    /** Chip janji layanan di kartu pembuka. */
    public static function janji(): array
    {
        return [
            ['ikon' => 'bi-shield-check', 'warna' => '#16a34a', 'teks' => 'Transaksi Aman'],
            ['ikon' => 'bi-lightning-charge-fill', 'warna' => '#d97706', 'teks' => 'Respons Cepat'],
            ['ikon' => 'bi-patch-check-fill', 'warna' => '#2563eb', 'teks' => 'Bergaransi'],
        ];
    }

    /** Alasan pelanggan mempercayai kami. */
    public static function nilai(): array
    {
        return [
            ['ikon' => 'bi-shield-check', 'warna' => '#2563eb', 'judul' => 'Terpercaya & Amanah',
                'teks' => 'Produk sesuai deskripsi dan transaksi yang aman. Kepercayaan Anda adalah prioritas kami.'],
            ['ikon' => 'bi-lightning-charge-fill', 'warna' => '#d97706', 'judul' => 'Respons Cepat',
                'teks' => 'Pesanan dan pertanyaan dilayani secepat mungkin pada jam operasional kami.'],
            ['ikon' => 'bi-patch-check-fill', 'warna' => '#16a34a', 'judul' => 'Bergaransi',
                'teks' => 'Setiap akun bergaransi selama masa aktif paket. Ada kendala? Kami bantu selesaikan.'],
            ['ikon' => 'bi-tags-fill', 'warna' => '#db2777', 'judul' => 'Harga Hemat',
                'teks' => 'Nikmati paket bundling dan flash sale untuk mendapatkan tools premium dengan harga terbaik.'],
        ];
    }

    /**
     * Angka ringkas. Nilainya dianimasikan purecounter di peramban, tetapi
     * angka akhirnya tetap tercetak di HTML supaya tetap terbaca bila JS mati.
     */
    public static function angka(): array
    {
        return [
            ['ikon' => 'bi-emoji-smile-fill', 'warna' => '#f26522', 'nilai' => 800, 'label' => 'Pelanggan Puas'],
            ['ikon' => 'bi-box-seam-fill', 'warna' => '#7c3aed', 'nilai' => 120, 'label' => 'Produk & Tools'],
            ['ikon' => 'bi-bag-check-fill', 'warna' => '#16a34a', 'nilai' => 1500, 'label' => 'Transaksi Selesai'],
            ['ikon' => 'bi-mortarboard-fill', 'warna' => '#2563eb', 'nilai' => 25, 'label' => 'Kampus & Instansi'],
        ];
    }
}
