<?php

namespace App\Support;

use App\Models\Testimoni;
use Illuminate\Support\Facades\Cache;

/**
 * Ringkasan bintang untuk data terstruktur di SELURUH halaman publik.
 *
 * Dipanggil dari partials/seo yang ikut setiap halaman, jadi hasilnya
 * di-cache: tanpa itu setiap kunjungan halaman apa pun menambah dua kueri
 * agregat hanya untuk sebaris JSON-LD.
 */
class RingkasanTestimoni
{
    public const KUNCI = 'testimoni:ringkasan-seo';

    /** @return array{nilai: float, jumlah: int} */
    public static function untukSeo(): array
    {
        return Cache::remember(self::KUNCI, now()->addHours(6), function () {
            $sebaran = Testimoni::tampilPublik()
                ->selectRaw('rating, count(*) as jumlah')
                ->groupBy('rating')->pluck('jumlah', 'rating');

            $jumlah = (int) $sebaran->sum();

            return [
                'jumlah' => $jumlah,
                'nilai' => $jumlah
                    ? round($sebaran->reduce(fn ($t, $n, $b) => $t + ($b * $n), 0) / $jumlah, 1)
                    : 0.0,
            ];
        });
    }

    /** Dipanggil setiap kali moderasi mengubah apa yang tampil di publik. */
    public static function lupakan(): void
    {
        Cache::forget(self::KUNCI);
    }
}
