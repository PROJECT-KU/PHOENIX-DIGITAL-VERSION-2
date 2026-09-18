<?php

namespace App\Support;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Pengingat perpanjangan akun Pesanan Toko.
 *
 * "Segera habis" = masa aktif berakhir hari ini s/d HARI hari lagi, pada
 * pesanan yang uangnya nyata (paid/processing/completed), dan langganannya
 * belum ditandai habis. Pelanggan diingatkan SEBELUM akunnya mati, dengan
 * tautan beli produk yang sama di toko — perpanjangan lewat Pemesanan Toko.
 */
class PengingatPerpanjangan
{
    public const HARI = 7;

    public static function scopeSegeraHabis(Builder $q): Builder
    {
        return $q->whereNotNull('end_date')
            ->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', today()->addDays(self::HARI))
            ->where(fn ($s) => $s->whereNull('subscription_status')->orWhere('subscription_status', '!=', 'habis'))
            ->whereHas('order', fn ($o) => $o->whereIn('status', ['paid', 'processing', 'completed']))
            ->where('delivery_status', '!=', 'cancelled')
            ->tap(fn ($x) => self::scopeBelumDiperpanjang($x));
    }

    /**
     * Keluarkan akun yang SUDAH diperpanjang (pesanan barunya sudah dibayar):
     *  1. ditautkan: pesanannya dibuat lewat tombol Perpanjang
     *     (diperpanjang_oleh_item_id terisi) — walau produknya diganti;
     *  2. terdeteksi: pelanggan yang sama membeli produk yang sama pada pesanan
     *     yang dibuat SESUDAH pesanan ini, dan pesanan itu sudah dibayar.
     *
     * Tanpa ini pelanggan yang sudah membayar perpanjangan tetap tampil di
     * Segera Habis / Akun Habis dan bisa diingatkan lagi lewat WA.
     */
    public static function scopeBelumDiperpanjang(Builder $q): Builder
    {
        return $q->whereNotExists(fn ($s) => self::kueriPerpanjangan($s));
    }

    /**
     * Subkueri "ada pembelian ulang yang SUDAH DIBAYAR" untuk baris order_items
     * luar. Pesanan perpanjangan yang masih draft / menunggu bayar belum
     * dihitung — pelanggannya tetap perlu diingatkan.
     */
    private static function kueriPerpanjangan($s)
    {
        return $s->selectRaw('1')
            ->from('order_items as baru')
            ->join('orders as ob', 'ob.id', '=', 'baru.order_id')
            ->join('orders as ol', 'ol.id', '=', 'order_items.order_id')
            ->where(fn ($w) => $w
                ->whereColumn('baru.id', 'order_items.diperpanjang_oleh_item_id')
                ->orWhere(fn ($x) => $x
                    ->whereColumn('baru.product_id', 'order_items.product_id')
                    ->whereColumn('ob.customer_id', 'ol.customer_id')
                    ->whereColumn('ob.created_at', '>', 'ol.created_at')))
            ->whereIn('ob.status', ['paid', 'processing', 'completed'])
            ->whereNull('ob.deleted_at')
            ->whereNull('baru.deleted_at');
    }

    /** Sisa hari (0 = berakhir hari ini). */
    public static function sisaHari(OrderItem $item): ?int
    {
        return $item->end_date ? (int) today()->diffInDays(Carbon::parse($item->end_date)->startOfDay(), false) : null;
    }

    /**
     * Tautan api.whatsapp.com dengan pesan siap kirim.
     *
     * @param  'segera'|'habis'  $jenis
     */
    public static function tautanWa(OrderItem $item, string $jenis): ?string
    {
        $order = $item->order;
        $digit = preg_replace('/\D/', '', (string) ($order?->customer?->no_hp));
        if (str_starts_with($digit, '0')) {
            $digit = '62'.substr($digit, 1);
        }
        if (strlen($digit) < 9) {
            return null;
        }

        $tgl = fn ($d) => $d ? Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : '-';
        $akun = $item->product_name ?: ($item->product->nama_akun ?? 'akun');
        $tautanBeli = $item->product_id
            ? route('shop.detail-product', $item->product_id)
            : 'https://phoenixdigitalwarehouse.com/';

        $isi = $jenis === 'habis'
            ? "Akun *{$akun}* yang Anda order pada tanggal *{$tgl($order->created_at)}* dengan masa aktif sampai *{$tgl($item->end_date)}* *SUDAH HABIS MASA AKTIFNYA*."
            : self::kalimatSegera($item, $akun, $tgl($item->end_date));

        $pesan = 'ID Transaksi: '.$order->order_number."\n\n"
            .'Halo '.($order->customer->nama ?? 'Kak').",\n"
            .$isi."\n\n"
            ."Untuk memperpanjang akun *{$akun}*, silakan order langsung melalui website kami:\n"
            .$tautanBeli."\n\n"
            ."Jika ada kendala, jangan ragu untuk menghubungi kami.\n"
            ."Terima kasih telah menggunakan layanan kami.\n\n"
            ."Salam hangat,\nPhoenix Digital Warehouse\n"
            ."Instagram: phoenixdigital_warehouse\n"
            .'Website: https://phoenixdigitalwarehouse.com/';

        return 'https://api.whatsapp.com/send?phone='.$digit.'&text='.rawurlencode($pesan);
    }

    private static function kalimatSegera(OrderItem $item, string $akun, string $berakhir): string
    {
        $sisa = self::sisaHari($item);
        $kapan = match (true) {
            $sisa === 0 => '*HARI INI*',
            $sisa === 1 => '*BESOK*',
            default => "dalam *{$sisa} hari lagi*",
        };

        return "Kami ingin mengingatkan, masa aktif akun *{$akun}* Anda akan berakhir {$kapan}, pada tanggal *{$berakhir}*.";
    }
}
