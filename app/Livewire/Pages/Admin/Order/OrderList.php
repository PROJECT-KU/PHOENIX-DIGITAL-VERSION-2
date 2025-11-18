<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    public string $activeTab = 'all';
    public string $search = '';

    protected $queryString = ['activeTab'];

    public string $searchInput = '';

    public function searchCustomer()
    {
        $this->search = $this->searchInput;

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->searchInput = '';
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        return Order::query()
            ->with('customer')
            ->when($this->search, function ($q) {
                $q->whereHas('customer', function ($q2) {
                    $q2->where('no_hp', 'like', "%{$this->search}%");
                })
                    ->orWhere('order_number', 'like', "%{$this->search}%");
            })
            ->when($this->activeTab === 'processing', function ($q) {
                $q->where('status', 'processing');
            })
            ->when($this->activeTab === 'completed', function ($q) {
                $q->where('status', 'completed');
            })
            ->when($this->activeTab === 'neworder', function ($q) {
                $q->where('status', 'paid');
            })
            ->when($this->activeTab === 'cancelled', function ($q) {
                $q->where('status', 'cancelled');
            })
            ->latest()
            ->paginate(20);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-list', [
            'orders' => $this->orders
        ]);
    }
}
