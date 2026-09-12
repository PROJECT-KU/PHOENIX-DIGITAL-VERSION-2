<?php

namespace App\Livewire\Pages\Public\Legal;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman /faq — pertanyaan yang paling sering ditanyakan.
 *
 * Isi pertanyaan disusun di sini, bukan di view, karena dipakai DUA kali:
 * untuk tampilan dan untuk data terstruktur FAQPage (JSON-LD) yang dibaca
 * Google. Sebelumnya keduanya ditulis terpisah, jadi jawabannya bisa
 * berbeda tanpa ada yang menyadari.
 */
class FaqPage extends Component
{
    public const WA = '6289505967995';

    /** Tanggal perubahan ISI terakhir menurut riwayat git, bukan tata letak. */
    public const DIPERBARUI = '13 Juli 2026';

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.legal.faq', [
            'daftar' => self::daftar(),
            'waTanya' => 'https://wa.me/'.self::WA.'?text='.rawurlencode('Halo Phoenix Digital, saya ingin bertanya.'),
        ]);
    }

    /**
     * @return array<int, array{ikon: string, warna: string, judul: string, tanya: string, jawab: string}>
     *                        judul = ringkas untuk daftar isi; jawab = HTML yang kami tulis sendiri.
     */
    public static function daftar(): array
    {
        $shop = route('shop.index');
        $terms = route('terms');
        $privacy = route('privacy');
        $wa = 'https://wa.me/'.self::WA;

        return [
            [
                'ikon' => 'bi-bag-check', 'warna' => '#2563eb', 'judul' => 'Bagaimana cara memesan?',
                'tanya' => 'Bagaimana cara memesan?',
                'jawab' => 'Pilih produk atau paket bundling di halaman <a href="'.$shop.'">Shop</a>, masukkan ke keranjang, lalu lanjut ke checkout. Isi nomor WhatsApp, nama, dan email, kemudian selesaikan pembayaran. Untuk pemesanan kolektif kampus/instansi, silakan <a href="'.$wa.'" target="_blank" rel="noopener">booking via WhatsApp</a>.',
            ],
            [
                'ikon' => 'bi-shield-check', 'warna' => '#16a34a', 'judul' => 'Metode pembayaran & keamanan',
                'tanya' => 'Metode pembayaran apa saja & bagaimana agar aman?',
                'jawab' => 'Pembayaran <b>hanya melalui Transfer Bank dan QRIS</b>. Demi keamanan, pastikan pembayaran selalu tertuju <b>atas nama Phoenix Digital Warehouse</b>. Selain nama itu, dipastikan penipuan — jangan lanjutkan dan konfirmasikan ke admin kami.',
            ],
            [
                'ikon' => 'bi-clock-history', 'warna' => '#d97706', 'judul' => 'Berapa lama pesanan diproses?',
                'tanya' => 'Berapa lama pesanan diproses?',
                'jawab' => 'Pesanan diproses setelah pembayaran terverifikasi. Detail akun/lisensi dikirim melalui WhatsApp atau kanal yang disepakati secepatnya pada jam operasional.',
            ],
            [
                'ikon' => 'bi-patch-check', 'warna' => '#0d9488', 'judul' => 'Apakah akun bergaransi?',
                'tanya' => 'Apakah akun bergaransi?',
                'jawab' => 'Ya. Setiap akun bergaransi selama masa aktif sesuai paket yang dibeli. Bila ada kendala pada masa garansi, hubungi kami dan tim akan membantu.',
            ],
            [
                'ikon' => 'bi-phone', 'warna' => '#7c3aed', 'judul' => 'Batas perangkat per akun',
                'tanya' => 'Berapa batas perangkat per akun?',
                'jawab' => 'Maksimal <b>2 (dua) perangkat</b> per akun. Jika dipakai di lebih dari 2 perangkat, akun dapat <b>terblokir otomatis</b> oleh sistem penyedia — kondisi ini menghanguskan garansi dan di luar kebijakan kami. Selengkapnya lihat <a href="'.$terms.'">Syarat &amp; Ketentuan</a>.',
            ],
            [
                'ikon' => 'bi-arrow-counterclockwise', 'warna' => '#db2777', 'judul' => 'Kebijakan pengembalian dana',
                'tanya' => 'Bagaimana kebijakan pengembalian dana (refund)?',
                'jawab' => 'Jika akun <b>belum diserahkan</b>, dana dikembalikan <b>100%</b>. Jika akun <b>sudah diserahkan/diaktifkan</b>, pengembalian maksimal <b>50%</b>. Detail ada di <a href="'.$terms.'">Syarat &amp; Ketentuan</a>.',
            ],
            [
                'ikon' => 'bi-lock', 'warna' => '#4f46e5', 'judul' => 'Bagaimana data saya dijaga?',
                'tanya' => 'Bagaimana data saya dijaga?',
                'jawab' => 'Kami hanya menggunakan data Anda untuk memproses pesanan dan layanan purnajual. Selengkapnya baca <a href="'.$privacy.'">Kebijakan Privasi</a>.',
            ],
        ];
    }

    /**
     * Data terstruktur FAQPage untuk Google — jawabannya diambil dari daftar
     * yang sama dengan yang tampil, tanpa tag HTML.
     */
    public static function dataTerstruktur(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['tanya'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(html_entity_decode(strip_tags($f['jawab']), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                ],
            ], self::daftar()),
        ];
    }
}
