<?php

namespace App\Support;

use App\Models\ProductReview;

/**
 * Ringkasan bintang satu produk/paket untuk data terstruktur.
 *
 * Halaman produk sudah menghitung & menampilkan rata-ratanya, tapi angka itu
 * tidak pernah ikut ke JSON-LD — sehingga bintang kuning di hasil pencarian
 * tidak pernah muncul untuk halaman yang paling sering dicari orang.
 */
class RingkasanUlasan
{
    /** @return array{nilai: float, jumlah: int} */
    public static function untuk(string $jenis, $targetId): array
    {
        $sebaran = ProductReview::approved()->untuk($jenis, $targetId)
            ->selectRaw('rating, count(*) as jumlah')
            ->groupBy('rating')->pluck('jumlah', 'rating');

        $jumlah = (int) $sebaran->sum();

        return [
            'jumlah' => $jumlah,
            'nilai' => $jumlah
                ? round($sebaran->reduce(fn ($t, $n, $b) => $t + ($b * $n), 0) / $jumlah, 1)
                : 0.0,
        ];
    }

    /**
     * Potongan aggregateRating siap tempel — array KOSONG bila belum ada
     * ulasan, karena Google menolak aggregateRating tanpa ulasan.
     */
    public static function jsonLd(string $jenis, $targetId): array
    {
        $r = self::untuk($jenis, $targetId);

        if ($r['jumlah'] < 1) {
            return [];
        }

        return [
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $r['nilai'],
                'reviewCount' => $r['jumlah'],
                'bestRating' => 5,
                'worstRating' => 1,
            ],
        ];
    }
}
