<?php

namespace App\Support;

use App\Models\ProductBundlings;
use Illuminate\Support\Facades\Storage;

/**
 * Data tampilan satu paket bundling, siap cetak.
 *
 * Dipakai daftar paket (/bundling/product), halaman detail paket, dan deret
 * "Paket Lainnya" — satu aturan di satu tempat, supaya warna, harga, dan isi
 * paket yang sama tidak mungkin tampil berbeda di halaman berbeda.
 *
 * Harga dari HargaPaket::untuk(), sumber yang sama dengan keranjang. Semua
 * perbandingan dilakukan di sini supaya Blade tidak memuat ">" di sela
 * direktif blok (jebakan penanda morph Livewire).
 *
 * Relasi product1..product5 sebaiknya sudah dimuat (with) sebelum dipanggil.
 */
class KartuPaket
{
    /** Warna kategori Produktivitas — terlalu kusam untuk jadi aksen kartu. */
    public const WARNA_NETRAL = '#475569';

    /** Aksen cadangan bila tak ada produk isi yang berwarna. */
    public const WARNA_MEREK = '#f26522';

    /** Kemiringan ubin tumpukan, bergantian supaya terbaca "beberapa barang". */
    private const PUTAR = [-7, 5, -3, 6];

    /**
     * @return array<string, mixed>
     */
    public static function data(ProductBundlings $item): array
    {
        $hp = HargaPaket::untuk($item);
        $durasi = collect($item->bundleProducts())->keyBy('product_id');

        $isi = collect([1, 2, 3, 4, 5])
            ->map(fn ($i) => $item->{'product'.$i})
            ->filter()
            ->values()
            ->map(function ($p) use ($durasi) {
                $kat = KategoriBeranda::untukProduk($p->nama_akun);
                $d = $durasi->get($p->id);

                return [
                    'id' => $p->id,
                    'nama' => $p->nama_akun,
                    'url' => route('shop.detail-product', $p->id),
                    'kategori' => $kat['label'] ?? null,
                    'warna' => $kat['warna'] ?? '#f26522',
                    'ikon' => $kat['ikon'] ?? 'bi-box-seam',
                    'durasi' => $d ? $d['duration_value'].' '.ucfirst($d['duration_type']) : null,
                ];
            });

        $tumpuk = $isi->take(4)->values()->map(fn ($p, $i) => $p + ['putar' => self::PUTAR[$i]])->all();

        $hemat = $hp['coret'] > $hp['bayar'] ? $hp['coret'] - $hp['bayar'] : 0;
        $berkas = $item->gambar ? basename($item->gambar) : null;

        return [
            'id' => $item->id,
            'nama' => $item->nama_paket,
            'url' => route('bundling.detail', $item->id),
            'gambar' => $berkas && Storage::disk('public')->exists('img/ProductBundlings/'.$berkas)
                ? asset('storage/img/ProductBundlings/'.$berkas)
                : null,
            // Aksen = warna produk pertama yang BUKAN abu netral. Hampir semua
            // paket diawali Grammarly (Produktivitas, abu slate), dan mengikuti
            // produk pertama saja membuat kartu-kartu kusam abu-abu. Bila SEMUA
            // isinya abu (mis. Grammarly + DeepL), aksennya jingga merek —
            // halaman detail paket yang seluruhnya abu terbaca seperti nonaktif.
            'warna' => $isi->first(fn ($p) => strtolower($p['warna']) !== self::WARNA_NETRAL)['warna'] ?? self::WARNA_MEREK,
            'isi' => $isi->all(),
            'tumpuk' => $tumpuk,
            'jumlahIsi' => $isi->count(),
            'isiTampil' => $isi->take(3)->all(),
            'isiLain' => max(0, $isi->count() - 3),
            'harga' => $hp['bayar'],
            'hargaAsli' => $hemat ? $hp['coret'] : null,
            'hemat' => $hemat ? 'Hemat Rp'.number_format($hemat, 0, ',', '.') : null,
            'diskon' => $hemat && $hp['coret'] ? '-'.round($hemat / $hp['coret'] * 100).'%' : null,
            // Promo berkode tidak berlaku sendiri: kodenya WAJIB terlihat.
            'kode' => $hp['butuh_kode'] ? ($hp['promo']->kode_promo ?? null) : null,
            'jadwal' => $item->jadwalLabel(),
        ];
    }
}
