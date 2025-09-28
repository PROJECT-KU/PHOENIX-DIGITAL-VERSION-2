<?php

namespace App\Livewire\Pages\Admin\Loan;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\User;
use App\Models\Loan;

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
            $query->where(function ($q) {
                $q->where('nama_peminjam', 'like', '%' . $this->search . '%')
                    ->orWhere('deskripsi', 'like', '%' . $this->search . '%')
                    ->orWhereHas('penginput', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
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

        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = [Loan::STATUS_PENDING, Loan::STATUS_BERJALAN, Loan::STATUS_LUNAS];

        return view('livewire.pages.admin.loan.loan-list', compact('loans', 'users', 'statusOptions'));
    }
}
