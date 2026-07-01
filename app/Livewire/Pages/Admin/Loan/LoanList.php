<?php

namespace App\Livewire\Pages\Admin\Loan;

use App\Livewire\Concerns\MergesPinjamanData;
use Livewire\Component;
use Livewire\WithPagination;

class LoanList extends Component
{
    use MergesPinjamanData, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $bulan = '';

    public $tahun = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'bulan' => ['except' => ''],
        'tahun' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        // Default ke periode bulan & tahun berjalan (seperti spending)
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingBulan()
    {
        $this->resetPage();
    }

    public function updatingTahun()
    {
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->bulan = '';
        $this->tahun = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.pages.admin.loan.loan-list', [
            'rows' => $this->buildMergedRows('peminjaman'),
            'totalLoans' => $this->buildTotalLoans(),
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ])->layout('livewire.layout.templateindex');
    }

    public function downloadPdf()
    {
        return $this->generatePdf('peminjaman');
    }
}
