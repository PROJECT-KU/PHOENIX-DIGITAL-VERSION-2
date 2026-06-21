<?php

namespace App\Livewire\Pages\Admin\Customer;

use App\Models\Customer;
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

            // Sinkronkan poin sebelum ditampilkan: hitung ulang dari pesanan terbayar
            // yang belum diperhitungkan (mis. order dibuat setelah customer jadi member
            // aktif, atau order yang langsung berstatus paid sehingga event "updated" di
            // OrderObserver tidak terpicu). Aman & idempotent karena updatePoints() hanya
            // memproses pesanan dengan points_calculated = false.
            if ($this->customer->status_member === 'active') {
                $this->customer->updatePoints();
                $this->customer->refresh();
            }

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
            'email' => 'required|email|unique:customers,email,' . ($this->customer->id ?? null),
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
            session()->flash('error', 'Gagal menambahkan customer: ' . $e->getMessage());
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

            if ($this->statusMember === 'active' && empty($this->customer->kode_ref)) {
                $updateData['kode_ref'] = Customer::generateReferralCode();
                $updateData['member_since'] = now();
            }

            $this->customer->update($updateData);

            if ($this->statusMember === 'active') {
                // Selalu sinkronkan poin untuk member aktif (bukan hanya saat baru
                // diaktifkan) agar pesanan baru ikut diperhitungkan. Idempotent.
                $this->customer->updatePoints();

                if ($oldStatus === 'non-active') {
                    if ($this->customer->point > 0) {
                        session()->flash('success', 'Customer berhasil diupdate. Poin member telah dihitung: ' . number_format($this->customer->point, 0, ',', '.') . ' poin dari semua transaksi tahun ini');
                    } else {
                        session()->flash('success', 'Customer berhasil diupdate sebagai member aktif');
                    }
                } else {
                    session()->flash('success', 'Customer berhasil diupdate');
                }
            } else {
                session()->flash('success', 'Customer berhasil diupdate');
            }

            $this->resetForm();
            $this->dispatch('customer-updated');
            $this->redirectRoute('admin.customer.index', navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate customer: ' . $e->getMessage());
            $this->dispatch('failed-update-data-customer');
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
