<?php

namespace App\Livewire\Pages\Admin\Banners;

use Livewire\Component;
use Livewire\Attributes\Layout;

class BannersCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.Banners.Banners-create');
    }
}
