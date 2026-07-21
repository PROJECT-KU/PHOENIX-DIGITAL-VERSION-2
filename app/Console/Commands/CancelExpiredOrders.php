<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\QrisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Batalkan order pending yang sudah melewati batas waktu pembayaran (QRIS/order kedaluwarsa).';

    public function handle(QrisService $qris): int
    {
        $now = now();
        $count = 0;
        $diselamatkan = 0;

        Order::where('status', 'pending')
            // Jangan sentuh order yang sudah punya pembayaran lunas.
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'settlement'))
            ->with(['payments' => fn ($q) => $q->latest()])
            ->chunkById(200, function ($orders) use (&$count, &$diselamatkan, $now, $qris) {
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

                    // PENGAMAN: sebelum membatalkan order QRIS, tanyakan sekali lagi
                    // ke penyedia. Bila customer ternyata sudah membayar (mis. tepat
                    // di menit kedaluwarsa), tandai LUNAS — jangan dibatalkan.
                    if ($order->payment_method === 'qris_dinamis' && $order->qris_trx_id
                        && $qris->checkStatus($order) === 'paid') {
                        $order->update(['status' => 'paid', 'paid_at' => now()]);
                        $order->payments()->where('status', 'pending')->update(['status' => 'settlement']);
                        $diselamatkan++;
                        Log::info('QRIS: order '.$order->order_number.' hampir dibatalkan tapi ternyata sudah dibayar → paid.');

                        continue;
                    }

                    // Tandai QRIS yang masih pending sebagai expire, lalu batalkan order.
                    $order->payments()->where('status', 'pending')->update(['status' => 'expire']);
                    $order->update(['status' => 'cancelled']);
                    $count++;
                }
            });

        $this->info("Order kedaluwarsa dibatalkan: {$count}. Diselamatkan (ternyata sudah dibayar): {$diselamatkan}.");

        return self::SUCCESS;
    }
}
