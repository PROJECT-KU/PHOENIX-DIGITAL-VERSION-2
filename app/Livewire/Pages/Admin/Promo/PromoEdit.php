<?php

namespace App\Livewire\Pages\Admin\Promo;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Promo;

class PromoEdit extends Component
{
    public Promo $promo;

    public function mount(Promo $promo)
    {
        $this->promo = $promo;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.promo.promo-edit');
    }
}
