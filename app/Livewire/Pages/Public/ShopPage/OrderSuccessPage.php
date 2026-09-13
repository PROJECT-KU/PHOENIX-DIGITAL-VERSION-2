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
        $pembeli = $this->order->customer;
        $memberAktif = $pembeli && $pembeli->status_member === 'active';

        /*
         | Kadaluarsa tahunan hanya DIBACA di sini, tidak diterapkan.
         |
         | Customer::applyYearlyExpiry() menyimpan ke database, dan halaman ini
         | cuma menggambar hasil pesanan — sebuah GET tidak boleh mengubah data
         | pelanggan hanya karena halamannya dibuka (atau dimuat ulang, atau
         | dibuka perayap). Cukup jangan tampilkan poin yang tahunnya sudah
         | lewat; penolkan yang sesungguhnya tetap terjadi di titik pakai.
         |
         | Bagi member yang baru saja membayar, OrderObserver sudah memanggil
         | updatePoints() — yang di dalamnya menerapkan kadaluarsa — jadi angka
         | di sini memang angka terbaru. Penjagaan ini untuk sisanya: pesanan
         | yang memakai poin (used_points) dilewati observer, sehingga poinnya
         | bisa saja masih milik tahun lalu.
         */
        $lewatTahun = $pembeli
            && $pembeli->points_year !== null
            && (int) $pembeli->points_year < now()->year;

        $poinTampil = $memberAktif && ! $lewatTahun;

        return view('livewire.pages.public.shop-page.order-success-page', [
            'order' => $this->order,
            'memberAktif' => $memberAktif,
            'poin' => $poinTampil ? (int) $pembeli->point : 0,
            'sisaPoin' => $poinTampil ? (int) $pembeli->point_balance : 0,
        ]);
    }
}
