<?php

namespace App\Livewire\Pages\Public\Bundling;

use Livewire\Component;
use App\Models\ProductBundlings;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Index extends Component
{
    public $perPage = 8;

    use WithPagination;

    public function addToCart($bundlingId)
    {
        $bundling = ProductBundlings::findOrFail($bundlingId);

        if (!$bundling) {
            $this->dispatch('cart-error', message: 'Bundling tidak ditemukan');
            return;
        }

        $cart = session()->get('cart', []);
        $cartKey = "bundling_{$bundling->id}";
        $imageName = $bundling->gambar ? basename($bundling->gambar) : null;
        $price = (int) preg_replace('/[^0-9]/', '', $bundling->harga_bundling);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity']++;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['quantity'] * $cart[$cartKey]['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $bundling->id,
                'product_name' => $bundling->nama_paket,
                'product_image' => $imageName,
                'duration_type' => null,
                'duration_value' => null,
                'type' => 'bundling',
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price
            ];
        }
        // session()->put('cart', $cart);

        // $this->dispatch('cart-updated', count: $this->getCartCount());
        // $this->dispatch('cart-success', message: 'Bundling berhasil ditambahkan ke keranjang!');
        // $this->dispatch('redirect-home');

        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: $this->getCartCount());
        $this->dispatch('success-add-to-cart');
    }

    private function getCartCount(): int
    {
        $cart = session()->get('cart', []);
        return array_sum(array_column($cart, 'quantity') ?: [0]);
    }

    public function loadMore()
    {
        $this->perPage += 12;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $bundlings = ProductBundlings::latest()->paginate($this->perPage);

        return view('livewire.pages.public.bundling.index', [
            'bundlings' => $bundlings
        ]);
    }
}
