<?php

namespace App\Livewire\Pages\Admin\GajiKaryawans;

use App\Models\GajiKaryawans;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GajiKaryawansEdit extends Component
{
    public GajiKaryawans $gajikaryawan;

    public function mount(GajiKaryawans $gajikaryawan)
    {
        $this->gajikaryawan = $gajikaryawan;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.gaji-karyawans.gaji-karyawans-edit', [
            'gajikaryawan' => $this->gajikaryawan,
        ]);
    }
}
