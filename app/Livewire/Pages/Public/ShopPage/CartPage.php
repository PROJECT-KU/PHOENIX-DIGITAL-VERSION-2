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
        $cart = $this->normalizeCart(session()->get('cart', []));
        session()->put('cart', $cart);

        $this->cart = $cart;
        $this->calculateTotal();
    }

    /**
     * Pastikan harga item benar:
     * - Durasi yang ADA di paket → pakai harga paket.
     * - Durasi DI LUAR paket (custom) → harga per bulan × jumlah bulan.
     */
    private function normalizeCart(array $cart): array
    {
        foreach ($cart as $key => $item) {
            // Akun digital: setiap baris selalu 1 item.
            $cart[$key]['quantity'] = 1;

            if (($item['type'] ?? 'product') !== 'product' || empty($item['product_id'])) {
                // Bundling / item non-produk: subtotal = harga baris.
                $cart[$key]['subtotal'] = (int) ($item['price'] ?? $item['subtotal'] ?? 0);

                continue;
            }

            $product = \App\Models\Product::find($item['product_id']);
            if (! $product) {
                $cart[$key]['subtotal'] = (int) ($item['price'] ?? 0);

                continue;
            }

            $type = $item['duration_type'] ?? null;
            $value = (int) ($item['duration_value'] ?? 0);
            if (! $type || $value < 1) {
                $cart[$key]['subtotal'] = (int) ($item['price'] ?? 0);

                continue;
            }

            $inPackages = $product->daftarHarga()
                ->contains(fn ($r) => $r['durasi_type'] === $type && (int) $r['durasi_value'] === $value);

            if ($inPackages) {
                $unit = (int) $product->hargaUntuk($value, $type);
            } else {
                $perBulan = (int) ($product->harga_perbulan ?? 0);
                $unit = ($type === 'bulan' && $perBulan > 0) ? $perBulan * $value : (int) ($item['price'] ?? 0);
            }

            if ($unit > 0) {
                $cart[$key]['price'] = $unit;
                $cart[$key]['subtotal'] = $unit;
            } else {
                $cart[$key]['subtotal'] = (int) ($item['price'] ?? 0);
            }
        }

        return $cart;
    }

    private function calculateTotal()
    {
        $this->total = array_sum(array_column($this->cart, 'subtotal'));
        $this->totalQuantity = count($this->cart);
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

            $this->dispatch('success', 'berhasil hapus produk dari keranjang');
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
