<?php

namespace App\Livewire\Pages\Admin\Spending;

use Livewire\Attributes\Layout;
use Livewire\Component;

class SpendingCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.spending.spending-create')
            ->layout('livewire.layout.templateindex');
    }
}
