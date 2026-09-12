<?php

namespace App\Livewire\Pages\Public\Services;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman /layanan — jasa pengembangan teknologi (website, aplikasi, konten,
 * sistem informasi, desain, otomasi).
 *
 * Isinya statis, bukan dari katalog produk, dan semua pemesanan lewat
 * WhatsApp. Datanya disusun di sini, bukan di view, supaya view hanya urusan
 * tampilan dan isinya bisa diuji. Tiap kartu membawa WARNANYA sendiri (--c di
 * view), sama seperti kartu kategori di Shop dan Bundling.
 */
class ServicesPage extends Component
{
    public const WA = '6289505967995';

    /** Fitur paket website yang tampil langsung; sisanya dilipat. */
    public const FITUR_TAMPIL = 7;

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.services.services', [
            'layanan' => self::layanan(),
            'termasuk' => self::termasuk(),
            'paketWeb' => self::paketWebsite(),
            'langkah' => self::langkah(),
            'perbandingan' => self::perbandingan(),
            'waKonsultasi' => self::wa('Halo Phoenix Digital, saya ingin konsultasi layanan teknologi.'),
            'waProyek' => self::wa('Halo Phoenix Digital, saya ingin mendiskusikan kebutuhan proyek teknologi.'),
        ]);
    }

    /** Tautan WhatsApp admin dengan pesan pembuka yang sudah terisi. */
    public static function wa(string $pesan): string
    {
        return 'https://wa.me/'.self::WA.'?text='.rawurlencode($pesan);
    }

    /** @return array<int, array{ikon: string, warna: string, judul: string, desk: string, bonus: string, fitur: array<int, string>, harga: string, wa: string}> */
    public static function layanan(): array
    {
        $daftar = [
            ['bi-code-slash', '#2563eb', 'Pengembangan Website',
                'Landing page, company profile, hingga toko online yang cepat, responsif, dan mudah dikelola.',
                'Gratis domain & hosting 1 tahun',
                ['Desain modern & mobile-friendly', 'SEO dasar — terindeks Google', 'Gratis SSL (HTTPS) & panel admin'],
                'Rp 500.000'],
            ['bi-phone', '#7c3aed', 'Aplikasi Mobile',
                'Aplikasi Android & iOS untuk bisnis, layanan, maupun internal perusahaan Anda.',
                'Gratis domain & hosting 1 tahun',
                ['Android & iOS', 'UI/UX rapi & ringan', 'Bantu publish ke Play Store'],
                'Rp 2.000.000'],
            ['bi-camera-reels', '#db2777', 'Konten Sosial Media',
                'Desain feed, video pendek, dan copywriting untuk menaikkan brand di media sosial.',
                'Gratis 3× revisi desain',
                ['Desain feed & story', 'Video pendek (reels/tiktok)', 'Copywriting & caption'],
                'Rp 500.000'],
            ['bi-diagram-3', '#0d9488', 'Sistem Informasi / Aplikasi Web',
                'Dashboard, manajemen data, kasir (POS), hingga sistem internal sesuai alur kerja Anda.',
                'Gratis training penggunaan',
                ['Custom sesuai kebutuhan', 'Multi-user & hak akses', 'Laporan & ekspor data'],
                'Rp 1.500.000'],
            ['bi-palette', '#d97706', 'UI/UX & Desain',
                'Wireframe, prototype, dan desain antarmuka yang menarik sekaligus mudah digunakan.',
                'Gratis file sumber (editable)',
                ['Wireframe & prototype', 'Desain UI konsisten', 'Logo & identitas brand'],
                'Rp 750.000'],
            ['bi-robot', '#16a34a', 'Bot WhatsApp & Otomasi',
                'Auto-reply, notifikasi otomatis, dan integrasi untuk menghemat waktu operasional.',
                'Gratis setup & konfigurasi awal',
                ['Auto-reply & broadcast', 'Notifikasi otomatis', 'Integrasi sistem/API'],
                'Rp 500.000'],
        ];

        return array_map(fn ($l) => [
            'ikon' => $l[0],
            'warna' => $l[1],
            'judul' => $l[2],
            'desk' => $l[3],
            'bonus' => $l[4],
            'fitur' => $l[5],
            'harga' => $l[6],
            'wa' => self::wa('Halo Phoenix Digital, saya tertarik dengan layanan '.$l[2].'.'),
        ], $daftar);
    }

    /** Nilai lebih yang sudah termasuk di setiap proyek. */
    public static function termasuk(): array
    {
        return [
            ['ikon' => 'bi-gift', 'warna' => '#f26522', 'judul' => 'Domain & hosting 1 tahun', 'ket' => 'Gratis untuk website & aplikasi'],
            ['ikon' => 'bi-shield-check', 'warna' => '#16a34a', 'judul' => 'Garansi & support', 'ket' => 'Perbaikan bug & bantuan purnajual'],
            ['ikon' => 'bi-arrow-repeat', 'warna' => '#2563eb', 'judul' => 'Revisi terjamin', 'ket' => 'Revisi sampai sesuai kesepakatan'],
            ['ikon' => 'bi-file-earmark-code', 'warna' => '#7c3aed', 'judul' => 'Source code milik Anda', 'ket' => 'Tanpa terkunci ke vendor'],
            ['ikon' => 'bi-chat-dots', 'warna' => '#0d9488', 'judul' => 'Konsultasi gratis', 'ket' => 'Bantu rancang solusi terbaik'],
            ['ikon' => 'bi-lightning-charge', 'warna' => '#d97706', 'judul' => 'Cepat & amanah', 'ket' => 'Progres jelas, tepat waktu'],
            ['ikon' => 'bi-phone', 'warna' => '#db2777', 'judul' => 'Responsif semua perangkat', 'ket' => 'Rapi di HP, tablet & desktop'],
            ['ikon' => 'bi-search', 'warna' => '#4f46e5', 'judul' => 'Siap terindeks Google', 'ket' => 'SEO dasar sudah termasuk'],
        ];
    }

    /**
     * Paket berjenjang khusus Pengembangan Website.
     *
     * "hemat" dihitung dari harga coret, bukan ditulis tangan, supaya tidak
     * pernah berselisih dengan dua angka yang tercetak di sebelahnya.
     */
    public static function paketWebsite(): array
    {
        $daftar = [
            [
                'nama' => 'Paket Starter Company', 'ikon' => 'bi-rocket-takeoff', 'warna' => '#2563eb',
                'lama' => 'Rp 1.500.000', 'harga' => 'Rp 500.000', 'populer' => false,
                'untuk' => 'UMKM & startup baru',
                'fitur' => ['1–5 Halaman', 'Gratis Domain & hosting 1 tahun', 'WhatsApp Button', 'SEO dasar (meta title & description)', 'Form kontak', 'Keamanan SSL (HTTPS)', 'Desain profesional & responsif', 'Page Speed Optimization (basic)', 'Training singkat admin', 'Garansi bug 3 bulan', 'Revisi 2×'],
            ],
            [
                'nama' => 'Paket Ecommerce', 'ikon' => 'bi-bag-check', 'warna' => '#f26522',
                'lama' => 'Rp 7.500.000', 'harga' => 'Rp 2.000.000', 'populer' => true,
                'untuk' => 'Brand online & UMKM aktif jualan',
                'fitur' => ['Unlimited produk', 'Payment gateway otomatis', 'Voucher & diskon', 'Desain responsif', 'Laporan penjualan', 'SEO produk', 'Gratis Domain & hosting 1 tahun', 'Keamanan SSL (HTTPS)', 'Page Speed Optimization', 'Revisi 5×'],
            ],
            [
                'nama' => 'Paket Custom', 'ikon' => 'bi-buildings', 'warna' => '#7c3aed',
                'lama' => 'Rp 15.000.000', 'harga' => 'Rp 8.000.000', 'populer' => false,
                'untuk' => 'Perusahaan & sistem kompleks',
                'fitur' => ['Sistem web kompleks', 'Multi role user', 'Integrasi API pihak ketiga', 'Keamanan lanjutan', 'Dokumentasi sistem', 'Unlimited email bisnis', 'Gratis Domain & hosting 1 tahun', 'SEO lanjutan', 'Keamanan SSL (HTTPS)', 'Page Speed Optimization', 'Unlimited revisi', 'Support prioritas', 'SLA & maintenance khusus'],
            ],
        ];

        return array_map(function ($p) {
            $lama = self::angka($p['lama']);
            $harga = self::angka($p['harga']);

            return $p + [
                'hemat' => $lama > $harga ? (int) round((1 - $harga / $lama) * 100) : 0,
                'fiturUtama' => array_slice($p['fitur'], 0, self::FITUR_TAMPIL),
                'fiturLain' => array_slice($p['fitur'], self::FITUR_TAMPIL),
                'wa' => self::wa('Halo Phoenix Digital, saya tertarik dengan '.$p['nama'].' (website).'),
            ];
        }, $daftar);
    }

    /**
     * Alur kerja, bergaya "Cara Pesan". Isinya hanya merangkum janji yang
     * sudah tertulis di halaman ini (konsultasi, revisi, source code, training,
     * garansi) — bukan janji baru.
     */
    public static function langkah(): array
    {
        return [
            ['ikon' => 'bi-chat-dots', 'warna' => '#16a34a', 'judul' => 'Konsultasi gratis', 'ket' => 'Ceritakan kebutuhan Anda lewat WhatsApp. Kami bantu rancang solusi yang paling pas.'],
            ['ikon' => 'bi-clipboard-check', 'warna' => '#2563eb', 'judul' => 'Rancangan & estimasi', 'ket' => 'Cakupan, fitur, dan harga disepakati jelas di awal — tanpa biaya tersembunyi.'],
            ['ikon' => 'bi-code-square', 'warna' => '#7c3aed', 'judul' => 'Pengerjaan & revisi', 'ket' => 'Progres dikabarkan berkala, revisi sesuai paket sampai hasilnya sesuai kesepakatan.'],
            ['ikon' => 'bi-box-seam', 'warna' => '#f26522', 'judul' => 'Serah terima & support', 'ket' => 'Source code dan akses diserahkan, training penggunaan, lalu garansi perbaikan bug.'],
        ];
    }

    /** Pembeda dari vendor lain — semua sudah termasuk harga. */
    public static function perbandingan(): array
    {
        return [
            ['Domain & hosting 1 tahun', 'Gratis, sudah termasuk', 'Biaya tambahan'],
            ['Source code proyek', 'Sepenuhnya milik Anda', 'Sering ditahan / terkunci'],
            ['SSL / HTTPS (keamanan)', 'Gratis, sudah termasuk', 'Sering dikenai biaya'],
            ['Garansi & perbaikan bug', 'Termasuk (mulai 3 bulan)', 'Bayar terpisah'],
            ['Revisi', 'Sesuai paket, jelas di awal', 'Dibatasi / bayar per revisi'],
            ['Training penggunaan', 'Gratis', 'Tidak ada / berbayar'],
            ['Transparansi harga', 'All-in, tanpa biaya tersembunyi', 'Banyak biaya tak terduga'],
            ['Dukungan (support)', 'Respons cepat via WhatsApp', 'Lambat / sistem tiket'],
            ['Ketepatan waktu', 'Estimasi jelas & tepat waktu', 'Sering molor tanpa kabar'],
            ['Desain', 'Custom sesuai brand Anda', 'Template seragam / kaku'],
            ['Kepemilikan akun & akses', 'Semua diserahkan ke Anda', 'Sering dipegang vendor'],
            ['Komunikasi', 'Langsung dengan tim developer', 'Lewat perantara / lambat'],
        ];
    }

    private static function angka(string $rupiah): int
    {
        return (int) preg_replace('/\D/', '', $rupiah);
    }
}
