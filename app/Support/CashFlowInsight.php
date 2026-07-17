<?php

namespace App\Support;

use App\Models\CashFlow;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Ringkasan & pembacaan kondisi cash flow untuk panel Insight.
 *
 * PENTING — kelas ini TIDAK ikut campur ke filter/periode/logic tabel cash flow.
 * Dia menghitung sendiri dari nol memakai rentang tanggal yang sama, lalu
 * menambah tiga sudut pandang yang tidak ada di tabel:
 *
 *   1. periode sebelumnya  — bulan/siklus/tahun tepat sebelum yang dipilih
 *   2. kuartal             — setahun dibagi 4 (Q1 Jan-Mar ... Q4 Okt-Des)
 *   3. YTD                 — 1 Januari s/d akhir periode terpilih
 *
 * Rentang siklus gaji diambil dari PeriodeGaji — SATU sumber dengan fitur Gaji
 * dan dengan CashFlowList, jadi angkanya tidak bisa melenceng sendiri kalau
 * tanggal gajian diubah.
 *
 * Seluruh keluaran kelas ini adalah ANGKA AGREGAT (SUM/GROUP BY). Tidak ada
 * nama pelanggan, nomor HP, e-mail, isi pesan/ulasan, atau rahasia akun.
 * Kalau menambah metrik baru, tetap HANYA agregat — jangan pernah select kolom
 * identitas atau teks bebas.
 */
class CashFlowInsight
{
    // ===================== AMBANG PENILAIAN (rule of thumb) =====================
    // Semua ambang penilaian dikumpulkan di sini supaya gampang disetel kalau
    // ternyata tidak cocok dgn kenyataan bisnis setelah data nyata terkumpul.
    // Semuanya PERSENTASE (bukan angka absolut), jadi tetap masuk akal baik saat
    // data masih sedikit maupun sudah ribuan.

    /** Batas sebuah pengeluaran/pemasukan disebut "menonjol": >= 40% total tipenya. */
    private const AMBANG_MENONJOL = 40.0;

    /** Perubahan di bawah ini dianggap datar, bukan naik/turun. */
    private const AMBANG_DATAR_PERSEN = 5.0;

    /** Pesanan batal >= ini (% dari total pesanan) dianggap tinggi & diperingatkan. */
    private const AMBANG_BATAL_TINGGI = 25.0;

    /** Retensi di bawah ini (% pembeli yang kembali beli) dianggap rendah. */
    private const AMBANG_RETENSI_RENDAH = 20.0;

    public function __construct(
        private ?int $bulan,
        private ?int $tahun,
        private bool $siklus,
    ) {}

    /**
     * Tahun yang jadi acuan kuartal & YTD. Kalau filter tahun kosong
     * (mode "Semua Periode"), pakai tahun berjalan — bukan diam-diam kosong.
     */
    private function tahunAcuan(): int
    {
        return (int) ($this->tahun ?: now()->year);
    }

    /**
     * Rentang periode yang sedang dipilih admin, sebagai [mulai, akhirEksklusif].
     *
     * Null berarti "Semua Periode" — tidak ada rentang, jadi perbandingan
     * periode-sebelumnya tidak masuk akal dan memang dilewati.
     */
    private function rangeTerpilih(): ?array
    {
        if ($this->siklus && $this->bulan) {
            return [
                PeriodeGaji::mulai((int) $this->bulan, $this->tahunAcuan()),
                PeriodeGaji::akhir((int) $this->bulan, $this->tahunAcuan())->addDay()->startOfDay(),
            ];
        }

        if ($this->bulan && $this->tahun) {
            $mulai = Carbon::create((int) $this->tahun, (int) $this->bulan, 1)->startOfDay();

            return [$mulai, $mulai->copy()->addMonth()];
        }

        if ($this->tahun) {
            $mulai = Carbon::create((int) $this->tahun, 1, 1)->startOfDay();

            return [$mulai, $mulai->copy()->addYear()];
        }

        return null;
    }

    /**
     * Rentang periode TEPAT SEBELUM yang dipilih, dengan panjang yang setara.
     *
     * Siklus gaji mundur satu siklus (bukan mundur 30 hari): siklus Juli =
     * 21 Jun-20 Jul, sebelumnya = 21 Mei-20 Jun. Mundur pakai hari kalender
     * akan meleset tiap kali jumlah hari bulannya beda.
     */
    private function rangeSebelumnya(): ?array
    {
        if ($this->siklus && $this->bulan) {
            $acuan = Carbon::create($this->tahunAcuan(), (int) $this->bulan, 1)->subMonth();

            return [
                PeriodeGaji::mulai($acuan->month, $acuan->year),
                PeriodeGaji::akhir($acuan->month, $acuan->year)->addDay()->startOfDay(),
            ];
        }

        if ($this->bulan && $this->tahun) {
            $mulai = Carbon::create((int) $this->tahun, (int) $this->bulan, 1)->subMonth()->startOfDay();

            return [$mulai, $mulai->copy()->addMonth()];
        }

        if ($this->tahun) {
            $mulai = Carbon::create((int) $this->tahun - 1, 1, 1)->startOfDay();

            return [$mulai, $mulai->copy()->addYear()];
        }

        return null;
    }

    /**
     * Label manusiawi untuk sebuah rentang [mulai, akhirEksklusif].
     * Akhir eksklusif dimundurkan sehari dulu supaya yang tampil tanggal
     * terakhir yang benar-benar ikut terhitung.
     */
    private function labelRange(array $range): string
    {
        [$mulai, $akhirEks] = $range;
        $akhir = $akhirEks->copy()->subDay();

        return $mulai->translatedFormat('d M Y').' – '.$akhir->translatedFormat('d M Y');
    }

    /**
     * Jumlahkan pemasukan & pengeluaran pada satu rentang.
     * Hanya SUM — tidak menyentuh kolom yang berisi data pribadi.
     */
    private function ringkas(array $range): array
    {
        [$mulai, $akhirEks] = $range;

        $rows = CashFlow::query()
            ->where('transaction_date', '>=', $mulai)
            ->where('transaction_date', '<', $akhirEks)
            ->selectRaw('type, COALESCE(SUM(amount), 0) as total, COUNT(*) as jumlah')
            ->groupBy('type')
            ->get();

        $income = (float) ($rows->firstWhere('type', 'income')->total ?? 0);
        $expense = (float) ($rows->firstWhere('type', 'expense')->total ?? 0);

        return [
            'label' => $this->labelRange($range),
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'transaksi' => (int) $rows->sum('jumlah'),
        ];
    }

    /**
     * Rincian per kategori untuk satu tipe (income/expense) pada satu rentang.
     * Kategori adalah label buatan sistem ("Gaji Karyawan", "Pinjaman"), bukan
     * teks bebas yang diketik admin — jadi aman ikut ke ringkasan.
     */
    private function perKategori(array $range, string $type): array
    {
        [$mulai, $akhirEks] = $range;

        $rows = CashFlow::query()
            ->where('transaction_date', '>=', $mulai)
            ->where('transaction_date', '<', $akhirEks)
            ->where('type', $type)
            ->selectRaw('category, COALESCE(SUM(amount), 0) as total, COUNT(*) as jumlah')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $total = (float) $rows->sum('total');

        return $rows->map(fn ($r) => [
            'nama' => $r->category ?: 'Tanpa Kategori',
            'total' => (float) $r->total,
            'jumlah' => (int) $r->jumlah,
            'pct' => $total > 0 ? round(((float) $r->total / $total) * 100, 1) : 0.0,
        ])->all();
    }

    /**
     * Empat kuartal pada tahun acuan. Kuartal yang sedang dilihat admin
     * ditandai 'aktif' supaya bisa disorot di tampilan.
     */
    private function kuartal(): array
    {
        $tahun = $this->tahunAcuan();
        $kuartalAktif = $this->bulan ? (int) ceil((int) $this->bulan / 3) : null;

        $hasil = [];
        foreach ([1, 2, 3, 4] as $q) {
            $mulai = Carbon::create($tahun, ($q - 1) * 3 + 1, 1)->startOfDay();
            $range = [$mulai, $mulai->copy()->addMonths(3)];

            $ringkas = $this->ringkas($range);
            $ringkas['label'] = 'Q'.$q;
            $ringkas['rentang'] = $mulai->translatedFormat('M').' – '.$mulai->copy()->addMonths(2)->translatedFormat('M').' '.$tahun;
            $ringkas['aktif'] = $kuartalAktif === $q;
            $ringkas['kosong'] = $ringkas['transaksi'] === 0;

            $hasil[] = $ringkas;
        }

        return $hasil;
    }

    /**
     * Year-to-date: 1 Januari tahun acuan s/d akhir periode terpilih.
     *
     * Kalau filternya "Semua Periode", batas atasnya hari ini — bukan
     * 31 Desember, supaya tidak menjanjikan angka untuk bulan yang belum terjadi.
     */
    private function ytd(): array
    {
        $tahun = $this->tahunAcuan();
        $mulai = Carbon::create($tahun, 1, 1)->startOfDay();

        $terpilih = $this->rangeTerpilih();
        $akhirEks = $terpilih ? $terpilih[1] : now()->copy()->addDay()->startOfDay();

        // Periode terpilih bisa saja mulai sebelum 1 Januari (siklus Januari
        // mulai 21 Desember). Jangan sampai rentangnya jadi terbalik.
        if ($akhirEks->lte($mulai)) {
            $akhirEks = $mulai->copy()->addDay();
        }

        $ringkas = $this->ringkas([$mulai, $akhirEks]);
        $ringkas['label'] = $mulai->translatedFormat('M').' – '.$akhirEks->copy()->subDay()->translatedFormat('M Y');

        return $ringkas;
    }

    /**
     * Satu transaksi terbesar untuk sebuah tipe — dipakai menjelaskan
     * "kenapa" angkanya besar, bukan sekadar melaporkan bahwa besar.
     *
     * Sengaja hanya kategori + tanggal + nominal. Kolom `description` TIDAK
     * diambil: itu teks bebas ketikan admin dan bisa memuat nama orang.
     */
    private function terbesar(array $range, string $type, float $totalTipe): ?array
    {
        [$mulai, $akhirEks] = $range;

        $row = CashFlow::query()
            ->where('transaction_date', '>=', $mulai)
            ->where('transaction_date', '<', $akhirEks)
            ->where('type', $type)
            ->orderByDesc('amount')
            ->first(['category', 'amount', 'transaction_date']);

        if (! $row || (float) $row->amount <= 0) {
            return null;
        }

        $nominal = (float) $row->amount;
        $pct = $totalTipe > 0 ? round(($nominal / $totalTipe) * 100, 1) : 0.0;

        return [
            'nama' => $row->category ?: 'Tanpa Kategori',
            'total' => $nominal,
            'tanggal' => $row->transaction_date->translatedFormat('d M Y'),
            'pct' => $pct,
            'menonjol' => $pct >= self::AMBANG_MENONJOL,
        ];
    }

    /** Perubahan persen dari $lama ke $baru. Null kalau pembandingnya nol. */
    private function delta(float $baru, float $lama): ?float
    {
        if ($lama <= 0) {
            return null;
        }

        return round((($baru - $lama) / $lama) * 100, 1);
    }

    /**
     * Analisis produk untuk saran promo — HANYA dipanggil saat tombol AI
     * ditekan (bukan tiap render), jadi panel tetap ringan.
     *
     * Memberi AI bahan menjawab: perlu promo atau tidak, produk mana (yang
     * laris untuk dorong volume, atau yang sepi untuk digerakkan), dari
     * pesanan yang benar-benar SELESAI (completed) sepanjang tahun acuan.
     *
     * PRIVASI: hanya agregat produk (nama produk, jumlah terjual, omzet).
     * Kolom sensitif order_items (username/password/link akun, pembeli)
     * TIDAK DISENTUH — query hanya menyeleksi product_id/name/quantity/subtotal.
     */
    public function produkUntukPromo(): array
    {
        $tahun = $this->tahunAcuan();

        $terjual = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.status', 'completed')
            ->whereRaw('YEAR(COALESCE(o.paid_at, o.created_at)) = ?', [$tahun])
            ->whereNotNull('oi.product_id')
            ->selectRaw('oi.product_id,
                MAX(oi.product_name) as nama,
                COUNT(DISTINCT oi.order_id) as freq,
                COALESCE(SUM(oi.quantity), 0) as qty,
                COALESCE(SUM(oi.subtotal), 0) as omzet')
            ->groupBy('oi.product_id')
            ->orderByDesc('freq')
            ->orderByDesc('omzet')
            ->get();

        $bentuk = fn ($r) => [
            'nama' => $r->nama ?: 'Produk',
            'freq' => (int) $r->freq,
            'qty' => (int) $r->qty,
            'omzet' => (float) $r->omzet,
        ];

        // Laris = 5 teratas berdasarkan frekuensi pesanan selesai.
        $laris = $terjual->take(5)->map($bentuk)->values()->all();

        // Sepi = yang TERJUAL tapi paling jarang (di luar daftar laris).
        $idLaris = $terjual->take(5)->pluck('product_id');
        $sepi = $terjual->reject(fn ($r) => $idLaris->contains($r->product_id))
            ->sortBy('freq')
            ->take(5)
            ->map($bentuk)
            ->values()
            ->all();

        $totalProduk = (int) DB::table('products')->count();
        $terjualCount = $terjual->count();

        return [
            'tahun' => $tahun,
            'total_produk' => $totalProduk,
            'terjual_count' => $terjualCount,
            'belum_terjual' => max(0, $totalProduk - $terjualCount),
            'laris' => $laris,
            'sepi' => $sepi,
        ];
    }

    /**
     * Rangkuman penyelesaian task pada periode gaji terpilih — HANYA dipanggil
     * saat tombol AI ditekan.
     *
     * Task selalu dikelompokkan per periode gaji (kolom periode_bulan/tahun),
     * jadi dipilih memakai bulan/tahun yang sama dengan yang sedang dilihat.
     * Keterlambatan memakai Task::hariTerlambat() yang sudah teruji — bukan
     * hitung ulang di sini, supaya konsisten dgn fitur Gaji & bonus.
     *
     * Berguna sebagai pengingat produktivitas: task yang telat memangkas bonus
     * dan menandakan pekerjaan molor — perbaikannya bikin bulan depan lebih baik.
     *
     * @return array<string,mixed>|null  null bila tidak ada task di periode itu
     */
    public function ringkasanTask(): ?array
    {
        $q = Task::query();

        if ($this->bulan) {
            $q->where('periode_bulan', (int) $this->bulan)
                ->where('periode_tahun', $this->tahunAcuan());
        } else {
            $q->where('periode_tahun', $this->tahunAcuan());
        }

        $tasks = $q->get();

        if ($tasks->isEmpty()) {
            return null;
        }

        $selesai = $tasks->where('progress', 'selesai');
        $terlambat = $selesai->filter(fn (Task $t) => $t->hariTerlambat() > 0);

        return [
            'periode' => $this->bulan
                ? ($this->daftarBulan()[(int) $this->bulan] ?? '').' '.$this->tahunAcuan()
                : 'tahun '.$this->tahunAcuan(),
            'total' => $tasks->count(),
            'selesai' => $selesai->count(),
            'dikerjakan' => $tasks->where('progress', 'dikerjakan')->count(),
            'belum' => $tasks->where('progress', 'belum')->count(),
            'terlambat' => $terlambat->count(),
            'hari_telat_total' => (int) $terlambat->sum(fn (Task $t) => $t->hariTerlambat()),
        ];
    }

    private function daftarBulan(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    /**
     * KPI bisnis lintas fitur (public + admin) untuk saran surplus menyeluruh —
     * HANYA dipanggil saat tombol AI ditekan.
     *
     * ================== PRIVASI (WAJIB DIJAGA) ==================
     * SEMUA yang di sini adalah COUNT / SUM / AVG. TIDAK ADA satu pun kolom
     * berisi identitas atau rahasia yang diseleksi:
     *   - TIDAK ambil nama/email/no_hp pelanggan (customers, abandoned_carts,
     *     customer_messages, testimonis)
     *   - TIDAK ambil username/password/link akun (data_akuns, order_items)
     *   - TIDAK ambil isi pesan/ulasan/testimoni (message, ulasan, pesan)
     *   - TIDAK ambil nomor pesanan, token, bukti bayar
     * Kalau menambah metrik baru di sini, tetap HANYA agregat. Jangan pernah
     * select kolom teks bebas atau identitas.
     * ============================================================
     *
     * Skop: angka setahun (tahun acuan) untuk yang butuh sinyal cukup, dan
     * potret "saat ini" untuk stok/member/promo aktif.
     */
    public function kpiBisnis(): array
    {
        $tahun = $this->tahunAcuan();
        $inTahun = fn ($kolom) => fn ($q) => $q->whereRaw("YEAR($kolom) = ?", [$tahun]);

        // ---- Funnel pesanan (setahun) ----
        $statusOrder = DB::table('orders')->whereNull('deleted_at')
            ->whereRaw('YEAR(COALESCE(paid_at, created_at)) = ?', [$tahun])
            ->selectRaw('status, COUNT(*) n')
            ->groupBy('status')
            ->pluck('n', 'status');

        $totalOrder = (int) $statusOrder->sum();
        $selesai = (int) ($statusOrder['completed'] ?? 0);
        $batal = (int) ($statusOrder['cancelled'] ?? 0);

        $aov = (float) DB::table('orders')->whereNull('deleted_at')
            ->where('status', 'completed')
            ->whereRaw('YEAR(COALESCE(paid_at, created_at)) = ?', [$tahun])
            ->avg('total');

        // ---- Keranjang ditinggalkan (setahun) ----
        $cartTotal = (int) DB::table('abandoned_carts')->tap($inTahun('created_at'))->count();
        $cartRecovered = (int) DB::table('abandoned_carts')->tap($inTahun('created_at'))->whereNotNull('recovered_at')->count();

        // ---- Promo (setahun + potret aktif) ----
        $orderPakaiPromo = (int) DB::table('order_promo')
            ->join('orders', 'orders.id', '=', 'order_promo.order_id')
            ->whereNull('orders.deleted_at')
            ->whereRaw('YEAR(COALESCE(orders.paid_at, orders.created_at)) = ?', [$tahun])
            ->distinct()->count('order_promo.order_id');
        $totalDiskon = (float) DB::table('order_promo')
            ->join('orders', 'orders.id', '=', 'order_promo.order_id')
            ->whereNull('orders.deleted_at')
            ->whereRaw('YEAR(COALESCE(orders.paid_at, orders.created_at)) = ?', [$tahun])
            ->sum('order_promo.jumlah_diskon');
        $promoAktif = (int) DB::table('promos')->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('mulai_promo', '<=', now())
            ->where('selesai_promo', '>=', now())
            ->count();

        // ---- Pelanggan & retensi ----
        $totalPelanggan = (int) DB::table('customers')->whereNull('deleted_at')->count();
        $pelangganBaru = (int) DB::table('customers')->whereNull('deleted_at')->tap($inTahun('created_at'))->count();
        $memberAktif = (int) DB::table('customers')->whereNull('deleted_at')->where('status_member', 'active')->count();
        // Pembeli = pelanggan yang PERNAH belanja selesai; berulang = yang >1x.
        // Retensi dihitung sebagai RASIO terhadap pembeli, bukan angka absolut —
        // kalau pakai absolut, "berulang <= 1" cuma masuk akal di data kecil dan
        // diam saja saat data nyata besar (mis. 20 berulang dari 500 pembeli).
        $bakuPembeli = DB::table('orders')->whereNull('deleted_at')
            ->where('status', 'completed')->whereNotNull('customer_id');

        $pembeli = (clone $bakuPembeli)->distinct()->count('customer_id');

        $pembeliBerulang = (clone $bakuPembeli)
            ->selectRaw('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()->count();

        // ---- Bukti sosial ----
        $testiAktif = (int) DB::table('testimonis')->where('status', 'active')->count();
        $ulasanTampil = (int) DB::table('product_reviews')->where('status', 'approved')->count();
        $ratingRata = (float) DB::table('product_reviews')->where('status', 'approved')->avg('rating');

        // ---- Stok akun & beban dukungan ----
        $akunAktif = (int) DB::table('data_akuns')->where('status', 'active')->count();
        $pesanBelumTangani = (int) DB::table('customer_messages')->whereIn('status', ['open', 'in_progress'])->count();

        return [
            'tahun' => $tahun,
            'order' => [
                'total' => $totalOrder,
                'selesai' => $selesai,
                'batal' => $batal,
                'batal_persen' => $totalOrder > 0 ? round($batal / $totalOrder * 100, 1) : 0.0,
                'selesai_persen' => $totalOrder > 0 ? round($selesai / $totalOrder * 100, 1) : 0.0,
                'menunggu' => (int) (($statusOrder['pending'] ?? 0) + ($statusOrder['paid'] ?? 0) + ($statusOrder['processing'] ?? 0)),
                'aov' => $aov,
            ],
            'cart' => ['total' => $cartTotal, 'recovered' => $cartRecovered, 'hilang' => max(0, $cartTotal - $cartRecovered)],
            'promo' => ['order_pakai' => $orderPakaiPromo, 'total_diskon' => $totalDiskon, 'aktif' => $promoAktif],
            'pelanggan' => [
                'total' => $totalPelanggan,
                'baru' => $pelangganBaru,
                'pembeli' => $pembeli,
                'berulang' => $pembeliBerulang,
                // Rasio retensi: berapa % pembeli yang kembali belanja lagi.
                // Dipakai sebagai patokan (bukan angka absolut) supaya tetap
                // masuk akal baik saat data masih sedikit maupun sudah ribuan.
                'retensi_persen' => $pembeli > 0 ? round($pembeliBerulang / $pembeli * 100, 1) : 0.0,
                'member' => $memberAktif,
            ],
            'sosial' => ['testimoni' => $testiAktif, 'ulasan' => $ulasanTampil, 'rating' => round($ratingRata, 1)],
            'operasional' => ['akun_aktif' => $akunAktif, 'pesan_belum' => $pesanBelumTangani],
        ];
    }

    /**
     * ANALISA MENYELURUH tanpa AI — dihitung PHP, instan, gratis, nol data keluar.
     *
     * Mencakup seluruh sistem: keuangan + proyeksi ke depan (bulan depan,
     * kuartal depan, prospek tahun), prospek produk (yang perlu ditambah/dievaluasi),
     * promo, penyelesaian task, kesehatan bisnis (public + admin), dan rencana aksi.
     *
     * Semua berbasis ATURAN dari angka — selalu akurat, tidak mengarang. Proyeksi
     * ditandai jelas sebagai "perkiraan kasar" karena masa depan tak pasti.
     *
     * @return array<int,array{judul:string,ikon:string,nada:string,poin:array<int,string>}>
     */
    public function analisaLengkap(): array
    {
        $d = $this->data();
        $kpi = $this->kpiBisnis();
        $produk = $this->produkUntukPromo();
        $task = $this->ringkasanTask();

        $p = $d['periode'];
        $ytd = $d['ytd'];

        $seksi = [];

        // ===== 1. KEUANGAN & PROYEKSI KE DEPAN =====
        $poinKeu = [];
        $poinKeu[] = 'Periode '.$p['label'].': '.($p['net'] < 0 ? 'minus ' : 'surplus ').self::rp($p['net'])
            .' (uang masuk '.self::rp($p['income']).', keluar '.self::rp($p['expense']).').';

        if ($s = $d['sebelumnya']) {
            $poinKeu[] = 'Dibanding periode lalu: pemasukan '.$this->arah($s['delta_income'])
                .', pengeluaran '.$this->arah($s['delta_expense']).'.';
        }

        // Proyeksi pakai NET OPERASIONAL (di luar Pinjaman) — Pinjaman itu uang
        // berputar keluar-masuk, bukan untung/rugi nyata. Tanpa ini, satu
        // pinjaman besar bikin proyeksi meleset jauh & menakut-nakuti.
        // Dicocokkan secara longgar ("mengandung kata pinjaman") — kategori dibuat
        // sistem (LoanForm/PengembalianForm/GajiKaryawansForm) dgn nilai "Pinjaman",
        // tapi cocok longgar ini bikin tetap benar kalau kelak ada varian nama
        // seperti "Pinjaman Karyawan". Kalau tidak ketemu, hasilnya 0 = aman.
        $totalKategori = fn (array $rows) => (float) collect($rows)
            ->filter(fn ($k) => stripos((string) $k['nama'], 'pinjaman') !== false)
            ->sum('total');

        $pinjExp = $totalKategori($d['kategori']['expense'] ?? []);
        $pinjInc = $totalKategori($d['kategori']['income'] ?? []);
        $netOp = $p['net'] - $pinjInc + $pinjExp; // buang efek pinjaman
        $adaPinjaman = $pinjExp > 0 || $pinjInc > 0;
        $catatanOp = $adaPinjaman ? ' (di luar pinjaman yang cuma uang berputar)' : '';

        $projBulan = $netOp;
        $projKuartal = $netOp * 3;
        $projTahun = $netOp * 12; // run-rate: kalau pola operasional bulan ini berlanjut setahun

        $poinKeu[] = 'Perkiraan kasar BULAN DEPAN'.$catatanOp.', kalau pola bulan ini berlanjut: '
            .($projBulan < 0 ? 'minus ' : 'surplus ').self::rp($projBulan).'.';
        $poinKeu[] = 'Perkiraan kasar KUARTAL DEPAN: '.($projKuartal < 0 ? 'minus ' : 'surplus ').self::rp($projKuartal).'.';
        $poinKeu[] = 'Prospek setahun penuh (pola operasional bulan ini × 12): sekitar '
            .($projTahun < 0 ? 'minus ' : 'surplus ').self::rp($projTahun).'. '
            .'Sejak awal tahun sebenarnya sudah tercatat '.($ytd['net'] < 0 ? 'minus ' : 'surplus ').self::rp($ytd['net']).'.';
        if ($adaPinjaman) {
            $poinKeu[] = 'Catatan: periode ini ada Pinjaman keluar '.self::rp($pinjExp).' / masuk '.self::rp($pinjInc)
                .' — itu uang berputar, bukan biaya/untung, jadi tidak dihitung di proyeksi.';
        }

        $seksi[] = [
            'judul' => 'Keuangan & Proyeksi ke Depan',
            'ikon' => 'bi-cash-coin',
            'nada' => $p['net'] < 0 ? 'peringatan' : 'baik',
            'poin' => $poinKeu,
        ];

        // ===== 2. PROSPEK PRODUK =====
        $poinPro = [];
        if (! empty($produk['laris'])) {
            $top = $produk['laris'][0];
            $poinPro[] = 'Produk terlaris: '.$top['nama'].' ('.$top['freq'].'x terjual, omzet '.self::rp($top['omzet']).'). '
                .'Jaga stoknya jangan sampai habis, dan tawarkan lebih gencar.';
            $poinPro[] = 'Pertimbangkan TAMBAH varian/produk sejenis '.$top['nama'].' ke depan — permintaannya paling tinggi.';
        }
        if (! empty($produk['sepi'])) {
            $s0 = $produk['sepi'][0];
            $poinPro[] = 'Produk paling sepi: '.$s0['nama'].' (cuma '.$s0['freq'].'x) — evaluasi harganya atau promosikan supaya bergerak.';
        }
        if ($produk['belum_terjual'] > 0) {
            $poinPro[] = $produk['belum_terjual'].' dari '.$produk['total_produk'].' produk BELUM PERNAH terjual tahun ini — '
                .'pertimbangkan evaluasi atau berhenti menyetok yang tidak diminati supaya modal fokus ke yang laku.';
        }
        if (empty($poinPro)) {
            $poinPro[] = 'Belum ada penjualan produk yang selesai tahun ini untuk dianalisa.';
        }

        $seksi[] = [
            'judul' => 'Prospek Produk',
            'ikon' => 'bi-box-seam',
            'nada' => 'info',
            'poin' => $poinPro,
        ];

        // ===== 3. PROMO YANG BAIK KE DEPAN =====
        $poinProm = [];
        $poinProm[] = $kpi['promo']['order_pakai'].' pesanan memakai promo tahun ini, total diskon diberikan '
            .self::rp($kpi['promo']['total_diskon']).'. Promo aktif sekarang: '.$kpi['promo']['aktif'].'.';
        $poinProm[] = 'Untuk naikkan penjualan tanpa gerus untung: promo TIPIS 10–15% untuk produk laris (dorong volume), '
            .'promo lebih besar 20–30% untuk produk sepi (menggerakkan yang mandek).';
        $poinProm[] = 'Penting: persen promo jangan melebihi untung per produk — cek modal vs harga jual dulu sebelum memasang.';

        $seksi[] = [
            'judul' => 'Promo yang Baik ke Depan',
            'ikon' => 'bi-tag',
            'nada' => 'info',
            'poin' => $poinProm,
        ];

        // ===== 4. PENYELESAIAN TASK =====
        if ($task) {
            $poinTask = [];
            $poinTask[] = $task['selesai'].' dari '.$task['total'].' task selesai; '
                .$task['dikerjakan'].' sedang dikerjakan, '.$task['belum'].' belum mulai.';
            if ($task['terlambat'] > 0) {
                $poinTask[] = $task['terlambat'].' task selesai TERLAMBAT (menumpuk '.$task['hari_telat_total'].' hari lewat deadline). '
                    .'Kurangi keterlambatan bulan depan — task telat memangkas bonus dan menandakan pekerjaan molor.';
            } else {
                $poinTask[] = 'Semua task selesai tepat waktu — pertahankan.';
            }
            if ($task['belum'] > 0) {
                $poinTask[] = 'Ada '.$task['belum'].' task belum mulai — dorong diselesaikan supaya tidak menumpuk ke bulan depan.';
            }
            $seksi[] = [
                'judul' => 'Penyelesaian Task',
                'ikon' => 'bi-list-check',
                'nada' => ($task['terlambat'] > 0 || $task['belum'] > 0) ? 'peringatan' : 'baik',
                'poin' => $poinTask,
            ];
        }

        // ===== 5. KESEHATAN BISNIS (PUBLIC + ADMIN) =====
        $o = $kpi['order'];
        $poinBis = [];
        $poinBis[] = 'Pesanan tahun ini: '.$o['total'].' total — '.$o['selesai'].' selesai ('.$o['selesai_persen'].'%), '
            .$o['batal'].' batal ('.$o['batal_persen'].'%). Rata-rata nilai pesanan selesai '.self::rp($o['aov']).'.';
        if ($o['batal_persen'] >= self::AMBANG_BATAL_TINGGI) {
            $poinBis[] = 'Tingkat pesanan batal '.$o['batal_persen'].'% TERGOLONG TINGGI — banyak penjualan hilang. '
                .'Cari tahu kenapa (harga, stok, proses bayar ribet?) dan perbaiki.';
        }
        if ($kpi['cart']['hilang'] > 0) {
            $poinBis[] = $kpi['cart']['hilang'].' keranjang ditinggalkan tanpa jadi beli — ingatkan lewat email/WA supaya diselamatkan.';
        }
        $poinBis[] = 'Pelanggan: '.$kpi['pelanggan']['total'].' total, '.$kpi['pelanggan']['baru'].' baru tahun ini. '
            .'Dari '.$kpi['pelanggan']['pembeli'].' yang pernah belanja, '.$kpi['pelanggan']['berulang']
            .' kembali beli lagi (retensi '.$kpi['pelanggan']['retensi_persen'].'%).'
            .($kpi['pelanggan']['pembeli'] > 0 && $kpi['pelanggan']['retensi_persen'] < self::AMBANG_RETENSI_RENDAH
                ? ' Retensi tergolong rendah — dorong pelanggan lama balik lagi (promo member, follow-up sebelum masa langganan habis).'
                : '');
        $poinBis[] = 'Bukti sosial: '.$kpi['sosial']['testimoni'].' testimoni & '.$kpi['sosial']['ulasan']
            .' ulasan (bintang '.$kpi['sosial']['rating'].'). Testimoni menaikkan kepercayaan calon pembeli — kumpulkan lebih banyak.';
        $poinBis[] = 'Operasional: '.$kpi['operasional']['akun_aktif'].' akun siap dijual, '
            .$kpi['operasional']['pesan_belum'].' pesan pelanggan belum ditangani'
            .($kpi['operasional']['pesan_belum'] > 0 ? ' — segera balas supaya tidak kecewa.' : '.');

        $seksi[] = [
            'judul' => 'Kesehatan Bisnis (Public + Admin)',
            'ikon' => 'bi-heart-pulse',
            'nada' => $o['batal_persen'] >= 25 ? 'peringatan' : 'info',
            'poin' => $poinBis,
        ];

        // ===== 6. RENCANA AKSI KE DEPAN (pakai net operasional, konsisten) =====
        $poinRen = [];
        $poinRen[] = 'BULAN DEPAN: '.($netOp < 0
            ? 'operasional masih minus '.self::rp(abs($netOp)).$catatanOp.' — dorong produk laris & tekan pengeluaran rutin.'
            : 'operasional sudah surplus '.self::rp($netOp).$catatanOp.' — jaga pemasukan, sisihkan sebagian buat cadangan/stok.');
        $poinRen[] = 'KUARTAL DEPAN: pantau tren antar kuartal, tingkatkan produk laris, kurangi pesanan batal ('.$o['batal_persen'].'%) & keranjang yang ditinggalkan.';
        $poinRen[] = 'TAHUN DEPAN: '.($projTahun < 0
            ? 'pola operasional sekarang mengarah minus — perlu naikkan penjualan & retensi pelanggan supaya berbalik surplus.'
            : 'pola operasional mengarah surplus '.self::rp($projTahun).'/tahun — pertahankan & perluas produk paling laku, plus perbaiki retensi supaya makin kuat.');

        $seksi[] = [
            'judul' => 'Rencana Aksi ke Depan',
            'ikon' => 'bi-signpost-2',
            'nada' => $netOp < 0 ? 'peringatan' : 'baik',
            'poin' => $poinRen,
        ];

        return $seksi;
    }

    /**
     * Seluruh angka yang dipakai panel Insight — dan persis inilah (dalam
     * bentuk ringkas) yang dikirim ke AI kalau tombol Analisa ditekan.
     */
    public function data(): array
    {
        $terpilih = $this->rangeTerpilih();

        // "Semua Periode": tidak ada rentang jelas. Pakai tahun acuan penuh
        // sebagai periode, dan lewati perbandingan periode-sebelumnya.
        $rangeUtama = $terpilih ?: [
            Carbon::create($this->tahunAcuan(), 1, 1)->startOfDay(),
            Carbon::create($this->tahunAcuan(), 1, 1)->addYear()->startOfDay(),
        ];

        $periode = $this->ringkas($rangeUtama);

        $sebelumnya = null;
        if ($rangeSeb = $this->rangeSebelumnya()) {
            $sebelumnya = $this->ringkas($rangeSeb);
            $sebelumnya['delta_income'] = $this->delta($periode['income'], $sebelumnya['income']);
            $sebelumnya['delta_expense'] = $this->delta($periode['expense'], $sebelumnya['expense']);
        }

        return [
            'periode' => $periode,
            'sebelumnya' => $sebelumnya,
            'kuartal' => $this->kuartal(),
            'ytd' => $this->ytd(),
            'kategori' => [
                'expense' => $this->perKategori($rangeUtama, 'expense'),
                'income' => $this->perKategori($rangeUtama, 'income'),
            ],
            'terbesar' => [
                'expense' => $this->terbesar($rangeUtama, 'expense', $periode['expense']),
                'income' => $this->terbesar($rangeUtama, 'income', $periode['income']),
            ],
        ];
    }

    private static function rp(float $n): string
    {
        return 'Rp '.number_format(abs($n), 0, ',', '.');
    }

    /**
     * Bacaan kondisi dalam kalimat — dihitung, bukan dikarang.
     *
     * Tiap butir: ['nada' => bahaya|peringatan|baik|info, 'judul', 'teks'].
     * Ini yang tampil walau tombol AI dimatikan atau modelnya sedang tidak
     * bisa dihubungi, jadi admin tidak pernah melihat panel kosong.
     */
    public function narasi(array $d): array
    {
        $out = [];
        $p = $d['periode'];

        // 1. Kondisi utama periode ini
        if ($p['transaksi'] === 0) {
            return [[
                'nada' => 'info',
                'judul' => 'Belum ada transaksi',
                'teks' => 'Periode '.$p['label'].' belum punya catatan cash flow sama sekali, jadi belum ada yang bisa dibaca.',
            ]];
        }

        if ($p['net'] < 0) {
            $out[] = [
                'nada' => 'bahaya',
                'judul' => 'Periode ini minus '.self::rp($p['net']),
                'teks' => 'Pengeluaran '.self::rp($p['expense']).' lebih besar daripada pemasukan '.self::rp($p['income']).'. '
                    .($p['income'] > 0
                        ? 'Artinya tiap Rp 1 yang masuk, keluar Rp '.number_format($p['expense'] / $p['income'], 2, ',', '.').'.'
                        : 'Tidak ada pemasukan tercatat sama sekali di periode ini.'),
            ];
        } else {
            $out[] = [
                'nada' => 'baik',
                'judul' => 'Periode ini surplus '.self::rp($p['net']),
                'teks' => 'Pemasukan '.self::rp($p['income']).' menutup pengeluaran '.self::rp($p['expense'])
                    .', sisa '.self::rp($p['net']).'.',
            ];
        }

        // 2. Pengeluaran terbesar — menjawab "kenapa", bukan cuma "berapa"
        $tb = $d['terbesar']['expense'] ?? null;
        if ($tb && $tb['menonjol']) {
            $out[] = [
                'nada' => 'peringatan',
                'judul' => 'Satu pengeluaran mendominasi',
                'teks' => $tb['nama'].' sebesar '.self::rp($tb['total']).' pada '.$tb['tanggal'].' menyumbang '
                    .$tb['pct'].'% dari seluruh pengeluaran periode ini. '
                    .'Kalau ini pengeluaran sekali saja, kondisi rutinmu sebenarnya lebih sehat daripada yang terlihat di angka total.',
            ];
        } elseif ($kat = ($d['kategori']['expense'][0] ?? null)) {
            $out[] = [
                'nada' => 'info',
                'judul' => 'Pengeluaran terbesar: '.$kat['nama'],
                'teks' => self::rp($kat['total']).' dari '.$kat['jumlah'].' transaksi — '.$kat['pct'].'% dari total pengeluaran.',
            ];
        }

        // 3. Bandingkan dengan periode sebelumnya
        if ($s = $d['sebelumnya']) {
            $out[] = [
                'nada' => $this->nadaPerbandingan($s),
                'judul' => 'Dibanding periode sebelumnya',
                'teks' => 'Periode lalu ('.$s['label'].') pemasukan '.self::rp($s['income'])
                    .' dan pengeluaran '.self::rp($s['expense']).'. '
                    .'Pemasukan '.$this->arah($s['delta_income']).', pengeluaran '.$this->arah($s['delta_expense']).'.',
            ];
        }

        // 4. Kuartal
        $out[] = $this->narasiKuartal($d['kuartal']);

        // 5. YTD
        $y = $d['ytd'];
        $out[] = [
            'nada' => $y['net'] < 0 ? 'peringatan' : 'baik',
            'judul' => 'Sejak awal tahun ('.$y['label'].')',
            'teks' => 'Total pemasukan '.self::rp($y['income']).', pengeluaran '.self::rp($y['expense']).' — '
                .($y['net'] < 0
                    ? 'masih minus '.self::rp($y['net']).' secara kumulatif.'
                    : 'surplus '.self::rp($y['net']).' secara kumulatif.')
                .' Angka inilah gambaran sebenarnya sepanjang tahun, bukan angka satu periode.',
        ];

        return array_values(array_filter($out));
    }

    private function nadaPerbandingan(array $s): string
    {
        $di = $s['delta_income'];
        $de = $s['delta_expense'];

        // Yang paling perlu diwaspadai: pemasukan turun sementara pengeluaran naik.
        if ($di !== null && $de !== null && $di < -self::AMBANG_DATAR_PERSEN && $de > self::AMBANG_DATAR_PERSEN) {
            return 'bahaya';
        }
        if ($di !== null && $di < -self::AMBANG_DATAR_PERSEN) {
            return 'peringatan';
        }
        if ($di !== null && $di > self::AMBANG_DATAR_PERSEN) {
            return 'baik';
        }

        return 'info';
    }

    /** "naik 23%" / "turun 12%" / "hampir sama" / "belum bisa dibandingkan". */
    private function arah(?float $delta): string
    {
        if ($delta === null) {
            return 'belum bisa dibandingkan (periode lalu nol)';
        }
        if (abs($delta) < self::AMBANG_DATAR_PERSEN) {
            return 'hampir sama';
        }

        return ($delta > 0 ? 'naik ' : 'turun ').number_format(abs($delta), 1, ',', '.').'%';
    }

    /**
     * Saran tindakan — dihitung dari angka, bukan template kosong.
     *
     * Tiap butir: ['ikon' => bootstrap-icon, 'teks' => saran]. Maksimal 3,
     * yang paling penting di atas. Ditulis sebagai anjuran ("sebaiknya",
     * "coba"), bukan perintah, dan menandai dugaan dgn "kalau/kemungkinan"
     * supaya admin tidak menganggapnya kepastian.
     *
     * Catatan "Pinjaman": di data ini Pinjaman adalah uang yang dipinjamkan
     * keluar / diterima — bukan biaya operasional. Kalau pengeluaran terbesar
     * ternyata Pinjaman, angka minus sering menyesatkan (uang cuma diputar,
     * bukan hilang), jadi sarannya beda dari biaya operasional biasa.
     */
    public function saran(array $d): array
    {
        $p = $d['periode'];
        $out = [];

        if ($p['transaksi'] === 0) {
            return [];
        }

        $katExpense = $d['kategori']['expense'] ?? [];
        $teratas = $katExpense[0] ?? null;
        $teratasPinjaman = $teratas && stripos($teratas['nama'], 'pinjaman') !== false;

        // 1. Kondisi minus
        if ($p['net'] < 0) {
            if ($teratasPinjaman) {
                $out[] = [
                    'ikon' => 'bi-arrow-repeat',
                    'teks' => 'Pengeluaran terbesar periode ini adalah Pinjaman ('.self::rp($teratas['total']).') — '
                        .'itu uang yang dipinjamkan keluar, bukan biaya yang hilang. '
                        .'Kalau dilunasi, ia kembali sebagai pemasukan. Jadi angka minus di sini belum tentu kerugian nyata; '
                        .'pantau saja apakah pinjaman-pinjaman itu benar-benar tertagih.',
                ];
            } elseif ($teratas) {
                $out[] = [
                    'ikon' => 'bi-scissors',
                    'teks' => 'Karena periode ini minus, titik paling cepat memperbaiki adalah pengeluaran terbesarmu: '
                        .$teratas['nama'].' ('.$teratas['pct'].'% dari total). '
                        .'Coba tinjau — kalau ada yang bisa ditunda atau dikurangi, dampaknya paling terasa di sini.',
                ];
            }
        }

        // 2. Tren pemasukan turun (sinyal paling perlu ditindak)
        if ($s = $d['sebelumnya']) {
            $di = $s['delta_income'];
            if ($di !== null && $di < -self::AMBANG_DATAR_PERSEN) {
                $out[] = [
                    'ikon' => 'bi-graph-up-arrow',
                    'teks' => 'Pemasukan turun '.number_format(abs($di), 1, ',', '.').'% dibanding periode lalu. '
                        .'Kalau biayamu sudah tipis, menaikkan penjualan biasanya lebih berdampak daripada memangkas biaya lagi — '
                        .'misalnya dorong produk yang paling laku atau tawarkan ke pelanggan lama.',
                ];
            } elseif ($di !== null && $di > self::AMBANG_DATAR_PERSEN && $p['net'] >= 0) {
                $out[] = [
                    'ikon' => 'bi-graph-up-arrow',
                    'teks' => 'Pemasukan naik '.number_format($di, 1, ',', '.').'% dan periode ini surplus — momentum bagus. '
                        .'Kalau ini karena satu hal yang berhasil (promo/produk tertentu), catat apa itu supaya bisa diulang.',
                ];
            }
        }

        // 3. YTD masih minus → target impas
        $y = $d['ytd'];
        if ($y['net'] < 0 && $y['transaksi'] > 0) {
            $bulanBerjalan = max(1, $this->bulanBerjalanYtd());
            $rataButuh = $y['expense'] / $bulanBerjalan;
            $out[] = [
                'ikon' => 'bi-bullseye',
                'teks' => 'Sejak awal tahun masih minus '.self::rp($y['net']).' secara kumulatif. '
                    .'Sekadar gambaran kasar, untuk sekadar impas rata-rata pemasukan bulanan perlu di sekitar '
                    .self::rp($rataButuh).' — angka ini termasuk pinjaman & modal, jadi pakai sebagai patokan longgar, bukan target pasti.',
            ];
        }

        // 4. Kondisi sehat → jaga cadangan
        if ($p['net'] > 0 && empty($out)) {
            $out[] = [
                'ikon' => 'bi-piggy-bank',
                'teks' => 'Periode ini surplus '.self::rp($p['net']).' dan tidak ada sinyal mengkhawatirkan. '
                    .'Sebaiknya sisihkan sebagian surplus sebagai dana cadangan atau modal stok, jangan langsung terpakai habis — '
                    .'supaya bulan yang sepi tidak langsung membuat kas minus.',
            ];
        }

        return array_slice($out, 0, 3);
    }

    /** Berapa bulan berjalan yang dicakup YTD (untuk rata-rata kasar). */
    private function bulanBerjalanYtd(): int
    {
        $tahun = $this->tahunAcuan();

        // Kalau tahun acuan = tahun berjalan, batasnya bulan sekarang.
        if ($tahun === (int) now()->year) {
            return (int) now()->month;
        }

        // Tahun lampau penuh 12 bulan; kalau ada bulan terpilih pakai itu.
        return $this->bulan ? (int) $this->bulan : 12;
    }

    private function narasiKuartal(array $kuartal): array
    {
        $terisi = array_values(array_filter($kuartal, fn ($q) => ! $q['kosong']));

        if (empty($terisi)) {
            return [
                'nada' => 'info',
                'judul' => 'Per kuartal',
                'teks' => 'Belum ada transaksi tercatat di tahun ini, jadi perbandingan antar kuartal belum bisa dibaca.',
            ];
        }

        $potongan = array_map(
            fn ($q) => $q['label'].' '.($q['net'] < 0 ? 'minus ' : 'surplus ').self::rp($q['net']),
            $terisi
        );

        $aktif = null;
        foreach ($kuartal as $q) {
            if ($q['aktif']) {
                $aktif = $q;
                break;
            }
        }

        $teks = 'Sepanjang tahun ini: '.implode(', ', $potongan).'.';

        if ($aktif && ! $aktif['kosong']) {
            $teks .= ' Periode yang sedang kamu lihat ada di '.$aktif['label'].' ('.$aktif['rentang'].').';
        }

        if (count($terisi) === 1) {
            $teks .= ' Baru satu kuartal yang terisi — polanya belum bisa disimpulkan.';
        }

        return [
            'nada' => 'info',
            'judul' => 'Per kuartal (setahun dibagi 4)',
            'teks' => $teks,
        ];
    }
}
