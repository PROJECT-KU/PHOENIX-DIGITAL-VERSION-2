<?php

namespace App\Livewire\Pages\Admin\Orcha\Armada;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Daftar armada Orcha beserta tarif bertingkatnya.
 */
class OrchaArmadaList extends Component
{
    use MemanggilOrcha;

    /**
     * Orcha menolak menghapus unit yang sudah pernah disewa — datanya masih
     * dipakai pemesanan lama.
     */
    public function hapus(int $id): void
    {
        $this->hapusData("/kendaraan/{$id}", 'Kendaraan dihapus.');
    }

    public function render()
    {
        $parameter = $this->parameterDaftar();
        unset($parameter['status']);

        if ($this->filterStatus !== '') {
            $parameter['jenis'] = $this->filterStatus;
        }

        $hasil = $this->muat('/kendaraan', $parameter);

        return view('livewire.pages.admin.orcha.armada.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihan' => $this->rujukan('jenis_kendaraan'),
        ])->layout('livewire.layout.templateindex');
    }
}
