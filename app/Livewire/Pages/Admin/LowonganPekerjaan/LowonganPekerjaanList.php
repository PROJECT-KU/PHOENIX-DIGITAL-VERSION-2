<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use App\Models\Lowongan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LowonganPekerjaanList extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    #[Layout('layouts.app')]
    public function render()
    {
        $dataLowongan = Lowongan::latest()
            ->where('title', 'like', "%{$this->search}%")
            ->paginate($this->perPage);

        return view('livewire.pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-list', [
            'dataLowongan' => $dataLowongan,
        ]);
    }

    #[On('delete-lowongan-data')]
    public function delete($id)
    {
        try {
            Lowongan::findOrFail($id)->delete();
            session()->flash('success', 'berhasil menghapus data lowongan');
        } catch (\Exception $e) {
            session()->flash('error', 'gagal menghapus data lowongan');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
