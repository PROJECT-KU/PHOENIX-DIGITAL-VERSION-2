<?php

namespace App\Livewire\Pages\Admin\GajiKaryawans;

use App\Exports\GajiKaryawansExport;
use App\Models\GajiKaryawans;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class GajiKaryawansList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $karyawanFilter = '';
    public $idtransaksiFilter = '';
    public $norekFilter = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'karyawanFilter' => ['except' => ''],
        'idtransaksiFilter' => ['except' => ''],
        'norekFilter' => ['except' => ''],
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
    public function updatingKaryawanFilter()
    {
        $this->resetPage();
    }
    public function updatingIDTransaksiFilter()
    {
        $this->resetPage();
    }
    public function updatingNorekFilter()
    {
        $this->resetPage();
    }
    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->karyawanFilter = '';
        $this->idtransaksiFilter = '';
        $this->norekFilter = '';
        $this->resetPage();
    }

    public function deletegajikaryawan($id)
    {
        $gajikaryawan = GajiKaryawans::find($id);

        if (!$gajikaryawan) {
            $this->dispatch('delete-error', ['message' => 'Data tidak ditemukan!'], browserEvent: true);
            return;
        }

        $gajikaryawan->delete();

        $this->dispatch('gajikaryawan-deleted', ['id' => $id], browserEvent: true);
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total ?? 0, 0, ',', '.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = GajiKaryawans::with(['karyawan']);

        // Search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $search = '%' . $this->search . '%'; // ✅ definisikan variabel lokal
                $q->where('deskripsi', 'like', $search)
                    ->orWhere('id_transaksi', 'like', $search)
                    ->orWhereHas('karyawan', function ($q) use ($search) {
                        $q->where('name', 'like', $search);
                    })
                    ->orWhere('bank', 'like', $search)
                    ->orWhere('no_rek', 'like', $search)
                    ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%d %M %Y') LIKE ?", [$search])
                    ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%M %Y') LIKE ?", [$search]) // Bisa cari "Juni 2024"
                    ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%Y') LIKE ?", [$search])   // Bisa cari "2024"
                    ->orWhere('gaji_pokok', 'like', $search)
                    ->orWhere('bonus_kinerja', 'like', $search)
                    ->orWhere('bonus_lainnya', 'like', $search)
                    ->orWhere('tunjangan_kesehatan', 'like', $search)
                    ->orWhere('tunjangan_thr', 'like', $search)
                    ->orWhere('tunjangan_ketenagakerjaan', 'like', $search)
                    ->orWhere('tunjangan_lainnya', 'like', $search)
                    ->orWhere('potongan', 'like', $search)
                    ->orWhere('pph21', 'like', $search)
                    ->orWhere('total', 'like', $search)
                    ->orWhere('deskripsi', 'like', $search)
                    ->orWhere('status', 'like', $search);
            });
        }

        if (!empty($this->statusFilter)) {
            $query->byStatus($this->statusFilter);
        }
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->byDateRange($this->startDate, $this->endDate);
        }
        if (!empty($this->karyawanFilter)) {
            $query->byPenginput($this->karyawanFilter);
        }
        if (!empty($this->idtransaksiFilter)) {
            $query->byIDTransaksi($this->idtransaksiFilter);
        }
        if (!empty($this->norekFilter)) {
            $query->byNorek($this->norekFilter);
        }

        $gajikaryawan = $query->orderBy('tanggal_transaksi', 'desc')
            ->paginate($this->perPage);

        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['pending', 'completed'];

        $idTransaksiOptions = GajiKaryawans::select('id_transaksi')
            ->distinct()
            ->orderBy('id_transaksi', 'asc')
            ->pluck('id_transaksi');

        $norekOptions = GajiKaryawans::select('no_rek')
            ->distinct()
            ->orderBy('no_rek', 'asc')
            ->pluck('no_rek');

        return view('livewire.pages.admin.gaji-karyawans.gaji-karyawans-list', [
            'gajikaryawan' => $gajikaryawan,
            'users' => $users,
            'statusOptions' => $statusOptions,
            'idTransaksiOptions' => $idTransaksiOptions,
            'norekOptions' => $norekOptions,
        ]);
    }

    public function exportExcel()
    {
        try {
            $status = $this->status ?? null;
            $start = $this->startDate ?? null;
            $end = $this->endDate ?? null;

            $filename = 'gaji_karyawan_' . ($status ?? 'semua') . '_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new GajiKaryawansExport($status, $start, $end), $filename);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Gagal mengekspor data: ' . $e->getMessage()
            ]);
        }
    }
}
