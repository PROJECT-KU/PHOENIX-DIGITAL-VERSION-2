<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Product;
use App\Models\ProductBundlings;
use Livewire\Attributes\Layout;
use Livewire\Component;

class WishlistPage extends Component
{
    public array $productIds = [];

    /** Dipanggil dari Alpine (baca localStorage) — tidak menyentuh logika lain. */
    public function load($ids): void
    {
        $ids = is_array($ids) ? $ids : [];
        $this->productIds = array_values(array_filter(array_map('strval', $ids)));
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $products = ! empty($this->productIds)
            ? Product::whereIn('id', $this->productIds)->get()
            : collect();

        // Paket bundling ikut bisa disimpan. Tanpa ini id paket tetap terhitung
        // di lencana header tetapi tidak pernah muncul di halaman wishlist.
        // Hanya yang masih tayang: paket berjadwal bisa sudah berakhir.
        $pakets = ! empty($this->productIds)
            ? ProductBundlings::tayang()->whereIn('id', $this->productIds)->get()
            : collect();

        return view('livewire.pages.public.shop-page.wishlist-page', [
            'products' => $products,
            'pakets' => $pakets,
        ]);
    }
}
