<?php

namespace App\Livewire\Pages\Public\ShopPage;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Order;

class OrderSuccessPage extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.order-success-page', [
            'order' => $this->order,
        ]);
    }
}
