<?php

namespace App\Livewire\Pages\Admin\Spending;

use Livewire\Attributes\Layout;
use Livewire\Component;

class SpendingCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.spending.spending-create');
    }
}
