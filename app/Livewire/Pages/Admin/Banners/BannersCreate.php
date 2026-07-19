<?php

namespace App\Livewire\Pages\Admin\Banners;

use Livewire\Attributes\Layout;
use Livewire\Component;

class BannersCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.Banners.Banners-create')
            ->layout('livewire.layout.templateindex');
    }
}
