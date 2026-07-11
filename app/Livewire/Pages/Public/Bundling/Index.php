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

    // Modal detail bundling
    public bool $showBundleDetail = false;

    public ?array $detailBundle = null;

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

        // Bila ditambah dari popup detail → tutup popup (kembali ke homepage bundling)
        $this->showBundleDetail = false;

        $this->dispatch('cart-updated', count: $this->getCartCount());
        $this->dispatch('cart-success', message: 'Bundling berhasil ditambahkan ke keranjang!');
    }

    /** Buka modal detail bundling. */
    public function openDetail($bundlingId)
    {
        $bundling = ProductBundlings::find($bundlingId);
        if (! $bundling) {
            $this->dispatch('cart-error', message: 'Bundling tidak ditemukan.');

            return;
        }

        $durs = $bundling->durations ?? [];
        $products = [];
        foreach ([1, 2, 3, 4, 5] as $i) {
            $p = $bundling->{'product'.$i};
            if ($p) {
                $dur = $durs['product_'.$i] ?? null;
                $products[] = [
                    'nama' => $p->nama_akun,
                    'dur_value' => (int) ($dur['value'] ?? 1),
                    'dur_type' => ucfirst($dur['type'] ?? 'bulan'),
                ];
            }
        }

        $this->detailBundle = [
            'id' => $bundling->id,
            'nama' => $bundling->nama_paket,
            'gambar' => $bundling->gambar,
            'deskripsi' => $bundling->deskripsi,
            'produk' => $products,
            'harga_awal' => $bundling->harga_awal,
            'harga_bundling' => $bundling->harga_bundling,
        ];
        $this->showBundleDetail = true;
    }

    public function closeDetail()
    {
        $this->showBundleDetail = false;
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
        $bundlings = ProductBundlings::where('status', 'active')->latest()->paginate($this->perPage);

        return view('livewire.pages.public.bundling.index', [
            'bundlings' => $bundlings
        ]);
    }
}
