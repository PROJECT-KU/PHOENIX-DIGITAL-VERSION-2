<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Models\JobApplication;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PelamarKerjaDetail extends Component
{
    public $pelamar;

    public function mount($id)
    {
        $this->pelamar = JobApplication::with('job')->findOrFail($id);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.pelamar-kerja.pelamar-kerja-detail');
    }
}
