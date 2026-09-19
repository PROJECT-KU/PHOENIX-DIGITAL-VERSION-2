<?php

namespace App\Support;

use App\Actions\Finance\SyncCashFlowAction;
use App\Actions\Finance\SyncOrderPrivateCostAction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Spending;
use Illuminate\Support\Facades\DB;

/**
 * Batal SATU item pesanan toko. Aturan pemilik (19 Sep 2026):
 *
 *  - BELUM dibayar → item dihapus dan total pesanan dikurangi. Syaratnya sama
 *    dengan mengubah pesanan (EditPesanan): tanpa bukti, tanpa QRIS terbit,
 *    tanpa diskon — nominal yang sudah di tangan pelanggan tidak boleh berubah.
 *  - SUDAH dibayar → total pesanan (omzet) TIDAK diubah. Item ditandai batal
 *    (delivery_status 'cancelled'), uang yang dikembalikan dicatat sebagai
 *    Pengeluaran + cash flow. Modal item dilepas hanya bila akunnya belum
 *    sempat dikirim: akun yang sudah terkirim sudah memakan modal.
 *
 * Item jasa yang sudah dibayar tidak dibatalkan di sini — pakai "Batalkan"
 * per pengecekan (kuota) atau batalkan seluruh pesanan.
 */
class BatalItemPesanan
{
    public const DIBAYAR = ['paid', 'processing', 'completed'];

    /** 'kurangi' | 'refund' | null (tidak bisa). */
    public static function mode(Order $order): ?string
    {
        if (in_array($order->status, self::DIBAYAR, true)) {
            return 'refund';
        }

        return EditPesanan::alasanTidakBisa($order) ? null : 'kurangi';
    }

    /** Alasan item ini tidak bisa dibatalkan, atau null bila bisa. */
    public static function alasanTidakBisa(OrderItem $item): ?string
    {
        $order = $item->order;

        return match (true) {
            $item->delivery_status === 'cancelled' => 'Item ini sudah dibatalkan.',
            $order->status === 'cancelled' => 'Pesanannya sudah dibatalkan.',
            self::mode($order) === 'refund' && (bool) optional($item->product)->butuh_file => 'Item jasa dibatalkan per pengecekan, bukan di sini.',
            self::mode($order) === null => EditPesanan::alasanTidakBisa($order),
            default => null,
        };
    }

    /** Item ini satu-satunya yang belum dibatalkan di pesanannya? */
    public static function itemTerakhir(OrderItem $item): bool
    {
        return ! $item->order->items()
            ->where('id', '!=', $item->id)
            ->where('delivery_status', '!=', 'cancelled')
            ->exists();
    }

    /**
     * @return string pesan untuk admin
     */
    public static function batalkan(OrderItem $item, string $alasan, ?int $refund = null): string
    {
        if ($tolak = self::alasanTidakBisa($item)) {
            throw new \RuntimeException($tolak);
        }

        $order = $item->order;
        $rp = fn ($n) => 'Rp '.number_format((int) $n, 0, ',', '.');
        $nama = $item->product_name ?: 'item';

        // Belum dibayar & tidak ada item lain: tidak ada yang tersisa untuk
        // dibayar, jadi pesanannya sendiri yang dibatalkan (item tetap ada
        // sebagai catatan isi pesanan).
        if (self::mode($order) === 'kurangi' && self::itemTerakhir($item)) {
            DB::transaction(function () use ($order) {
                $order->payments()->where('status', 'pending')->update(['status' => 'expire']);
                $order->update(['status' => 'cancelled', 'paid_at' => null]);
            });
            RiwayatPesanan::catat($order->id, 'batal', "Item terakhir {$nama} dibatalkan sebelum dibayar · pesanan dibatalkan ({$alasan})");

            return "Item {$nama} dibatalkan. Karena itu satu-satunya item, pesanan ikut dibatalkan.";
        }

        if (self::mode($order) === 'kurangi') {
            DB::transaction(function () use ($order, $item) {
                $kurang = (int) $item->subtotal;
                $item->delete();
                $order->update([
                    'subtotal' => max(0, (int) $order->subtotal - $kurang),
                    'total' => max(0, (int) $order->total - $kurang),
                ]);
            });
            RiwayatPesanan::catat($order->id, 'batal', "Item {$nama} dibatalkan sebelum dibayar · total berkurang {$rp($item->subtotal)} ({$alasan})");

            return "Item {$nama} dibatalkan. Total pesanan sekarang {$rp($order->fresh()->total)}.";
        }

        // Sudah dibayar: total tetap, refund = pengeluaran.
        $refund = max(0, min((int) $refund, (int) $item->subtotal));

        DB::transaction(function () use ($order, $item, $alasan, $refund, $nama) {
            $spendingId = null;
            if ($refund > 0) {
                $spending = Spending::create([
                    'tanggal_transaksi' => today(),
                    'nominal' => $refund,
                    'deskripsi' => "Refund {$nama} · pesanan {$order->order_number} · {$alasan}",
                    'status' => 'completed',
                    'jenis_pengeluaran' => 'lainnya',
                    'penginput_id' => auth()->id(),
                ]);
                app(SyncCashFlowAction::class)->execute($spending, [
                    'amount' => $refund,
                    'type' => 'expense',
                    'date' => $spending->tanggal_transaksi,
                    'category' => 'Refund Pesanan',
                    'description' => $spending->deskripsi,
                ]);
                $spendingId = $spending->id;
            }

            $item->update([
                'batal_setelah_kirim' => $item->delivery_status === 'delivered',
                'delivery_status' => 'cancelled',
                'dibatalkan_at' => now(),
                'dibatalkan_oleh' => auth()->id(),
                'alasan_batal' => mb_substr($alasan, 0, 500),
                'refund_nominal' => $refund,
                'refund_spending_id' => $spendingId,
            ]);

            // Modal dihitung ulang (item batal yang belum terkirim → modalnya lepas).
            app(SyncOrderPrivateCostAction::class)->execute($order->fresh('items.product'));

            // Semua item lain sudah terkirim → pesanan selesai. Pesanan yang
            // masih punya jasa diselesaikan lewat alur jasa, bukan di sini.
            // Bila SEMUA item batal, status dibiarkan: tidak ada yang terkirim.
            $masih = $order->items()->whereNotIn('delivery_status', ['delivered', 'cancelled'])->exists();
            $adaTerkirim = $order->items()->where('delivery_status', 'delivered')->exists();
            if (! $masih && $adaTerkirim && ! $order->butuhUpload() && $order->status !== 'completed') {
                $order->update(['status' => 'completed', 'paid_at' => $order->paid_at ?: now()]);
            }
        });

        RiwayatPesanan::catat($order->id, 'batal', "Item {$nama} dibatalkan · refund {$rp($refund)} dicatat sebagai pengeluaran ({$alasan})");

        return $refund > 0
            ? "Item {$nama} dibatalkan. Refund {$rp($refund)} dicatat di Pengeluaran; total pesanan tetap."
            : "Item {$nama} dibatalkan tanpa refund; total pesanan tetap.";
    }
}
