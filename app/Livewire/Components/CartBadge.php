<?php

namespace App\Livewire\Components;

use Livewire\Attributes\On;
use Livewire\Component;

class CartBadge extends Component
{
    public $cartCount = 0;

    public function mount()
    {
        $this->updateCount();
    }

    #[On('cart-updated')]
    public function updateCount()
    {
        $cart = session()->get('cart', []);
        $this->cartCount = array_sum(array_column($cart, 'quantity'));
    }

    public function render()
    {
        return view('livewire.components.cart-badge');
    }
}
