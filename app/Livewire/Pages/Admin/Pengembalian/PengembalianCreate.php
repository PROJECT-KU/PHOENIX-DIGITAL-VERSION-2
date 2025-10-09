<?php

namespace App\Livewire\Pages\Admin\Pengembalian;

use Livewire\Component;
use Livewire\Attributes\Layout;

class PengembalianCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.pengembalian.pengembalian-create');
    }
}
