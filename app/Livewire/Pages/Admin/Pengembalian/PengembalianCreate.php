<?php

namespace App\Livewire\Pages\Admin\Pengembalian;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PengembalianCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.pengembalian.pengembalian-create')
            ->layout('livewire.layout.templateindex');
    }
}
