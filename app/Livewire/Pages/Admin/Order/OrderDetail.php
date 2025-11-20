<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderDetail extends Component
{
    public ?Order $order = null;

    public function mount(Order $order)
    {
        $this->order = $order->load([
            'customer',
            'items.product',
        ]);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-detail');
    }
}
