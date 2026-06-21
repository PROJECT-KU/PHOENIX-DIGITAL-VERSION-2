<?php

namespace App\Livewire\Pages\Admin\Customer;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CustomerCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.customer.customer-create')
            ->layout('livewire.layout.templateindex');
    }
}
