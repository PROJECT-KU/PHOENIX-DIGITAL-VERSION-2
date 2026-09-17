<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Actions\Finance\SyncCashFlowAction;
use App\Exports\CampBatchExport;
use App\Models\PemesananRsc;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PemesananrscList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // 🔹 State/Filter properties
    public $search = '';

    // Filter periode (seragam dengan Pesanan Toko).
    public $filterMonth = '';

    public $filterYear = '';

    public $statusFilter = '';

    public $startDate = '';

    public $endDate = '';

    public $pembeliFilter = '';

    public $kategoriFilter = '';

    public $batchFilter = '';

    // Saringan akun utama & PIC. $akunFilter sudah dirujuk saring() sejak
    // lama tetapi propertinya tidak pernah ada.
    public $akunFilter = '';

    public $picFilter = '';

    /** Masa akun: '' | 'segera' (≤ BATAS_SEGERA hari lagi) | 'lewat'. */
    public $masaFilter = '';

    public $urut = 'dibuat';

    public $arahUrut = 'desc';

    /** Akun dianggap "segera berakhir" bila berakhir dalam sekian hari. */
    public const BATAS_SEGERA = 7;

    /** Kolom yang boleh dipakai mengurutkan => ekspresi di kueri per batch. */
    protected const URUTAN = [
        'dibuat' => 'batch_created',
        'mulai' => 'tanggal_mulai_camp',
        'berakhir' => 'akun_berakhir',
        'peserta' => 'total_peserta',
        'total' => 'total_harga',
    ];

    public $perPage = 10;

    // property export data
    public $showExportModal = false;

    public $searchBatchExport = '';

    public $selectedBatches = [];

    public function openExportModal()
    {
        $this->reset(['searchBatchExport', 'selectedBatches']);
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    public function getAvailableBatchesForExportProperty()
    {
        return PemesananRsc::select('nama_camp', 'batch_camp')
            ->distinct()
            ->when($this->searchBatchExport, function ($q) {
                $q->where('nama_camp', 'like', '%'.$this->searchBatchExport.'%')
                    ->orWhere('batch_camp', 'like', '%'.$this->searchBatchExport.'%');
            })
            ->orderBy('nama_camp')
            ->orderBy('batch_camp', 'desc')
            ->get()
            ->map(function ($item) {
                $item->key = $item->nama_camp.'|'.$item->batch_camp;

                return $item;
            });
    }

    public function exportExcel()
    {
        $this->validate(['selectedBatches' => 'required|array|min:1'], ['selectedBatches.required' => 'Pilih minimal satu batch untuk di export']);

        $this->showExportModal = false;
        $fileName = 'Export_Peserta_camp_'.date('Y-m-d_H-i').'.xlsx';

        return Excel::download(new CampBatchExport($this->selectedBatches), $fileName);
    }

    public function exportInvoice()
    {
        $this->validate(['selectedBatches' => 'required|array|min:1'], ['selectedBatches.required' => 'Pilih minimal satu batch untuk di buatkan invoice']);

        $this->showExportModal = false;

        // Sumber tunggal item invoice (sama dgn preview) — termasuk daftar akun
        // untuk batch metode "per_akun".
        $invoiceItems = PemesananRsc::invoiceItemsFor($this->selectedBatches);

        $data = [
            'invoiceNumber' => 'INV-'.date('Y').'-'.rand(1000, 9999),
            'date' => now()->translatedFormat('d F Y'),
            'items' => $invoiceItems,
            'grandTotal' => $invoiceItems->sum('total_harga'),
        ];

        $pdf = Pdf::loadView('exports.invoice-pdf', $data);

        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Invoice_RumahScopus_'.date('Y-m-d').'.pdf');
    }

    // 🔹 URL query sync
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'pembeliFilter' => ['except' => ''],
        'kategoriFilter' => ['except' => ''],
        'batchFilter' => ['except' => ''],
        'akunFilter' => ['except' => '', 'as' => 'akun'],
        'picFilter' => ['except' => '', 'as' => 'pic'],
        'masaFilter' => ['except' => '', 'as' => 'masa'],
        'urut' => ['except' => 'dibuat'],
        'arahUrut' => ['except' => 'desc', 'as' => 'arah'],
        'page' => ['except' => 1],
    ];

    // 🔹 Reset page saat filter berubah
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    // Reset filter periode (seragam dengan Pesanan Toko).
    public function resetFilters()
    {
        $this->reset(['search', 'filterMonth', 'filterYear', 'statusFilter', 'akunFilter', 'picFilter', 'masaFilter']);
        $this->resetPage();
    }

    public function updatingAkunFilter()
    {
        $this->resetPage();
    }

    public function updatingPicFilter()
    {
        $this->resetPage();
    }

    public function updatingMasaFilter()
    {
        $this->resetPage();
    }

    /** Kartu "segera berakhir" bisa diklik: menyaring, klik lagi melepas. */
    public function saringMasa(string $masa): void
    {
        $this->masaFilter = $this->masaFilter === $masa ? '' : $masa;
        $this->resetPage();
    }

    /** Klik judul kolom: kolom baru mulai dari besar→kecil, kolom sama dibalik. */
    public function urutkan(string $kolom): void
    {
        if (! array_key_exists($kolom, self::URUTAN)) {
            return;
        }

        if ($this->urut === $kolom) {
            $this->arahUrut = $this->arahUrut === 'asc' ? 'desc' : 'asc';
        } else {
            $this->urut = $kolom;
            $this->arahUrut = $kolom === 'mulai' || $kolom === 'berakhir' ? 'asc' : 'desc';
        }

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
        if (! auth()->user()->hasPermission('delete_pesananrsc')) {
            $this->dispatch('batch-delete-error', message: 'Anda tidak memiliki izin menghapus data pesanan RSC.');

            return;
        }

        DB::beginTransaction();
        try {
            $pemesananList = PemesananRsc::where('nama_camp', $nama_camp)
                ->where('batch_camp', $batch_camp)
                ->get();

            if ($pemesananList->isEmpty()) {
                $this->dispatch('batch-delete-error', message: 'Data tidak ditemukan!');

                return;
            }

            $action = new SyncCashFlowAction;
            $modalAction = app(\App\Actions\Finance\SyncRscPrivateCostAction::class);

            foreach ($pemesananList as $pemesanan) {
                $action->delete($pemesanan);
                // Hapus juga baris modal private (bila ada) supaya tidak jadi
                // cash flow yatim setelah baris pemesanan dihapus.
                $modalAction->delete($pemesanan);
            }

            PemesananRsc::where('nama_camp', $nama_camp)
                ->where('batch_camp', $batch_camp)
                ->delete();

            DB::commit();

            $this->dispatch('batch-deleted', message: 'Batch berhasil dihapus beserta '.$pemesananList->count().' peserta.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('batch-delete-error', message: 'Gagal menghapus batch: '.$e->getMessage());
        }
    }

    /**
     * Seluruh saringan daftar di SATU tempat.
     *
     * Dipakai bersama oleh tabel dan oleh kartu ringkasan di atasnya, supaya
     * angka ringkasan selalu menceritakan baris yang sama dengan yang terlihat
     * — bukan hasil kueri kedua yang kebetulan mirip.
     */
    protected function saring($query): void
    {
        // 🔍 Filter: Pencarian umum (mencari SEMUA data).
        // Memakai subquery per-batch agar batch tetap tampil utuh (jumlah peserta
        // & daftar nama tidak terpotong) meski yang cocok hanya satu peserta.
        if (! empty($this->search)) {
            $search = '%'.$this->search.'%';

            $query->whereIn(DB::raw("CONCAT(nama_camp, '|', batch_camp)"), function ($sub) use ($search) {
                $sub->select(DB::raw("CONCAT(pr.nama_camp, '|', pr.batch_camp)"))
                    ->from('pemesanan_rsc as pr')
                    ->leftJoin('users as u', 'u.id', '=', 'pr.pic')
                    ->leftJoin('data_akuns as da', 'da.id', '=', 'pr.akun')
                    ->where(function ($q) use ($search) {
                        $q->where('pr.nama_camp', 'like', $search)
                            ->orWhere('pr.batch_camp', 'like', $search)
                            ->orWhere('pr.id_transaksi', 'like', $search)
                            ->orWhere('pr.nama_pembeli', 'like', $search)
                            ->orWhere('pr.telp_pembeli', 'like', $search)
                            ->orWhere('pr.jumlah_pemesanan', 'like', $search)
                            ->orWhere('pr.status', 'like', $search)
                            ->orWhere('pr.deskripsi', 'like', $search)
                            ->orWhere('pr.harga_satuan', 'like', $search)
                            ->orWhere('pr.total', 'like', $search)
                            ->orWhere('pr.username', 'like', $search)
                            ->orWhere('pr.link_akses', 'like', $search)
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_mulai_camp, '%d %M %Y') LIKE ?", [$search])
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_akhir_camp, '%d %M %Y') LIKE ?", [$search])
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_pemesanan, '%d %M %Y') LIKE ?", [$search])
                            ->orWhereRaw("DATE_FORMAT(pr.tanggal_berakhir, '%d %M %Y') LIKE ?", [$search])
                            ->orWhere('u.name', 'like', $search)
                            ->orWhere('da.nama_akun', 'like', $search);
                    });
            });
        }

        // 🔹 Filter berdasarkan akun utama & PIC
        if (! empty($this->akunFilter)) {
            $query->where('akun', $this->akunFilter);
        }
        if (! empty($this->picFilter)) {
            $query->where('pic', $this->picFilter);
        }

        // 🔹 Masa akun. "Habis" tidak ikut dihitung: statusnya sudah
        // menandakan akunnya selesai.
        if ($this->masaFilter === 'segera') {
            $query->segeraBerakhir(self::BATAS_SEGERA);
        } elseif ($this->masaFilter === 'lewat') {
            $query->lewatMasa();
        }

        // 🔹 Filter berdasarkan tanggal
        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $query->whereBetween('tanggal_mulai_camp', [$this->startDate, $this->endDate]);
        } elseif (! empty($this->startDate)) {
            $query->whereDate('tanggal_mulai_camp', '>=', $this->startDate);
        } elseif (! empty($this->endDate)) {
            $query->whereDate('tanggal_akhir_camp', '<=', $this->endDate);
        }

        // 🔹 Filter periode (bulan/tahun) berdasarkan tanggal mulai camp
        if (! empty($this->filterMonth)) {
            $query->whereMonth('tanggal_mulai_camp', $this->filterMonth);
        }
        if (! empty($this->filterYear)) {
            $query->whereYear('tanggal_mulai_camp', $this->filterYear);
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

    }

    #[Layout('livewire.layout.templateindex')]
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
                DB::raw('MIN(id) as first_id'),
                DB::raw('COUNT(*) as total_peserta'),
                DB::raw('SUM(CAST(total as DECIMAL(15,2))) as total_harga'),
                // Waktu batch DIBUAT = created_at peserta paling awal di batch itu.
                // Wajib diagregasi (MIN) karena query ini di-GROUP BY per batch;
                // created_at mentah tidak boleh dipakai di ORDER BY tanpa agregat.
                DB::raw('MIN(created_at) as batch_created'),
                DB::raw('MIN(tanggal_berakhir) as akun_berakhir'),
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

        $this->saring($query);

        // 🔹 Ambil hasil — urut dari batch yang PALING BARU DIBUAT.
        // Dulu diurut tanggal_mulai_camp: batch yang baru diinput tapi campnya
        // dijadwalkan lama tenggelam di bawah, padahal itu yang baru dikerjakan.
        // Bawaan: batch yang PALING BARU DIBUAT di atas; judul kolom bisa
        // mengganti urutannya. Kunci urutan dicek terhadap daftar putih.
        $kolomUrut = self::URUTAN[$this->urut] ?? 'batch_created';
        $arah = $this->arahUrut === 'asc' ? 'asc' : 'desc';
        $pemesananrsc = $query->orderBy($kolomUrut, $arah)
            ->orderByDesc('batch_created')
            ->paginate($this->perPage);

        // Ringkasan: dihitung dari tabel mentah dengan saringan yang SAMA.
        // Nilai hanya dari status 'baru' — sama dengan aturan buku kas
        // (SyncCashFlowAction): status lain tidak menghasilkan uang masuk,
        // jadi menjumlahkannya akan membuat angka di sini lebih besar dari
        // pemasukan yang sebenarnya tercatat.
        $dasar = PemesananRsc::query();
        $this->saring($dasar);

        $ringkas = [
            'batch' => (int) $pemesananrsc->total(),
            'peserta' => (int) (clone $dasar)->count(),
            'nilai' => (float) (clone $dasar)->where('status', 'baru')->sum('total'),
            'berjalan' => (int) (clone $dasar)
                ->whereDate('tanggal_mulai_camp', '<=', today())
                ->whereDate('tanggal_akhir_camp', '>=', today())
                ->select('nama_camp', 'batch_camp')->distinct()->get()->count(),
        ];

        // Masa akun dihitung TANPA saringan masa, supaya kartunya tetap
        // menunjukkan jumlahnya saat saringan itu sedang dipakai.
        $tanpaMasa = PemesananRsc::query();
        $masa = $this->masaFilter;
        $this->masaFilter = '';
        $this->saring($tanpaMasa);
        $this->masaFilter = $masa;
        $ringkas['segera'] = (int) (clone $tanpaMasa)->segeraBerakhir(self::BATAS_SEGERA)
            ->select('nama_camp', 'batch_camp')->distinct()->get()->count();
        $ringkas['lewat'] = (int) (clone $tanpaMasa)->lewatMasa()
            ->select('nama_camp', 'batch_camp')->distinct()->get()->count();

        // Pilihan saringan: hanya akun & PIC yang memang pernah dipakai RSC.
        $pilihanAkun = \App\Models\DataAkun::whereIn('id', PemesananRsc::query()->select('akun')->distinct())
            ->orderBy('nama_akun')->get(['id', 'nama_akun']);
        $pilihanPic = \App\Models\User::whereIn('id', PemesananRsc::query()->select('pic')->distinct())
            ->orderBy('name')->get(['id', 'name']);

        // 🔹 Peta akun TAMBAHAN per batch (hanya untuk batch di halaman ini) agar
        // kolom Akun bisa menampilkan "akun utama + N akun lain" tanpa N+1 query.
        // Key = "nama_camp|batch_camp".
        $pasanganBatch = $pemesananrsc->map(fn ($i) => $i->nama_camp.'|'.$i->batch_camp)->all();
        $akunTambahanPerBatch = empty($pasanganBatch)
            ? collect()
            : \App\Models\RscBatchAkun::whereIn(
                DB::raw("CONCAT(nama_camp, '|', batch_camp)"),
                $pasanganBatch
            )
                ->orderBy('id')
                ->get(['nama_camp', 'batch_camp', 'nama_akun'])
                ->groupBy(fn ($r) => $r->nama_camp.'|'.$r->batch_camp)
                ->map(fn ($g) => $g->pluck('nama_akun')->filter()->values()->all());

        // 🔹 Data dropdown periode (seragam dengan Pesanan Toko)
        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM'),
        ]);

        // Tahun dihitung di PHP, bukan YEAR() milik MySQL, supaya kueri yang
        // sama juga berjalan di basis data uji (SQLite).
        $years = PemesananRsc::whereNotNull('tanggal_mulai_camp')
            ->distinct()
            ->pluck('tanggal_mulai_camp')
            ->map(fn ($t) => (int) substr((string) $t, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-list', compact(
            'pemesananrsc',
            'akunTambahanPerBatch',
            'months',
            'years',
            'ringkas',
            'pilihanAkun',
            'pilihanPic',
        ));
    }
}
