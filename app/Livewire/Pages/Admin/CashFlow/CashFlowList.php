<?php

namespace App\Livewire\Pages\Admin\CashFlow;

use App\Models\CashFlow;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class CashFlowList extends Component
{
    use WithPagination;

    public $dateFilter;

    public $typeFilter;

    #[Layout('layouts.app')]
    public function render()
    {
        $query = CashFlow::with('sourceable')
            ->latest('transaction_date');
        if ($this->dateFilter) {
            $query->whereDate('transaction_date', $this->dateFilter);
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        return view('livewire.pages.admin.cash-flow.cash-flow-list', [
            'reports' => $query->paginate(10),
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $totalIncome - $totalExpense,
            ],
        ]);
    }
}
