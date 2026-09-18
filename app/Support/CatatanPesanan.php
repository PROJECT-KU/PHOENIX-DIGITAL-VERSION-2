<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;

/**
 * Menyelesaikan catatan pesanan — satu tempat untuk daftar & detail.
 *
 * - Catatan internal admin (order_items.processing_notes) adalah pekerjaan:
 *   setelah beres, dihapus.
 * - Catatan pelanggan (orders.customer_notes) adalah data pesanan: TIDAK
 *   dihapus, hanya ditandai ditangani (siapa & kapan).
 * - Catatan untuk pelanggan (account_notes) tidak disentuh: itu isi yang
 *   dikirim ke pembeli, bukan pekerjaan.
 */
class CatatanPesanan
{
    public static function hapusInternal(OrderItem $item): void
    {
        $item->forceFill(['processing_notes' => null])->save();
    }

    public static function tandaiPelangganDitangani(Order $order): void
    {
        if (blank($order->customer_notes) || $order->catatan_ditangani_at) {
            return;
        }

        $order->forceFill([
            'catatan_ditangani_at' => now(),
            'catatan_ditangani_oleh' => auth()->id(),
        ])->saveQuietly();

        RiwayatPesanan::catat($order->id, 'catatan', 'Catatan pelanggan ditandai ditangani');
    }

    public static function selesaikanSemua(Order $order): void
    {
        foreach ($order->items as $item) {
            if (filled($item->processing_notes)) {
                self::hapusInternal($item);
            }
        }
        self::tandaiPelangganDitangani($order);
    }
}
