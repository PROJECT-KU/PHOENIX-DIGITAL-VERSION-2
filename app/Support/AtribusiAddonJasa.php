<?php

namespace App\Support;

use App\Models\OrderItem;
use App\Models\Product;
use Closure;

/**
 * Atribusi PENJUALAN & MODAL add-on jasa ke produknya sendiri.
 *
 * Satu pesanan bisa memuat pemeriksaan berbeda: mis. "cek AI" + add-on
 * "cek plagiasi turnitin". Secara akuntansi keduanya layanan terpisah dengan
 * omset & modal sendiri, walau dibeli dalam 1 order. Tanpa ini, pendapatan
 * add-on (tersimpan di order_items.addons) tak masuk omset mana pun, dan
 * modal pemeriksaan add-on tak terhitung.
 *
 * Aturan:
 *  - Add-on yang NAMA-nya cocok dengan produk jasa → penjualan + modal masuk
 *    ke produk itu (mis. add-on "cek plagiasi turnitin" → produk turnitin).
 *  - Add-on lain (mis. target parafrase "plagiasi di bawah 30%") → penjualannya
 *    masuk ke produk INDUK item, tanpa modal (bukan pemeriksaan berakun sendiri).
 */
class AtribusiAddonJasa
{
    /**
     * @param  Closure  $filterOrder  Menerapkan status paid + periode ke query order.
     * @param  ?string  $hargaCutoff  Batas tanggal harga modal yang berlaku.
     * @return array{penjualan: array<string,float>, modal: array<string,float>}
     *         Keduanya dikunci product_id.
     */
    public static function hitung(Closure $filterOrder, ?string $hargaCutoff): array
    {
        $jasa = Product::where('butuh_file', true)->get();

        // Nama produk jasa (lowercase) → product, untuk memetakan add-on.
        $byNama = $jasa->keyBy(fn ($p) => mb_strtolower(trim($p->nama_akun)));

        // Modal per 1 pemeriksaan tiap produk jasa (harga berlaku s/d periode).
        $modalKali = [];
        foreach ($jasa as $p) {
            $modalKali[(string) $p->id] = (float) $p->modalSatuan(1, 'kali', $hargaCutoff);
        }

        $penjualan = [];
        $modal = [];

        OrderItem::query()
            ->whereHas('order', $filterOrder)
            ->whereNotNull('addons')
            ->select(['id', 'product_id', 'quantity', 'addons'])
            ->chunkById(200, function ($items) use (&$penjualan, &$modal, $byNama, $modalKali) {
                foreach ($items as $it) {
                    $qty = max(1, (int) $it->quantity);

                    foreach (($it->addons ?? []) as $ad) {
                        $harga = (int) ($ad['harga'] ?? 0);
                        if ($harga <= 0) {
                            continue;
                        }

                        $cocok = $byNama->get(mb_strtolower(trim($ad['nama'] ?? '')));

                        if ($cocok) {
                            // Add-on = layanan berdiri sendiri → produknya sendiri.
                            $pid = (string) $cocok->id;
                            $penjualan[$pid] = ($penjualan[$pid] ?? 0) + $harga * $qty;
                            $modal[$pid] = ($modal[$pid] ?? 0) + ($modalKali[$pid] ?? 0) * $qty;
                        } elseif ($it->product_id) {
                            // Add-on opsi (mis. target parafrase) → tetap diakui di
                            // produk induk agar pendapatannya tak hilang; tanpa modal.
                            $pid = (string) $it->product_id;
                            $penjualan[$pid] = ($penjualan[$pid] ?? 0) + $harga * $qty;
                        }
                    }
                }
            });

        return ['penjualan' => $penjualan, 'modal' => $modal];
    }
}
