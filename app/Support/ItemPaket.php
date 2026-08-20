<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductBundlings;

/**
 * Memecah paket bundling menjadi baris-baris produk penyusunnya.
 *
 * Satu paket berisi beberapa akun yang berbeda, dan tiap akun butuh slot
 * kredensialnya sendiri: username, password, tanggal mulai/berakhir, status
 * langganan. Bila paket disimpan sebagai SATU baris pesanan, admin cuma punya
 * satu slot untuk dua akun atau lebih — akunnya tidak bisa dikirim sama sekali
 * (kejadian INV-20260820-0008).
 *
 * Karena itu paket selalu dipecah saat pesanan dibuat, baik dari checkout
 * publik maupun dari form admin, memakai pembagian harga yang sama persis.
 */
class ItemPaket
{
    /**
     * Harga normal satu produk untuk durasi tertentu.
     *
     * Durasi non-katalog (mis. 2/3/4 bulan) tidak punya harga khusus, jadi
     * dihitung dari harga per bulan — tanpa ini durasinya berharga Rp 0.
     */
    public static function hargaNormal(Product $produk, string $tipeDurasi, int $nilaiDurasi): int
    {
        $harga = (int) $produk->hargaUntuk($nilaiDurasi, $tipeDurasi);

        if ($harga > 0) {
            return $harga;
        }

        if ($tipeDurasi === 'bulan' && (int) ($produk->harga_perbulan ?? 0) > 0 && $nilaiDurasi > 0) {
            return (int) $produk->harga_perbulan * $nilaiDurasi;
        }

        return $harga;
    }

    /**
     * Baris produk penyusun paket, lengkap dengan pembagian harganya.
     *
     * Harga paket dibagi proporsional terhadap harga normal tiap produk, dan
     * sisa pembulatan dilimpahkan ke baris terakhir supaya jumlahnya PERSIS
     * sama dengan harga paket — pesanan tidak boleh meleset satu rupiah pun
     * dari yang dibayar pelanggan.
     *
     * @return array<int, array{product_id:string, product_name:string, duration_type:string, duration_value:int, normal:int, distributed:int}>
     */
    public static function pecah(ProductBundlings $paket, int $hargaPaket): array
    {
        $baris = [];

        foreach ($paket->bundleProducts() as $bp) {
            $produk = Product::find($bp['product_id']);

            if (! $produk) {
                continue;
            }

            $baris[] = [
                'product_id' => $produk->id,
                'product_name' => $produk->nama_akun,
                'duration_type' => $bp['duration_type'],
                'duration_value' => (int) $bp['duration_value'],
                'normal' => self::hargaNormal($produk, $bp['duration_type'], (int) $bp['duration_value']),
                'distributed' => 0,
            ];
        }

        return self::bagiHarga($baris, $hargaPaket);
    }

    /**
     * Bagi $hargaPaket ke baris-baris yang sudah punya kunci 'normal'.
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @return array<int, array<string, mixed>>
     */
    public static function bagiHarga(array $baris, int $hargaPaket): array
    {
        if (empty($baris)) {
            return [];
        }

        $totalNormal = array_sum(array_column($baris, 'normal'));
        $jumlah = count($baris);
        $terpakai = 0;
        $kunciTerakhir = array_key_last($baris);

        foreach ($baris as $i => $b) {
            if ($i === $kunciTerakhir) {
                $bagian = $hargaPaket - $terpakai; // sisa agar total pas
            } else {
                $bobot = $totalNormal > 0 ? ((int) $b['normal'] / $totalNormal) : (1 / max(1, $jumlah));
                $bagian = (int) round($hargaPaket * $bobot);
                $terpakai += $bagian;
            }

            $baris[$i]['distributed'] = max(0, $bagian);
        }

        return $baris;
    }

    /** Nama item pesanan: menandai produk ini bagian dari paket mana. */
    public static function namaItem(string $namaPaket, string $namaProduk): string
    {
        return '['.$namaPaket.'] '.$namaProduk;
    }
}
