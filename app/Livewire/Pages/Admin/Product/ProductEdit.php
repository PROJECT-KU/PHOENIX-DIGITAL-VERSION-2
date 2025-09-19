<?php

namespace App\Livewire\Pages\Admin\Product;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ProductEdit extends Component
{
    public Product $product;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.product.product-edit');
    }
}
