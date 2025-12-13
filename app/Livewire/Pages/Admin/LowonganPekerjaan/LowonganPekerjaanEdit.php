<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use App\Models\Lowongan;
use Livewire\Attributes\Layout;
use Livewire\Component;

class LowonganPekerjaanEdit extends Component
{
    public Lowongan $lowongan;

    public function mount(Lowongan $lowongan)
    {
        $this->lowongan = $lowongan;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-edit');
    }
}
