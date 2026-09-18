<?php

namespace App\Livewire\Pages\Admin\Order;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class OrderCreate extends Component
{
    /** Item pesanan lama yang diperpanjang (dari tab Segera Habis / Akun Habis). */
    #[Url(as: 'perpanjang')]
    public ?string $perpanjang = null;

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-create');
    }
}
