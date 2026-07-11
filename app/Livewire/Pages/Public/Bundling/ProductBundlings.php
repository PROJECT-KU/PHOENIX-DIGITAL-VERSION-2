<?php

namespace App\Livewire\Pages\Public\Bundling;

use App\Models\ProductBundlings as ModelsProductBundlings;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ProductBundlings extends Component
{
    use WithPagination;
    public $perPage = 4;
    protected $paginationTheme = 'bootstrap';
    public $search = '';

    public function mount()
    {
        $this->search = request('search', '');
    }

    #[On('search-updated')]
    public function updateSearch($search)
    {
        // $this->search = $search;

        // if (!empty(trim($search))) {
        //     $this->redirect('/shop?search=' . urlencode($search));
        // } else {
        //     $this->redirect('/shop', navigate: true);
        // }
    }

    public function clearSearch()
    {
        // $this->search = '';
        // $this->redirect('/shop', navigate: true);
    }

    public function addToCart($bundlingId)
    {
        // dd($bundlingId);
        $bundling = ModelsProductBundlings::findOrFail($bundlingId);

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
        $this->dispatch('cart-success', message: 'Bundling berhasil ditambahkan ke keranjang!');
    }

    private function getCartCount(): int
    {
        $cart = session()->get('cart', []);
        return array_sum(array_column($cart, 'quantity') ?: [0]);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $bundlings = ModelsProductBundlings::with([
            'product1',
            'product2',
            'product3',
            'product4',
            'product5',
        ])
            ->where('status', 'active')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_paket', 'like', "%{$this->search}%")
                        ->orWhere('deskripsi', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.pages.public.bundling.product-bundlings', [
            'bundlings' => $bundlings,
        ]);
    }
}
