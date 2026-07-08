<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product;

    public int $quantity = 1;

    public ?string $durationType = null;

    public ?int $durationValue = null;

    public function mount($id)
    {
        $this->product = Product::findOrFail($id);
    }

    public function selectPackage(string $type, int $value)
    {
        $this->durationType = $type;
        $this->durationValue = $value;
    }

    public function addToCart()
    {
        if (! $this->durationType || ! $this->durationValue) {
            $this->dispatch('cart-error', message: 'Silakan pilih paket harga terlebih dahulu.');

            return;
        }

        $this->quantity = max(1, (int) $this->quantity);

        $price = $this->getPrice($this->product, $this->durationType, $this->durationValue);

        if (! $price) {
            $this->dispatch('cart-error', message: 'Paket tidak valid.');

            return;
        }

        $cart = session()->get('cart', []);
        $cartKey = "{$this->product->id}_{$this->durationType}_{$this->durationValue}";
        $imageName = $this->product->image ? basename($this->product->image) : null;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $this->quantity;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['quantity'] * $cart[$cartKey]['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $this->product->id,
                'product_name' => $this->product->nama_akun,
                'product_image' => $imageName,
                'duration_type' => $this->durationType,
                'duration_value' => $this->durationValue,
                'price' => $price,
                'quantity' => $this->quantity,
                'subtotal' => $price * $this->quantity,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: $this->getCartCount());

        $this->dispatch('cart-success', message: 'Produk berhasil ditambahkan ke keranjang!');
    }

    private function getPrice(Product $product, string $durationType, int $durationValue)
    {
        $harga = $product->hargaUntuk($durationValue, $durationType);

        return $harga > 0 ? $harga : null;
    }

    private function getCartCount(): int
    {
        $cart = session()->get('cart', []);

        return array_sum(array_column($cart, 'quantity') ?: [0]);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.product-detail');
    }
}
