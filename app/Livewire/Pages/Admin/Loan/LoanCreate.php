<?php

namespace App\Livewire\Pages\Admin\Loan;

use Livewire\Attributes\Layout;
use Livewire\Component;

class LoanCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.loan.loan-create')
            ->layout('livewire.layout.templateindex');
    }
}
