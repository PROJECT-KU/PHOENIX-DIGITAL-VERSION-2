<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Models\PemesananRsc;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PemesananrscEdit extends Component
{
    public PemesananRsc $pemesananrsc;

    public function mount(PemesananRsc $pemesananrsc)
    {
        $this->pemesananrsc = $pemesananrsc;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-edit', [
            'pemesananrsc' => $this->pemesananrsc,
        ]);
    }
}
