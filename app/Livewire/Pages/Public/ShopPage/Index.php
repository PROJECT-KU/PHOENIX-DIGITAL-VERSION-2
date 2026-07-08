<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Product;
use App\Services\PromoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $perPage = 8;

    public $search = '';

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount()
    {
        $this->search = request('search', '');
    }

    #[On('search-updated')]
    public function updateSearch($search)
    {
        $this->search = $search;

        if (! empty(trim($search))) {
            $this->redirect('/shop?search=' . urlencode($search));
        } else {
            $this->redirect('/shop', navigate: true);
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->redirect('/shop', navigate: true);
    }

    public function addToCart($productId, $durationType, $durationValue)
    {
        $product = Product::findOrFail($productId);

        // Tentukan harga berdasarkan durasi
        $price = $this->getPrice($product, $durationType, $durationValue);

        if (! $price) {
            session()->flash('error', 'Paket yang dipilih tidak tersedia');

            return;
        }

        // Get cart dari session atau buat array kosong
        $cart = session()->get('cart', []);

        // Generate unique key untuk cart item
        $cartKey = "{$productId}_{$durationType}_{$durationValue}";

        // Cek apakah item sudah ada di cart
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity']++;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['quantity'] * $cart[$cartKey]['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $productId,
                'product_name' => $product->nama_akun,
                'product_image' => $product->image,
                'duration_type' => $durationType,
                'duration_value' => $durationValue,
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ];
        }
        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: $this->getCartCount());
        // $this->dispatch('success-add-to-cart');
        $this->dispatch('cart-success', message: 'Produk berhasil ditambahkan ke keranjang!');
    }

    private function getPrice($product, $durationType, $durationValue)
    {
        $harga = $product->hargaUntuk((int) $durationValue, $durationType);

        return $harga > 0 ? $harga : null;
    }

    private function getCartCount()
    {
        $cart = session()->get('cart', []);

        return array_sum(array_column($cart, 'quantity'));
    }

    public function getProductPromos($productId)
    {
        return $this->promoService->getProductPromos($productId, null);
    }

    public function getBestDiscount($productId)
    {
        return $this->promoService->getBestProductDiscount($productId, null);
    }


    public function loadMore()
    {
        $this->perPage += 12;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $products = Product::when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nama_akun', 'like', "%{$this->search}%")
                    ->orWhere('deskripsi', 'like', "%{$this->search}%");
            });
        })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.pages.public.shop-page.index', [
            'products' => $products,
        ]);
    }
}
