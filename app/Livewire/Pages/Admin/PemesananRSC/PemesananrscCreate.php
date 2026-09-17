<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class PemesananrscCreate extends Component
{
    /** "nama|batch" dari tombol "Salin Batch" di halaman detail. */
    #[Url(as: 'salin')]
    public ?string $salin = null;

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-create');
    }
}
