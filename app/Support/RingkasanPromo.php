<?php

namespace App\Support;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Berapa kali tiap jenis promo benar-benar terpakai pada satu periode, dan
 * berapa rupiah yang dilepas karenanya.
 *
 * Dipisah dari komponen dasbor supaya bisa diuji tanpa merender halamannya:
 * kueri grafik di dasbor memakai MONTH() milik MySQL, sedangkan pengujian
 * berjalan di SQLite.
 *
 * Dua sumber yang berbeda sengaja disatukan di sini:
 *  - Flash sale, kode promo, dan promo otomatis tercatat di tabel pivot
 *    order_promo (satu baris tiap promo yang menempel pada satu pesanan).
 *  - Kode rujukan TIDAK lewat pivot itu; kodenya menempel langsung pada
 *    pesanan (referral_code + referral_discount).
 */
class RingkasanPromo
{
    /** Pesanan yang uangnya benar-benar masuk — sama dengan aturan pendapatan. */
    public const STATUS_DIBAYAR = ['paid', 'processing', 'completed'];

    /**
     * @return array{
     *     flash_sale: array{jumlah:int, nilai:float},
     *     kode_promo: array{jumlah:int, nilai:float},
     *     auto_promo: array{jumlah:int, nilai:float},
     *     referral: array{jumlah:int, nilai:float},
     *     total_jumlah: int,
     *     total_nilai: float
     * }
     */
    public static function periode(Carbon $mulai, Carbon $akhirEksklusif): array
    {
        /*
         | Tanggal yang dipakai adalah tanggal BAYAR (paid_at), bukan tanggal
         | pesanan dibuat — sama dengan kartu "Total Kode Unik" di dasbor.
         | Pesanan yang dibuat akhir periode lalu dan baru dibayar periode ini
         | uangnya masuk periode ini, jadi diskonnya pun keluar periode ini.
         */
        $dalamPeriode = fn ($q) => $q
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) >= ?', [$mulai->toDateTimeString()])
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) < ?', [$akhirEksklusif->toDateTimeString()]);

        $pivot = DB::table('order_promo')
            ->join('orders', 'orders.id', '=', 'order_promo.order_id')
            ->join('promos', 'promos.id', '=', 'order_promo.promo_id')
            ->whereIn('orders.status', self::STATUS_DIBAYAR)
            ->where($dalamPeriode)
            ->groupBy('promos.tipe_promo')
            ->selectRaw('promos.tipe_promo as tipe, COUNT(*) as jumlah, COALESCE(SUM(order_promo.jumlah_diskon), 0) as nilai')
            ->get()
            ->keyBy('tipe');

        $rujukan = Order::query()
            ->whereIn('orders.status', self::STATUS_DIBAYAR)
            ->whereNotNull('orders.referral_code')
            ->where($dalamPeriode)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(orders.referral_discount), 0) as nilai')
            ->first();

        $ambil = fn (string $tipe) => [
            'jumlah' => (int) ($pivot[$tipe]->jumlah ?? 0),
            'nilai' => (float) ($pivot[$tipe]->nilai ?? 0),
        ];

        $hasil = [
            'flash_sale' => $ambil('flash_sale'),
            'kode_promo' => $ambil('kode_promo'),
            'auto_promo' => $ambil('auto_promo'),
            'referral' => [
                'jumlah' => (int) ($rujukan->jumlah ?? 0),
                'nilai' => (float) ($rujukan->nilai ?? 0),
            ],
        ];

        $hasil['total_jumlah'] = array_sum(array_column($hasil, 'jumlah'));
        $hasil['total_nilai'] = array_sum(array_column($hasil, 'nilai'));

        return $hasil;
    }

    /**
     * Rincian per PROMO, bukan per jenis: nama promonya apa dan dipakai
     * berapa kali.
     *
     * "Flash sale 12 kali" tidak bisa ditindaklanjuti — yang menentukan promo
     * mana yang layak diulang adalah nama promonya. Kode rujukan ikut di sini
     * sebagai barisnya sendiri, dikelompokkan per KODE, karena di situlah
     * pertanyaannya sama: kode siapa yang benar-benar membawa pembeli.
     *
     * @return array<int, array{nama:string, kode:?string, tipe:string, jumlah:int, nilai:float}>
     */
    public static function rincian(Carbon $mulai, Carbon $akhirEksklusif, int $batas = 8): array
    {
        $dalamPeriode = fn ($q) => $q
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) >= ?', [$mulai->toDateTimeString()])
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) < ?', [$akhirEksklusif->toDateTimeString()]);

        $promo = DB::table('order_promo')
            ->join('orders', 'orders.id', '=', 'order_promo.order_id')
            ->join('promos', 'promos.id', '=', 'order_promo.promo_id')
            ->whereIn('orders.status', self::STATUS_DIBAYAR)
            ->where($dalamPeriode)
            ->groupBy('promos.id', 'promos.nama_promo', 'promos.kode_promo', 'promos.tipe_promo')
            ->selectRaw('promos.nama_promo as nama, promos.kode_promo as kode, promos.tipe_promo as tipe,
                         COUNT(*) as jumlah, COALESCE(SUM(order_promo.jumlah_diskon), 0) as nilai')
            ->get()
            ->map(fn ($r) => [
                'nama' => (string) $r->nama,
                'kode' => $r->kode ? (string) $r->kode : null,
                'tipe' => (string) $r->tipe,
                'jumlah' => (int) $r->jumlah,
                'nilai' => (float) $r->nilai,
            ]);

        $rujukan = Order::query()
            ->whereIn('orders.status', self::STATUS_DIBAYAR)
            ->whereNotNull('orders.referral_code')
            ->where($dalamPeriode)
            ->groupBy('orders.referral_code')
            ->selectRaw('orders.referral_code as kode, COUNT(*) as jumlah, COALESCE(SUM(orders.referral_discount), 0) as nilai')
            ->get()
            ->map(fn ($r) => [
                'nama' => 'Kode rujukan '.$r->kode,
                'kode' => (string) $r->kode,
                'tipe' => 'referral',
                'jumlah' => (int) $r->jumlah,
                'nilai' => (float) $r->nilai,
            ]);

        return $promo->concat($rujukan)
            ->sortByDesc(fn ($b) => [$b['jumlah'], $b['nilai']])
            ->take($batas)
            ->values()
            ->all();
    }
}
