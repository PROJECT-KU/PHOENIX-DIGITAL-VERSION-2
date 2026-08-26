<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembatalan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaPembatalanList extends Component
{
    use MemanggilOrcha;

    public function render()
    {
        $hasil = $this->muat('/pembatalan', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.pembatalan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_pembatalan'),
        ])->layout('livewire.layout.templateindex');
    }
}
