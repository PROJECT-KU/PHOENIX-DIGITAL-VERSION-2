<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use Livewire\Component;
use Livewire\Attributes\Layout;

class DataAkunCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.data-akun.DataAkun-create');
    }
}
