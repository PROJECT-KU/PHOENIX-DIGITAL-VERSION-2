<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Produk paling laku, dihitung dari pesanan yang benar-benar dibayar.
 *
 * Sebelumnya "Produk Terlaris" di beranda adalah empat kartu yang ditulis
 * tetap di dalam Blade — nama, gambar, dan keterangannya dipatok, dan semuanya
 * menaut ke /shop, bukan ke produknya. Daftar itu tidak pernah berubah meski
 * yang laku sudah berganti berbulan-bulan, dan tidak ada yang tahu ia bohong
 * karena tampilannya tetap meyakinkan.
 *
 * Yang dihitung adalah JUMLAH PESANAN, bukan jumlah unit: "paling sering
 * dibeli" berarti paling banyak orang memesannya. Satu pesanan berisi 10 bulan
 * langganan tidak boleh mengalahkan sepuluh pesanan dari sepuluh orang berbeda.
 */
class ProdukTerlaris
{
    /** Hanya pesanan yang uangnya sudah masuk — sama dengan aturan buku kas. */
    public const STATUS_DIHITUNG = ['paid', 'completed'];

    /** Jendela hitung bawaan, dalam hari. */
    public const JENDELA_HARI = 30;

    /**
     * Ambil produk terlaris, lengkap dengan jumlah pesanannya.
     *
     * Punya dua jaring pengaman, karena bagian beranda TIDAK BOLEH kosong:
     * bila jendela 30 hari belum cukup mengisi, hitungannya diperluas ke
     * sepanjang masa; bila masih kurang juga (toko baru, atau produk barunya
     * belum laku), sisanya ditambal produk terbaru.
     *
     * @return Collection<int, Product> tiap produk membawa atribut `pesanan`
     */
    public static function ambil(int $jumlah = 5, ?int $hariTerakhir = self::JENDELA_HARI): Collection
    {
        $terpilih = self::hitung($jumlah, $hariTerakhir);

        if ($hariTerakhir !== null && $terpilih->count() < $jumlah) {
            $terpilih = self::gabung($terpilih, self::hitung($jumlah, null), $jumlah);
        }

        if ($terpilih->count() < $jumlah) {
            $terpilih = self::gabung($terpilih, self::terbaru($jumlah), $jumlah);
        }

        return $terpilih->values();
    }

    /**
     * Peringkat murni dari data pesanan.
     *
     * @return Collection<int, Product>
     */
    private static function hitung(int $jumlah, ?int $hariTerakhir): Collection
    {
        $baris = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            // Ikut produknya: order_items menyimpan product_id apa adanya, jadi
            // produk yang sudah DIHAPUS tetap tercatat sebagai penjualan dan akan
            // muncul sebagai kartu kosong tanpa nama.
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('orders.status', self::STATUS_DIHITUNG)
            // Produk yang sedang dijeda tidak bisa di-checkout. Menampilkannya
            // sebagai unggulan beranda hanya mengantar orang ke jalan buntu.
            ->where(fn ($q) => $q->whereNull('products.dijeda')->orWhere('products.dijeda', false))
            ->when($hariTerakhir, fn ($q) => $q->where('orders.created_at', '>=', now()->subDays($hariTerakhir)))
            ->groupBy('order_items.product_id')
            ->select(
                'order_items.product_id',
                DB::raw('COUNT(DISTINCT orders.id) AS pesanan'),
                DB::raw('SUM(order_items.quantity) AS unit'),
            )
            ->orderByDesc('pesanan')
            ->orderByDesc('unit')
            ->limit($jumlah)
            ->get();

        if ($baris->isEmpty()) {
            return collect();
        }

        $produk = Product::whereIn('id', $baris->pluck('product_id'))->get()->keyBy('id');

        return $baris
            ->map(function ($b) use ($produk) {
                $p = $produk->get($b->product_id);

                return $p ? tap($p, fn ($x) => $x->pesanan = (int) $b->pesanan) : null;
            })
            ->filter()
            ->values();
    }

    /** Penambal terakhir: produk terbaru yang belum masuk daftar. */
    private static function terbaru(int $jumlah): Collection
    {
        return Product::where(fn ($q) => $q->whereNull('dijeda')->orWhere('dijeda', false))
            ->latest()
            ->take($jumlah)
            ->get()
            ->each(fn ($p) => $p->pesanan = 0);
    }

    /**
     * Menyambung daftar tanpa produk kembar, lalu dipotong sesuai jumlah.
     *
     * @param  Collection<int, Product>  $utama
     * @param  Collection<int, Product>  $tambahan
     * @return Collection<int, Product>
     */
    private static function gabung(Collection $utama, Collection $tambahan, int $jumlah): Collection
    {
        $sudahAda = $utama->pluck('id')->all();

        return $utama
            ->concat($tambahan->reject(fn ($p) => in_array($p->id, $sudahAda, true)))
            ->take($jumlah);
    }
}
