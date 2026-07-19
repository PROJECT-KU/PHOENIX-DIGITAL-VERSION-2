<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PemesananrscCreate extends Component
{
    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-create');
    }
}
