<?php

namespace App\Livewire\Pages\Admin\Spending;

use App\Models\User;
use Livewire\Component;
use App\Models\Spending;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Exports\SpendingExport;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class SpendingList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $penginputFilter = '';
    public $picPembeliFilter = '';
    public $jenisPengeluaran = '';

    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'penginputFilter' => ['except' => ''],
        'picPembeliFilter' => ['except' => ''],
        'jenisPengeluaran' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // method filtering
    public function updatingSearch()
    {
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
    public function updatingPenginputFilter()
    {
        $this->resetPage();
    }
    public function updatingPicPembeliFilter()
    {
        $this->resetPage();
    }
    public function updatingJenisPengeluaran()
    {
        $this->resetPage();
    }
    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->penginputFilter = '';
        $this->picPembeliFilter = '';
        $this->jenisPengeluaran = '';
        $this->resetPage();
    }

    #[On('delete-spending-data')]
    public function delete($id)
    {
        try {
            $spending = Spending::findOrFail($id);
            $spending->delete();

            $this->dispatch('show-alert', [
                'type' => 'success',
                'message' => 'Berhasil menghapus data pengeluaran'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Gagal menghapus data pengeluaran'
            ]);
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Spending::with(['penginput', 'picPembeli']);

        // default jenisPengeluaran kalau belum dipilih
        if (empty($this->jenisPengeluaran)) {
            $this->jenisPengeluaran = 'lainnya';
        }

        // Filter berdasarkan jenis pengeluaran
        if ($this->jenisPengeluaran === 'pembelian_akun') {
            $query->where('jenis_pengeluaran', 'pembelian_akun');
        } else {
            $query->where('jenis_pengeluaran', 'lainnya');
        }

        // Search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('deskripsi', 'like', '%' . $this->search . '%')
                    ->orWhere('nominal', 'like', '%' . $this->search . '%')
                    ->orWhere('id_transaksi', 'like', '%' . $this->search . '%')
                    ->orWhereHas('penginput', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('picPembeli', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if (!empty($this->statusFilter)) {
            $query->byStatus($this->statusFilter);
        }
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->byDateRange($this->startDate, $this->endDate);
        }
        if (!empty($this->penginputFilter)) {
            $query->byPenginput($this->penginputFilter);
        }
        if (!empty($this->picPembeliFilter)) {
            $query->byPicPembeli($this->picPembeliFilter);
        }
        if (!empty($this->jenisPengeluaran)) {
            $query->byJenisPengeluaran($this->jenisPengeluaran);
        }

        $spendings = $query->orderBy('tanggal_transaksi', 'desc')
            ->paginate($this->perPage);
        
        // Ambil total pengeluaran per jenis
        $totalSpendings = Spending::select(
                'jenis_pengeluaran as jenisPengeluaran',
                DB::raw('SUM(nominal) as total_pengeluaran')
            )
            ->groupBy('jenis_pengeluaran')
            ->get();

        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['pending', 'completed'];
        $jenisPengeluaranOptions = ['pembelian_akun', 'lainnya'];

        return view('livewire.pages.admin.spending.spending-list', compact('spendings', 'users', 'statusOptions', 'jenisPengeluaranOptions', 'totalSpendings'));
    }

    public function exportExcel()
    {
        try {
            $jenis = $this->jenisPengeluaran ?? null;
            $start = $this->startDate ?? null;
            $end = $this->endDate ?? null;

            $filename = 'pengeluaran_' . ($jenis ?? 'semua') . '_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new SpendingExport($jenis, $start, $end), $filename);

        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Gagal mengekspor data: ' . $e->getMessage()
            ]);
        }
    }
}
