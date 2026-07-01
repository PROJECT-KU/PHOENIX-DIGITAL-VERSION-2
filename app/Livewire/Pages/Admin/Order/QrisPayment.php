<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use App\Services\QrisService;
use Livewire\Attributes\Layout;
use Livewire\Component;

class QrisPayment extends Component
{
    public Order $order;

    public bool $generating = false;

    public ?string $errorMessage = null;

    public function mount(Order $order)
    {
        $this->order = $order;

        // Hanya untuk QRIS dinamis & order yang belum lunas
        abort_unless($order->payment_method === 'qris_dinamis', 404);

        if (in_array($order->status, ['paid', 'processing', 'completed'])) {
            return redirect()->route('admin.pesanantoko.detail', $order);
        }

        // Generate QR jika belum ada atau sudah kedaluwarsa
        if (empty($order->qris_content) || $this->isExpired()) {
            $this->generateQr();
        }
    }

    public function isExpired(): bool
    {
        return $this->order->expired_at && now()->greaterThan($this->order->expired_at);
    }

    public function generateQr(): void
    {
        $this->generating = true;
        $this->errorMessage = null;

        $result = app(QrisService::class)->createInvoice($this->order->fresh());
        $this->order->refresh();

        if (! $result['success']) {
            $this->errorMessage = $result['message'] ?? 'Gagal membuat QRIS.';
        }

        $this->generating = false;
    }

    /** Tombol "Buat QR Baru" saat QR kedaluwarsa */
    public function refreshQr(): void
    {
        $this->generateQr();
        $this->dispatch('qris-refreshed', content: $this->order->qris_content);
    }

    /** Polling: cek apakah sudah dibayar */
    public function checkPayment(): void
    {
        // Ambil status terbaru dari DB (bisa saja sudah dibayar lewat halaman share customer)
        $this->order->refresh();

        // Sudah lunas dari channel mana pun → tetap picu sukses di admin
        if (in_array($this->order->status, ['paid', 'processing', 'completed'])) {
            $this->dispatch('qris-paid');

            return;
        }

        $status = app(QrisService::class)->checkStatus($this->order);

        if ($status === 'paid') {
            $this->order->update(['status' => 'paid', 'paid_at' => now()]);
            $this->order->refresh();
            $this->dispatch('qris-paid');
        }
    }

    /** Simpan sebagai draft untuk dilanjutkan nanti */
    public function saveDraft()
    {
        $this->order->update(['status' => 'draft']);

        session()->flash('successCreated', 'Pesanan disimpan sebagai draft. Bisa dilanjutkan kapan saja dari daftar pesanan.');

        return redirect()->route('admin.pesanantoko.index');
    }

    public function getWaLinkProperty(): string
    {
        $payUrl = route('qris.show', $this->order->share_token);
        $amount = 'Rp '.number_format((int) $this->order->total, 0, ',', '.');
        $nama = $this->order->customer->nama ?? 'Pelanggan';

        $text = "Halo {$nama}, berikut pembayaran QRIS untuk pesanan *{$this->order->order_number}*.\n\n"
            ."Total: *{$amount}*\n"
            ."Scan / buka QR di sini (berlaku 30 menit):\n{$payUrl}\n\n"
            .'Terima kasih 🙏';

        $phone = preg_replace('/[^0-9]/', '', (string) ($this->order->customer->no_hp ?? ''));
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.qris-payment');
    }
}
