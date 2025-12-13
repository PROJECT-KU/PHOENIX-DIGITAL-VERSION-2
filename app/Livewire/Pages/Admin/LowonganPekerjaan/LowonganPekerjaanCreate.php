<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use Livewire\Attributes\Layout;
use Livewire\Component;

class LowonganPekerjaanCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-create');
    }
}
