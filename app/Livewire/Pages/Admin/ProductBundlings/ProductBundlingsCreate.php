<?php

namespace App\Livewire\Pages\Admin\ProductBundlings;

use Livewire\Attributes\Layout;
use Livewire\Component;

class ProductBundlingsCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.ProductBundlings.ProductBundlings-create')
            ->layout('livewire.layout.templateindex');
    }
}
