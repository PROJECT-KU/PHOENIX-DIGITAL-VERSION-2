<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use App\Services\PaymentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PaymentPage extends Component
{
    public Order $order;
    public $snapToken;
    public $paymentUrl;

    public function mount(Order $order)
    {
        // Verify order belongs to current session
        if (!$order) {
            session()->flash('error', 'Order tidak ditemukan');
            return redirect()->route('shop.index');
        }

        // Check if order already paid
        if ($order->status === 'paid') {
            session()->flash('info', 'Order sudah dibayar');
            return redirect()->route('order.success', $order);
        }

        // Check if order expired
        if ($order->isExpired()) {
            session()->flash('error', 'Order sudah kadaluarsa');
            return redirect()->route('shop.index');
        }

        $this->order = $order;

        // Create or get payment
        if (!$order->payment_url) {
            $paymentService = new PaymentService();
            $result = $paymentService->createPayment($order);

            if ($result['success']) {
                $this->snapToken = $result['snap_token'];
                $this->paymentUrl = $result['snap_url'];
            } else {
                session()->flash('error', 'Gagal membuat pembayaran: ' . $result['message']);
            }
        } else {
            $this->paymentUrl = $order->payment_url;
        }
    }

    public function checkPaymentStatus()
    {
        $this->order->refresh();

        if ($this->order->status === 'paid') {
            session()->flash('success', 'Pembayaran berhasil!');
            return redirect()->route('order.success', $this->order);
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.payment-page');
    }
}
