<?php

namespace App\Livewire\Pages\Public\Legal;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman /privacy — Kebijakan Privasi.
 *
 * Isi pasal disusun di sini, bukan di view, karena dipakai DUA kali: untuk
 * daftar isi dan untuk badan halaman. Sebelumnya judul pasal diketik terpisah
 * dari isinya, dengan catatan "urutannya HARUS sama" — pengaman yang hanya
 * bergantung pada kedisiplinan penyunting.
 */
class PrivacyPage extends Component
{
    public const WA = '6289505967995';

    /** Tanggal perubahan ISI terakhir menurut riwayat git, bukan tata letak. */
    public const DIPERBARUI = '11 Juli 2026';

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.legal.privacy', [
            'pasal' => self::pasal(),
            'ringkas' => self::ringkas(),
            'waBantuan' => 'https://wa.me/'.self::WA,
        ]);
    }

    /** Tiga janji utama, ditaruh di atas supaya terbaca lebih dulu. */
    public static function ringkas(): array
    {
        return [
            ['ikon' => 'bi-slash-circle', 'warna' => '#e11d48', 'judul' => 'Data tidak dijual',
                'ket' => 'Tidak dibagikan ke pihak lain untuk tujuan komersial.'],
            ['ikon' => 'bi-bag-check', 'warna' => '#16a34a', 'judul' => 'Hanya untuk pesananmu',
                'ket' => 'Memproses pesanan, mengirim akun, dan memberi dukungan.'],
            ['ikon' => 'bi-person-check', 'warna' => '#2563eb', 'judul' => 'Bisa diubah & dihapus',
                'ket' => 'Kapan saja, tinggal minta lewat WhatsApp kami.'],
        ];
    }

    /**
     * @return array<int, array{judul: string, ikon: string, warna: string, sorot: bool, isi: array<int, string>}>
     *                        isi = HTML yang kami tulis sendiri.
     */
    public static function pasal(): array
    {
        return [
            [
                'judul' => 'Data yang Kami Kumpulkan', 'ikon' => 'bi-person-lines-fill', 'warna' => '#2563eb', 'sorot' => false,
                'isi' => ['Kami mengumpulkan data yang Anda berikan saat bertransaksi, seperti nama, nomor WhatsApp/email, serta detail pesanan. Data ini diperlukan untuk memproses pesanan Anda.'],
            ],
            [
                'judul' => 'Penggunaan Data', 'ikon' => 'bi-clipboard-check', 'warna' => '#16a34a', 'sorot' => false,
                'isi' => ['Data digunakan untuk memproses pesanan, mengirim akun/lisensi, memberi dukungan, dan menginformasikan status transaksi atau promo yang relevan.'],
            ],
            [
                'judul' => 'Keamanan Data', 'ikon' => 'bi-shield-lock-fill', 'warna' => '#7c3aed', 'sorot' => false,
                'isi' => ['Kami menjaga kerahasiaan data Anda dan menerapkan langkah yang wajar untuk melindunginya dari akses yang tidak sah.'],
            ],
            [
                'judul' => 'Berbagi Data', 'ikon' => 'bi-slash-circle', 'warna' => '#e11d48', 'sorot' => true,
                'isi' => ['Kami <b>tidak menjual</b> data pribadi Anda. Data hanya digunakan untuk keperluan transaksi dan operasional layanan Phoenix Digital.'],
            ],
            [
                'judul' => 'Cookies', 'ikon' => 'bi-sliders', 'warna' => '#d97706', 'sorot' => false,
                'isi' => ['Situs kami menggunakan cookies untuk meningkatkan pengalaman penggunaan (mis. keranjang belanja). Anda dapat menonaktifkannya melalui pengaturan browser.'],
            ],
            [
                'judul' => 'Hak Anda', 'ikon' => 'bi-person-check', 'warna' => '#0891b2', 'sorot' => true,
                'isi' => ['Anda berhak meminta perubahan atau penghapusan data pribadi Anda. Hubungi kami untuk mengajukan permintaan tersebut.'],
            ],
        ];
    }
}
