<?php

namespace App\Livewire\Pages\Admin\Customer;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CustomerEdit extends Component
{
    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.customer.customer-edit');
    }
}
