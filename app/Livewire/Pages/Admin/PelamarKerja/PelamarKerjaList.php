<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Models\JobApplication;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PelamarKerjaList extends Component
{
    use WithPagination;
    public $perPage = 10;
    public $search = '';

    #[Layout('layouts.app')]
    public function render()
    {
        $dataPelamar = JobApplication::with('job')
            ->where('name', 'like', "%{$this->search}%")
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.pages.admin.pelamar-kerja.pelamar-kerja-list', [
            'dataPelamar' => $dataPelamar
        ]);
    }
}
