<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembayaran;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
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
    use MemanggilOrcha;

    public ?int $sedangDicek = null;

    public string $statusBaru = '';

    public string $catatanAdmin = '';

    public function buka(array $baris): void
    {
        $this->sedangDicek = (int) $baris['id'];
        $this->statusBaru = $baris['status'] ?? 'menunggu';
        $this->catatanAdmin = (string) ($baris['catatan_admin'] ?? '');
    }

    public function tutup(): void
    {
        $this->reset(['sedangDicek', 'statusBaru', 'catatanAdmin']);
    }

    public function simpan(): void
    {
        if (! $this->sedangDicek) {
            return;
        }

        $this->kirimPerubahan(
            "/pembayaran/{$this->sedangDicek}/status",
            ['status' => $this->statusBaru, 'catatan_admin' => $this->catatanAdmin],
            'Status pembayaran diperbarui di Orcha.'
        );

        $this->tutup();
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
