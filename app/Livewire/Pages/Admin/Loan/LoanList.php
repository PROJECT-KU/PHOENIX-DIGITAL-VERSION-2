<?php

namespace App\Livewire\Pages\Admin\Loan;

use App\Models\Loan;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Exports\LoanExport;
use Maatwebsite\Excel\Facades\Excel;

class LoanList extends Component
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

    // Reset pagination tiap kali filter berubah
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
            $loan = Loan::findOrFail($id);
            $loan->delete();

            $this->dispatch('loan-deleted');
        } catch (\Exception $e) {
            $this->dispatch('delete-loan-error', message: 'Terjadi kesalahan saat menghapus loan!');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Loan::with('penginput')
            ->select('loans.*')
            ->selectRaw('(SELECT SUM(nominal) 
                        FROM loans l2 
                        WHERE l2.nama_peminjam = loans.nama_peminjam) as total_borrower_loan');

        // Search
        if (!empty($this->search)) {
            $searchDate = null;
            if (strtotime($this->search)) {
                $searchDate = date('Y-m-d', strtotime(str_replace('/', '-', $this->search)));
            }

            $query->where(function ($q) use ($searchDate) {
                $q->where('nama_peminjam', 'like', '%' . $this->search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $this->search . '%')
                    ->orWhere('id_transaksi', 'like', '%' . $this->search . '%')
                    ->orWhere('nominal', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
                    ->orWhereHas('penginput', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });

                if ($searchDate) {
                    $q->orWhereDate('tanggal_peminjam', $searchDate);
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

        $loans = $query->orderBy('tanggal_peminjam', 'desc')
            ->paginate($this->perPage);
            
        // Gabungan total pinjaman - pengembalian
        $totalLoans = DB::table('loans')
            ->select('nama_peminjam',
                DB::raw('SUM(loans.nominal) as total_pinjaman'),
                DB::raw('(SELECT COALESCE(SUM(pengembalians.nominal), 0)
                        FROM pengembalians
                        WHERE pengembalians.nama_pengembalian = loans.nama_peminjam) as total_pengembalian'),
                DB::raw('(SUM(loans.nominal) - 
                        (SELECT COALESCE(SUM(pengembalians.nominal), 0)
                        FROM pengembalians
                        WHERE pengembalians.nama_pengembalian = loans.nama_peminjam)) as sisa_peminjaman')
            )
            ->groupBy('nama_peminjam')
            ->get();

        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = [Loan::STATUS_PENDING, Loan::STATUS_BERJALAN, Loan::STATUS_LUNAS];

        return view('livewire.pages.admin.loan.loan-list', compact('loans', 'users', 'statusOptions', 'totalLoans'));
    }

    public function exportExcel()
    {
        try {
            $filename = 'data_peminjaman_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new LoanExport, $filename);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Gagal mengekspor data: ' . $e->getMessage()
            ]);
        }
    }
}
