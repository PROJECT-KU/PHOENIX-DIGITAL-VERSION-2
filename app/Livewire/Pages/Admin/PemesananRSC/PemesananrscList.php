<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Models\PemesananRsc;
use App\Models\User;
use App\Models\DataAkun;
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
    public function deletepemesananrsc($id)
    {
        $pemesananrsc = PemesananRsc::find($id);

        if (!$pemesananrsc) {
            $this->dispatch('delete-error', ['message' => 'Data tidak ditemukan!'], browserEvent: true);
            return;
        }

        $pemesananrsc->delete();

        $this->dispatch('pemesananrsc-deleted', ['id' => $id], browserEvent: true);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        // ✅ Include relasi agar bisa panggil dataakun->nama_akun
        $query = PemesananRsc::with(['dataakun', 'users']);

        // 🔍 Filter: Pencarian umum
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $search = '%' . $this->search . '%';

                $q->where('deskripsi', 'like', $search)
                    ->orWhere('id_transaksi', 'like', $search)
                    ->orWhere('nama_camp', 'like', $search)
                    ->orWhere('batch_camp', 'like', $search)
                    ->orWhereRaw("DATE_FORMAT(tanggal_mulai_camp, '%d %M %Y') LIKE ?", [$search])
                    ->orWhereRaw("DATE_FORMAT(tanggal_mulai_camp, '%M %Y') LIKE ?", [$search]) // Bisa cari "Juni 2024"
                    ->orWhereRaw("DATE_FORMAT(tanggal_mulai_camp, '%Y') LIKE ?", [$search])   // Bisa cari "2024"
                    ->orWhereRaw("DATE_FORMAT(tanggal_akhir_camp, '%d %M %Y') LIKE ?", [$search])
                    ->orWhereRaw("DATE_FORMAT(tanggal_akhir_camp, '%M %Y') LIKE ?", [$search]) // Bisa cari "Juni 2024"
                    ->orWhereRaw("DATE_FORMAT(tanggal_akhir_camp, '%Y') LIKE ?", [$search])   // Bisa cari "2024"
                    ->orWhere('nama_pembeli', 'like', $search)
                    ->orWhere('telp_pembeli', 'like', $search)
                    ->orWhere('jumlah_pemesanan', 'like', $search)
                    ->orWhereRaw("DATE_FORMAT(tanggal_pemesanan, '%d %M %Y') LIKE ?", [$search])
                    ->orWhereRaw("DATE_FORMAT(tanggal_pemesanan, '%M %Y') LIKE ?", [$search]) // Bisa cari "Juni 2024"
                    ->orWhereRaw("DATE_FORMAT(tanggal_pemesanan, '%Y') LIKE ?", [$search])   // Bisa cari "2024"
                    ->orWhereRaw("DATE_FORMAT(tanggal_berakhir, '%d %M %Y') LIKE ?", [$search])
                    ->orWhereRaw("DATE_FORMAT(tanggal_berakhir, '%M %Y') LIKE ?", [$search]) // Bisa cari "Juni 2024"
                    ->orWhereRaw("DATE_FORMAT(tanggal_berakhir, '%Y') LIKE ?", [$search])   // Bisa cari "2024"
                    ->orWhere('username', 'like', $search)
                    ->orWhere('password', 'like', $search)
                    ->orWhere('link_akses', 'like', $search)
                    ->orWhere('harga_satuan', 'like', $search)
                    ->orWhere('total', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhereHas('users', fn($q) => $q->where('name', 'like', $search))
                    ->orWhereHas('dataakun', fn($q) => $q->where('nama_akun', 'like', $search));
            });
        }

        // 🔹 Filter berdasarkan akun
        if (!empty($this->akunFilter)) {
            $query->where('akun', $this->akunFilter);
        }

        // 🔹 Filter berdasarkan tanggal
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('tanggal_pemesanan', [$this->startDate, $this->endDate]);
        } elseif (!empty($this->startDate)) {
            $query->whereDate('tanggal_pemesanan', '>=', $this->startDate);
        } elseif (!empty($this->endDate)) {
            $query->whereDate('tanggal_berakhir', '<=', $this->endDate);
        }

        // 🔹 Filter status
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // 🔹 Filter nama pembeli
        if (!empty($this->pembeliFilter)) {
            $query->where('nama_pembeli', $this->pembeliFilter);
        }

        // 🔹 Filter berdasarkan kategori
        if (!empty($this->kategoriFilter)) {
            $query->where('nama_camp', $this->kategoriFilter);
        }

        // 🔹 Filter berdasarkan batch
        if (!empty($this->batchFilter)) {
            $query->where('batch_camp', $this->batchFilter);
        }

        // 🔹 Ambil hasil
        $pemesananrsc = $query->latest()->paginate($this->perPage);

        // 🔹 Data dropdown
        $users = User::select('id', 'name')->orderBy('name')->get();
        $dataakun = DataAkun::select('id', 'nama_akun')->orderBy('nama_akun')->get();
        $statusOptions = ['habis', 'pengganti', 'perpanjang', 'baru'];
        $jenisPengeluaranOptions = ['pembelian_akun', 'lainnya'];
        $pembeliList = PemesananRsc::select('nama_pembeli')->distinct()->orderBy('nama_pembeli')->pluck('nama_pembeli');
        $kategoriList = PemesananRsc::select('nama_camp')->distinct()->whereNotNull('nama_camp')->orderBy('nama_camp')->pluck('nama_camp');
        $batchList = PemesananRsc::select('batch_camp')->distinct()->whereNotNull('batch_camp')->orderBy('batch_camp')->pluck('batch_camp');

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-list', compact(
            'pemesananrsc',
            'users',
            'statusOptions',
            'jenisPengeluaranOptions',
            'dataakun',
            'pembeliList',
            'kategoriList',
            'batchList'
        ));
    }
}
