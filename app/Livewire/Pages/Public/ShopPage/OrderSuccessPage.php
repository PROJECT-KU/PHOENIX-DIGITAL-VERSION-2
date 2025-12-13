<?php

namespace App\Livewire\Pages\Public\ShopPage;

use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderSuccessPage extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.order-success-page');
    }
}
