<?php

namespace App\Support;

/**
 * "Paling hemat" untuk pilihan paket durasi.
 *
 * Dipakai jendela pilih durasi di /shop DAN kartu "Pilih Paket" di halaman
 * detail produk — satu aturan di satu tempat, supaya kedua tempat tidak
 * mungkin menunjuk paket yang berbeda untuk produk yang sama.
 */
class PaketHemat
{
    /** Jumlah bulan sebuah paket, atau null bila satuannya bukan bulan/tahun. */
    public static function bulan(?string $tipe, $nilai): ?int
    {
        return match (strtolower((string) $tipe)) {
            'bulan' => (int) $nilai,
            'tahun' => (int) $nilai * 12,
            default => null,
        };
    }

    /**
     * Kunci paket dengan harga per bulan terendah, atau null.
     *
     * Hanya bila setidaknya DUA paket bisa dibandingkan dan benar-benar ada
     * selisih — label yang menempel ke satu-satunya paket, atau ke semua paket
     * yang sama murahnya, tidak memberi tahu apa pun.
     *
     * @param  array<int|string, float|int|null>  $perBulan  harga per bulan tiap paket, null bila tak bisa dihitung
     */
    public static function terhemat(array $perBulan): int|string|null
    {
        $sah = array_filter($perBulan, fn ($v) => $v !== null);

        if (count($sah) < 2 || min($sah) >= max($sah)) {
            return null;
        }

        $kunci = array_search(min($sah), $sah, true);

        return $kunci === false ? null : $kunci;
    }

    /**
     * "≈ Rp6.300/bulan" untuk paket LEBIH dari sebulan, selain itu null.
     *
     * Paket sebulan tidak diberi keterangan ini — itu harganya sendiri. Yang
     * sulit dihitung pembeli di kepalanya adalah harga per bulan paket panjang.
     */
    public static function setaraPerBulan(int $harga, ?int $bulan, string $rp = 'Rp'): ?string
    {
        if (! $bulan || $bulan <= 1) {
            return null;
        }

        return '≈ '.$rp.number_format((int) round($harga / $bulan), 0, ',', '.').'/bulan';
    }
}
