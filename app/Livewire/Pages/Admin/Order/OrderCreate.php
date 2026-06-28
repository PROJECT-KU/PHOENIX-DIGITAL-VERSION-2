<?php

namespace App\Livewire\Pages\Admin\Order;

use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderCreate extends Component
{
    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-create');
    }
}
