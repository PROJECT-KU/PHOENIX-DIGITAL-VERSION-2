<?php

namespace App\Actions\Jasa;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Menuntaskan pesanan jasa yang seluruh pekerjaannya sudah diserahkan.
 *
 * Dikeluarkan dari layar admin agar bisa dipakai dua pihak: tombol Simpan
 * Hasil, dan perintah perbaikan untuk pesanan yang menjadi tuntas BELAKANGAN —
 * mis. karena hitungan kuotanya diperbaiki. Selama logikanya hanya hidup di
 * dalam komponen Livewire, pesanan seperti itu tidak akan pernah dinilai ulang
 * dan menggantung di status 'paid' selamanya.
 */
class SelesaikanPesananJasa
{
    public function __construct(private SyncCashFlowAction $syncCashFlow) {}

    /**
     * @return bool true bila pesanan berakhir 'completed'
     */
    public function execute(Order $order): bool
    {
        if (! $order->butuhUpload()) {
            return false;
        }

        return (bool) DB::transaction(function () use ($order) {
            // 1) Item JASA ditandai terkirim; item non-jasa tidak disentuh.
            foreach ($order->items as $item) {
                if ((bool) optional($item->product)->butuh_file && $item->delivery_status !== 'delivered') {
                    $item->update(['delivery_status' => 'delivered']);
                }
            }

            // 2) Pesanan hanya selesai bila TIDAK ADA item yang masih menunggu.
            //    Pesanan campuran (jasa + akun) tetap menunggu akunnya dikirim.
            if ($order->items()->where('delivery_status', '!=', 'delivered')->exists()) {
                return false;
            }

            $order->update([
                'status' => 'completed',
                'paid_at' => $order->paid_at ?: now(),
            ]);

            $this->syncCashFlow->execute($order, [
                'amount' => $order->total,
                'type' => 'income',
                'date' => $order->paid_at ?: $order->created_at,
                'category' => 'e-commerce',
                'description' => $order->deskripsi ?? 'Pembelian jasa dari e-commerce',
            ]);

            return true;
        });
    }
}
