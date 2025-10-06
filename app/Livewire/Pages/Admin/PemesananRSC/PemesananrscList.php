<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Models\PemesananRsc;
use App\Models\User;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PemesananrscList extends Component
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

    #[On('delete-pemesananrsc-data')]
    public function delete($id)
    {
        try {
            $pemesananrsc = PemesananRsc::findOrFail($id);
            $pemesananrsc->delete();

            $this->dispatch('success-delete-pemesanarsc');
        } catch (\Exception $e) {
            $this->dispatch('failed-delete-pemesanarsc');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = PemesananRsc::with(['penginput', 'picPembeli']);

        // Search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('deskripsi', 'like', '%' . $this->search . '%')
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

        $pemesananrsc = $query->orderBy('tanggal_transaksi', 'desc')
            ->paginate($this->perPage);

        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['pending', 'completed'];
        $jenisPengeluaranOptions = ['pembelian_akun', 'lainnya'];

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-list', compact('pemesananrsc', 'users', 'statusOptions', 'jenisPengeluaranOptions'));
    }
}
