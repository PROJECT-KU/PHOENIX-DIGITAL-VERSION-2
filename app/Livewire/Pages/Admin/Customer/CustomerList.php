<?php

namespace App\Livewire\Pages\Admin\Customer;

use App\Models\Customer;
use App\Models\Promo;
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
        if (! auth()->user()->hasPermission('delete_customer')) {
            $this->dispatch('customer-delete-error', message: 'Anda tidak memiliki izin menghapus pelanggan.');

            return;
        }

        Customer::findOrFail($id)->delete();

        $this->dispatch('customer-deleted');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::query()
            ->where(function ($query) {
                $query->where('nama', 'like', "%{$this->searchCustomer}%")
                    ->orWhere('email', 'like', "%{$this->searchCustomer}%")
                    ->orWhere('no_hp', 'like', "%{$this->searchCustomer}%")
                    ->orWhere('kode_ref', 'like', "%{$this->searchCustomer}%")
                    ->orWhere('status_member', 'like', "%{$this->searchCustomer}%")
                    ->orWhere('point', 'like', "%{$this->searchCustomer}%");
            })
            ->when($this->activeTab === 'member', function ($q) {
                $q->where('status_member', 'active');
            })
            ->latest()
            ->paginate(10);

        // Pengaman kadaluarsa tahunan: nolkan poin milik tahun sebelumnya pada
        // baris halaman ini (self-healing bila command terjadwal terlewat).
        foreach ($customers as $customer) {
            $customer->applyYearlyExpiry();
        }

        $activePromos = Promo::active()
            ->orderByDesc('prioritas')
            ->orderByDesc('mulai_promo')
            ->get();

        return view('livewire.pages.admin.customer.customer-list', [
            'customers' => $customers,
            'activePromos' => $activePromos,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
