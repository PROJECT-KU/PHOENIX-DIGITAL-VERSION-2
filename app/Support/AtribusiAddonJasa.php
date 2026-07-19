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
 * PENTING: order_items.subtotal SUDAH termasuk harga add-on
 * (subtotal = harga produk + addons_total). Karena itu penjualan add-on
 * DIPINDAHKAN dari produk induk, bukan ditambahkan — kalau ditambahkan,
 * satu penjualan terhitung dua kali dan omset menggelembung.
 *
 * Aturan:
 *  - Add-on yang NAMA-nya cocok dengan produk jasa → penjualannya PINDAH dari
 *    produk induk ke produk itu, dan modal pemeriksaannya DITAMBAHKAN di sana
 *    (modal induk memang belum mencakup pemeriksaan add-on).
 *  - Add-on lain (mis. target parafrase "plagiasi di bawah 30%") → dibiarkan
 *    apa adanya di produk induk; ia bagian dari layanan induk, bukan
 *    pemeriksaan berdiri sendiri.
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

                        // Add-on opsi (tanpa produk seenama, mis. target parafrase)
                        // dibiarkan di produk induk — sudah benar di subtotal.
                        if (! $cocok || ! $it->product_id) {
                            continue;
                        }

                        $induk = (string) $it->product_id;
                        $pid = (string) $cocok->id;
                        $nilai = $harga * $qty;

                        // PINDAHKAN penjualan: keluar dari induk, masuk ke produk add-on.
                        $penjualan[$induk] = ($penjualan[$induk] ?? 0) - $nilai;
                        $penjualan[$pid] = ($penjualan[$pid] ?? 0) + $nilai;

                        // Modal pemeriksaan add-on: DITAMBAHKAN (modal induk belum
                        // menghitung pemeriksaan ini).
                        $modal[$pid] = ($modal[$pid] ?? 0) + ($modalKali[$pid] ?? 0) * $qty;
                    }
                }
            });

        return ['penjualan' => $penjualan, 'modal' => $modal];
    }
}
