<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use App\Models\DataAkun;
use Livewire\Component;
use Livewire\Attributes\Layout;
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

        if (!$DataAkun) {
            $this->dispatch('delete-error', ['message' => 'Data Akun tidak ditemukan!'], browserEvent: true);
            return;
        }

        $DataAkun->delete();

        $this->dispatch('DataAkun-deleted', ['id' => $id], browserEvent: true);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        $DataAkuns = DataAkun::latest()
            ->where('nama_akun', 'like', "%{$this->searchDataAkun}%")
            ->orWhere('username_akun', 'like', "%{$this->searchDataAkun}%")
            ->orWhere('link_login_akun', 'like', "%{$this->searchDataAkun}%")
            ->orWhereHas('pj', function ($query) {
                $query->where('name', 'like', "%{$this->searchDataAkun}%");
            })
            ->orWhere('deskripsi', 'like', "%{$this->searchDataAkun}%")
            ->orWhere('status', 'like', "%{$this->searchDataAkun}%")
            ->paginate(10);

        return view('livewire.pages.admin.data-akun.DataAkun-list', [
            'DataAkun' => $DataAkuns
        ]);
    }
}
