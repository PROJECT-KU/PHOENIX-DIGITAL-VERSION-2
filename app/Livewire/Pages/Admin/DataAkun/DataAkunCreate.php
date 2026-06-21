<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use Livewire\Attributes\Layout;
use Livewire\Component;

class DataAkunCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.data-akun.DataAkun-create')
            ->layout('livewire.layout.templateindex');
    }
}
