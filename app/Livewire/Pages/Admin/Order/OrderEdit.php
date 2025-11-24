<?php

namespace App\Livewire\Pages\Admin\Order;

use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderEdit extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-edit');
    }
}
