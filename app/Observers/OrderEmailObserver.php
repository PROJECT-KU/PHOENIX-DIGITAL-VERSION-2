<?php

namespace App\Observers;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

/**
 * Observer TERPISAH khusus email — tidak menyentuh OrderObserver yang sudah ada.
 * Mengirim email otomatis saat status order berubah (paid/cancelled).
 * Gagal kirim email di-catch → tidak pernah mengganggu alur pembayaran.
 */
class OrderEmailObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $new = $order->status;
        $old = $order->getOriginal('status');
        if ($new === $old) {
            return;
        }

        $email = optional($order->customer)->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            if ($new === 'paid') {
                Mail::mailer('phoenix')->to($email)->send(new OrderStatusMail($order, 'paid'));
            } elseif ($new === 'cancelled') {
                Mail::mailer('phoenix')->to($email)->send(new OrderStatusMail($order, 'cancelled'));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
