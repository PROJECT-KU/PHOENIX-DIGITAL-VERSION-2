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

    public string $activeTab = 'all';

    protected $queryString = ['activeTab'];

    #[On('delete-customer')]
    public function deleteCustomer($id)
    {
        Customer::findOrFail($id)->delete();

        $this->dispatch('customer-deleted');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $customers = Customer::query()
            ->where(function ($query) {
                $query->where('nama', 'like', "%{$this->searchCustomer}%")
                    ->orWhere('email', 'like', "%{$this->searchCustomer}%")
                    ->orWhere('no_hp', 'like', "%{$this->searchCustomer}%");
            })
            ->when($this->activeTab === 'member', function ($q) {
                $q->where('status_member', 'active');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.customer.customer-list', [
            'customers' => $customers,
        ]);
    }
}
