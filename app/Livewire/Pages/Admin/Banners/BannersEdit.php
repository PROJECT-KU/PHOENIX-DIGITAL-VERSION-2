<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use Livewire\Attributes\Layout;
use Livewire\Component;

class BannersEdit extends Component
{
    public Banners $Banners;

    public function mount(Banners $Banners)
    {
        $this->Banners = $Banners;
    }

    public function render()
    {
        return view('livewire.pages.admin.Banners.Banners-edit', [
            'Banners' => $this->Banners,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
