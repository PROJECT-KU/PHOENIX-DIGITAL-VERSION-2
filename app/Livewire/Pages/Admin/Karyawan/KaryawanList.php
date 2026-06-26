<?php

namespace App\Livewire\Pages\Admin\Karyawan;

use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class KaryawanList extends Component
{
    use WithPagination;

    public $search = '';

    public $filterRole = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterRole']);
        $this->resetPage();
    }

    #[On('delete-karyawan-data')]
    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_karyawan')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus data karyawan.');

            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('swal-success', message: 'Data karyawan berhasil dihapus.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $users = User::with(['detail', 'role'])
            ->when($this->filterRole, fn ($q) => $q->where('role_id', $this->filterRole))
            ->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.karyawan.karyawan-list', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'totalKaryawan' => User::count(),
        ]);
    }
}
