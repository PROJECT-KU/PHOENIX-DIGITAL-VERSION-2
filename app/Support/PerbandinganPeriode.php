<?php

namespace App\Support;

use App\Models\CashFlow;
use Carbon\Carbon;

/**
 * Perbandingan PERIODE BERJALAN vs PERIODE SEBELUMNYA untuk pemasukan,
 * pengeluaran, dan saldo bersih.
 *
 * Kenapa periode, bukan hari: ketiga angka itu memang angka periode (siklus
 * 21–20). Membandingkannya dengan "kemarin" seperti kartu Pendapatan Hari Ini
 * tidak punya arti — pemasukan periode berjalan hampir selalu lebih besar dari
 * pemasukan satu hari, dan selisihnya tidak memberi tahu apa pun.
 *
 * Sumber angkanya SAMA dengan kartu yang dibandingkan (tabel cash_flows,
 * berdasarkan transaction_date), supaya pembandingnya tidak pernah berasal
 * dari perhitungan yang berbeda dengan angka di atasnya.
 */
class PerbandinganPeriode
{
    /**
     * @return array{
     *     pemasukan: array, pengeluaran: array, saldo: array,
     *     label_sebelumnya: string
     * }
     */
    public static function ringkas(?Carbon $acuan = null): array
    {
        $acuan ??= now();

        $ini = PeriodeGaji::dariTanggal($acuan);
        $iniMulai = PeriodeGaji::mulai($ini['bulan'], $ini['tahun']);
        $iniAkhir = PeriodeGaji::akhir($ini['bulan'], $ini['tahun'])->copy()->addDay()->startOfDay();

        // Periode sebelumnya = satu siklus ke belakang. subMonthNoOverflow
        // supaya 31 Maret tidak melompat ke 3 Maret.
        $lalu = PeriodeGaji::dariTanggal($iniMulai->copy()->subDay());
        $laluMulai = PeriodeGaji::mulai($lalu['bulan'], $lalu['tahun']);
        $laluAkhir = PeriodeGaji::akhir($lalu['bulan'], $lalu['tahun'])->copy()->addDay()->startOfDay();

        $jumlah = fn (string $jenis, Carbon $mulai, Carbon $akhir) => (float) CashFlow::where('type', $jenis)
            ->where('transaction_date', '>=', $mulai)
            ->where('transaction_date', '<', $akhir)
            ->sum('amount');

        $masukIni = $jumlah('income', $iniMulai, $iniAkhir);
        $keluarIni = $jumlah('expense', $iniMulai, $iniAkhir);
        $masukLalu = $jumlah('income', $laluMulai, $laluAkhir);
        $keluarLalu = $jumlah('expense', $laluMulai, $laluAkhir);

        return [
            'pemasukan' => self::banding($masukIni, $masukLalu),
            'pengeluaran' => self::banding($keluarIni, $keluarLalu),
            'saldo' => self::banding($masukIni - $keluarIni, $masukLalu - $keluarLalu),
            'label_sebelumnya' => PeriodeGaji::label($lalu['bulan'], $lalu['tahun']),
        ];
    }

    /**
     * @return array{sekarang: float, sebelumnya: float, selisih: float, persen: ?float, arah: int}
     *                                                                                              persen null = tidak bisa dipersenkan (pembandingnya nol atau minus)
     *                                                                                              arah: 1 naik, 0 sama, -1 turun
     */
    private static function banding(float $sekarang, float $sebelumnya): array
    {
        return [
            'sekarang' => $sekarang,
            'sebelumnya' => $sebelumnya,
            'selisih' => $sekarang - $sebelumnya,
            'persen' => self::persen($sekarang, $sebelumnya),
            'arah' => $sekarang <=> $sebelumnya,
        ];
    }

    /**
     * Persentase perubahan terhadap periode lalu.
     *
     * NULL bila pembandingnya nol ATAU MINUS. Pada pembanding nol, membaginya
     * tidak punya arti; pada pembanding minus, tandanya terbalik — saldo yang
     * naik dari −100.000 ke +50.000 akan terhitung "−150%", padahal keadaannya
     * justru membaik. Tampilan menerjemahkan null jadi kalimat, bukan angka.
     */
    private static function persen(float $sekarang, float $sebelumnya): ?float
    {
        if ($sebelumnya <= 0) {
            return null;
        }

        return round((($sekarang - $sebelumnya) / $sebelumnya) * 100, 1);
    }
}
