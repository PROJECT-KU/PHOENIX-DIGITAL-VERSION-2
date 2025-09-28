<?php

namespace App\Livewire\Pages\Admin\ProductBundlings;

use App\Models\ProductBundlings;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ProductBundlingsEdit extends Component
{
    public ProductBundlings $ProductBundlings;

    public function mount(ProductBundlings $ProductBundlings)
    {
        $this->ProductBundlings = $ProductBundlings;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.ProductBundlings.ProductBundlings-edit', [
            'ProductBundlings' => $this->ProductBundlings,
        ]);
    }
}
