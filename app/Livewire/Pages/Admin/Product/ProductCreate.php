<?php

namespace App\Livewire\Pages\Admin\Product;

use Livewire\Attributes\Layout;
use Livewire\Component;

class ProductCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.product.product-create');
    }
}
