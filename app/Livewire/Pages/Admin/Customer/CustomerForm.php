<?php

namespace App\Livewire\Pages\Admin\Customer;

use App\Models\Customer;
use App\Models\Order;
use Livewire\Component;

class CustomerForm extends Component
{
    public ?Customer $customer = null;

    public $name = '';

    public $email = '';

    public $phone = '';

    public $statusMember = 'non-active';

    public $mode = 'create';

    public function mount($customer = null)
    {
        if ($customer) {
            $this->customer = $customer;
            $this->name = $this->customer->nama;
            $this->email = $this->customer->email;
            $this->phone = $this->customer->no_hp;
            $this->statusMember = $this->customer->status_member;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.($this->customer->id ?? null),
            'phone' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15',
            'statusMember' => 'required|string',
        ]);
        if ($this->mode === 'create') {
            $this->createCustomer();
        } else {
            $this->updateCustomer();
        }
    }

    private function createCustomer()
    {
        try {
            Customer::create([
                'nama' => $this->name,
                'email' => $this->email,
                'no_hp' => $this->phone,
                'status_member' => $this->statusMember,
            ]);

            $this->dispatch('customer-created');
            $this->resetForm();
            $this->redirectRoute('admin.customer.index', navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan customer: '.$e->getMessage());
            $this->dispatch('failed-create-data-customer');
        }
    }

    private function updateCustomer()
    {
        try {
            $oldStatus = $this->customer->status_member;

            $updateData = [
                'nama' => $this->name,
                'email' => $this->email,
                'no_hp' => $this->phone,
                'status_member' => $this->statusMember,
            ];

            if ($oldStatus === 'non-active' && $this->statusMember === 'active') {
                $updateData['member_since'] = now();

                $this->customer->update($updateData);

                $this->calculatePointsFromLastTransaction();

                if ($this->customer->point > 0) {
                    session()->flash('success', 'Customer berhasil diupdate. Poin member telah dihitung: '.number_format($this->customer->point, 0, ',', '.').' poin dari transaksi terakhir');
                } else {
                    session()->flash('success', 'Customer berhasil diupdate sebagai member aktif');
                }
            } else {
                if ($oldStatus === 'active' && $this->statusMember === 'non-active') {
                    $updateData['member_since'] = null;
                }

                $this->customer->update($updateData);
            }

            $this->resetForm();
            $this->dispatch('customer-updated');
            $this->redirectRoute('admin.customer.index', navigate: true);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate customer: '.$e->getMessage());
            $this->dispatch('failed-update-data-customer');
        }
    }

    private function calculatePointsFromLastTransaction()
    {
        $lastOrder = Order::where('customer_id', $this->customer->id)
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->where('points_calculated', false)
            ->where('used_points', false)
            ->latest('created_at')
            ->first();

        if (! $lastOrder) {
            return;
        }

        $totalAmount = $lastOrder->total + $this->customer->point_balance;

        $newPoints = floor($totalAmount / 50000);
        $newBalance = $totalAmount % 50000;

        $this->customer->update([
            'point' => $this->customer->point + $newPoints,
            'point_balance' => $newBalance,
        ]);

        if ($newPoints > 0) {
            $lastOrder->update(['points_calculated' => true]);
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->statusMember = '';
    }

    public function render()
    {
        return view('livewire.pages.admin.customer.customer-form');
    }
}
