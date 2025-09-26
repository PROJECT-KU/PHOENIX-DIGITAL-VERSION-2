<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use Livewire\Component;
use Livewire\Attributes\Layout;

class BannersEdit extends Component
{
    public Banners $Banners;

    public function mount(Banners $Banners)
    {
        $this->Banners = $Banners;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.Banners.Banners-edit', [
            'Banners' => $this->Banners,
        ]);
    }
}
