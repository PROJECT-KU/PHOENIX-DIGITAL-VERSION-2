<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Actions\Finance\SyncCashFlowAction;
use App\Exports\CampBatchExport;
use App\Models\PemesananRsc;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PemesananrscList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // 🔹 State/Filter properties
    public $search = '';

    // Filter periode (seragam dengan Pesanan Toko).
    public $filterMonth = '';

    public $filterYear = '';

    public $statusFilter = '';

    public $startDate = '';

    public $endDate = '';

    public $pembeliFilter = '';

    public $kategoriFilter = '';

    public $batchFilter = '';

    public $perPage = 10;

    // property export data
    public $showExportModal = false;

    public $searchBatchExport = '';

    public $selectedBatches = [];

    public function openExportModal()
    {
        $this->reset(['searchBatchExport', 'selectedBatches']);
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    public function getAvailableBatchesForExportProperty()
    {
        return PemesananRsc::select('nama_camp', 'batch_camp')
            ->distinct()
            ->when($this->searchBatchExport, function ($q) {
                $q->where('nama_camp', 'like', '%'.$this->searchBatchExport.'%')
                    ->orWhere('batch_camp', 'like', '%'.$this->searchBatchExport.'%');
            })
            ->orderBy('nama_camp')
            ->orderBy('batch_camp', 'desc')
            ->get()
            ->map(function ($item) {
                $item->key = $item->nama_camp.'|'.$item->batch_camp;

                return $item;
            });
    }

    public function exportExcel()
    {
        $this->validate(['selectedBatches' => 'required|array|min:1'], ['selectedBatches.required' => 'Pilih minimal satu batch untuk di export']);

        $this->showExportModal = false;
        $fileName = 'Export_Peserta_camp_'.date('Y-m-d_H-i').'.xlsx';

        return Excel::download(new CampBatchExport($this->selectedBatches), $fileName);
    }

    public function exportInvoice()
    {
        $this->validate(['selectedBatches' => 'required|array|min:1'], ['selectedBatches.required' => 'Pilih minimal satu batch untuk di buatkan invoice']);

        $this->showExportModal = false;

        // Sumber tunggal item invoice (sama dgn preview) — termasuk daftar akun
        // untuk batch metode "per_akun".
        $invoiceItems = PemesananRsc::invoiceItemsFor($this->selectedBatches);

        $data = [
            'invoiceNumber' => 'INV-'.date('Y').'-'.rand(1000, 9999),
            'date' => now()->translatedFormat('d F Y'),
            'items' => $invoiceItems,
            'grandTotal' => $invoiceItems->sum('total_harga'),
        ];

        $pdf = Pdf::loadView('exports.invoice-pdf', $data);

        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Invoice_RumahScopus_'.date('Y-m-d').'.pdf');
    }

    // 🔹 URL query sync
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'pembeliFilter' => ['except' => ''],
        'kategoriFilter' => ['except' => ''],
        'batchFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // 🔹 Reset page saat filter berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    // Reset filter periode (seragam dengan Pesanan Toko).
    public function resetFilters()
    {
        $this->reset(['search', 'filterMonth', 'filterYear']);
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingpembeliFilter()
    {
        $this->resetPage();
    }

    public function updatingkategoriFilter()
    {
        $this->resetPage();
    }

    public function updatingbatchFilter()
    {
        $this->resetPage();
    }

    // 🔹 Reset semua filter
    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->pembeliFilter = '';
        $this->kategoriFilter = '';
        $this->batchFilter = '';
        $this->resetPage();
    }

    // 🔹 Hapus data
    public function confirmDeleteBatch($namaCamp, $batchCamp, $totalPeserta)
    {
        $this->dispatch('will-delete-batch-pemesanan', [
            'nama_camp' => $namaCamp,
            'batch_camp' => $batchCamp,
            'total_peserta' => $totalPeserta,
        ]);
    }

    #[On('delete-batch-pemesanan')]
    public function deleteBatch($nama_camp, $batch_camp)
    {
        if (! auth()->user()->hasPermission('delete_pesananrsc')) {
            $this->dispatch('batch-delete-error', message: 'Anda tidak memiliki izin menghapus data pesanan RSC.');

            return;
        }

        DB::beginTransaction();
        try {
            $pemesananList = PemesananRsc::where('nama_camp', $nama_camp)
                ->where('batch_camp', $batch_camp)
                ->get();

            if ($pemesananList->isEmpty()) {
                $this->dispatch('batch-delete-error', message: 'Data tidak ditemukan!');

                return;
            }

            $action = new SyncCashFlowAction;

            foreach ($pemesananList as $pemesanan) {
                $action->delete($pemesanan);
            }

            PemesananRsc::where('nama_camp', $nama_camp)
                ->where('batch_camp', $batch_camp)
                ->delete();

            DB::commit();

            $this->dispatch('batch-deleted', message: 'Batch berhasil dihapus beserta '.$pemesananList->count().' peserta.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('batch-delete-error', message: 'Gagal menghapus batch: '.$e->getMessage());
        }
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $query = PemesananRsc::query()
            ->select([
                'nama_camp',
                'batch_camp',
                'tanggal_mulai_camp',
                'tanggal_akhir_camp',
                'akun',
                'pic',
                'status',
                DB::raw('MIN(id) as first_id'),
                DB::raw('COUNT(*) as total_peserta'),
                DB::raw('GROUP_CONCAT(DISTINCT nama_pembeli SEPARATOR ", ") as nama_pembeli_list'),
                DB::raw('SUM(CAST(total as DECIMAL(15,2))) as total_harga'),
            ])
            ->with(['dataakun', 'users'])
            ->groupBy([
                'nama_camp',
                'batch_camp',
                'tanggal_mulai_camp',
                'tanggal_akhir_camp',
                'akun',
                'pic',
                'status',
            ]);

        // 🔍 Filter: Pencarian umum (mencari SEMUA data).
        // Memakai subquery per-batch agar batch tetap tampil utuh (jumlah peserta
        // & daftar nama tidak terpotong) meski yang cocok hanya satu peserta.
        if (! empty($this->search)) {
            $search = '%'.$this->search.'%';

            $query->whereIn(DB::raw("CONCAT(nama_camp, '|', batch_camp)"), function ($sub) use ($search) {
                $sub->select(DB::raw("CONCAT(pr.nama_camp, '|', pr.batch_camp)"))
                    ->from('pemesanan_rsc as pr')
                    ->leftJoin('users as u', 'u.id', '=', 'pr.pic')
                    ->leftJoin('data_akuns as da', 'da.id', '=', 'pr.akun')
                    ->where(function ($q) use ($search) {
                        $q->where('pr.nama_camp', 'like', $search)
                            ->orWhere('pr.batch_camp', 'like', $search)
                            ->orWhere('pr.id_transaksi', 'like', $search)
                            ->orWhere('pr.nama_pembeli', 'like', $search)
                            ->orWhere('pr.telp_pembeli', 'like', $search)
                            ->orWhere('pr.jumlah_pemesanan', 'like', $search)
                            ->orWhere('pr.status', 'like', $search)
                            ->orWhere('pr.deskripsi', 'like', $search)
                            ->orWhere('pr.harga_satuan', 'like', $search)
                            ->orWhere('pr.total', 'like', $search)
                            ->orWhere('pr.username', 'like', $search)
                            ->orWhere('pr.link_akses', 'like', $search)
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_mulai_camp, '%d %M %Y') LIKE ?", [$search])
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_akhir_camp, '%d %M %Y') LIKE ?", [$search])
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_pemesanan, '%d %M %Y') LIKE ?", [$search])
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_berakhir, '%d %M %Y') LIKE ?", [$search])
                            ->orWhere('u.name', 'like', $search)
                            ->orWhere('da.nama_akun', 'like', $search);
                    });
            });
        }

        // 🔹 Filter berdasarkan akun
        if (! empty($this->akunFilter)) {
            $query->where('akun', $this->akunFilter);
        }

        // 🔹 Filter berdasarkan tanggal
        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $query->whereBetween('tanggal_mulai_camp', [$this->startDate, $this->endDate]);
        } elseif (! empty($this->startDate)) {
            $query->whereDate('tanggal_mulai_camp', '>=', $this->startDate);
        } elseif (! empty($this->endDate)) {
            $query->whereDate('tanggal_akhir_camp', '<=', $this->endDate);
        }

        // 🔹 Filter periode (bulan/tahun) berdasarkan tanggal mulai camp
        if (! empty($this->filterMonth)) {
            $query->whereMonth('tanggal_mulai_camp', $this->filterMonth);
        }
        if (! empty($this->filterYear)) {
            $query->whereYear('tanggal_mulai_camp', $this->filterYear);
        }

        // 🔹 Filter status
        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // 🔹 Filter berdasarkan kategori
        if (! empty($this->kategoriFilter)) {
            $query->where('nama_camp', $this->kategoriFilter);
        }

        // 🔹 Filter berdasarkan batch
        if (! empty($this->batchFilter)) {
            $query->where('batch_camp', $this->batchFilter);
        }

        // 🔹 Ambil hasil
        $pemesananrsc = $query->latest('tanggal_mulai_camp')->paginate($this->perPage);

        // 🔹 Data dropdown periode (seragam dengan Pesanan Toko)
        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM'),
        ]);

        $years = PemesananRsc::selectRaw('YEAR(tanggal_mulai_camp) as tahun')
            ->whereNotNull('tanggal_mulai_camp')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-list', compact(
            'pemesananrsc',
            'months',
            'years',
        ));
    }
}
