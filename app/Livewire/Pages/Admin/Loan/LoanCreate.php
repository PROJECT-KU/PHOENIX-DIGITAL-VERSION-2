<?php

namespace App\Livewire\Pages\Admin\Loan;

use Livewire\Component;
use Livewire\Attributes\Layout;

class LoanCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.loan.loan-create');
    }
}
