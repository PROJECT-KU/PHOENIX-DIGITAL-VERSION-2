<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use App\Services\PaymentService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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

    public function mount(Order $order)
    {
        $this->order = $order;

        // Sudah dibayar
        if ($order->status === 'paid') {

            return redirect()->route(
                'order.success',
                $order
            );
        }

        $payment = $order
            ->payments()
            ->latest()
            ->first();

        $needNewQris = false;

        if (! $payment) {

            $needNewQris = true;
        } elseif ($payment->status === 'settlement') {

            return redirect()->route(
                'order.success',
                $order
            );
        } elseif (
            $payment->status === 'expire'
            || (
                $payment->status === 'pending'
                && $payment->expired_at
                && $payment->expired_at->isPast()
            )
        ) {

            // tandai payment lama expire
            $payment->update([
                'status' => 'expire',
            ]);

            $needNewQris = true;
        }

        if ($needNewQris) {

            $result = app(PaymentService::class)
                ->createQrisPayment($order);

            if (! ($result['success'] ?? false)) {

                session()->flash(
                    'error',
                    $result['message'] ?? 'Gagal membuat QRIS'
                );

                return;
            }

            $this->payment = $result['payment'];

            $this->qrisContent = $result['qris_content'] ?? null;
            $this->qrisInvoiceId = $result['invoice_id'] ?? null;
            $this->qrisNmid = $result['nmid'] ?? null;
        } else {

            $this->payment = $payment;

            $data = $payment->gateway_response['data'] ?? [];

            $this->qrisContent = $data['qris_content'] ?? null;
            $this->qrisInvoiceId = $data['qris_invoiceid'] ?? null;
            $this->qrisNmid = $data['qris_nmid'] ?? null;
        }

        $this->generateQrCode();
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

        if ($payment->status === 'settlement') {
            return;
        }

        if ($payment->status === 'expire') {
            return;
        }

        if (
            $payment->expired_at &&
            $payment->expired_at->isPast()
        ) {
            return;
        }

        $paid = app(PaymentService::class)
            ->checkQrisPayment($payment);

        if ($paid) {

            $this->order->refresh();

            $this->dispatch(
                'payment-success',
                url: route(
                    'order.success',
                    $this->order
                )
            );
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
