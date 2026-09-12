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

    /**
     * Kueri dasar riwayat — dipakai daftar pesanan DAN ringkasannya, supaya
     * angka di ringkasan tidak mungkin berbeda dari isi daftarnya.
     * null = perangkat ini belum punya riwayat sama sekali.
     */
    private function kueriRiwayat()
    {
        // Sudah dipulihkan lewat No. HP → riwayat berdasar No. HP (bila kode
        // pesanan diisi, hanya pesanan itu yang tampil).
        $phone = Cookie::get('history_phone');

        if ($phone) {
            $code = Cookie::get('history_order');

            return Order::where('status', '!=', 'draft')
                ->whereHas('customer', fn ($q) => $q->where('no_hp', $phone))
                ->when($code, fn ($q) => $q->where('order_number', $code));
        }

        // Default: berdasar token perangkat (pesanan yang dibuat di perangkat ini)
        $token = Cookie::get('guest_token');

        return $token
            ? Order::where('status', '!=', 'draft')->where('guest_token', $token)
            : null;
    }

    #[Computed()]
    public function myOrders()
    {
        $kueri = $this->kueriRiwayat();

        if (! $kueri) {
            // Paginator kosong (agar API-nya konsisten: total(), links(), dll)
            return Order::whereRaw('1 = 0')->paginate($this->perPage);
        }

        return $kueri->with('items')->latest()->paginate($this->perPage);
    }

    /**
     * Jumlah pesanan per status, untuk kartu ringkasan di atas daftar.
     * Dihitung di database, bukan dari halaman yang sedang tampil — kalau
     * dihitung dari halaman, angkanya hanya benar untuk 5 pesanan pertama.
     *
     * @return array{total: int, status: array<string, int>}
     */
    #[Computed()]
    public function ringkasan(): array
    {
        $kueri = $this->kueriRiwayat();

        if (! $kueri) {
            return ['total' => 0, 'status' => []];
        }

        $per = $kueri->reorder()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->map(fn ($n) => (int) $n)
            ->all();

        return ['total' => array_sum($per), 'status' => $per];
    }

    /**
     * Warna, ikon, dan sebutan sebuah status pesanan — SATU sumber, dipakai
     * kartu pesanan sekaligus lencana di ringkasannya.
     *
     * @return array{warna: string, ikon: string, label: string}
     */
    public static function status(?string $status): array
    {
        return match ($status) {
            'paid' => ['warna' => '#16a34a', 'ikon' => 'bi-check-circle-fill', 'label' => 'Lunas'],
            'completed' => ['warna' => '#0d9488', 'ikon' => 'bi-patch-check-fill', 'label' => 'Selesai'],
            'pending' => ['warna' => '#d97706', 'ikon' => 'bi-hourglass-split', 'label' => 'Menunggu Pembayaran'],
            'cancelled' => ['warna' => '#e11d48', 'ikon' => 'bi-x-circle-fill', 'label' => 'Dibatalkan'],
            default => ['warna' => '#64748b', 'ikon' => 'bi-receipt', 'label' => ucfirst((string) $status)],
        };
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
