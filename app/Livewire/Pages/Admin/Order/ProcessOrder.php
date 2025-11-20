<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\DataAkun;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProcessOrder extends Component
{
    public OrderItem $orderItem;

    public Order $order;

    // Form fields
    public $selectedDataAkunId;

    public $accountUsername;

    public $accountPassword;

    public $accountLink;

    public $accountNotes;

    #[Validate('required|date')]
    public $startDate;

    public $endDate;

    #[Validate('required|in:baru,perpanjang,pengganti')]
    public $subscriptionStatus = 'baru';

    public $processingNotes;

    // Data untuk dropdown
    public $availableAccounts = [];

    public $isLoadingAccounts = false;

    public function mount($id)
    {
        $this->orderItem = OrderItem::FindOrFail($id);
        $this->order = Order::FindOrFail($this->orderItem->order_id);

        $this->startDate = now()->format('Y-m-d');
        $this->calculateEndDate();

        $this->loadAvailableAccounts();

        // Jika order item sudah pernah diproses, load datanya
        // if ($this->orderItem->data_akun_id) {
        //     $this->loadExistingData();
        // }
    }

    public function loadAvailableAccounts()
    {
        $this->isLoadingAccounts = true;

        $this->availableAccounts = DataAkun::available()
            ->get();

        $this->isLoadingAccounts = false;
    }

    public function loadExistingData()
    {
        $this->selectedDataAkunId = $this->orderItem->data_akun_id;
        $this->accountUsername = $this->orderItem->account_username;
        $this->accountPassword = $this->orderItem->account_password;
        $this->accountLink = $this->orderItem->account_link;
        $this->accountNotes = $this->orderItem->account_notes;
        $this->startDate = $this->orderItem->start_date?->format('Y-m-d');
        $this->endDate = $this->orderItem->end_date?->format('Y-m-d');
        $this->subscriptionStatus = $this->orderItem->subscription_status;
        $this->processingNotes = $this->orderItem->processing_notes;
    }

    public function updatedSelectedDataAkunId($value)
    {
        if (! $value) {
            $this->resetAccountFields();

            return;
        }

        $dataAkun = DataAkun::find($value);

        if ($dataAkun) {
            $this->accountUsername = $dataAkun->username_akun;
            $this->accountPassword = $dataAkun->password_akun;
            $this->accountLink = $dataAkun->link_login_akun;

            session()->flash('info', 'Data akun berhasil dimuat');
        }
    }

    public function updatedStartDate()
    {
        $this->calculateEndDate();
    }

    public function calculateEndDate()
    {
        if (! $this->startDate) {
            $this->endDate = null;

            return;
        }

        try {
            $startDate = Carbon::parse($this->startDate);

            if ($this->orderItem->duration_type === 'tahun') {
                $endDate = $startDate->copy()->addYears($this->orderItem->duration_value);
            } else {
                $endDate = $startDate->copy()->addMonths($this->orderItem->duration_value);
            }

            $this->endDate = $endDate->format('Y-m-d');
        } catch (\Exception $e) {
            $this->endDate = null;
        }
    }

    private function resetAccountFields()
    {
        $this->accountUsername = '';
        $this->accountPassword = '';
        $this->accountLink = '';
    }

    public function processOrder()
    {
        $this->validate([
            'selectedDataAkunId' => 'nullable|exists:data_akuns,id',
            'accountUsername' => 'required|string|max:255',
            'accountPassword' => 'required|string',
            'accountLink' => 'nullable|url|max:255',
            'accountNotes' => 'nullable|string',
            'startDate' => 'required|date',
            'subscriptionStatus' => 'required|in:baru,perpanjang,pengganti',
            'processingNotes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Update order item
            $this->orderItem->update([
                'data_akun_id' => $this->selectedDataAkunId,
                'account_username' => $this->accountUsername,
                'account_password' => $this->accountPassword,
                'account_link' => $this->accountLink,
                'account_notes' => $this->accountNotes,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'subscription_status' => $this->subscriptionStatus,
                'delivery_status' => 'processing',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'processing_notes' => $this->processingNotes,
            ]);

            // Update remaining days
            $this->orderItem->updateRemainingDays();
            // update status order
            $this->order->update([
                'status' => 'processing',
            ]);

            DB::commit();

            session()->flash('success', 'Order item berhasil diproses');

            // Redirect ke halaman deliver
            return redirect()->route('admin.pesanantoko.detail', [
                'order' => $this->order,
                'orderItem' => $this->orderItem,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memproses order: '.$e->getMessage());
        }
    }

    public function cancelProcessing()
    {
        return redirect()->route('admin.pesanantoko.index', $this->order);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.process-order');
    }
}
