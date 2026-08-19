<?php

namespace App\Support;

use App\Models\Order;

/**
 * Perbandingan HARI INI vs KEMARIN untuk pemasukan & kode unik.
 *
 * SATU sumber untuk Dashboard dan Cash Flow. Keduanya menampilkan angka yang
 * sama persis di layar berbeda; kalau rumusnya disalin dua kali, cukup satu
 * kali salah sunting untuk membuat dua layar saling bertentangan tanpa ada
 * yang sadar.
 *
 * Syarat status & patokan tanggalnya sengaja SAMA dengan "Pendapatan Hari Ini"
 * yang sudah lebih dulu ada: status paid/processing/completed, tanggal dibaca
 * dari COALESCE(paid_at, created_at). Dengan begitu kode unik selalu merupakan
 * bagian dari pemasukan pada hari yang sama, bukan hitungan yang berdiri sendiri.
 */
class PerbandinganHarian
{
    /** Status yang dianggap "uangnya sudah masuk". */
    private const STATUS_DIBAYAR = ['paid', 'processing', 'completed'];

    /**
     * Pemasukan & kode unik, hari ini vs kemarin, lengkap dengan selisihnya.
     *
     * @return array{pendapatan: array, kode_unik: array}
     */
    public static function ringkas(): array
    {
        return [
            'pendapatan' => self::banding('total'),
            'kode_unik' => self::banding('unique_code'),
        ];
    }

    /**
     * @return array{hari_ini: float, kemarin: float, selisih: float, persen: ?float, arah: int}
     *                                                                                           persen null = tidak bisa dihitung (kemarin nol, tak ada pembanding)
     *                                                                                           arah: 1 naik, 0 sama, -1 turun
     */
    private static function banding(string $kolom): array
    {
        $hariIni = self::jumlah($kolom, today()->toDateString());
        $kemarin = self::jumlah($kolom, today()->subDay()->toDateString());

        return [
            'hari_ini' => $hariIni,
            'kemarin' => $kemarin,
            'selisih' => $hariIni - $kemarin,
            'persen' => self::persen($hariIni, $kemarin),
            'arah' => $hariIni <=> $kemarin,
        ];
    }

    private static function jumlah(string $kolom, string $tanggal): float
    {
        return (float) Order::whereIn('status', self::STATUS_DIBAYAR)
            ->whereRaw('DATE(COALESCE(paid_at, created_at)) = ?', [$tanggal])
            ->sum($kolom);
    }

    /**
     * Persentase perubahan terhadap kemarin.
     *
     * Mengembalikan NULL bila kemarin nol — bukan 0% dan bukan 100%. Pembagian
     * dengan nol tidak punya arti, dan menampilkan "naik 100%" saat kemarin
     * kosong itu menyesatkan: naik dari nol bukan kenaikan yang bisa dipersen.
     * Tampilan menerjemahkan null jadi kalimat, bukan angka.
     */
    private static function persen(float $hariIni, float $kemarin): ?float
    {
        if ($kemarin <= 0) {
            return null;
        }

        return round((($hariIni - $kemarin) / $kemarin) * 100, 1);
    }
}
