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
use Livewire\WithFileUploads;

class ProcessOrder extends Component
{
    use WithFileUploads;

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

    // Bonus
    public $bonusDurationValue;

    public $bonusDurationType = 'bulan';

    public $bonusDescription;

    public $selectedEbooks = []; // id ebook yang dipilih dari pustaka

    public $availableEbooks = []; // daftar ebook aktif

    #[Validate('required|in:baru,perpanjang,pengganti')]
    public $subscriptionStatus = 'baru';

    public $processingNotes;

    public $availableAccounts = [];

    public $isLoadingAccounts = false;

    public function mount($id)
    {
        $this->orderItem = OrderItem::FindOrFail($id);
        $this->order = Order::FindOrFail($this->orderItem->order_id);

        $this->startDate = now()->format('Y-m-d');
        $this->calculateEndDate();

        $this->loadAvailableAccounts();

        // Pustaka ebook aktif untuk dipilih
        $this->availableEbooks = \App\Models\Ebook::active()->orderBy('judul')->get();

        // Ebook yang sudah terpilih untuk item ini
        $this->selectedEbooks = $this->orderItem->ebooks()->pluck('ebooks.id')->map(fn ($v) => (string) $v)->toArray();

        if ($this->orderItem->data_akun_id) {
            $this->loadExistingData();
        }
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
        $this->bonusDurationValue = $this->orderItem->bonus_duration_value;
        $this->bonusDurationType = $this->orderItem->bonus_duration_type ?? 'bulan';
        $this->bonusDescription = $this->orderItem->bonus_description;
    }

    // Dipanggil dari picker SweetAlert (JS) saat admin memilih akun
    public function pickAccount($id)
    {
        $this->selectedDataAkunId = $id;
        $this->updatedSelectedDataAkunId($id);
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

    public function updatedBonusDurationValue()
    {
        $this->calculateEndDate();
    }

    public function updatedBonusDurationType()
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
            $endDate = Carbon::parse($this->startDate);

            // Durasi yang dibeli
            $endDate = $this->orderItem->duration_type === 'tahun'
                ? $endDate->addYears($this->orderItem->duration_value)
                : $endDate->addMonths($this->orderItem->duration_value);

            // Bonus durasi (jika diisi)
            if ((int) $this->bonusDurationValue > 0) {
                $endDate = $this->bonusDurationType === 'tahun'
                    ? $endDate->addYears((int) $this->bonusDurationValue)
                    : $endDate->addMonths((int) $this->bonusDurationValue);
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
            'bonusDurationValue' => 'nullable|integer|min:1',
            'bonusDurationType' => 'nullable|in:bulan,tahun',
            'bonusDescription' => 'nullable|string|max:255',
            'selectedEbooks' => 'nullable|array',
            'selectedEbooks.*' => 'exists:ebooks,id',
        ]);

        try {
            DB::beginTransaction();

            $this->orderItem->update([
                'data_akun_id' => $this->selectedDataAkunId,
                'account_username' => $this->accountUsername,
                'account_password' => $this->accountPassword,
                'account_link' => $this->accountLink,
                'account_notes' => $this->accountNotes,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'bonus_duration_value' => $this->bonusDurationValue ?: null,
                'bonus_duration_type' => $this->bonusDurationValue ? $this->bonusDurationType : null,
                'bonus_description' => $this->bonusDescription,
                'subscription_status' => $this->subscriptionStatus,
                'delivery_status' => 'processing',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'processing_notes' => $this->processingNotes,
            ]);

            // Sinkronkan ebook bonus yang dipilih dari pustaka
            $this->orderItem->ebooks()->sync($this->selectedEbooks);

            $this->orderItem->updateRemainingDays();

            $this->order->update([
                'status' => 'processing',
            ]);

            $customer = $this->order->customer;
            if ($customer->status_member === 'active') {
                $customer->updatePoints();
            }

            DB::commit();

            session()->flash('success', 'Order item berhasil diproses');

            return redirect()->route('admin.pesanantoko.detail', [
                'order' => $this->order,
                'orderItem' => $this->orderItem,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memproses order: ' . $e->getMessage());
        }
    }

    public function cancelProcessing()
    {
        return redirect()->route('admin.pesanantoko.index', $this->order);
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.order.process-order');
    }
}
