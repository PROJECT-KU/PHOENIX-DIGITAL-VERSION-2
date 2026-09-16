<?php

namespace App\Support;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * BERAPA KALI, bukan hanya berapa rupiah.
 *
 * Seluruh dasbor sebelumnya bicara rupiah. Saat omset naik seperlima, tidak
 * ada cara tahu itu karena pembelinya bertambah atau karena yang dibeli lebih
 * mahal — dua hal yang butuh tindakan berbeda.
 *
 * Pelanggan lama dipisah dari pelanggan baru karena inilah nadi bisnis
 * langganan: pelanggan yang memperpanjang tidak memerlukan biaya iklan, dan
 * turunnya angka itu adalah peringatan paling awal yang bisa didapat.
 */
class RingkasanPenjualan
{
    /** Pesanan yang uangnya sudah masuk. */
    public const STATUS_DIBAYAR = ['paid', 'processing', 'completed'];

    /**
     * @return array{
     *     pesanan: int, nilai: float, rata: float,
     *     pelanggan: int, baru: int, kembali: int, tanpa_akun: int
     * }
     */
    public static function periode(Carbon $mulai, Carbon $akhirEksklusif): array
    {
        $dibayar = fn () => Order::whereIn('status', self::STATUS_DIBAYAR)
            ->whereRaw('COALESCE(paid_at, created_at) >= ?', [$mulai->toDateTimeString()])
            ->whereRaw('COALESCE(paid_at, created_at) < ?', [$akhirEksklusif->toDateTimeString()]);

        $pesanan = (int) $dibayar()->count();
        $nilai = (float) $dibayar()->sum('total');

        $pelanggan = (int) $dibayar()->whereNotNull('customer_id')->distinct()->count('customer_id');

        // Pelanggan BARU = yang pesanan berbayar PERTAMANYA jatuh di periode
        // ini. Dihitung dari seluruh riwayat, bukan hanya periode ini —
        // tanpa itu, pelanggan lama yang kebetulan membeli lagi akan
        // terhitung baru setiap periode.
        $baru = (int) Order::whereIn('status', self::STATUS_DIBAYAR)
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('MIN(COALESCE(paid_at, created_at)) >= ?', [$mulai->toDateTimeString()])
            ->havingRaw('MIN(COALESCE(paid_at, created_at)) < ?', [$akhirEksklusif->toDateTimeString()])
            ->get([DB::raw('customer_id')])
            ->count();

        return [
            'pesanan' => $pesanan,
            'nilai' => $nilai,
            'rata' => $pesanan > 0 ? $nilai / $pesanan : 0.0,
            'pelanggan' => $pelanggan,
            'baru' => min($baru, $pelanggan),
            'kembali' => max($pelanggan - $baru, 0),
            // Pesanan yang dibuat tanpa akun pelanggan (pembeli tamu di kasir).
            // Dipisah supaya "pelanggan" tidak terbaca lebih kecil dari
            // "pesanan" tanpa penjelasan.
            'tanpa_akun' => (int) $dibayar()->whereNull('customer_id')->count(),
        ];
    }

    /**
     * Angka periode ini berikut pembandingnya satu periode ke belakang.
     *
     * @return array{kini: array, lalu: array, label_sebelumnya: string, pesanan: array, rata: array, kembali: array}
     */
    public static function banding(Carbon $acuan): array
    {
        $ini = PeriodeGaji::dariTanggal($acuan);
        $kini = self::periode(
            PeriodeGaji::mulai($ini['bulan'], $ini['tahun']),
            PeriodeGaji::akhir($ini['bulan'], $ini['tahun'])->copy()->addDay()->startOfDay()
        );

        [$laluMulai, $laluAkhir, $label] = PerbandinganPeriode::periodeSebelum($acuan);
        $lalu = self::periode($laluMulai, $laluAkhir);

        return [
            'kini' => $kini,
            'lalu' => $lalu,
            'label_sebelumnya' => $label,
            'pesanan' => PerbandinganPeriode::banding((float) $kini['pesanan'], (float) $lalu['pesanan']),
            'rata' => PerbandinganPeriode::banding($kini['rata'], $lalu['rata']),
            'kembali' => PerbandinganPeriode::banding((float) $kini['kembali'], (float) $lalu['kembali']),
        ];
    }
}
