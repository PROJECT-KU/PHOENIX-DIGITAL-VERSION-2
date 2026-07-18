<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderSuccessPage extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Keamanan 1 — Kepemilikan: hanya perangkat pembeli (guest_token cocok) yang boleh melihat.
        $token = Cookie::get('guest_token');
        if ($order->guest_token && $token !== $order->guest_token) {
            return redirect()->route('homepage');
        }

        // Keamanan 2 — Status: hanya order yang benar-benar sudah dibayar.
        if (! in_array($order->status, ['paid', 'completed'])) {
            return redirect()->route('payment', $order);
        }

        // Untuk pesanan jasa: unggah & unduh hasil dipindah ke halaman pengecekan
        // ber-link permanen (/cek/{token}) — halaman ini hanya menautkan ke sana.
        $order->load(['items.product']);
        $this->order = $order;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.order-success-page', [
            'order' => $this->order,
        ]);
    }
}
