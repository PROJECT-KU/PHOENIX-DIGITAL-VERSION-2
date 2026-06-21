<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use App\Models\DataAkun;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class DataAkunList extends Component
{
    use WithPagination;

    public $searchDataAkun = '';

    public function updatedSearchDataAkun()
    {
        $this->resetPage();
    }

    public function deleteDataAkun($id)
    {
        $DataAkun = DataAkun::find($id);

        // Pastikan data ditemukan
        if (! $DataAkun) {
            $this->dispatch('DataAkunDeleteError', message: 'Data Akun tidak ditemukan!');
            return;
        }

        // Sesuaikan string 'active' dengan nilai yang ada di database Anda (misal: 'Aktif', 'Active', atau 1)
        if (strtolower($DataAkun->status) === 'active' || strtolower($DataAkun->status) === 'aktif') {
            $this->dispatch('DataAkunDeleteError', message: 'Data Akun masih aktif dan tidak bisa dihapus!');
            return;
        }

        // Hapus record dari DB jika status tidak aktif
        $DataAkun->delete();

        $this->dispatch('DataAkunDeleted', id: $id);
    }

    public function render()
    {
        $DataAkuns = DataAkun::latest()
            ->where('nama_akun', 'like', "%{$this->searchDataAkun}%")
            ->orWhere('username_akun', 'like', "%{$this->searchDataAkun}%")
            ->orWhere('password_akun', 'like', "%{$this->searchDataAkun}%")
            ->orWhere('link_login_akun', 'like', "%{$this->searchDataAkun}%")
            ->orWhereHas('pj', function ($query) {
                $query->where('name', 'like', "%{$this->searchDataAkun}%");
            })
            ->orWhere('deskripsi', 'like', "%{$this->searchDataAkun}%")
            ->orWhere('status', 'like', "%{$this->searchDataAkun}%")
            ->paginate(10);

        return view('livewire.pages.admin.data-akun.DataAkun-list', [
            'DataAkun' => $DataAkuns,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
