<?php

namespace App\Livewire\Pages\Admin\Promo;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PromoCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.promo.promo-create');
    }
}
