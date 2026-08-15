<?php

namespace App\Livewire\Pages\Admin\Orcha;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaDashboard extends Component
{
    use MemanggilOrcha;

    public function segarkan(): void
    {
        cache()->forget('orcha.perlu-ditindak');
        cache()->forget('orcha.rujukan');
    }

    public function render()
    {
        $isi = $this->muat('/dashboard')['data'] ?? [];

        return view('livewire.pages.admin.orcha.orcha-dashboard', [
            'kartu' => $isi['kartu'] ?? [],
            'paketPerKategori' => $isi['paket_per_kategori'] ?? [],
            'kendaraanPerJenis' => $isi['kendaraan_per_jenis'] ?? [],
            'pendaftaranTerbaru' => $isi['pendaftaran_terbaru'] ?? [],
            'penyewaanTerbaru' => $isi['penyewaan_terbaru'] ?? [],
            'perluDitindak' => $isi['perlu_ditindak'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
