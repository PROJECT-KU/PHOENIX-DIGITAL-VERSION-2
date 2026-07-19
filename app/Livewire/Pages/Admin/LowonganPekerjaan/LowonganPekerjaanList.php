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

    #[Layout('livewire.layout.templateindex')]
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
        if (! auth()->user()->hasPermission('delete_lowongan')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus data lowongan.');

            return;
        }

        try {
            Lowongan::findOrFail($id)->delete();
            $this->dispatch('swal-success', message: 'Data lowongan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('swal-error', message: 'Gagal menghapus data lowongan.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
