<?php

namespace App\Livewire\Pages\Admin\Product;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProductEdit extends Component
{
    public Product $product;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function render()
    {
        return view('livewire.pages.admin.product.product-edit')
            ->layout('livewire.layout.templateindex');
    }
}
