<?php

namespace App\Livewire\Pages\Admin\Orcha\PaketWisata;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Daftar paket wisata Orcha.
 *
 * Datanya dari API; penambahan dan pengubahannya lewat OrchaPaketForm.
 */
class OrchaPaketList extends Component
{
    use MemanggilOrcha;

    /**
     * Orcha menolak menghapus paket yang sudah punya pendaftar. Alasannya
     * diteruskan apa adanya ke admin lewat toast.
     */
    public function hapus(int $id): void
    {
        $this->hapusData("/paket-wisata/{$id}", 'Paket wisata dihapus.');
    }

    public function render()
    {
        $parameter = $this->parameterDaftar();
        unset($parameter['status']);

        if ($this->filterStatus !== '') {
            $parameter['kategori'] = $this->filterStatus;
        }

        $hasil = $this->muat('/paket-wisata', $parameter);

        return view('livewire.pages.admin.orcha.paket-wisata.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihan' => $this->rujukan('kategori_paket'),
        ])->layout('livewire.layout.templateindex');
    }
}
