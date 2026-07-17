<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Batalkan order pending yang sudah melewati batas waktu pembayaran (QRIS/order kedaluwarsa).';

    public function handle(): int
    {
        $now = now();
        $count = 0;

        Order::where('status', 'pending')
            // Jangan sentuh order yang sudah punya pembayaran lunas.
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'settlement'))
            ->with(['payments' => fn ($q) => $q->latest()])
            ->chunkById(200, function ($orders) use (&$count, $now) {
                foreach ($orders as $order) {
                    $latest = $order->payments->first();
                    $expired = false;

                    // 1) QRIS terakhir sudah kedaluwarsa (batas waktu pembayaran lewat).
                    if ($latest && ($latest->status === 'expire'
                        || ($latest->expired_at && $latest->expired_at->lessThanOrEqualTo($now)))) {
                        $expired = true;
                    }

                    // 2) Masa berlaku order-nya sendiri sudah habis (pengaman).
                    if ($order->expired_at && $order->expired_at->lessThanOrEqualTo($now)) {
                        $expired = true;
                    }

                    if (! $expired) {
                        continue;
                    }

                    // Tandai QRIS yang masih pending sebagai expire, lalu batalkan order.
                    $order->payments()->where('status', 'pending')->update(['status' => 'expire']);
                    $order->update(['status' => 'cancelled']);
                    $count++;
                }
            });

        $this->info("Order kedaluwarsa dibatalkan: {$count}.");

        return self::SUCCESS;
    }
}
