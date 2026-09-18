<?php

namespace App\Support;

use App\Models\Order;

/**
 * Kapan pesanan toko boleh diubah (produk, durasi, jumlah, catatan).
 *
 * Hanya SEBELUM ada uang atau nominal yang terkunci di luar sistem. Setiap
 * syarat di bawah menjaga satu hal yang tidak boleh berubah diam-diam:
 *  - sudah dibayar / diproses → omzet, cash flow, dan modal sudah tercatat;
 *  - bukti transfer sudah masuk → pelanggan membayar nominal LAMA;
 *  - QRIS dinamis sudah terbit → QR di tangan pelanggan bernominal lama;
 *  - ada diskon promo/poin/rujukan → kuota promo & poin sudah terpakai dan
 *    potongannya dihitung dari isi lama;
 *  - naskah jasa sudah diunggah / akun sudah dikirim → pekerjaan sudah jalan.
 * Selain itu: batalkan lalu buat pesanan baru.
 */
class EditPesanan
{
    /** Alasan pesanan TIDAK boleh diubah, atau null bila boleh. */
    public static function alasanTidakBisa(Order $order): ?string
    {
        return match (true) {
            ! in_array($order->status, ['draft', 'pending'], true) => 'Pesanan sudah '.(RiwayatPesanan::STATUS[$order->status] ?? $order->status).' — hanya pesanan yang belum dibayar yang bisa diubah.',
            filled($order->bukti_pembayaran) => 'Bukti pembayaran sudah diunggah, artinya pelanggan sudah membayar nominal lama.',
            $order->payment_method === 'qris_dinamis' && filled($order->qris_content) => 'QRIS sudah dibuat dengan nominal lama. Batalkan lalu buat pesanan baru.',
            0 < (int) $order->total_discount || (bool) $order->used_points || filled($order->referral_code) => 'Pesanan memakai promo, poin, atau kode rujukan — potongannya dihitung dari isi lama. Batalkan lalu buat pesanan baru.',
            $order->uploads()->exists() => 'Pelanggan sudah mengunggah naskah jasa.',
            $order->items()->where(fn ($q) => $q->where('delivery_status', 'delivered')->orWhereNotNull('processed_at'))->exists() => 'Ada akun yang sudah diproses atau dikirim.',
            default => null,
        };
    }
}
