<?php

namespace App\Livewire\Pages\Admin\ProductBundlings;

use Livewire\Component;
use Livewire\Attributes\Layout;

class ProductBundlingsCreate extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.ProductBundlings.ProductBundlings-create');
    }
}
