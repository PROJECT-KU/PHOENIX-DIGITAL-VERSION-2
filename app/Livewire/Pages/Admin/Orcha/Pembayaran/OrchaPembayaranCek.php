<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembayaran;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Pembayaran\Concerns\KabarPembayaran;
use Livewire\Component;

/**
 * Lembar cek satu bukti pembayaran.
 *
 * Halaman tersendiri, bukan jendela di atas daftar.
 *
 * Yang dikerjakan di sini adalah memutuskan uang orang lain: nominalnya
 * dicocokkan dengan mutasi rekening, buktinya diperbesar dan dibaca, lalu
 * statusnya ditetapkan — dan penetapan itu mengirim email ke pelanggan yang
 * tidak bisa ditarik kembali. Pekerjaan sepanjang itu pantas mendapat
 * halamannya sendiri, dengan alamat yang bisa disalin dan dibagikan ke admin
 * lain.
 *
 * Ia juga dibuka lewat alamat biasa, jadi tombolnya di daftar cukup berupa
 * tautan: tidak ada yang perlu berhasil lebih dulu sebelum halamannya terbuka.
 */
class OrchaPembayaranCek extends Component
{
    use KabarPembayaran;
    use MemanggilOrcha;

    public int $pembayaranId;

    public array $bukti = [];

    public string $statusBaru = '';

    public string $catatanAdmin = '';

    public function mount(int $pembayaran): void
    {
        $this->pembayaranId = $pembayaran;

        $baris = $this->muat("/pembayaran/{$pembayaran}")['data'] ?? [];

        if ($baris === []) {
            $this->galat = $this->galat ?: 'Bukti pembayaran itu tidak ditemukan di Orcha.';

            return;
        }

        $this->bukti = $baris;
        $this->statusBaru = $baris['status'] ?? 'menunggu';
        $this->catatanAdmin = (string) ($baris['catatan_admin'] ?? '');
    }

    public function simpan(): void
    {
        $this->kirimPerubahan(
            "/pembayaran/{$this->pembayaranId}/status",
            ['status' => $this->statusBaru, 'catatan_admin' => $this->catatanAdmin],
            'Status pembayaran diperbarui di Orcha.'
        );

        // Data di layar disegarkan supaya lencana status dan pesan WhatsApp-nya
        // mengikuti yang BARU tersimpan. Tanpa ini admin membaca kabar lama dan
        // mengirimkannya ke pelanggan.
        $this->bukti = $this->muat("/pembayaran/{$this->pembayaranId}")['data'] ?? $this->bukti;

    }

    /** Kembali ke daftar. Dulu menutup jendela; sekarang berpindah halaman. */
    public function tutup(): void
    {
        $this->redirectRoute('admin.orcha.pembayaran', navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.pembayaran.cek', [
            'pilihanStatus' => $this->rujukan('status_pembayaran'),
        ])->layout('livewire.layout.templateindex');
    }
}
