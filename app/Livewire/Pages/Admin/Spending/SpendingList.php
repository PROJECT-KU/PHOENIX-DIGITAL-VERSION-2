<?php

namespace App\Livewire\Pages\Admin\Spending;

use App\Models\Spending;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SpendingList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $bulan = '';

    public $tahun = '';

    public $jenisPengeluaran = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'bulan' => ['except' => ''],
        'tahun' => ['except' => ''],
        'jenisPengeluaran' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        // Default ke periode bulan & tahun berjalan (seperti cashflow)
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

    public function updatingJenisPengeluaran()
    {
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->bulan = '';
        $this->tahun = '';
        $this->resetPage();
    }

    #[On('delete-spending-data')]
    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_spending')) {
            $this->dispatch('spending-delete-error', message: 'Anda tidak memiliki izin menghapus data pengeluaran.');

            return;
        }

        try {
            $spending = Spending::findOrFail($id);
            $spending->delete();

            $this->dispatch('spending-deleted');
        } catch (\Exception $e) {
            $this->dispatch('spending-delete-error', message: 'Gagal menghapus data pengeluaran');
        }
    }

    public function render()
    {
        // default jenisPengeluaran kalau belum dipilih
        if (empty($this->jenisPengeluaran)) {
            $this->jenisPengeluaran = 'lainnya';
        }

        $isSearching = ! empty($this->search);

        $spendings = $this->buildFilteredQuery()
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(10);

        // Total pengeluaran per jenis: ikut hasil pencarian bila sedang mencari,
        // selain itu mengikuti periode terpilih.
        $totalQuery = Spending::select(
            'jenis_pengeluaran as jenisPengeluaran',
            DB::raw('SUM(nominal) as total_pengeluaran')
        );
        if ($isSearching) {
            $this->applySearch($totalQuery);
        } else {
            $this->applyPeriode($totalQuery);
        }
        $totalSpendings = $totalQuery->groupBy('jenis_pengeluaran')->get();

        return view('livewire.pages.admin.spending.spending-list', [
            'spendings' => $spendings,
            'totalSpendings' => $totalSpendings,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ])->layout('livewire.layout.templateindex');
    }

    /**
     * Query data pengeluaran sesuai state aktif:
     * - sedang mencari  -> seluruh data yang cocok pencarian (lintas jenis & periode)
     * - tanpa mencari   -> jenis pengeluaran (tab) + periode (bulan/tahun) terpilih
     * Dipakai bersama oleh tabel daftar maupun export PDF agar selalu konsisten.
     */
    protected function buildFilteredQuery()
    {
        if (empty($this->jenisPengeluaran)) {
            $this->jenisPengeluaran = 'lainnya';
        }

        $jenis = $this->jenisPengeluaran === 'pembelian_akun' ? 'pembelian_akun' : 'lainnya';

        $query = Spending::with(['penginput', 'picPembeli']);

        if (! empty($this->search)) {
            $this->applySearch($query);
        } else {
            $query->where('jenis_pengeluaran', $jenis);
            $this->applyPeriode($query);
        }

        return $query;
    }

    /**
     * Terapkan kondisi pencarian ke query (lintas semua data).
     */
    protected function applySearch($query): void
    {
        $term = '%' . $this->search . '%';

        // Untuk pencarian tanggal: ubah nama bulan Indonesia jadi angka
        // contoh: "Juni 2026" -> "06 2026", lalu cocokkan ke format tanggal.
        $dateTerm = '%' . $this->normalizeDateSearch($this->search) . '%';

        $query->where(function ($q) use ($term, $dateTerm) {
            $q->where('deskripsi', 'like', $term)
                ->orWhere('nominal', 'like', $term)
                ->orWhere('id_transaksi', 'like', $term)
                ->orWhere('status', 'like', $term)
                ->orWhereHas('penginput', function ($q) use ($term) {
                    $q->where('name', 'like', $term);
                })
                ->orWhereHas('picPembeli', function ($q) use ($term) {
                    $q->where('name', 'like', $term);
                })
                // Waktu Transaksi (tanggal bulan tahun)
                ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%d %m %Y') LIKE ?", [$dateTerm])
                ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%Y-%m-%d') LIKE ?", [$dateTerm])
                // Waktu Data Dibuat (tanggal bulan tahun jam)
                ->orWhereRaw("DATE_FORMAT(created_at, '%d %m %Y') LIKE ?", [$dateTerm])
                ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", [$dateTerm]);
        });
    }

    /**
     * Ubah kata pencarian tanggal berbahasa Indonesia menjadi format angka.
     * Contoh: "Juni 2026" -> "06 2026", "15 Januari" -> "15 01".
     */
    protected function normalizeDateSearch(string $term): string
    {
        $bulan = [
            'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
            'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
            'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
        ];

        $hasil = mb_strtolower(trim($term));

        foreach ($bulan as $nama => $angka) {
            $hasil = str_replace($nama, $angka, $hasil);
        }

        // Rapikan spasi ganda
        return preg_replace('/\s+/', ' ', $hasil);
    }

    protected function applyPeriode($query): void
    {
        if ($this->tahun) {
            $query->whereYear('tanggal_transaksi', $this->tahun);
        }
        if ($this->bulan) {
            $query->whereMonth('tanggal_transaksi', $this->bulan);
        }
    }

    /**
     * Export PDF mengikuti data yang sedang tampil:
     * - sedang mencari -> hanya data hasil pencarian
     * - filter periode -> hanya data periode tersebut (sesuai tab jenis)
     * - tanpa keduanya -> data default (jenis tab + periode berjalan)
     */
    public function downloadPdf()
    {
        // Export selalu mencakup SEMUA kategori (Pembelian Akun + Lainnya).
        // Hanya mengikuti pencarian atau periode, bukan tab jenis yang aktif.
        $query = Spending::with(['penginput', 'picPembeli']);

        if (! empty($this->search)) {
            $this->applySearch($query);
        } else {
            $this->applyPeriode($query);
        }

        $records = $query->orderBy('tanggal_transaksi', 'desc')->get();

        $rows = $records->map(function (Spending $s) {
            $isAkun = $s->jenis_pengeluaran === 'pembelian_akun';

            return [
                'id_transaksi' => $s->id_transaksi,
                'tanggal' => $s->tanggal_transaksi_formatted,
                'jenis' => $isAkun ? 'Pembelian Akun' : 'Lainnya',
                'is_akun' => $isAkun,
                'deskripsi' => $s->deskripsi ?: '-',
                'status' => ucfirst($s->status),
                'penginput' => $s->namaPenginput,
                'pic' => $isAkun ? ($s->namaPicPembeli ?: '-') : '-',
                'nominal' => (float) $s->nominal,
            ];
        })->toArray();

        if (! empty($this->search)) {
            $konteks = 'Hasil Pencarian: "' . $this->search . '" (Semua Kategori)';
        } else {
            $konteks = 'Semua Kategori — ' . $this->periodeLabel();
        }

        $pdf = Pdf::loadView('livewire.pages.admin.spending.spending-report-pdf', [
            'konteks' => $konteks,
            'rows' => $rows,
            'summary' => [
                'total' => $records->sum(fn (Spending $s) => (float) $s->nominal),
                'count' => $records->count(),
                'pembelian_akun' => $records->where('jenis_pengeluaran', 'pembelian_akun')->sum(fn (Spending $s) => (float) $s->nominal),
                'lainnya' => $records->where('jenis_pengeluaran', 'lainnya')->sum(fn (Spending $s) => (float) $s->nominal),
            ],
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-pengeluaran-' . now()->format('Ymd-His') . '.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    protected function periodeLabel(): string
    {
        $namaBulan = $this->bulan ? ($this->daftarBulan()[(int) $this->bulan] ?? '') : '';

        if ($this->bulan && $this->tahun) {
            return $namaBulan . ' ' . $this->tahun;
        }
        if ($this->tahun) {
            return 'Tahun ' . $this->tahun;
        }
        if ($this->bulan) {
            return 'Bulan ' . $namaBulan;
        }

        return 'Semua Periode';
    }

    protected function daftarBulan(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    protected function daftarTahun(): array
    {
        $tahunSekarang = (int) now()->year;

        // 6 tahun terakhir hingga tahun berjalan
        return range($tahunSekarang, $tahunSekarang - 5);
    }
}
