<?php

namespace App\Console\Commands;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    protected $signature = 'payment:remind';

    protected $description = 'Kirim email pengingat untuk pesanan yang mendekati batas waktu pembayaran.';

    public function handle(): int
    {
        $now = now();
        $soon = now()->addMinutes(10); // ingatkan ~10 menit sebelum kedaluwarsa
        $count = 0;

        Order::where('status', 'pending')
            ->whereHas('customer', fn ($q) => $q->whereNotNull('email'))
            ->whereHas('payments', fn ($q) => $q->where('status', 'pending')
                ->whereNotNull('expired_at')
                ->whereBetween('expired_at', [$now, $soon]))
            ->with(['customer', 'items'])
            ->chunkById(100, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    // Kirim sekali saja per order.
                    if (! Cache::add('payment-reminded:'.$order->id, true, 3600)) {
                        continue;
                    }

                    $email = optional($order->customer)->email;
                    if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    try {
                        Mail::mailer('phoenix')->to($email)->send(new OrderStatusMail($order, 'reminder'));
                        $count++;
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            });

        $this->info("Pengingat pembayaran terkirim: {$count}.");

        return self::SUCCESS;
    }
}
