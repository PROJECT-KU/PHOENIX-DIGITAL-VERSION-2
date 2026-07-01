<?php

namespace App\Livewire\Pages\Admin\Pengembalian;

use App\Models\Pengembalian;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PengembalianEdit extends Component
{
    public $pengembalianId;

    public function mount($id)
    {
        $this->pengembalianId = $id;

        // Verify spending exists
        if (! Pengembalian::find($id)) {
            session()->flash('error', 'Data pengembalian tidak ditemukan.');

            return redirect()->route('admin.pengembalian.index');
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.pengembalian.pengembalian-edit')
            ->layout('livewire.layout.templateindex');
    }
}
