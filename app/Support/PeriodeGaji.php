<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * Sumber kebenaran PERIODE PENGGAJIAN.
 *
 * Perusahaan gajian setiap tanggal 20 (cutoff). Maka periode gaji bulan X =
 * tanggal 21 bulan (X-1) s/d tanggal 20 bulan X, dan dibayar pada tanggal 20 bulan X.
 *
 * Contoh: periode "Juli 2026" = 21 Juni 2026 s/d 20 Juli 2026, dibayar 20 Juli 2026.
 * Jadi presensi/lembur/penyelesaian task pada rentang itu masuk ke gaji periode Juli.
 *
 * Hari cutoff bisa diubah lewat Setting `payroll_cutoff_day` (default 20) tanpa ubah kode.
 */
class PeriodeGaji
{
    public const DEFAULT_CUTOFF = 20;

    /**
     * Tanggal cutoff (hari gajian). Dibatasi 1..28 agar selalu valid di semua bulan.
     */
    public static function cutoffDay(): int
    {
        $day = (int) Setting::get('payroll_cutoff_day', self::DEFAULT_CUTOFF);

        return ($day >= 1 && $day <= 28) ? $day : self::DEFAULT_CUTOFF;
    }

    /**
     * Awal periode = sehari setelah cutoff pada bulan sebelumnya (mis. 21 Juni).
     */
    public static function mulai(int $bulan, int $tahun): Carbon
    {
        return Carbon::create($tahun, $bulan, 1)
            ->subMonthNoOverflow()
            ->setDay(self::cutoffDay())
            ->addDay()
            ->startOfDay();
    }

    /**
     * Akhir periode = tanggal cutoff pada bulan tsb (mis. 20 Juli).
     */
    public static function akhir(int $bulan, int $tahun): Carbon
    {
        return Carbon::create($tahun, $bulan, self::cutoffDay())->endOfDay();
    }

    /**
     * Rentang periode gaji.
     *
     * @return array{0: Carbon, 1: Carbon} [mulai, akhir]
     */
    public static function range(int $bulan, int $tahun): array
    {
        return [self::mulai($bulan, $tahun), self::akhir($bulan, $tahun)];
    }

    /**
     * Tanggal pembayaran gaji untuk periode tsb (tanggal cutoff).
     */
    public static function tanggalBayar(int $bulan, int $tahun): Carbon
    {
        return Carbon::create($tahun, $bulan, self::cutoffDay())->startOfDay();
    }

    /**
     * Sebuah tanggal masuk periode gaji yang mana?
     * Tanggal SETELAH cutoff (mis. 21 Juni) → periode bulan BERIKUTNYA (Juli).
     * Tanggal s/d cutoff (mis. 20 Juli) → periode bulan itu (Juli).
     *
     * @return array{bulan: int, tahun: int}
     */
    public static function dariTanggal($tanggal): array
    {
        $t = $tanggal instanceof Carbon ? $tanggal->copy() : Carbon::parse($tanggal);

        if ($t->day > self::cutoffDay()) {
            $t = $t->addMonthNoOverflow();
        }

        return ['bulan' => (int) $t->month, 'tahun' => (int) $t->year];
    }

    /**
     * Label rentang untuk ditampilkan, mis. "21 Jun – 20 Jul 2026".
     */
    public static function label(int $bulan, int $tahun): string
    {
        [$mulai, $akhir] = self::range($bulan, $tahun);

        return $mulai->locale('id')->translatedFormat('j M').' – '.$akhir->locale('id')->translatedFormat('j M Y');
    }
}
