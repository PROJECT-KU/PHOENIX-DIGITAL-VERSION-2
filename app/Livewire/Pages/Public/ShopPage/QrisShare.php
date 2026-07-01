<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use App\Services\QrisService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class QrisShare extends Component
{
    public Order $order;

    public bool $paid = false;

    public function mount(string $token)
    {
        $this->order = Order::where('share_token', $token)
            ->where('payment_method', 'qris_dinamis')
            ->firstOrFail();

        $this->paid = in_array($this->order->status, ['paid', 'processing', 'completed']);
    }

    public function isExpired(): bool
    {
        return $this->order->expired_at && now()->greaterThan($this->order->expired_at);
    }

    /** Polling status pembayaran dari sisi customer */
    public function checkPayment(): void
    {
        if ($this->paid) {
            return;
        }

        if (in_array($this->order->status, ['paid', 'processing', 'completed'])) {
            $this->paid = true;
            $this->dispatch('qris-paid');

            return;
        }

        $status = app(QrisService::class)->checkStatus($this->order->fresh());

        if ($status === 'paid') {
            $this->order->update(['status' => 'paid', 'paid_at' => now()]);
            $this->order->refresh();
            $this->paid = true;
            $this->dispatch('qris-paid');
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.qris-share');
    }
}
