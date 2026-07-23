<?php

namespace App\Livewire\Pages\Admin\CashFlow;

use App\Models\CashFlow;
use App\Models\GajiKaryawans;
use App\Models\Loan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PemesananRsc;
use App\Models\Pengembalian;
use App\Models\Spending;
use App\Support\CashFlowInsight;
use App\Support\PeriodeGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class CashFlowList extends Component
{
    use WithPagination;

    public $bulan;

    public $tahun;

    // Mode periode: 'kalender' (1 s/d akhir bulan, default) atau 'siklus20' = siklus gaji
    // (21 s/d 20, mengikuti setelan payroll_cutoff_day — sama persis dgn fitur Gaji).
    // Nilai 'siklus20' dipertahankan apa adanya agar state/URL lama tidak rusak.
    public $modePeriode = 'kalender';

    public $produkPage = 1;

    public $produkPerPage = 8;

    /** Analisa menyeluruh (PHP, instan). Kosong = tombol belum ditekan. */
    public array $analisaLengkap = [];

    public function mount(): void
    {
        // Default: siklus gaji 21-20 periode BERJALAN (seragam dgn fitur Gaji/Task),
        // bukan bulan kalender. Pada tgl 21+ periode ini beda dari bulan kalender.
        $p = PeriodeGaji::dariTanggal(now());
        $this->bulan = $p['bulan'];
        $this->tahun = $p['tahun'];
        $this->modePeriode = 'siklus20';
    }

    public function updated($property): void
    {
        // Reset paginasi setiap kali filter periode berubah
        if (in_array($property, ['bulan', 'tahun', 'modePeriode'])) {
            $this->resetPage();
            $this->produkPage = 1;
            // Analisa terikat ke periode. Tanpa ini, admin ganti bulan lalu
            // membaca analisa bulan lama sebagai bulan baru.
            $this->analisaLengkap = [];
        }
    }

    /**
     * Tombol "Analisa Lengkap" — instan, gratis, nol data keluar dari server.
     *
     * Menghasilkan analisa menyeluruh berbasis aturan dari angka: keuangan +
     * proyeksi ke depan, prospek produk, promo, task, kesehatan bisnis, dan
     * rencana aksi. Dihitung saat diklik, bukan tiap render, supaya panel ringan.
     */
    public function analisaTanpaAi(): void
    {
        $this->analisaLengkap = $this->insight()->analisaLengkap();
    }

    /**
     * Angka untuk panel Insight — periode ini, periode sebelumnya, per kuartal,
     * dan sejak awal tahun.
     *
     * Dihitung TERPISAH dari filteredQuery()/hitungOmsetBersih(): dia cuma
     * membaca ulang tabel cash_flows dgn rentang yang sama, tidak mengubah
     * satu pun perilaku tabel, filter, periode, atau unduhan PDF yang sudah ada.
     */
    protected function insight(): CashFlowInsight
    {
        return new CashFlowInsight(
            bulan: $this->bulan ? (int) $this->bulan : null,
            tahun: $this->tahun ? (int) $this->tahun : null,
            siklus: $this->usesSiklus(),
        );
    }

    /**
     * Apakah filter memakai siklus gaji 21-20 (butuh bulan terpilih).
     */
    protected function usesSiklus(): bool
    {
        return $this->modePeriode === 'siklus20' && ! empty($this->bulan);
    }

    /**
     * Rentang siklus gaji untuk bulan/tahun terpilih — SATU sumber dengan fitur Gaji,
     * yaitu setelan `payroll_cutoff_day` (default tgl 20). Mis. Juli = 21 Jun s/d 20 Jul.
     *
     * Sengaja tidak mematok angka 20 secara hardcode: kalau tanggal gajian diubah,
     * cash flow ikut sendiri dan tidak diam-diam melenceng dari periode gaji.
     *
     * Dikembalikan sebagai [mulai, akhirEksklusif] agar kontrak lama
     * (>= mulai, < akhirEksklusif) di filteredQuery/periodeLabel tetap berlaku.
     */
    protected function siklusRange(): array
    {
        $tahun = (int) ($this->tahun ?: now()->year);
        $bulan = (int) $this->bulan;

        return [
            PeriodeGaji::mulai($bulan, $tahun),
            PeriodeGaji::akhir($bulan, $tahun)->addDay()->startOfDay(),
        ];
    }

    public function produkNext(): void
    {
        $this->produkPage++;
    }

    public function produkPrev(): void
    {
        if ($this->produkPage > 1) {
            $this->produkPage--;
        }
    }

    public function produkGoto($page): void
    {
        $this->produkPage = max(1, (int) $page);
    }

    public function resetFilter(): void
    {
        // "Reset" = kembali ke default: siklus 21-20 periode berjalan.
        $p = PeriodeGaji::dariTanggal(now());
        $this->bulan = $p['bulan'];
        $this->tahun = $p['tahun'];
        $this->modePeriode = 'siklus20';
        $this->produkPage = 1;
        $this->resetPage();
        $this->analisaLengkap = [];
    }

    public function render()
    {
        $query = $this->filteredQuery();

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        $omset = $this->hitungOmsetBersih();

        // Paginasi rincian per produk (data in-memory)
        $allProduk = $omset['per_produk'];
        $produkTotal = count($allProduk);
        $produkTotalPages = max(1, (int) ceil($produkTotal / $this->produkPerPage));
        $this->produkPage = min(max(1, (int) $this->produkPage), $produkTotalPages);
        $produkItems = array_slice($allProduk, ($this->produkPage - 1) * $this->produkPerPage, $this->produkPerPage);

        // Rentang siklus dikirim dari sini supaya view TIDAK menghitung ulang sendiri —
        // kalau view punya rumus sendiri, chip di layar bisa beda dgn data yg difilter.
        // 'siklusAkhir' sudah inklusif (siap tampil), beda dgn akhirEksklusif utk query.
        $siklusMulai = $siklusAkhir = null;
        if ($this->usesSiklus()) {
            [$m, $aEks] = $this->siklusRange();
            $siklusMulai = $m;
            $siklusAkhir = $aEks->copy()->subDay();
        }

        // Angka + bacaan otomatis untuk panel Insight. Selalu dihitung — murah,
        // hanya SUM/GROUP BY pada tabel cash_flows.
        $insight = $this->insight();
        $insightData = $insight->data();

        // Pendapatan HARI INI — kartu statistik tambahan, tak mengubah filter/omset.
        $pendapatanHariIni = (float) CashFlow::where('type', 'income')
            ->whereDate('transaction_date', today())
            ->sum('amount');

        return view('livewire.pages.admin.cash-flow.cash-flow-list', [
            'pendapatanHariIni' => $pendapatanHariIni,
            'insightData' => $insightData,
            'insightNarasi' => $insight->narasi($insightData),
            'insightSaran' => $insight->saran($insightData),
            'siklusMulai' => $siklusMulai,
            'siklusAkhir' => $siklusAkhir,
            'reports' => $query->paginate(10),
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $totalIncome - $totalExpense,
            ],
            'omset' => $omset,
            'produkItems' => $produkItems,
            'produkTotal' => $produkTotal,
            'produkTotalPages' => $produkTotalPages,
            'totalKodeUnik' => $this->hitungTotalKodeUnik(),
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ])->layout('livewire.layout.templateindex');
    }

    /**
     * Unduh laporan cashflow lengkap sesuai periode terpilih sebagai PDF.
     */
    public function downloadReport()
    {
        $query = $this->filteredQuery();
        $records = (clone $query)->get();

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        $rows = $records->map(fn (CashFlow $cf) => [
            'tanggal' => $cf->transaction_date->format('d/m/Y'),
            'kategori' => ucfirst($cf->category ?? '-'),
            'tipe' => $cf->type,
            'deskripsi' => $cf->description ?: '-',
            'sumber' => $this->sumberLabel($cf),
            'amount' => (float) $cf->amount,
        ])->toArray();

        $pdf = Pdf::loadView('livewire.pages.admin.cash-flow.report-pdf', [
            'periode' => $this->periodeLabel(),
            'rows' => $rows,
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $totalIncome - $totalExpense,
            ],
            'omset' => $this->hitungOmsetBersih(),
            'totalKodeUnik' => $this->hitungTotalKodeUnik(),
        ])->setPaper('a4', 'portrait');

        $slug = str($this->periodeLabel())->slug();

        return response()->streamDownload(fn () => print ($pdf->output()), "laporan-cashflow-{$slug}.pdf");
    }

    /**
     * Query dasar cashflow yang sudah difilter sesuai periode (dipakai bersama
     * oleh tampilan tabel maupun unduhan laporan).
     */
    protected function filteredQuery()
    {
        $query = CashFlow::with('sourceable')->latest('created_at');

        if ($this->usesSiklus()) {
            [$mulai, $akhir] = $this->siklusRange();
            $query->where('transaction_date', '>=', $mulai)
                ->where('transaction_date', '<', $akhir);
        } else {
            if ($this->tahun) {
                $query->whereYear('transaction_date', $this->tahun);
            }
            if ($this->bulan) {
                $query->whereMonth('transaction_date', $this->bulan);
            }
        }

        return $query;
    }

    /**
     * Label sumber transaksi dalam bentuk teks untuk laporan.
     */
    protected function sumberLabel(CashFlow $cf): string
    {
        $s = $cf->sourceable;

        if (! $s) {
            return '-';
        }

        return match ($cf->sourceable_type) {
            Order::class => 'Order #'.($s->order_number ?? '-'),
            Loan::class => 'Pinjaman '.($s->nama_peminjam ?? ''),
            Pengembalian::class => 'Pengembalian '.($s->nama_pengembalian ?? ''),
            GajiKaryawans::class => 'Gaji '.($s->karyawan->name ?? 'User'),
            Spending::class => 'Pengeluaran '.($s->jenis_pengeluaran === 'pembelian_akun' ? 'Pembelian Akun' : 'Lainnya'),
            \App\Models\Modal::class => 'Modal Operasional',
            \App\Models\OrderItem::class => 'Modal Akun: '.($s->product_name ?? '-'),
            \App\Models\Pemasukan::class => $s->kategori ? 'Pemasukan: '.$s->kategori : 'Pemasukan Lainnya',
            PemesananRsc::class => 'Pesanan Rumah Scopus',
            default => class_basename($cf->sourceable_type),
        };
    }

    /**
     * Label periode terpilih untuk judul laporan & nama file.
     */
    protected function periodeLabel(): string
    {
        $namaBulan = $this->bulan ? ($this->daftarBulan()[$this->bulan] ?? '') : '';

        if ($this->usesSiklus()) {
            [$mulai, $akhir] = $this->siklusRange();

            return $mulai->translatedFormat('d M Y').' – '.$akhir->copy()->subDay()->translatedFormat('d M Y');
        }

        if ($this->bulan && $this->tahun) {
            return $namaBulan.' '.$this->tahun;
        }
        if ($this->tahun) {
            return 'Tahun '.$this->tahun;
        }
        if ($this->bulan) {
            return $namaBulan.' (Semua Tahun)';
        }

        return 'Semua Periode';
    }

    /**
     * Hitung omset bersih penjualan pada periode terpilih.
     *
     * Omset Bersih = Total Penjualan (SUM subtotal item) - Total Modal (Pembelian Akun).
     * Modal kini diambil dari pengeluaran berjenis "pembelian_akun" (bukan harga_awal),
     * dikelompokkan per produk. Hanya dari pesanan yang sudah dibayar (paid/processing/completed).
     */
    protected function hitungOmsetBersih(): array
    {
        $paidStatuses = ['paid', 'processing', 'completed'];

        // Per-periode: bisa mode kalender (bulan/tahun) atau siklus 20-20.
        $tahun = $this->tahun;
        $bulan = $this->bulan;
        $usesSiklus = $this->usesSiklus();
        [$siklusMulai, $siklusAkhir] = $usesSiklus ? $this->siklusRange() : [null, null];

        $applyOrderPeriode = function ($q) use ($tahun, $bulan, $usesSiklus, $siklusMulai, $siklusAkhir) {
            if ($usesSiklus) {
                $q->whereRaw('COALESCE(paid_at, created_at) >= ?', [$siklusMulai])
                    ->whereRaw('COALESCE(paid_at, created_at) < ?', [$siklusAkhir]);

                return;
            }
            if ($tahun) {
                $q->whereRaw('YEAR(COALESCE(paid_at, created_at)) = ?', [$tahun]);
            }
            if ($bulan) {
                $q->whereRaw('MONTH(COALESCE(paid_at, created_at)) = ?', [$bulan]);
            }
        };

        // Penjualan per produk (pesanan yang sudah dibayar, pada periode terpilih)
        $penjualanRows = OrderItem::query()
            ->whereHas('order', function ($q) use ($paidStatuses, $applyOrderPeriode) {
                $q->whereIn('status', $paidStatuses);
                $applyOrderPeriode($q);
            })
            ->selectRaw('order_items.product_id, COALESCE(SUM(order_items.subtotal), 0) as penjualan')
            ->groupBy('order_items.product_id')
            ->get();

        // ===== Modal per produk =====
        // Produk JASA (butuh_file) juga bermodal (per pengecekan, dari katalog),
        // jadi diperlakukan seperti private walau tipe_akun-nya bukan 'private'.
        $jasaIds = \App\Models\Product::where('butuh_file', true)->pluck('id')->all();
        // Jasa PER HALAMAN (parafrase): modal = per halaman × jumlah halaman dikerjakan.
        $jasaHalamanIds = \App\Models\Product::where('butuh_file', true)->where('jasa_mode', 'halaman')->pluck('id')->all();
        $privateIds = \App\Models\Product::where('tipe_akun', 'private')->pluck('id')
            ->merge($jasaIds)->unique()->values()->all();

        // Modal produk NON-private (sharing / tanpa produk) = total pembelian akun periode terpilih
        $modalNonPrivateRows = Spending::query()
            ->where('jenis_pengeluaran', 'pembelian_akun')
            // HANYA pembelian yang sudah selesai = biaya nyata. Tanpa filter ini
            // pembelian berstatus 'pending' ikut terhitung, sehingga modal di
            // Omset Bersih lebih besar daripada "modal terpakai" di fitur Modal
            // (yang memang hanya menghitung completed) — dua angka untuk hal sama.
            ->where('status', 'completed')
            ->when($usesSiklus, function ($q) use ($siklusMulai, $siklusAkhir) {
                $q->where('tanggal_transaksi', '>=', $siklusMulai->toDateString())
                    ->where('tanggal_transaksi', '<', $siklusAkhir->toDateString());
            })
            ->when(! $usesSiklus && $tahun, fn ($q) => $q->whereYear('tanggal_transaksi', $tahun))
            ->when(! $usesSiklus && $bulan, fn ($q) => $q->whereMonth('tanggal_transaksi', $bulan))
            ->when(! empty($privateIds), function ($q) use ($privateIds) {
                $q->where(function ($x) use ($privateIds) {
                    $x->whereNull('product_id')->orWhereNotIn('product_id', $privateIds);
                });
            })
            ->selectRaw('product_id, COALESCE(SUM(nominal), 0) as modal')
            ->groupBy('product_id')
            ->get();

        $modalByProduct = []; // key: product_id ('' utk null) => modal
        foreach ($modalNonPrivateRows as $r) {
            $modalByProduct[(string) $r->product_id] = (float) $r->modal;
        }

        // Batas tanggal harga modal yang berlaku (akhir periode terpilih).
        if ($usesSiklus) {
            // Akhir siklus gaji = tgl gajian (sehari sebelum akhir eksklusif).
            $hargaCutoff = $siklusAkhir->copy()->subDay()->toDateString();
        } elseif ($bulan && $tahun) {
            $hargaCutoff = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        } elseif ($tahun) {
            $hargaCutoff = Carbon::create($tahun, 12, 31)->toDateString();
        } elseif ($bulan) {
            $hargaCutoff = Carbon::create(now()->year, $bulan, 1)->endOfMonth()->toDateString();
        } else {
            $hargaCutoff = null;
        }

        // Modal produk PRIVATE = katalog modal satuan (harga BERLAKU s/d periode) x jumlah order
        if (! empty($privateIds)) {
            $catalog = \App\Models\ProductModalPrice::query()
                ->whereIn('product_id', $privateIds)
                ->when($hargaCutoff, fn ($q) => $q->where('berlaku_mulai', '<=', $hargaCutoff))
                ->orderBy('berlaku_mulai', 'desc')
                ->orderBy('created_at', 'desc')
                ->get(['product_id', 'durasi_value', 'durasi_type', 'harga']);
            $unitMap = [];
            foreach ($catalog as $c) {
                $k = $c->product_id.'|'.$c->durasi_value.'|'.$c->durasi_type;
                if (! array_key_exists($k, $unitMap)) {
                    $unitMap[$k] = (float) $c->harga; // berlaku terbaru menang
                }
            }

            $privItems = OrderItem::query()
                ->whereHas('order', function ($q) use ($paidStatuses, $applyOrderPeriode) {
                    $q->whereIn('status', $paidStatuses);
                    $applyOrderPeriode($q);
                })
                ->whereIn('order_items.product_id', $privateIds)
                ->selectRaw('order_items.product_id, order_items.duration_value, order_items.duration_type, COALESCE(SUM(order_items.quantity), 0) as qty, COALESCE(SUM(COALESCE(order_items.halaman_dihitung, order_items.jumlah_halaman) * order_items.quantity), 0) as halaman')
                ->groupBy('order_items.product_id', 'order_items.duration_value', 'order_items.duration_type')
                ->get();

            foreach ($privItems as $it) {
                $pidKey = (string) $it->product_id;

                if (in_array($it->product_id, $jasaHalamanIds, true)) {
                    // JASA PER HALAMAN: modal per 1 halaman × jumlah halaman DIKERJAKAN.
                    $perHalaman = $unitMap[$it->product_id.'|1|halaman'] ?? 0;
                    $tambah = $perHalaman * (int) $it->halaman;
                } elseif (in_array($it->product_id, $jasaIds, true)) {
                    // JASA PAKET: modal per 1× pengecekan × jumlah pengecekan × qty.
                    $perCheck = $unitMap[$it->product_id.'|1|kali'] ?? 0;
                    $tambah = $perCheck * max(1, (int) $it->duration_value) * (int) $it->qty;
                } else {
                    // Non-jasa: modal satuan tepat pada (durasi_value, durasi_type) × qty.
                    $unit = $unitMap[$it->product_id.'|'.$it->duration_value.'|'.$it->duration_type] ?? 0;
                    $tambah = $unit * (int) $it->qty;
                }

                $modalByProduct[$pidKey] = ($modalByProduct[$pidKey] ?? 0) + $tambah;
            }

            // Modal PRIVATE dari Rumah Scopus (RSC): satu akun patungan per batch
            // = satu modal (dari katalog Harga Modal, harga berlaku s/d tgl pesan).
            // Wajib ada supaya penjualan RSC private (yg sudah diakui di Omset)
            // tidak terlihat untung berlebih tanpa modalnya.
            foreach ($this->modalRscPrivatePerProduk($privateIds) as $pidKey => $nilai) {
                $modalByProduct[$pidKey] = ($modalByProduct[$pidKey] ?? 0) + $nilai;
            }
        }

        // Peta penjualan per produk
        $penjualanByProduct = [];
        foreach ($penjualanRows as $r) {
            $penjualanByProduct[(string) $r->product_id] = (float) $r->penjualan;
        }

        // Penjualan Rumah Scopus (RSC) IKUT diakui di sini, ditambahkan ke produk
        // induk akunnya (pemesanan_rsc.akun → data_akuns.product_id).
        //
        // Kenapa perlu: akun sharing dibeli sekali (tercatat di Pengeluaran →
        // pembelian akun) lalu dipakai juga untuk RSC. Kalau penjualan RSC tidak
        // diakui, modal akun itu terlihat "belum tertutup" padahal sudah
        // menghasilkan uang lewat RSC. Ini menambah sisi PENJUALAN saja —
        // pengeluaran tidak disentuh, jadi tidak ada dobel hitung.
        //
        // Akun yang belum ditautkan ke produk sengaja dilewati (bukan dijadikan
        // "Tanpa Produk"), supaya angka per produk tidak tertukar.
        foreach ($this->penjualanRscPerProduk() as $pid => $nilai) {
            $penjualanByProduct[$pid] = ($penjualanByProduct[$pid] ?? 0) + $nilai;
        }

        // Add-on jasa (mis. cek plagiasi turnitin pada pesanan cek AI): penjualan
        // & modalnya diakui pada PRODUK ADD-ON SENDIRI, bukan produk induk —
        // walau dibeli dalam satu order. Add-on opsi non-pemeriksaan diakui di
        // produk induk (tanpa modal). Lihat AtribusiAddonJasa.
        $addon = \App\Support\AtribusiAddonJasa::hitung(function ($q) use ($paidStatuses, $applyOrderPeriode) {
            $q->whereIn('status', $paidStatuses);
            $applyOrderPeriode($q);
        }, $hargaCutoff);
        foreach ($addon['penjualan'] as $pid => $nilai) {
            $penjualanByProduct[(string) $pid] = ($penjualanByProduct[(string) $pid] ?? 0) + $nilai;
        }
        foreach ($addon['modal'] as $pid => $nilai) {
            $modalByProduct[(string) $pid] = ($modalByProduct[(string) $pid] ?? 0) + $nilai;
        }

        $penjualan = array_sum($penjualanByProduct);
        $modal = array_sum($modalByProduct);
        $bersih = $penjualan - $modal;

        // ===== Rincian per produk =====
        $productIds = collect(array_keys($penjualanByProduct))
            ->merge(array_keys($modalByProduct))
            ->filter(fn ($k) => $k !== '' && $k !== null)
            ->unique()
            ->values();
        $names = \App\Models\Product::whereIn('id', $productIds)->pluck('nama_akun', 'id');

        $perProduk = [];
        foreach ($productIds as $pid) {
            $pj = (float) ($penjualanByProduct[(string) $pid] ?? 0);
            $md = (float) ($modalByProduct[(string) $pid] ?? 0);
            $perProduk[] = [
                'nama' => $names[$pid] ?? 'Produk',
                'penjualan' => $pj,
                'modal' => $md,
                'bersih' => $pj - $md,
                'tertutup' => $md <= 0 ? true : $pj >= $md,
            ];
        }

        // Tanpa produk = pembelian akun / spending lama yang tidak tertaut ke produk mana pun.
        // (Item paket bundling TIDAK masuk sini — saat checkout paket dipecah jadi OrderItem
        //  per produk dengan product_id asli, jadi omset & modalnya sudah terhitung per produk.)
        $pjNull = (float) ($penjualanByProduct[''] ?? 0);
        $mdNull = (float) ($modalByProduct[''] ?? 0);
        if ($pjNull > 0 || $mdNull > 0) {
            $perProduk[] = [
                'nama' => 'Tanpa Produk',
                'penjualan' => $pjNull,
                'modal' => $mdNull,
                'bersih' => $pjNull - $mdNull,
                'tertutup' => $mdNull <= 0 ? true : $pjNull >= $mdNull,
            ];
        }

        usort($perProduk, fn ($a, $b) => $b['bersih'] <=> $a['bersih']);

        return [
            'penjualan' => $penjualan,
            'modal' => $modal,
            'bersih' => $bersih,
            'margin' => $penjualan > 0 ? round(($bersih / $penjualan) * 100, 1) : 0,
            'per_produk' => $perProduk,
        ];
    }

    /**
     * Penjualan Rumah Scopus (RSC) per produk pada periode terpilih.
     *
     * Pemesanan RSC memakai akun dari Data Akun; produk induknya diambil dari
     * data_akuns.product_id. Nilainya = pemesanan_rsc.total.
     *
     * Syarat & tanggal dibuat SAMA PERSIS dengan pencatatan cash flow RSC supaya
     * tidak melenceng dari uang yang benar-benar diakui:
     *  - status 'baru'   → sama dgn SyncCashFlowAction::shouldRecord()
     *  - tanggal_pemesanan → sama dgn tanggal cash flow RSC
     *
     * Akun tanpa tautan produk dilewati (tidak bisa diakui ke produk mana pun).
     *
     * @return array<string,float>  product_id => total penjualan RSC
     */
    protected function penjualanRscPerProduk(): array
    {
        $rows = PemesananRsc::query()
            ->join('data_akuns', 'data_akuns.id', '=', 'pemesanan_rsc.akun')
            ->where('pemesanan_rsc.status', 'baru')
            ->whereNotNull('data_akuns.product_id')
            ->when($this->usesSiklus(), function ($q) {
                [$mulai, $akhir] = $this->siklusRange();
                $q->whereDate('pemesanan_rsc.tanggal_pemesanan', '>=', $mulai->toDateString())
                    ->whereDate('pemesanan_rsc.tanggal_pemesanan', '<', $akhir->toDateString());
            }, function ($q) {
                if ($this->tahun) {
                    $q->whereYear('pemesanan_rsc.tanggal_pemesanan', $this->tahun);
                }
                if ($this->bulan) {
                    $q->whereMonth('pemesanan_rsc.tanggal_pemesanan', $this->bulan);
                }
            })
            ->selectRaw('data_akuns.product_id, COALESCE(SUM(pemesanan_rsc.total), 0) as penjualan')
            ->groupBy('data_akuns.product_id')
            ->get();

        $peta = [];
        foreach ($rows as $r) {
            $peta[(string) $r->product_id] = (float) $r->penjualan;
        }

        return $peta;
    }

    /**
     * Modal akun PRIVATE dari Rumah Scopus (RSC) per produk pada periode terpilih.
     *
     * Hitungannya (jumlah akun × harga katalog, per_peserta vs per_akun) memakai
     * SATU metode inti bersama dgn pencatatan baris cash flow-nya:
     * SyncRscPrivateCostAction::modalPerProduk() — supaya angka di omset dan di
     * fitur Modal tidak mungkin berbeda.
     *
     * @param  array<int,string>  $privateIds  id produk bertipe private
     * @return array<string,float>  product_id => total modal
     */
    protected function modalRscPrivatePerProduk(array $privateIds): array
    {
        if (empty($privateIds)) {
            return [];
        }

        // Ambil satu baris REPRESENTATIF tiap batch dalam periode (status 'baru').
        // Batch-nya sendiri yang self-filter private di modalPerProduk(); di sini
        // cukup persempit ke batch yang tanggalnya masuk periode.
        $representatif = PemesananRsc::query()
            ->where('status', 'baru')
            ->when($this->usesSiklus(), function ($q) {
                [$mulai, $akhir] = $this->siklusRange();
                $q->whereDate('tanggal_pemesanan', '>=', $mulai->toDateString())
                    ->whereDate('tanggal_pemesanan', '<', $akhir->toDateString());
            }, function ($q) {
                if ($this->tahun) {
                    $q->whereYear('tanggal_pemesanan', $this->tahun);
                }
                if ($this->bulan) {
                    $q->whereMonth('tanggal_pemesanan', $this->bulan);
                }
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($r) => $r->nama_camp.'|'.$r->batch_camp)
            ->map(fn ($grp) => $grp->first());

        $action = app(\App\Actions\Finance\SyncRscPrivateCostAction::class);

        $peta = [];
        foreach ($representatif as $rep) {
            foreach ($action->modalPerProduk($rep) as $pid => $modal) {
                $peta[$pid] = ($peta[$pid] ?? 0) + $modal;
            }
        }

        return $peta;
    }

    /**
     * Total kode unik dari seluruh penjualan (pesanan yang sudah dibayar)
     * pada periode terpilih. Kode unik adalah nominal tambahan untuk
     * identifikasi pembayaran transfer.
     */
    protected function hitungTotalKodeUnik(): float
    {
        $paidStatuses = ['paid', 'processing', 'completed'];

        $query = Order::query()->whereIn('status', $paidStatuses);

        if ($this->usesSiklus()) {
            [$mulai, $akhir] = $this->siklusRange();
            $query->whereRaw('COALESCE(paid_at, created_at) >= ?', [$mulai])
                ->whereRaw('COALESCE(paid_at, created_at) < ?', [$akhir]);
        } else {
            if ($this->tahun) {
                $query->whereRaw('YEAR(COALESCE(paid_at, created_at)) = ?', [$this->tahun]);
            }
            if ($this->bulan) {
                $query->whereRaw('MONTH(COALESCE(paid_at, created_at)) = ?', [$this->bulan]);
            }
        }

        return (float) $query->sum('unique_code');
    }

    protected function daftarBulan(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    protected function daftarTahun(): array
    {
        $tahunSekarang = (int) now()->year;

        // 6 tahun terakhir hingga tahun berjalan
        return range($tahunSekarang, $tahunSekarang - 5);
    }
}
