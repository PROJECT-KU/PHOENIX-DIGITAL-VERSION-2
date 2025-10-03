<?php

namespace App\Livewire\Pages\Admin\GajiKaryawans;

use App\Models\GajiKaryawans;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

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
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'karyawanFilter' => ['except' => ''],
        'idtransaksiFilter' => ['except' => ''],
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
    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->karyawanFilter = '';
        $this->idtransaksiFilter = '';
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
                $q->where('deskripsi', 'like', '%' . $this->search . '%')
                    ->orWhere('id_transaksi', 'like', '%' . $this->search . '%')
                    ->orWhereHas('karyawan', function ($q) {
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
        if (!empty($this->karyawanFilter)) {
            $query->byPenginput($this->karyawanFilter);
        }
        if (!empty($this->idtransaksiFilter)) {
            $query->byIDTransaksi($this->idtransaksiFilter);
        }

        $gajikaryawan = $query->orderBy('tanggal_transaksi', 'desc')
            ->paginate($this->perPage);

        $users = User::select('id', 'name')->orderBy('name')->get();
        $statusOptions = ['pending', 'completed'];

        $idTransaksiOptions = GajiKaryawans::select('id_transaksi')
            ->distinct()
            ->orderBy('id_transaksi', 'asc')
            ->pluck('id_transaksi');

        return view('livewire.pages.admin.gaji-karyawans.gaji-karyawans-list', [
            'gajikaryawan' => $gajikaryawan,
            'users' => $users,
            'statusOptions' => $statusOptions,
            'idTransaksiOptions' => $idTransaksiOptions,
        ]);
    }
}
