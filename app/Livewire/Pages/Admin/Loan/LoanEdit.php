<?php

namespace App\Livewire\Pages\Admin\Loan;

use App\Models\Loan;
use Livewire\Component;
use Livewire\Attributes\Layout;

class LoanEdit extends Component
{
    public $loanId;

    public function mount($id)
    {
        $this->loanId = $id;

        // Verify spending exists
        if (!Loan::find($id)) {
            session()->flash('error', 'Data peminjaman tidak ditemukan.');
            return redirect()->route('admin.loan.index');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.loan.loan-edit');
    }
}
