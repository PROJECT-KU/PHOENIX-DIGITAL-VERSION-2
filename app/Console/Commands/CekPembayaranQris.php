<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\QrisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cek pembayaran QRIS di sisi server, tanpa perlu halaman terbuka.
 *
 * QRIS memakai model POLLING (bukan callback): status "paid" hanya diketahui
 * dengan menanyakan penyedia lewat QrisService::checkStatus. Selama ini polling
 * itu HANYA terjadi saat halaman pembayaran QRIS (admin) atau halaman share
 * (customer) sedang dibuka. Kalau admin cuma membagikan barcode lalu menutup
 * halaman, tidak ada yang mengecek — sehingga order tetap "pending" meski
 * customer sudah membayar dan uang sudah masuk.
 *
 * Command ini menutup celah itu: dijalankan scheduler tiap menit, mengecek
 * semua order QRIS yang masih pending, dan menandainya "paid" bila penyedia
 * sudah melaporkan lunas. Sama persis dengan yang dilakukan tombol polling di
 * QrisPayment::checkPayment, jadi konsisten dengan alur yang sudah ada.
 */
class CekPembayaranQris extends Command
{
    protected $signature = 'qris:cek-pembayaran {--jam=24 : Umur maksimal order (jam) yang dicek}';

    protected $description = 'Cek pembayaran QRIS order pending di sisi server, tandai lunas bila sudah dibayar.';

    public function handle(QrisService $qris): int
    {
        if (! $qris->isConfigured()) {
            $this->warn('QRIS belum dikonfigurasi (QRIS_* di .env). Dilewati.');

            return self::SUCCESS;
        }

        $batas = now()->subHours(max(1, (int) $this->option('jam')));
        $lunas = 0;
        $dicek = 0;

        // Cek order pending (di daftar) DAN draft (yang di-share admin lalu
        // disimpan): keduanya bisa saja sudah dibayar customer.
        Order::query()
            ->whereIn('status', ['pending', 'draft'])
            ->where('payment_method', 'qris_dinamis')
            ->whereNotNull('qris_trx_id')
            ->where('created_at', '>=', $batas)
            ->chunkById(100, function ($orders) use ($qris, &$lunas, &$dicek) {
                foreach ($orders as $order) {
                    $dicek++;

                    // Bisa saja sudah dilunasi lewat channel lain sejak query tadi.
                    $order->refresh();
                    if (! in_array($order->status, ['pending', 'draft'], true)) {
                        continue;
                    }

                    if ($qris->checkStatus($order) !== 'paid') {
                        continue;
                    }

                    // Sama seperti QrisPayment::checkPayment — hanya menandai lunas.
                    // Sinkronisasi cashflow tetap terjadi saat order ditandai
                    // "completed" oleh admin (OrderDetail::updateStatus).
                    $order->update(['status' => 'paid', 'paid_at' => now()]);
                    $order->payments()->where('status', 'pending')->update(['status' => 'settlement']);
                    $lunas++;

                    Log::info('QRIS auto-detect: order '.$order->order_number.' ditandai paid.');
                    $this->line("  <info>LUNAS</info>  {$order->order_number}");
                }
            });

        $this->info("QRIS dicek: {$dicek} order, ditandai lunas: {$lunas}.");

        return self::SUCCESS;
    }
}
