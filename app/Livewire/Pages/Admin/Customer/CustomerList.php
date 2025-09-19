<?php

namespace App\Livewire\Pages\Admin\Customer;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;
    public $searchCustomer = '';

    #[On('delete-customer')]
    public function deleteCustomer($id)
    {
        Customer::findOrFail($id)->delete();

        // event ke browser (misalnya untuk toast notifikasi)
        $this->dispatch('customer-deleted');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $customers = Customer::latest()
            ->where('nama', 'like', "%{$this->searchCustomer}%")
            ->orWhere('email', 'like', "%{$this->searchCustomer}%")
            ->orWhere('no_hp', 'like', "%{$this->searchCustomer}%")
            ->paginate(10);

        return view('livewire.pages.admin.customer.customer-list', [
            'customers' => $customers
        ]);
    }
}
