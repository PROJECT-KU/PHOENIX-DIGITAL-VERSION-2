<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderHistory extends Component
{
    public $phoneNumber;

    public $invoiceCode;

    protected $rules = [
        'phoneNumber' => 'required|numeric|min_digits:9',
        'invoiceCode' => 'required|string',
    ];

    #[Computed()]
    public function myOrders()
    {
        $token = Cookie::get('guest_token');

        if (! $token) {
            return collect([]);
        }

        return Order::with('items')
            ->where('guest_token', $token)
            ->latest()
            ->get();
    }

    private function normalizePhoneNumber($number)
    {
        $number = preg_replace('/[^0-9+]/', '', $number);

        if (str_starts_with($number, '0')) {
            return '+62'.substr($number, 1);
        }

        if (str_starts_with($number, '62')) {
            return '+'.$number;
        }

        return $number;
    }

    public function restoreSession()
    {
        $this->validate();

        $formattedPhone = $this->normalizePhoneNumber($this->phoneNumber);

        $targetOrder = Order::where('order_number', $this->invoiceCode)
            ->first();

        if (! $targetOrder || $targetOrder->customer->no_hp !== $formattedPhone) {

            $this->addError('invoiceCode', 'Kombinasi Nomor HP dan Kode Invoice tidak ditemukan.');

            return;
        }

        $masterToken = $targetOrder->guest_token;
        $currentToken = Cookie::get('guest_token');

        if ($masterToken === $currentToken) {
            // $this->reset('phoneNumber', 'invoiceCode');

            $this->dispatch('restore-success', [
                'message' => 'Riwayat pesanan anda sudah tampil.',
            ]);

            // $this->dispatch('success', message: 'Riwayat pesanan Anda sudah tampil.');

            return;
        }

        DB::beginTransaction();
        try {
            if ($currentToken) {
                Order::where('guest_token', $currentToken)
                    ->update(['guest_token' => $masterToken]);
            }

            Cookie::queue('guest_token', $masterToken, 2628000);

            DB::commit();

            $this->reset('phoneNumber', 'invoiceCode');

            $this->dispatch('restore-success', [
                'message' => 'Berhasil memulihkan riwayat pesanan!',
            ]);

            // $this->dispatch('success', message: 'Berhasil memulihkan riwayat pesanan!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Terjadi kesalahan sistem.');
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.order-history');
    }
}
