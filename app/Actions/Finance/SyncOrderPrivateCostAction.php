<?php

namespace App\Actions\Finance;

use App\Models\Order;
use App\Models\OrderItem;

class SyncOrderPrivateCostAction
{
    /**
     * Catat biaya modal akun PRIVATE per order item (= modal satuan x qty)
     * sebagai expense di cash flow, saat order sudah dibayar.
     * Item non-private / order belum dibayar -> hapus expense-nya.
     */
    public function execute(Order $order): void
    {
        $paid = in_array($order->status, ['paid', 'processing', 'completed'], true);
        $tanggal = $order->paid_at ?: $order->created_at;

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;
            // Produk jasa (butuh_file) juga punya modal (per pengecekan), walau
            // tipe_akun-nya bukan 'private' — ikutkan agar omset bersihnya benar.
            $adaModal = $product && ($product->tipe_akun === 'private' || $product->butuh_file);

            $amount = 0;
            if ($paid && $adaModal) {
                // Harga yang BERLAKU pada tanggal order (tidak berubah retroaktif).
                // modalOrderItem() otomatis: jasa paket = per 1× × jumlah cek;
                // jasa parafrase = per 1 halaman × jumlah halaman DIKERJAKAN.
                $unit = $product->modalOrderItem($item, $tanggal);
                $amount = $unit * (int) $item->quantity;
            }

            if ($amount > 0) {
                $item->cashFlow()->updateOrCreate(
                    ['sourceable_id' => $item->id, 'sourceable_type' => OrderItem::class],
                    [
                        'amount' => $amount,
                        'type' => 'expense',
                        'transaction_date' => $tanggal,
                        'category' => 'Modal Akun Private',
                        'description' => 'Modal '.$item->product_name.' ('.$this->labelSatuan($item, $product).') x'.$item->quantity,
                    ]
                );
            } else {
                $item->cashFlow()->delete();
            }
        }
    }

    /**
     * Label satuan pada deskripsi expense, mengikuti dasar perhitungannya.
     * Jasa per halaman memakai jumlah halaman yang DIKERJAKAN — memakai
     * duration_value mentah akan tertulis "1 halaman" padahal dibayar 25.
     */
    protected function labelSatuan(OrderItem $item, $product): string
    {
        if ($product && $product->jasaPerHalaman()) {
            $halaman = (int) ($item->halaman_dihitung ?? $item->jumlah_halaman ?? 0);

            return $halaman.' halaman';
        }

        return $item->duration_value.' '.$item->duration_type;
    }
}
