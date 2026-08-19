<?php

namespace App\Livewire\Concerns;

use App\Models\ProductBundlings;
use App\Support\HargaPaket;

/**
 * Modal detail paket bundling, dipakai bersama oleh halaman /bundling dan
 * halaman paket tersendiri supaya tombol "Lihat" berperilaku sama di keduanya.
 *
 * Harga diambil dari HargaPaket, sumber yang sama dengan kartu paket, agar
 * angka di modal tidak berbeda dengan angka yang barusan diklik pengunjung.
 */
trait DetailPaket
{
    public bool $showBundleDetail = false;

    /** @var array<string,mixed>|null */
    public ?array $detailBundle = null;

    public function openDetail($bundlingId)
    {
        $bundling = ProductBundlings::find($bundlingId);
        if (! $bundling) {
            $this->dispatch('cart-error', message: 'Bundling tidak ditemukan.');

            return;
        }

        $durs = $bundling->durations ?? [];
        $products = [];
        foreach ([1, 2, 3, 4, 5] as $i) {
            $p = $bundling->{'product'.$i};
            if ($p) {
                $dur = $durs['product_'.$i] ?? null;
                $products[] = [
                    'nama' => $p->nama_akun,
                    'dur_value' => (int) ($dur['value'] ?? 1),
                    'dur_type' => ucfirst($dur['type'] ?? 'bulan'),
                ];
            }
        }

        // Hanya nilai skalar yang disimpan; objek Promo tidak ikut supaya
        // properti publik Livewire tetap aman diserialisasi.
        $hp = HargaPaket::untuk($bundling);

        $this->detailBundle = [
            'id' => $bundling->id,
            'nama' => $bundling->nama_paket,
            'gambar' => $bundling->gambar,
            'deskripsi' => $bundling->deskripsi,
            'produk' => $products,
            'bayar' => (int) $hp['bayar'],
            'coret' => (int) $hp['coret'],
            'potongan' => (int) $hp['potongan'],
            'butuh_kode' => (bool) $hp['butuh_kode'],
        ];
        $this->showBundleDetail = true;
    }

    public function closeDetail()
    {
        $this->showBundleDetail = false;
    }
}
