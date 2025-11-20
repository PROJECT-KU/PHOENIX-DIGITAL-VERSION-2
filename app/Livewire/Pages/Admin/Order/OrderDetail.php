<?php

namespace App\Livewire\Pages\Admin\Order;

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
        $this->order = $order->load([
            'customer',
            'items.product',
        ]);
    }

    #[On('sent-on-whatsapp')]
    public function updateStatus($id)
    {
        DB::transaction(function () use ($id) {
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
                $this->order->update([
                    'status' => 'completed',
                ]);
            }
        });

        $this->order->refresh();
        $this->dispatch('close-wa-modal');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-detail');
    }
}
