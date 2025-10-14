<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Models\PemesananRsc;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PemesananrscEdit extends Component
{
    public $spendingId;

    public function mount($id)
    {
        $this->spendingId = $id;

        // Verify spending exists
        if (!Spending::find($id)) {
            session()->flash('error', 'Data pengeluaran tidak ditemukan.');
            return redirect()->route('admin.spending.index');
        }
    }
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-edit');
    }
}
