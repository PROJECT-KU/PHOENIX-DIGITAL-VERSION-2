<?php

namespace App\Livewire\Pages\Admin\Pengembalian;

use App\Models\Pengembalian;
use Livewire\Component;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Exports\PengembalianExport;
use Maatwebsite\Excel\Facades\Excel;

class PengembalianList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $penginputFilter = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'penginputFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // Reset pagination setiap kali filter berubah
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

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->penginputFilter = '';
        $this->resetPage();
    }

    public function delete($id)
    {
        try {
            $pengembalian = Pengembalian::findOrFail($id);
            $pengembalian->delete();

            $this->dispatch('pengembalian-deleted');
        } catch (\Exception $e) {
            $this->dispatch('delete-pengembalian-error', message: 'Terjadi kesalahan saat menghapus pengembalian!');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Pengembalian::with('penginput')
            ->select('pengembalians.*')
            ->selectRaw('(SELECT SUM(nominal) 
                        FROM pengembalians p2 
                        WHERE p2.nama_pengembalian = pengembalians.nama_pengembalian) as total_borrower_loan');

        // Search
        if (!empty($this->search)) {
            $searchDate = null;
            if (strtotime($this->search)) {
                $searchDate = date('Y-m-d', strtotime(str_replace('/', '-', $this->search)));
            }

            $query->where(function ($q) use ($searchDate) {
                $q->where('nama_pengembalian', 'like', '%' . $this->search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $this->search . '%')
                    ->orWhere('id_transaksi', 'like', '%' . $this->search . '%')
                    ->orWhere('nominal', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
                    ->orWhereHas('penginput', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });

                if ($searchDate) {
                    $q->orWhereDate('tanggal_pengembalian', $searchDate);
                }
            });
        }

        // Filter
        if (!empty($this->statusFilter)) {
            $query->byStatus($this->statusFilter);
        }
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->byDateRange($this->startDate, $this->endDate);
        }
        if (!empty($this->penginputFilter)) {
            $query->byPenginput($this->penginputFilter);
        }

        $pengembalian = $query->orderBy('tanggal_pengembalian', 'desc')
            ->paginate($this->perPage);

        // Total pengembalian per nama_pengembalian
        $totalLoans = Pengembalian::select('nama_pengembalian', DB::raw('SUM(nominal) as total'))
            ->groupBy('nama_pengembalian')
            ->get();

        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = [Pengembalian::STATUS_PENDING, Pengembalian::STATUS_BERJALAN, Pengembalian::STATUS_LUNAS];

        return view('livewire.pages.admin.pengembalian.pengembalian-list', compact('pengembalian', 'users', 'statusOptions', 'totalLoans'));
    }

    public function exportExcel()
    {
        try {
            $filename = 'pengembalian_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new PengembalianExport(), $filename);
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }
}
