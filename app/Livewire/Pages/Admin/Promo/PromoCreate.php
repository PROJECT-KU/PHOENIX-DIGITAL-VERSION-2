<?php

namespace App\Livewire\Pages\Admin\Promo;

use Livewire\Component;
use Livewire\Attributes\Layout;

class PromoCreate extends Component
{
 
  #[Layout('layouts.app')]
  public function render()
    {
        return view('livewire.pages.admin.promo.promo-create');
    }
}
