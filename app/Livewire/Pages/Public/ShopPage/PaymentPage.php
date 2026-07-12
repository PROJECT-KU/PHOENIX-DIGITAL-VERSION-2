<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use App\Services\PaymentService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PaymentPage extends Component
{
    public Order $order;

    public $snapToken;

    public $paymentUrl;

    public $payment;

    public $qrisContent;

    public $qrisNmid;

    public $qrisInvoiceId;

    public $qrCodeImage;

    public $needsQris = false;

    public $qrisError = null;

    public function mount(Order $order)
    {
        // Keamanan — Kepemilikan: hanya perangkat pembeli (guest_token cocok) yang
        // boleh melihat halaman ini (berisi QRIS + data pribadi pelanggan).
        $token = Cookie::get('guest_token');
        if ($order->guest_token && $token !== $order->guest_token) {
            return redirect()->route('homepage');
        }

        $this->order = $order;

        // Sudah dibayar
        if ($order->status === 'paid') {

            return redirect()->route(
                'order.success',
                $order
            );
        }

        // Sudah dibatalkan (mis. kedaluwarsa) → halaman expired
        if ($order->status === 'cancelled') {
            return redirect()->route('order.expired', $order);
        }

        $payment = $order
            ->payments()
            ->latest()
            ->first();

        if ($payment && $payment->status === 'settlement') {
            return redirect()->route('order.success', $order);
        }

        // QRIS lama masih berlaku → pakai ulang (cepat, tanpa panggilan API).
        if (
            $payment
            && $payment->status === 'pending'
            && (! $payment->expired_at || ! $payment->expired_at->isPast())
            && ! empty($payment->gateway_response['data']['qris_content'] ?? null)
        ) {
            $this->payment = $payment;

            $data = $payment->gateway_response['data'] ?? [];
            $this->qrisContent = $data['qris_content'] ?? null;
            $this->qrisInvoiceId = $data['qris_invoiceid'] ?? null;
            $this->qrisNmid = $data['qris_nmid'] ?? null;

            $this->generateQrCode();

            return;
        }

        // Tandai QRIS lama yang sudah kedaluwarsa.
        if (
            $payment
            && $payment->status === 'pending'
            && $payment->expired_at
            && $payment->expired_at->isPast()
        ) {
            $payment->update(['status' => 'expire']);
        }

        // Butuh QRIS baru — JANGAN panggil API di sini (bikin halaman lambat/stuck).
        // Halaman tampil dulu secara instan, QRIS dibuat via wire:init (prepareQris()).
        $this->needsQris = true;
    }

    /**
     * Dipanggil oleh wire:init setelah halaman tampil, supaya pembuatan QRIS
     * (panggilan API eksternal ~2-4 dtk) tidak memblokir render awal.
     */
    public function prepareQris()
    {
        if (! $this->needsQris) {
            return;
        }

        $result = app(PaymentService::class)
            ->createQrisPayment($this->order);

        if (! ($result['success'] ?? false)) {
            $this->qrisError = $result['message'] ?? 'Gagal membuat QRIS. Coba lagi.';
            $this->needsQris = false;

            return;
        }

        $this->payment = $result['payment'];
        $this->qrisContent = $result['qris_content'] ?? null;
        $this->qrisInvoiceId = $result['invoice_id'] ?? null;
        $this->qrisNmid = $result['nmid'] ?? null;
        $this->needsQris = false;

        $this->generateQrCode();
    }

    public function retryQris()
    {
        $this->qrisError = null;
        $this->needsQris = true;
        $this->prepareQris();
    }

    public function checkPaymentStatus()
    {
        $payment = $this->order
            ->payments()
            ->latest()
            ->first();

        if (! $payment) {
            return;
        }

        // Sudah dibayar → halaman sukses
        if ($payment->status === 'settlement') {
            $this->order->refresh();

            return redirect()->route('order.success', $this->order);
        }

        // Melebihi waktu / expire → batalkan pesanan → halaman expired
        if (
            $payment->status === 'expire'
            || ($payment->expired_at && $payment->expired_at->isPast())
        ) {
            if ($payment->status !== 'expire') {
                $payment->update(['status' => 'expire']);
            }

            if (! in_array($this->order->status, ['paid', 'completed', 'cancelled'])) {
                $this->order->update(['status' => 'cancelled']);
            }

            return redirect()->route('order.expired', $this->order);
        }

        $paid = app(PaymentService::class)
            ->checkQrisPayment($payment);

        if ($paid) {

            $this->order->refresh();

            return redirect()->route('order.success', $this->order);
        }
    }

    private function generateQrCode()
    {
        if (empty($this->qrisContent)) {
            return;
        }

        $writer = new PngWriter();

        $qrCode = new QrCode(
            $this->qrisContent
        );

        $result = $writer->write($qrCode);

        $this->qrCodeImage = base64_encode(
            $result->getString()
        );
    }

    public function generateNewQris()
    {
        $payment = $this->order
            ->payments()
            ->latest()
            ->first();

        if (
            $payment &&
            $payment->status !== 'settlement'
        ) {
            $payment->update([
                'status' => 'expire',
            ]);
        }

        return redirect()->route(
            'payment',
            $this->order
        );
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view(
            'livewire.pages.public.shop-page.payment-page'
        );
    }
}
