<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\DataAkun;
use App\Models\PemesananRsc;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PemesananrscList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // 🔹 State/Filter properties
    public $search = '';

    public $statusFilter = '';

    public $startDate = '';

    public $endDate = '';

    public $pembeliFilter = '';

    public $kategoriFilter = '';

    public $batchFilter = '';

    public $perPage = 10;

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
        DB::beginTransaction();
        try {
            $pemesananList = PemesananRsc::where('nama_camp', $nama_camp)
                ->where('batch_camp', $batch_camp)
                ->get();

            if ($pemesananList->isEmpty()) {
                session()->flash('error', 'Data tidak ditemukan!');

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

            session()->flash('success', 'Batch berhasil dihapus beserta '.$pemesananList->count().' peserta!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus batch: '.$e->getMessage());
        }
    }

    #[Layout('layouts.app')]
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
                DB::raw('MIN(id) as first_id'), // Ambil ID pertama untuk link edit
                DB::raw('COUNT(*) as total_peserta'), // Hitung jumlah peserta
                DB::raw('GROUP_CONCAT(DISTINCT nama_pembeli SEPARATOR ", ") as nama_pembeli_list'), // Optional: list nama
                DB::raw('SUM(CAST(total as DECIMAL(15,2))) as total_harga'), // Total harga per batch
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

        // 🔍 Filter: Pencarian umum
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $search = '%'.$this->search.'%';

                $q->where('nama_camp', 'like', $search)
                    ->orWhere('batch_camp', 'like', $search)
                    ->orWhereRaw("DATE_FORMAT(tanggal_mulai_camp, '%d %M %Y') LIKE ?", [$search])
                    ->orWhereRaw("DATE_FORMAT(tanggal_akhir_camp, '%d %M %Y') LIKE ?", [$search])
                    ->orWhereHas('users', fn ($q) => $q->where('name', 'like', $search))
                    ->orWhereHas('dataakun', fn ($q) => $q->where('nama_akun', 'like', $search));
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

        // 🔹 Data dropdown
        $users = User::select('id', 'name')->orderBy('name')->get();
        $dataakun = DataAkun::select('id', 'nama_akun')->orderBy('nama_akun')->get();
        $statusOptions = ['habis', 'pengganti', 'perpanjang', 'baru'];
        $jenisPengeluaranOptions = ['pembelian_akun', 'lainnya'];
        $kategoriList = PemesananRsc::select('nama_camp')->distinct()->whereNotNull('nama_camp')->orderBy('nama_camp')->pluck('nama_camp');
        $batchList = PemesananRsc::select('batch_camp')->distinct()->whereNotNull('batch_camp')->orderBy('batch_camp')->pluck('batch_camp');

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-list', compact(
            'pemesananrsc',
            'users',
            'statusOptions',
            'jenisPengeluaranOptions',
            'dataakun',
            'kategoriList',
            'batchList'
        ));
    }
}
