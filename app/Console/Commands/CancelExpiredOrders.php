<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\QrisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Batalkan order kedaluwarsa: pending (di daftar) 30 menit, draft 24 jam.';

    /** Draft yang dishare admin diberi tenggang lebih panjang sebelum dibatalkan. */
    private const DRAFT_JAM = 24;

    public function handle(QrisService $qris): int
    {
        $now = now();
        $batasDraft = $now->copy()->subHours(self::DRAFT_JAM);
        $count = 0;
        $diselamatkan = 0;

        // Pending: order di daftar, kedaluwarsa mengikuti QRIS (±30 menit).
        // Draft: admin "Simpan ke Draft" setelah share QR — sengaja diberi 24 jam
        //        supaya customer punya waktu bayar, tidak ikut aturan 30 menit.
        Order::whereIn('status', ['pending', 'draft'])
            // Jangan sentuh order yang sudah punya pembayaran lunas.
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'settlement'))
            ->with(['payments' => fn ($q) => $q->latest()])
            ->chunkById(200, function ($orders) use (&$count, &$diselamatkan, $now, $batasDraft, $qris) {
                foreach ($orders as $order) {
                    $expired = false;

                    if ($order->status === 'draft') {
                        // Draft: hanya kedaluwarsa setelah 24 jam sejak dibuat.
                        // Kedaluwarsa QR 30 menit sengaja DIABAIKAN untuk draft.
                        $expired = $order->created_at
                            && $order->created_at->lessThanOrEqualTo($batasDraft);
                    } else {
                        $latest = $order->payments->first();

                        // 1) QRIS terakhir sudah kedaluwarsa (batas waktu pembayaran lewat).
                        if ($latest && ($latest->status === 'expire'
                            || ($latest->expired_at && $latest->expired_at->lessThanOrEqualTo($now)))) {
                            $expired = true;
                        }

                        // 2) Masa berlaku order-nya sendiri sudah habis (pengaman).
                        if ($order->expired_at && $order->expired_at->lessThanOrEqualTo($now)) {
                            $expired = true;
                        }
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
