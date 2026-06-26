<?php

namespace App\Livewire\Pages\Admin\Karyawan;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class KaryawanList extends Component
{
    use WithPagination;

    public $search = '';

    #[On('delete-karyawan-data')]
    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_karyawan')) {
            session()->flash('error', 'Anda tidak memiliki izin menghapus data karyawan.');

            return;
        }

        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('message', 'Karyawan berhasil dihapus.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $users = User::with(['detail', 'role'])
            ->where('name', 'like', '%'.$this->search.'%')
            ->orWhere('email', 'like', '%'.$this->search.'%')
            ->paginate(10);

        return view('livewire.pages.admin.karyawan.karyawan-list', [
            'users' => $users,
        ]);
    }
}
