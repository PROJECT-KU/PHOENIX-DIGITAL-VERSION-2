<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use App\Models\Job;
use Livewire\Attributes\Layout;
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
        $dataLowongan = Job::latest()
            ->where('title', 'like', "%{$this->search}%")
            ->paginate($this->perPage);

        return view('livewire.pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-list', [
            'dataLowongan' => $dataLowongan
        ]);
    }
}
