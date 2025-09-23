<?php

namespace App\Livewire\Pages\Admin\DataAkun;

use App\Models\DataAkun;
use Livewire\Component;
use Livewire\Attributes\Layout;

class DataAkunEdit extends Component
{
    public DataAkun $dataAkun;

    public function mount(DataAkun $dataAkun)
    {
        $this->dataAkun = $dataAkun;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.data-akun.DataAkun-edit');
    }
}
