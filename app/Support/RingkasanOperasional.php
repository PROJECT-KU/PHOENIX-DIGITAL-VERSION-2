<?php

namespace App\Support;

use App\Models\DataAkun;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Angka-angka OPERASIONAL untuk dasbor: pekerjaan dan uang yang menunggu
 * tindakan, bukan rekap keuangan yang sudah terjadi.
 *
 * Semuanya sudah ada di database sejak lama, hanya tidak pernah ditampilkan —
 * dan yang tidak ditampilkan tidak dikerjakan. Saat kelas ini dibuat, data
 * nyata memuat 38 langganan yang habis dalam sepekan, 29 langganan yang sudah
 * habis tanpa pernah diberi tahu pemiliknya, dan 14 task lewat tenggat.
 *
 * Dipisah dari komponen dasbor supaya bisa diuji tanpa merender halaman
 * (kueri grafik dasbor memakai MONTH() milik MySQL, pengujian pakai SQLite).
 */
class RingkasanOperasional
{
    /** Pesanan yang uangnya sudah masuk. */
    public const STATUS_DIBAYAR = ['paid', 'processing', 'completed'];

    /** Langganan dianggap "segera habis" dalam rentang hari ini. */
    public const AMBANG_HABIS_HARI = 7;

    /** Stok akun dianggap menipis pada jumlah ini atau kurang. */
    public const AMBANG_STOK = 2;

    /**
     * Langganan yang akan habis & yang sudah habis.
     *
     * Yang "sudah habis tapi belum diberi tahu" dipisah sendiri: itulah yang
     * benar-benar hilang. Pelanggan yang tidak tahu langganannya habis tidak
     * akan memperpanjang, dan tak ada satu pun layar yang menghitungnya.
     */
    public static function langganan(): array
    {
        $segera = OrderItem::expiringSoon(self::AMBANG_HABIS_HARI);
        $habis = OrderItem::expired();

        return [
            'segera' => (clone $segera)->count(),
            'nilai_segera' => (float) (clone $segera)->sum('subtotal'),
            'habis' => (clone $habis)->count(),
            'belum_dikabari' => (clone $habis)->whereNull('habis_notified_at')->count(),
        ];
    }

    /**
     * DAFTAR langganan yang perlu dihubungi — bukan sekadar hitungannya.
     *
     * Angka "38 akan habis" tidak bisa ditindaklanjuti: yang menentukan
     * perpanjangan adalah siapa orangnya dan nomor mana yang dihubungi.
     * Yang sudah habis tapi belum dikabari ditaruh PALING ATAS: itulah yang
     * benar-benar bisa hilang, dan urutan tanggal biasa justru menguburnya di
     * bawah langganan yang masih hidup.
     *
     * @return array<int, array{nama:string, no_hp:?string, produk:string, tanggal:?Carbon, sisa:int, dikabari:bool}>
     */
    public static function langgananSegeraRinci(int $batas = 5): array
    {
        return OrderItem::query()
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(self::AMBANG_HABIS_HARI))
            ->where('delivery_status', '!=', 'cancelled')
            ->where(fn ($q) => $q->where('end_date', '>=', now())->orWhereNull('habis_notified_at'))
            ->with('order.customer')
            ->orderByRaw('CASE WHEN end_date < ? THEN 0 ELSE 1 END', [now()->toDateTimeString()])
            ->orderBy('end_date')
            ->limit($batas)
            ->get()
            ->map(function (OrderItem $item) {
                $akhir = $item->end_date ? Carbon::parse($item->end_date) : null;
                $pelanggan = $item->order?->customer;

                return [
                    'nama' => (string) ($pelanggan->nama ?? 'Tanpa nama'),
                    'no_hp' => $pelanggan->no_hp ?? null,
                    'produk' => (string) ($item->product_name ?: 'Tanpa nama'),
                    'tanggal' => $akhir,
                    // Negatif = sudah lewat. Dibulatkan ke hari penuh supaya
                    // "besok habis" tidak tertulis "0 hari lagi".
                    'sisa' => $akhir ? (int) ceil(now()->startOfDay()->diffInDays($akhir->copy()->startOfDay(), false)) : 0,
                    'dikabari' => $item->habis_notified_at !== null,
                ];
            })
            ->all();
    }

    /**
     * Antrean jasa yang menunggu dikerjakan.
     *
     * Dipisah bot vs manual: bot hanya menangani cek plagiasi Turnitin. Cek AI
     * dan parafrase SELALU dikerjakan admin, dan beban itu tidak terlihat di
     * mana pun sebelum ini.
     */
    public static function antreanJasa(): array
    {
        $menunggu = OrderUpload::where('status', 'menunggu');

        $manual = (clone $menunggu)->where('jenis', '!=', 'plagiasi');

        return [
            'bot' => (clone $menunggu)->where('jenis', 'plagiasi')->count(),
            'manual' => (clone $manual)->count(),
            'manual_terlama' => optional((clone $manual)->orderBy('created_at')->first())->created_at,
            'dikerjakan' => OrderUpload::where('status', 'diproses')->count(),
        ];
    }

    /**
     * Pesanan yang belum dibayar.
     *
     * Di sinilah bug QRIS dulu bersembunyi: pesanan nyangkut 'pending' tanpa
     * ada satu layar pun yang menghitungnya, sampai pembelinya menghubungi.
     */
    public static function pesananMenunggu(): array
    {
        $pending = Order::where('status', 'pending');

        return [
            'jumlah' => (clone $pending)->count(),
            'nilai' => (float) (clone $pending)->sum('total'),
            // Yang tenggat bayarnya tinggal di bawah sejam — paling mungkin
            // masih bisa diselamatkan dengan satu pesan WhatsApp.
            'segera_kedaluwarsa' => (clone $pending)
                ->whereNotNull('expired_at')
                ->whereBetween('expired_at', [now(), now()->addHour()])
                ->count(),
        ];
    }

    /** Task yang lewat tenggat dan belum selesai. */
    public static function taskTerlambat(): array
    {
        $terlambat = Task::whereDate('deadline_selesai', '<', today())
            ->where('progress', '!=', 'selesai');

        return [
            'jumlah' => (clone $terlambat)->count(),
            'terlama' => optional((clone $terlambat)->orderBy('deadline_selesai')->first())->deadline_selesai,
        ];
    }

    /**
     * Stok akun yang menipis, per produk.
     *
     * "Bebas" = akun berstatus active yang TIDAK sedang menempel pada langganan
     * berjalan. Akun yang langganannya sudah habis dihitung bebas lagi, karena
     * memang begitu ia dipakai ulang.
     */
    public static function stokAkun(int $batas = 5): array
    {
        $terpakai = OrderItem::whereNotNull('data_akun_id')
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->where('delivery_status', '!=', 'cancelled')
            ->pluck('data_akun_id')
            ->unique()
            ->all();

        return DataAkun::query()
            ->where('data_akuns.status', 'active')
            ->when($terpakai, fn ($q) => $q->whereNotIn('data_akuns.id', $terpakai))
            ->join('products', 'products.id', '=', 'data_akuns.product_id')
            ->groupBy('products.id', 'products.nama_akun')
            ->havingRaw('COUNT(*) <= ?', [self::AMBANG_STOK])
            ->orderByRaw('COUNT(*) asc')
            ->limit($batas)
            ->get([DB::raw('products.nama_akun as produk'), DB::raw('COUNT(*) as sisa')])
            ->map(fn ($r) => ['produk' => (string) $r->produk, 'sisa' => (int) $r->sisa])
            ->all();
    }

    /**
     * Produk & jasa paling laku pada satu periode.
     *
     * Dihitung dari pesanan yang UANGNYA MASUK — sama dengan aturan pendapatan,
     * supaya "terlaris" tidak pernah berisi pesanan yang batal.
     *
     * @return array<int, array{produk:string, jumlah:int, nilai:float}>
     */
    public static function produkTerlaris(Carbon $mulai, Carbon $akhirEksklusif, int $batas = 5): array
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', self::STATUS_DIBAYAR)
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) >= ?', [$mulai->toDateTimeString()])
            ->whereRaw('COALESCE(orders.paid_at, orders.created_at) < ?', [$akhirEksklusif->toDateTimeString()])
            ->groupBy('order_items.product_name')
            ->orderByRaw('SUM(order_items.subtotal) desc')
            ->limit($batas)
            ->get([
                DB::raw('order_items.product_name as produk'),
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('COALESCE(SUM(order_items.subtotal), 0) as nilai'),
            ])
            ->map(fn ($r) => [
                'produk' => (string) ($r->produk ?: 'Tanpa nama'),
                'jumlah' => (int) $r->jumlah,
                'nilai' => (float) $r->nilai,
            ])
            ->all();
    }

    /**
     * Pemasukan HARIAN sepanjang periode berjalan.
     *
     * Grafik dasbor yang lama hanya per bulan sepanjang tahun — berguna untuk
     * melihat musim, tidak untuk menjawab "minggu ini ramai atau sepi".
     *
     * @return array{tanggal: array<int, string>, nilai: array<int, float>}
     */
    public static function pemasukanHarian(Carbon $mulai, Carbon $akhirEksklusif): array
    {
        $perHari = Order::query()
            ->whereIn('status', self::STATUS_DIBAYAR)
            ->whereRaw('COALESCE(paid_at, created_at) >= ?', [$mulai->toDateTimeString()])
            ->whereRaw('COALESCE(paid_at, created_at) < ?', [$akhirEksklusif->toDateTimeString()])
            ->get(['total', 'paid_at', 'created_at'])
            ->groupBy(fn ($o) => Carbon::parse($o->paid_at ?? $o->created_at)->toDateString())
            ->map(fn ($baris) => (float) $baris->sum('total'));

        $tanggal = [];
        $nilai = [];

        // Hari yang tidak ada pesanannya tetap muncul sebagai nol — tanpa itu,
        // garis grafiknya melompati hari sepi dan tren terbaca lebih ramai
        // daripada kenyataannya.
        for ($hari = $mulai->copy(); $hari < $akhirEksklusif && $hari <= now(); $hari->addDay()) {
            $tanggal[] = $hari->locale('id')->translatedFormat('d M');
            $nilai[] = $perHari[$hari->toDateString()] ?? 0.0;
        }

        return ['tanggal' => $tanggal, 'nilai' => $nilai];
    }
}
