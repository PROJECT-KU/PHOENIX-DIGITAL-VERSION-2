<?php

namespace App\Livewire\Pages\Admin\Orcha\Penyewaan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Satu penyewaan, selengkapnya.
 *
 * Daftar sewa menjawab "siapa yang menyewa apa". Halaman ini menjawab yang
 * ditanyakan sesudahnya, dan biasanya saat unitnya sedang berdiri di depan
 * loket: jam berapa seharusnya kembali, sudah telat berapa lama, bagian mana
 * yang rusak dibanding saat diserahkan, dan berapa yang harus ditagih.
 */
class OrchaPenyewaanDetail extends Component
{
    use MemanggilOrcha;

    public int $penyewaanId;

    public array $data = [];

    public function mount(int $penyewaan): void
    {
        $this->penyewaanId = $penyewaan;
    }

    public function ubahStatus(string $status): void
    {
        $this->kirimPerubahan(
            "/penyewaan/{$this->penyewaanId}/status",
            ['status' => $status],
            'Status pemesanan sewa diperbarui di Orcha.'
        );
    }

    public function render()
    {
        $hasil = $this->muat("/penyewaan/{$this->penyewaanId}");
        $this->data = $hasil['data'] ?? [];

        return view('livewire.pages.admin.orcha.penyewaan.detail', [
            'sewa' => $this->data,
            'pilihanStatus' => $this->rujukan('status_penyewaan'),
            'bagianPeriksa' => $this->rujukan('pemeriksaan_kendaraan'),
            'pilihanKondisi' => $this->rujukan('kondisi_pemeriksaan'),
        ])->layout('livewire.layout.templateindex');
    }
}
