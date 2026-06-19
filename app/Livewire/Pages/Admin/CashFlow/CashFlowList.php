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
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;

class CashFlowList extends Component
{
    use WithPagination;

    public $bulan;

    public $tahun;

    public function mount(): void
    {
        // Default ke periode bulan & tahun berjalan
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function updated($property): void
    {
        // Reset paginasi setiap kali filter periode berubah
        if (in_array($property, ['bulan', 'tahun'])) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->bulan = '';
        $this->tahun = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->filteredQuery();

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        return view('livewire.pages.admin.cash-flow.cash-flow-list', [
            'reports' => $query->paginate(10),
            'summary' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $totalIncome - $totalExpense,
            ],
            'omset' => $this->hitungOmsetBersih(),
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
        $query = CashFlow::with('sourceable')->latest('transaction_date');

        if ($this->tahun) {
            $query->whereYear('transaction_date', $this->tahun);
        }
        if ($this->bulan) {
            $query->whereMonth('transaction_date', $this->bulan);
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
     * Omset Bersih = Total Penjualan (SUM subtotal item) - Total Modal (SUM harga_awal * qty)
     * Hanya dari pesanan yang sudah dibayar (paid/processing/completed).
     * leftJoin agar item tanpa produk terpadan (mis. bundling) tetap dihitung
     * penjualannya, sementara modalnya 0 bila harga_awal tidak ditemukan.
     */
    protected function hitungOmsetBersih(): array
    {
        $paidStatuses = ['paid', 'processing', 'completed'];

        $agg = OrderItem::query()
            ->whereHas('order', function ($q) use ($paidStatuses) {
                $q->whereIn('status', $paidStatuses);

                // Filter periode berdasarkan tanggal bayar (fallback ke tanggal dibuat)
                if ($this->tahun) {
                    $q->whereRaw('YEAR(COALESCE(paid_at, created_at)) = ?', [$this->tahun]);
                }
                if ($this->bulan) {
                    $q->whereRaw('MONTH(COALESCE(paid_at, created_at)) = ?', [$this->bulan]);
                }
            })
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as penjualan')
            ->selectRaw('COALESCE(SUM(COALESCE(products.harga_awal, 0) * order_items.quantity), 0) as modal')
            ->first();

        $penjualan = (float) ($agg->penjualan ?? 0);
        $modal = (float) ($agg->modal ?? 0);
        $bersih = $penjualan - $modal;

        return [
            'penjualan' => $penjualan,
            'modal' => $modal,
            'bersih' => $bersih,
            'margin' => $penjualan > 0 ? round(($bersih / $penjualan) * 100, 1) : 0,
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

        if ($this->tahun) {
            $query->whereRaw('YEAR(COALESCE(paid_at, created_at)) = ?', [$this->tahun]);
        }
        if ($this->bulan) {
            $query->whereRaw('MONTH(COALESCE(paid_at, created_at)) = ?', [$this->bulan]);
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
