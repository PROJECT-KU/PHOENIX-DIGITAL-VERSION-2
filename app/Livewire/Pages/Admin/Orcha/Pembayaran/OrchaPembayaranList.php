<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembayaran;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Pembayaran\Concerns\KabarPembayaran;
use Livewire\Component;

/**
 * Bukti transfer yang dikirim pelanggan lewat formulir di website.
 *
 * Menggantikan kebiasaan mengumpulkan bukti di percakapan WhatsApp: satu open
 * trip berisi enam peserta yang masing-masing membayar dua kali, dan pada H-5
 * pertanyaan "siapa yang belum lunas" harus bisa dijawab dalam hitungan menit.
 */
class OrchaPembayaranList extends Component
{
    use KabarPembayaran;
    use MemanggilOrcha;

    /**
     * Kata cari boleh datang dari alamatnya.
     *
     * Dipakai tautan "Kelola pembayaran ini" di halaman detail pendaftaran:
     * admin yang menekannya sedang melihat SATU pesanan, dan mendaratkannya di
     * seluruh daftar berarti menyuruhnya mengetik ulang kode yang barusan ada
     * di layarnya.
     *
     * Hanya dibaca saat halaman dibuka. Sesudah itu kotak carinya milik admin
     * sepenuhnya — Livewire tidak memuat ulang halaman, jadi ketikannya tidak
     * pernah tertimpa oleh alamat yang sudah lewat.
     *
     * Dipasang di sini, bukan sebagai #[Url] di MemanggilOrcha: trait itu
     * dipakai enam daftar lain, dan menambahkan pengikatan alamat di sana
     * mengubah perilaku semuanya sekaligus demi satu tautan.
     */
    public function mount(): void
    {
        $this->cari = trim((string) request()->query('cari', ''));
    }

    public function render()
    {
        $hasil = $this->muat('/pembayaran', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.pembayaran.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_pembayaran'),
        ])->layout('livewire.layout.templateindex');
    }
}
