<?php

namespace App\Livewire\Pages\Admin\Karyawan;

use Livewire\Attributes\Layout;
use Livewire\Component;

class KaryawanCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.karyawan.karyawan-create');
    }
}
