<?php

namespace App\Livewire\Concerns;

/**
 * Pengiriman event Meta Pixel dari komponen Livewire.
 *
 * Kenapa lewat event browser, bukan langsung memanggil fbq di blade:
 * menambah ke keranjang terjadi TANPA memuat ulang halaman (Livewire), jadi
 * skrip yang hanya jalan saat halaman dimuat tidak akan pernah terpicu.
 *
 * Satu pintu untuk semua tempat "tambah ke keranjang" (detail produk, katalog,
 * dua halaman bundling). Sebelumnya tiap tempat menyusun payload sendiri —
 * cara tercepat membuat nilai transaksi di laporan iklan berbeda-beda antar
 * halaman tanpa ada yang sadar.
 */
trait MengirimPixel
{
    /**
     * Kirim satu event standar Meta ke browser.
     *
     * Penerimanya: pendengar 'pixel-event' di partials/meta-pixel.blade.php.
     * Di halaman yang pixel-nya sengaja tidak dimuat (URL bertoken), event ini
     * diabaikan dengan sendirinya karena fbq tidak ada di sana.
     *
     * @param  string  $nama  nama event standar Meta, mis. 'AddToCart'
     * @param  array<string,mixed>  $data  parameter event (value, currency, dst.)
     */
    protected function kirimPixel(string $nama, array $data = []): void
    {
        $this->dispatch('pixel-event', nama: $nama, data: $data);
    }

    /**
     * Payload AddToCart dari SATU baris keranjang.
     *
     * Nilainya memakai `subtotal` (harga × jumlah, sudah termasuk add-on bila
     * ada), bukan `price` satuan — supaya angka di laporan iklan sama dengan
     * yang benar-benar masuk keranjang pembeli.
     *
     * @param  array<string,mixed>  $baris  satu item dari session('cart')
     * @return array<string,mixed>
     */
    protected function pixelDariBarisKeranjang(array $baris): array
    {
        return [
            'content_ids' => [(string) ($baris['product_id'] ?? '')],
            'content_name' => (string) ($baris['product_name'] ?? ''),
            'content_type' => 'product',
            'contents' => [[
                'id' => (string) ($baris['product_id'] ?? ''),
                'quantity' => (int) ($baris['quantity'] ?? 1),
            ]],
            'value' => (float) ($baris['subtotal'] ?? 0),
            'currency' => 'IDR',
        ];
    }
}
