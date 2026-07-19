<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PaymentExpired extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Keamanan — Kepemilikan: hanya perangkat pembeli yang boleh melihat.
        $token = Cookie::get('guest_token');
        if ($order->guest_token && $token !== $order->guest_token) {
            return redirect()->route('homepage');
        }

        // Hanya order yang memang dibatalkan/kedaluwarsa.
        if ($order->status !== 'cancelled') {
            return redirect()->route('payment', $order);
        }

        $this->order = $order;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.payment-expired');
    }
}
