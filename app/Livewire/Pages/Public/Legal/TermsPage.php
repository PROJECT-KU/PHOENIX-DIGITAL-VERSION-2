<?php

namespace App\Livewire\Pages\Public\Legal;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman /terms — Syarat & Ketentuan.
 *
 * Isi pasal disusun di sini, bukan di view, karena dipakai DUA kali: untuk
 * daftar isi dan untuk badan halaman. Sebelumnya judul pasal diketik terpisah
 * dari isinya, dengan catatan "urutannya HARUS sama" — pengaman yang hanya
 * bergantung pada kedisiplinan penyunting.
 */
class TermsPage extends Component
{
    public const WA = '6289505967995';

    /** Tanggal perubahan ISI terakhir menurut riwayat git, bukan tata letak. */
    public const DIPERBARUI = '11 Juli 2026';

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.legal.terms', [
            'pasal' => self::pasal(),
            'ringkas' => self::ringkas(),
            'waBantuan' => 'https://wa.me/'.self::WA,
        ]);
    }

    /** Tiga poin terpenting, ditaruh di atas supaya terbaca lebih dulu. */
    public static function ringkas(): array
    {
        return [
            ['ikon' => 'bi-credit-card-2-front', 'warna' => '#16a34a', 'judul' => 'Transfer Bank & QRIS',
                'ket' => 'Hanya dua metode itu, atas nama Phoenix Digital Warehouse.'],
            ['ikon' => 'bi-patch-check-fill', 'warna' => '#2563eb', 'judul' => 'Bergaransi',
                'ket' => 'Selama masa aktif paket yang dibeli.'],
            ['ikon' => 'bi-phone', 'warna' => '#e11d48', 'judul' => 'Maksimal 2 perangkat',
                'ket' => 'Lebih dari itu, akun terblokir otomatis oleh penyedia.'],
        ];
    }

    /**
     * @return array<int, array{judul: string, ikon: string, warna: string, sorot: bool, isi: array<int, string>}>
     *                        isi = HTML yang kami tulis sendiri.
     */
    public static function pasal(): array
    {
        $wa = 'https://wa.me/'.self::WA;
        $waKonfirmasi = $wa.'?text='.rawurlencode('Halo Phoenix Digital, saya ingin konfirmasi pembayaran.');

        return [
            [
                'judul' => 'Tentang Layanan', 'ikon' => 'bi-box-seam', 'warna' => '#2563eb', 'sorot' => false,
                'isi' => ['Phoenix Digital menyediakan akun premium, lisensi, dan tools AI untuk kebutuhan riset serta produktivitas. Kami mengutamakan layanan yang <b>terpercaya, amanah, dan respons cepat</b>. Kami juga melayani kebutuhan <b>kampus/instansi</b> — silakan <a href="'.$wa.'" target="_blank" rel="noopener">booking melalui WhatsApp</a> untuk pemesanan kolektif.'],
            ],
            [
                'judul' => 'Pemesanan & Pembayaran', 'ikon' => 'bi-credit-card-2-front', 'warna' => '#16a34a', 'sorot' => false,
                'isi' => [
                    'Pemesanan dilakukan melalui website. Metode pembayaran yang tersedia <b>hanya Transfer Bank dan QRIS</b>. Pesanan diproses setelah pembayaran terverifikasi.',
                    'Demi keamanan, pastikan pembayaran ditujukan <b>atas nama Phoenix Digital Warehouse</b>. Jika ragu, konfirmasikan terlebih dahulu ke admin kami melalui <a href="'.$waKonfirmasi.'" target="_blank" rel="noopener">WhatsApp 0895-0596-7995</a>.',
                ],
            ],
            [
                'judul' => 'Pengiriman Akun', 'ikon' => 'bi-send-check', 'warna' => '#0d9488', 'sorot' => false,
                'isi' => ['Detail akun/lisensi dikirim melalui WhatsApp atau kanal yang disepakati setelah pembayaran dikonfirmasi. Kami mengusahakan proses secepat mungkin pada jam operasional.'],
            ],
            [
                'judul' => 'Garansi', 'ikon' => 'bi-patch-check-fill', 'warna' => '#7c3aed', 'sorot' => false,
                'isi' => ['Setiap akun bergaransi selama masa aktif sesuai paket yang dibeli. Jika terjadi kendala pada masa garansi, hubungi kami dan tim akan membantu secepatnya.'],
            ],
            [
                'judul' => 'Batas Perangkat & Blokir Otomatis', 'ikon' => 'bi-phone', 'warna' => '#e11d48', 'sorot' => true,
                'isi' => ['Setiap akun hanya boleh digunakan pada <b>maksimal 2 (dua) perangkat</b>. Jika digunakan pada lebih dari 2 perangkat, akun akan <b>terblokir secara otomatis</b> oleh sistem penyedia. Kondisi ini <b>menghanguskan garansi</b>, berada <b>di luar kebijakan kami</b>, serta <b>tidak ada pembaruan maupun pembukaan pemblokiran</b>. Mohon patuhi batas perangkat demi kenyamanan bersama.'],
            ],
            [
                'judul' => 'Kebijakan Refund', 'ikon' => 'bi-arrow-counterclockwise', 'warna' => '#d97706', 'sorot' => true,
                'isi' => ['Jika akun <b>belum diserahkan</b>, dana dikembalikan <b>100%</b>. Namun jika akun <b>sudah diserahkan/diaktifkan</b>, pengembalian dana maksimal <b>50%</b> — karena akun telah digunakan/terpakai. Pengajuan refund menyertakan bukti pembayaran dan alasan yang jelas.'],
            ],
            [
                'judul' => 'Tanggung Jawab Pengguna', 'ikon' => 'bi-person-check', 'warna' => '#4f46e5', 'sorot' => false,
                'isi' => ['Pengguna wajib menjaga kerahasiaan akun yang diterima dan menggunakannya secara wajar. Kerusakan akibat pelanggaran ketentuan penyedia layanan asli di luar tanggung jawab kami.'],
            ],
            [
                'judul' => 'Larangan', 'ikon' => 'bi-slash-circle', 'warna' => '#db2777', 'sorot' => false,
                'isi' => ['Dilarang menjual ulang, menyalahgunakan, atau membagikan akun di luar kesepakatan tanpa izin. Pelanggaran dapat menggugurkan garansi.'],
            ],
            [
                'judul' => 'Perubahan Ketentuan', 'ikon' => 'bi-arrow-repeat', 'warna' => '#0891b2', 'sorot' => false,
                'isi' => ['Syarat &amp; Ketentuan dapat diperbarui sewaktu-waktu. Versi terbaru yang berlaku adalah yang tercantum pada halaman ini.'],
            ],
        ];
    }
}
