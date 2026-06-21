<?php

namespace App\Livewire\Pages\Admin\Promo;

use App\Models\Promo;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PromoEdit extends Component
{
    public Promo $promo;

    public function mount(Promo $promo)
    {
        $this->promo = $promo;
    }

    public function render()
    {
        return view('livewire.pages.admin.promo.promo-edit')
            ->layout('livewire.layout.templateindex');
    }
}
