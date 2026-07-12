<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Models\Testimoni;
use Livewire\Component;
use Livewire\WithPagination;

class TestimoniList extends Component
{
    use WithPagination;

    public $searchTestimoni = '';

    public function updatedSearchTestimoni()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        if (! auth()->user()->hasPermission('edit_testimoni')) {
            $this->dispatch('testimoni-deleteError', message: 'Anda tidak memiliki izin mengubah status.');

            return;
        }

        $testimoni = Testimoni::find($id);
        if (! $testimoni) {
            $this->dispatch('testimoni-deleteError', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        $testimoni->status = $testimoni->status === 'active' ? 'non-active' : 'active';
        $testimoni->save();

        $this->dispatch('testimoni-status', active: $testimoni->status === 'active');
    }

    public function deleteTestimoni($id)
    {
        if (! auth()->user()->hasPermission('delete_testimoni')) {
            $this->dispatch('testimoni-deleteError', message: 'Anda tidak memiliki izin menghapus testimoni.');

            return;
        }

        $testimoni = Testimoni::find($id);

        if (! $testimoni) {
            $this->dispatch('testimoni-deleteError', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        if ($testimoni->foto) {
            $filePath = storage_path('app/public/img/testimoni/' . $testimoni->foto);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $testimoni->delete();

        $this->dispatch('testimoni-deleted', id: $id);
    }

    public function render()
    {
        $Testimoni = Testimoni::query()
            ->where(function ($q) {
                $q->where('nama', 'like', "%{$this->searchTestimoni}%")
                    ->orWhere('peran', 'like', "%{$this->searchTestimoni}%")
                    ->orWhere('pesan', 'like', "%{$this->searchTestimoni}%")
                    ->orWhere('status', 'like', "%{$this->searchTestimoni}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.testimoni.testimoni-list', [
            'Testimoni' => $Testimoni,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
