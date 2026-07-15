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

    public function mount(): void
    {
        // Default ke periode bulan & tahun berjalan
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function updated($property): void
    {
        // Reset paginasi setiap kali filter periode berubah
        if (in_array($property, ['bulan', 'tahun', 'modePeriode'])) {
            $this->resetPage();
            $this->produkPage = 1;
        }
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
        $this->bulan = '';
        $this->tahun = '';
        $this->modePeriode = 'kalender';
        $this->produkPage = 1;
        $this->resetPage();
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

        return view('livewire.pages.admin.cash-flow.cash-flow-list', [
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
        $privateIds = \App\Models\Product::where('tipe_akun', 'private')->pluck('id')->all();

        // Modal produk NON-private (sharing / tanpa produk) = total pembelian akun periode terpilih
        $modalNonPrivateRows = Spending::query()
            ->where('jenis_pengeluaran', 'pembelian_akun')
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

        // Modal produk PRIVATE = katalog modal satuan (harga BERLAKU s/d periode) x jumlah order
        if (! empty($privateIds)) {
            // Batas tanggal harga yang berlaku (akhir periode terpilih).
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
                ->selectRaw('order_items.product_id, order_items.duration_value, order_items.duration_type, COALESCE(SUM(order_items.quantity), 0) as qty')
                ->groupBy('order_items.product_id', 'order_items.duration_value', 'order_items.duration_type')
                ->get();

            foreach ($privItems as $it) {
                $k = $it->product_id.'|'.$it->duration_value.'|'.$it->duration_type;
                $unit = $unitMap[$k] ?? 0;
                $pidKey = (string) $it->product_id;
                $modalByProduct[$pidKey] = ($modalByProduct[$pidKey] ?? 0) + $unit * (int) $it->qty;
            }
        }

        // Peta penjualan per produk
        $penjualanByProduct = [];
        foreach ($penjualanRows as $r) {
            $penjualanByProduct[(string) $r->product_id] = (float) $r->penjualan;
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
