<?php

namespace App\Support;

use App\Models\CashFlow;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * LABA: berapa dari omset yang benar-benar tersisa.
 *
 * Modal tiap pesanan sudah lama tercatat — SyncOrderPrivateCostAction menulis
 * satu baris expense per order item, bersumber pada OrderItem. Tetapi di
 * dasbor ia melebur ke dalam satu angka "Total Pengeluaran" bersama gaji,
 * iklan, dan listrik, sehingga tidak ada satu layar pun yang bisa menjawab
 * pertanyaan paling dasar: dari omset sekian, berapa yang modal dan berapa
 * yang untung.
 *
 * Modal dikenali dari SUMBERNYA (sourceable OrderItem), bukan dari nama
 * kategorinya: kategori adalah teks yang bisa diketik ulang admin, sedangkan
 * sumber ditulis kode dan tidak bisa meleset.
 *
 * Omset dan modal dihitung dari HIMPUNAN PESANAN yang sama — pesanan berbayar
 * pada periode ini — bukan dari tanggal baris buku kasnya. Dengan begitu
 * keduanya pasti menggambarkan pesanan yang sama, dan omsetnya persis sama
 * dengan kartu jumlah pesanan di sebelahnya. (Angka buku kas bisa sedikit
 * berbeda: barisnya baru tertulis saat pesanan diproses admin.)
 */
class RingkasanLaba
{
    /**
     * @return array{
     *     omset: float, modal: float, laba_kotor: float, margin: ?float,
     *     biaya_operasional: float
     * }
     */
    public static function periode(Carbon $mulai, Carbon $akhirEksklusif): array
    {
        $penjualan = RingkasanPenjualan::periode($mulai, $akhirEksklusif);
        $omset = $penjualan['nilai'];

        $modal = (float) DB::table('cash_flows')
            ->join('order_items', 'order_items.id', '=', 'cash_flows.sourceable_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('cash_flows.sourceable_type', OrderItem::class)
            ->where('cash_flows.type', 'expense')
            ->whereIn('orders.status', RingkasanPenjualan::STATUS_DIBAYAR)
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) >= ?', [$mulai->toDateTimeString()])
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) < ?', [$akhirEksklusif->toDateTimeString()])
            ->sum('cash_flows.amount');

        // Biaya operasional = seluruh pengeluaran buku kas MINUS modal. Gaji,
        // iklan, dan listrik tidak menempel pada satu pesanan pun, jadi
        // dasarnya tetap tanggal buku kas.
        $pengeluaran = (float) CashFlow::where('type', 'expense')
            ->where('transaction_date', '>=', $mulai)
            ->where('transaction_date', '<', $akhirEksklusif)
            ->sum('amount');

        $modalBuku = (float) CashFlow::where('type', 'expense')
            ->where('sourceable_type', OrderItem::class)
            ->where('transaction_date', '>=', $mulai)
            ->where('transaction_date', '<', $akhirEksklusif)
            ->sum('amount');

        $labaKotor = $omset - $modal;

        return [
            'omset' => $omset,
            'modal' => $modal,
            'laba_kotor' => $labaKotor,
            // Margin dihitung terhadap omset, dan null bila omsetnya nol —
            // membaginya tidak punya arti, dan tampilan menerjemahkan null
            // jadi kalimat, bukan angka karangan.
            'margin' => $omset > 0 ? round(($labaKotor / $omset) * 100, 1) : null,
            'biaya_operasional' => max($pengeluaran - $modalBuku, 0),
        ];
    }

    /**
     * Angka periode berjalan berikut pembandingnya satu periode ke belakang.
     *
     * @return array{
     *     kini: array, lalu: array, label_sebelumnya: string,
     *     laba_kotor: array, modal: array, biaya_operasional: array
     * }
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
            'laba_kotor' => PerbandinganPeriode::banding($kini['laba_kotor'], $lalu['laba_kotor']),
            'modal' => PerbandinganPeriode::banding($kini['modal'], $lalu['modal']),
            'biaya_operasional' => PerbandinganPeriode::banding($kini['biaya_operasional'], $lalu['biaya_operasional']),
        ];
    }
}
