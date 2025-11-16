<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product = null;

    public function mount($id)
    {
        $this->product = Product::findOrFail($id);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.product-detail');
    }
}
