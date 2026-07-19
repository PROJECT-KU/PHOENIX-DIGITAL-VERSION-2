<?php

namespace App\Livewire\Pages\Admin\Spending;

use App\Models\Spending;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SpendingEdit extends Component
{
    public $spendingId;

    public function mount($id)
    {
        $this->spendingId = $id;

        // Verify spending exists
        if (! Spending::find($id)) {
            session()->flash('error', 'Data pengeluaran tidak ditemukan.');

            return redirect()->route('admin.spending.index');
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.spending.spending-edit')
            ->layout('livewire.layout.templateindex');
    }
}
