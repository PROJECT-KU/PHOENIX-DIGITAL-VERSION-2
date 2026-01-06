<?php

namespace App\Livewire\Pages\Admin\CashFlow;

use App\Models\CashFlow;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CashFlowDetail extends Component
{
    public ?CashFlow $cashFlow = null;

    public $isOpen = false;

    protected $listeners = ['openDetail' => 'loadReport'];

    public function loadReport($id)
    {
        $this->cashFlow = CashFlow::with('sourceable')->find($id);
        $this->isOpen = true;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.cash-flow.cash-flow-detail');
    }
}
