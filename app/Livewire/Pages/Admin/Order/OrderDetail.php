<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderDetail extends Component
{
    public ?Order $order = null;

    public ?OrderItem $orderItem = null;

    public function mount(Order $order)
    {
        // Auto-update: tandai item yang end_date-nya sudah lewat menjadi 'habis'
        // dan segarkan sisa hari (sisi otomatis dari penentuan "habis").
        $order->load('items');
        foreach ($order->items as $item) {
            if ($item->end_date) {
                $item->updateRemainingDays();
            }
        }

        $this->order = $order->fresh()->load([
            'customer',
            'items.product', 'items.ebooks', 'items.processedBy',
        ]);
    }

    public function updateSubscriptionStatus(string $itemId, string $status): void
    {
        $allowed = ['baru', 'perpanjang', 'pengganti', 'habis'];

        if (! in_array($status, $allowed, true)) {
            return;
        }

        $item = OrderItem::where('order_id', $this->order->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->update(['subscription_status' => $status]);

        $this->order = $this->order->fresh()->load(['customer', 'items.product', 'items.ebooks', 'items.processedBy']);

        $this->dispatch('subscription-status-updated');
    }

    #[On('sent-on-whatsapp')]
    public function updateStatus($id, SyncCashFlowAction $syncCashFlow)
    {
        DB::transaction(function () use ($id, $syncCashFlow) {
            $this->orderItem = OrderItem::where('order_id', $this->order->id)
                ->where('id', $id)
                ->firstOrFail();

            $this->orderItem->update([
                'delivery_status' => 'delivered',
            ]);

            $masihAdaBelumDelivered = $this->order
                ->items()
                ->where('delivery_status', '!=', 'delivered')
                ->exists();

            if (! $masihAdaBelumDelivered) {
                // Saat pesanan selesai (semua item terkirim), catat WAKTU penyelesaian
                // sebagai tanggal bayar bila belum ada — agar tanggal bayar punya jam/waktu.
                $this->order->update([
                    'status' => 'completed',
                    'paid_at' => $this->order->paid_at ?: now(),
                ]);
            }

            $syncCashFlow->execute($this->order, [
                'amount' => $this->order->total,
                'type' => 'income',
                // Tanggal uang masuk = tanggal bayar (saat completed). Fallback ke tanggal dibuat.
                'date' => $this->order->paid_at ?: $this->order->created_at,
                'category' => 'e-commerce',
                'description' => $this->order->deskripsi ?? 'Pembelian akun dari e-commerce',
            ]);
        });

        $this->order->refresh();
        $this->dispatch('close-wa-modal');
    }

    // Tandai bahwa notifikasi "akun habis" sudah dikirim ke pelanggan via WhatsApp.
    // Tidak mengubah status order/delivery, hanya mencatat waktu pemberitahuan.
    #[On('habis-notified')]
    public function markHabisNotified($id)
    {
        $item = OrderItem::where('order_id', $this->order->id)
            ->where('id', $id)
            ->firstOrFail();

        $item->update(['habis_notified_at' => now()]);

        $this->order = $this->order->fresh()->load(['customer', 'items.product', 'items.ebooks', 'items.processedBy']);
        $this->dispatch('close-wa-modal');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-detail');
    }
}
