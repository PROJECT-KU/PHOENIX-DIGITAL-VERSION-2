<?php

namespace App\Livewire\Pages\Admin\GajiKaryawans;

use Livewire\Attributes\Layout;
use Livewire\Component;

class GajiKaryawansCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.gaji-karyawans.gaji-karyawans-create');
    }
}
