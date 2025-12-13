<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DeliverOrder extends Component
{
    public Order $order;

    public OrderItem $orderItem;

    public $deliveryNotes;

    public function mount(Order $order, OrderItem $orderItem)
    {
        $this->order = $order;
        $this->orderItem = $orderItem;

        // Validasi bahwa order item sudah diproses
        if ($this->orderItem->delivery_status !== 'processing') {
            session()->flash('error', 'Order item belum diproses');

            return redirect()->route('admin.pesanantoko.index', $this->order);
        }
    }

    public function deliverToCustomer()
    {
        $this->validate([
            'deliveryNotes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Update order item status
            $this->orderItem->update([
                'is_delivered' => true,
                'delivered_at' => now(),
                'delivery_status' => 'delivered',
                'processing_notes' => $this->orderItem->processing_notes."\n\nDelivery Notes: ".$this->deliveryNotes,
            ]);

            // Check apakah semua items sudah delivered
            $allDelivered = $this->order->items()
                ->where('is_delivered', false)
                ->count() === 0;

            if ($allDelivered) {
                $this->order->update(['status' => 'completed']);
            }

            DB::commit();

            // TODO: Send email/WhatsApp notification ke customer
            $this->sendDeliveryNotification();

            session()->flash('success', 'Akun berhasil dikirim ke pelanggan');

            return redirect()->route('admin.pesanantoko.index', $this->order);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengirim akun: '.$e->getMessage());
        }
    }

    private function sendDeliveryNotification()
    {
        // Kirim email ke customer dengan detail akun
        // Mail::to($this->order->customer->email)->send(new AccountDeliveredMail($this->orderItem));

        // Kirim WhatsApp (opsional)
        // WhatsAppService::send($this->order->customer->no_hp, $message);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.deliver-order');
    }
}
