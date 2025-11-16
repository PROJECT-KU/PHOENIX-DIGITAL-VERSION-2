<?php

namespace App\Livewire\Pages\Public\ShopPage;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class CartPage extends Component
{
    public $cart = [];
    public $total = 0;
    public $totalQuantity = 0;

    public function mount()
    {
        $this->loadCart();
    }

    private function loadCart()
    {
        $this->cart = session()->get('cart', []);
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->total = array_sum(array_column($this->cart, 'subtotal'));
        $this->totalQuantity = array_sum(array_column($this->cart, 'quantity'));
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity < 1) {
            $this->removeItem($cartKey);
            return;
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $quantity;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['price'] * $quantity;

            session()->put('cart', $cart);
            $this->loadCart();

            $this->dispatch('cart-updated');
        }
    }

    #[On('delete-product-cart')]
    public function removeItem($cartKey)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            session()->put('cart', $cart);

            $this->loadCart();
            $this->dispatch('cart-updated');

            $this->dispatch('success-delete-data');
        }
    }

    #[On('empty-cart')]
    public function clearCart()
    {
        session()->forget('cart');
        $this->loadCart();
        $this->dispatch('cart-updated');

        $this->dispatch('success-delete-data');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.cart-page');
    }
}
