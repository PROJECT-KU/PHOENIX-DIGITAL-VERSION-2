<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class OrderHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 5;

    public $phoneNumber;

    public $invoiceCode;

    protected $rules = [
        'phoneNumber' => 'required|numeric|min_digits:9',
        'invoiceCode' => 'nullable|string',
    ];

    protected $messages = [
        'phoneNumber.required' => 'Nomor HP wajib diisi.',
        'phoneNumber.numeric' => 'Nomor HP harus berupa angka.',
        'phoneNumber.min_digits' => 'Nomor HP minimal 9 digit.',
    ];

    #[Computed()]
    public function myOrders()
    {
        // Jika sudah pulihkan lewat No. HP → tampilkan riwayat berdasar No. HP
        // (bila kode pesanan diisi, hanya pesanan itu yang tampil).
        $phone = Cookie::get('history_phone');

        if ($phone) {
            $code = Cookie::get('history_order');

            return Order::with('items')
                ->where('status', '!=', 'draft')
                ->whereHas('customer', fn ($q) => $q->where('no_hp', $phone))
                ->when($code, fn ($q) => $q->where('order_number', $code))
                ->latest()
                ->paginate($this->perPage);
        }

        // Default: berdasar token perangkat (pesanan yang dibuat di perangkat ini)
        $token = Cookie::get('guest_token');

        if (! $token) {
            // Paginator kosong (agar API-nya konsisten: total(), links(), dll)
            return Order::whereRaw('1 = 0')->paginate($this->perPage);
        }

        return Order::with('items')
            ->where('status', '!=', 'draft')
            ->where('guest_token', $token)
            ->latest()
            ->paginate($this->perPage);
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
        $code = trim((string) $this->invoiceCode);

        // Cari pesanan: berdasar No. HP, dan (opsional) dipersempit ke kode pesanan.
        // Draft (belum bayar) tidak dihitung.
        $orders = Order::where('status', '!=', 'draft')
            ->whereHas('customer', fn ($q) => $q->where('no_hp', $formattedPhone))
            ->when($code !== '', fn ($q) => $q->where('order_number', $code))
            ->get();

        if ($orders->isEmpty()) {
            $this->addError('phoneNumber', $code !== ''
                ? 'Kombinasi Nomor HP dan Kode Pesanan tidak ditemukan.'
                : 'Tidak ada riwayat pesanan untuk nomor HP tersebut.');

            return;
        }

        // Simpan filter tampilan (persist di perangkat ini).
        Cookie::queue('history_phone', $formattedPhone, 2628000);

        if ($code !== '') {
            Cookie::queue('history_order', $code, 2628000);
        } else {
            Cookie::queue(Cookie::forget('history_order'));
        }

        $this->reset('phoneNumber', 'invoiceCode');

        $this->dispatch('restore-success', [
            'message' => $code !== ''
                ? 'Menampilkan pesanan dengan kode '.$code.'.'
                : 'Menampilkan '.$orders->count().' riwayat pesanan untuk nomor Anda.',
        ]);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.order-history');
    }
}
