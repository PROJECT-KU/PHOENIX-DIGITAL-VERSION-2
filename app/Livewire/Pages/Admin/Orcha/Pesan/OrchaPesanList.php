<?php

namespace App\Livewire\Pages\Admin\Orcha\Pesan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaPesanList extends Component
{
    use MemanggilOrcha;

    public bool $hanyaBelumDibaca = false;

    public function updatedHanyaBelumDibaca(): void
    {
        $this->halaman = 1;
    }

    public function tandaiDibaca(int $id): void
    {
        $this->kirimPerubahan("/pesan/{$id}/dibaca", [], 'Pesan ditandai sudah dibaca.');
    }

    public function render()
    {
        $parameter = $this->parameterDaftar([
            'keperluan' => $this->filterStatus,
            'belum_dibaca' => $this->hanyaBelumDibaca ? 1 : null,
        ]);

        // Halaman ini menyaring per keperluan, bukan status.
        unset($parameter['status']);

        $hasil = $this->muat('/pesan', $parameter);

        return view('livewire.pages.admin.orcha.pesan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanKeperluan' => $this->rujukan('keperluan_kontak'),
        ])->layout('livewire.layout.templateindex');
    }
}
