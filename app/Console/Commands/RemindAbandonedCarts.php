<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartMail;
use App\Models\AbandonedCart;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RemindAbandonedCarts extends Command
{
    protected $signature = 'cart:remind-abandoned';

    protected $description = 'Kirim email pengingat untuk keranjang yang ditinggalkan (belum checkout).';

    public function handle(): int
    {
        $cutoff = now()->subHour(); // beri jeda 1 jam sebelum mengingatkan
        $count = 0;

        AbandonedCart::whereNull('reminded_at')
            ->whereNull('recovered_at')
            ->where('updated_at', '<=', $cutoff)
            ->chunkById(100, function ($carts) use (&$count) {
                foreach ($carts as $cart) {
                    // Sudah beli setelah menyimpan keranjang? → dianggap pulih, tidak diingatkan.
                    //
                    // Pesanan 'cancelled' & 'draft' TIDAK dihitung pulih: dulu status
                    // tidak dilihat sama sekali, sehingga pembeli yang justru MEMBATALKAN
                    // pesanannya malah tidak pernah diingatkan — padahal dia yang paling
                    // perlu ditarik kembali.
                    $recovered = Order::whereHas('customer', fn ($q) => $q->where('email', $cart->email))
                        ->where('created_at', '>=', $cart->updated_at)
                        ->whereNotIn('status', ['cancelled', 'draft'])
                        ->exists();

                    if ($recovered) {
                        $cart->update(['recovered_at' => now()]);

                        continue;
                    }

                    if (empty($cart->items)) {
                        continue;
                    }

                    try {
                        Mail::mailer('phoenix')->to($cart->email)->send(new AbandonedCartMail($cart));
                        $cart->update(['reminded_at' => now()]);
                        $count++;
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            });

        $this->info("Reminder keranjang tertinggal terkirim: {$count}.");

        return self::SUCCESS;
    }
}
