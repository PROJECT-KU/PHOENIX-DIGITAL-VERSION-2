<?php

namespace App\Livewire\Pages\Admin\GajiKaryawans;

use App\Models\EmployeeDetail;
use App\Models\GajiKaryawans;
use App\Models\Presensi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class GajiKaryawansList extends Component
{
    use WithPagination;

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

    public function deletegajikaryawan($id)
    {
        if (! auth()->user()->hasPermission('delete_gajikaryawan')) {
            $this->dispatch('gajikaryawan-delete-error', message: 'Anda tidak memiliki izin menghapus data gaji.');

            return;
        }

        try {
            $gajikaryawan = GajiKaryawans::findOrFail($id);
            $gajikaryawan->delete();

            $this->dispatch('gajikaryawan-deleted');
        } catch (\Exception $e) {
            $this->dispatch('gajikaryawan-delete-error', message: 'Gagal menghapus data gaji karyawan');
        }
    }

    /**
     * Generate draft gaji (status pending) untuk SEMUA karyawan berdasarkan
     * gaji terakhir masing-masing, untuk periode terpilih. Komponen yang
     * bersifat per-periode (lembur, THR, potongan pinjaman) di-reset ke 0
     * agar aman, dan baris yang sudah ada untuk periode tsb dilewati.
     */
    public function generateGaji()
    {
        if (! auth()->user()->hasPermission('create_gajikaryawan')) {
            $this->dispatch('gajikaryawan-delete-error', message: 'Anda tidak memiliki izin membuat data gaji.');

            return;
        }

        $bulan = (int) ($this->bulan ?: now()->month);
        $tahun = (int) ($this->tahun ?: now()->year);

        // Tanggal pembayaran draft = akhir bulan periode
        $tanggal = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

        // Periode SEBELUMNYA (1 bulan sebelum periode target)
        $prev = Carbon::create($tahun, $bulan, 1)->subMonthNoOverflow();
        $prevBulan = $prev->month;
        $prevTahun = $prev->year;
        $prevLabel = $prev->locale('id')->translatedFormat('F Y');

        // Ambil gaji karyawan dari PERIODE bulan sebelumnya
        $gajiTerakhir = GajiKaryawans::whereNotNull('nama_karyawan')
            ->where('periode_bulan', $prevBulan)
            ->where('periode_tahun', $prevTahun)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('nama_karyawan');

        if ($gajiTerakhir->isEmpty()) {
            $this->dispatch('gaji-generate-info', message: "Tidak ada data gaji periode {$prevLabel} untuk disalin.");

            return;
        }

        $dibuat = 0;
        $dilewati = 0;

        try {
            DB::transaction(function () use ($gajiTerakhir, $bulan, $tahun, $tanggal, &$dibuat, &$dilewati) {
                foreach ($gajiTerakhir as $ref) {
                    // Lewati bila karyawan sudah punya gaji untuk PERIODE target
                    $sudahAda = GajiKaryawans::where('nama_karyawan', $ref->nama_karyawan)
                        ->where('periode_bulan', $bulan)
                        ->where('periode_tahun', $tahun)
                        ->exists();

                    if ($sudahAda) {
                        $dilewati++;

                        continue;
                    }

                    // ===== Presensi & lembur: SELALU ambil fresh untuk PERIODE TARGET
                    // (bukan salinan bulan sebelumnya) + tarif TERKINI dari profil karyawan.
                    $detail = EmployeeDetail::where('user_id', $ref->nama_karyawan)->first();
                    $tarifOff = (int) ($detail->tarif_presensi_offline ?? 0);
                    $tarifOn = (int) ($detail->tarif_presensi_online ?? 0);
                    $tarifLembur = (int) ($detail->tarif_lembur_per_jam ?? 0);

                    $rekap = Presensi::rekapBulan($ref->nama_karyawan, $bulan, $tahun);
                    $jmlOff = (int) $rekap['hari_offline'];
                    $jmlOn = (int) $rekap['hari_online'];
                    $jamLembur = (int) round($rekap['jam_lembur']);

                    $uangOff = $jmlOff * $tarifOff;
                    $uangOn = $jmlOn * $tarifOn;
                    $uangLembur = $jamLembur * $tarifLembur;

                    // Komponen tetap disalin; komponen per-periode di-reset / diisi dari data terkini.
                    // Bonus penyelesaian task mulai 0 — dihitung dari fitur Penyelesaian Task
                    // (tabel `tasks` mandiri) lalu diterapkan via tombol "Terapkan ke Gaji".
                    $pendapatan = (int) $ref->gaji_pokok + (int) $ref->bonus_kinerja + (int) $ref->bonus_lainnya
                        + $uangLembur + $uangOff + $uangOn
                        + (int) $ref->tunjangan_kesehatan + (int) $ref->tunjangan_ketenagakerjaan
                        + (int) $ref->tunjangan_lainnya + (int) $ref->tunjangan_transport + (int) $ref->tunjangan_makan;
                    $potongan = (int) $ref->potongan + (int) $ref->potongan_bpjs_kesehatan
                        + (int) $ref->potongan_bpjs_ketenagakerjaan + (int) $ref->pph21;

                    GajiKaryawans::create([
                        'id_transaksi' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5)),
                        'nama_karyawan' => $ref->nama_karyawan,
                        'bank' => $ref->bank,
                        'no_rek' => $ref->no_rek,
                        'tanggal_transaksi' => $tanggal,
                        'periode_bulan' => $bulan,
                        'periode_tahun' => $tahun,
                        'gaji_pokok' => $ref->gaji_pokok,
                        'bonus_kinerja' => $ref->bonus_kinerja,
                        'bonus_lainnya' => $ref->bonus_lainnya,
                        'task_budget' => 0, // pool budget kini per-periode di Setting, bukan per gaji
                        'bonus_penyelesaian_task' => 0,
                        'tasks' => $tasksTemplate,
                        'uang_lembur' => $uangLembur,
                        'jam_lembur' => $jamLembur,
                        'jumlah_hadir_offline' => $jmlOff,
                        'uang_hadir_offline' => $uangOff,
                        'jumlah_hadir_online' => $jmlOn,
                        'uang_hadir_online' => $uangOn,
                        'tunjangan_kesehatan' => $ref->tunjangan_kesehatan,
                        'tunjangan_thr' => 0,
                        'tunjangan_ketenagakerjaan' => $ref->tunjangan_ketenagakerjaan,
                        'tunjangan_lainnya' => $ref->tunjangan_lainnya,
                        'tunjangan_transport' => $ref->tunjangan_transport,
                        'tunjangan_makan' => $ref->tunjangan_makan,
                        'potongan' => $ref->potongan,
                        'potongan_bpjs_kesehatan' => $ref->potongan_bpjs_kesehatan,
                        'potongan_bpjs_ketenagakerjaan' => $ref->potongan_bpjs_ketenagakerjaan,
                        'potongan_pinjaman' => 0,
                        'pph21' => $ref->pph21,
                        'total' => $pendapatan - $potongan,
                        'deskripsi' => 'Draft gaji hasil generate',
                        'status' => 'pending',
                    ]);

                    $dibuat++;
                }
            });

            $this->resetPage();
            $this->dispatch('gaji-generated', message: "Berhasil membuat {$dibuat} draft gaji (dilewati {$dilewati} yang sudah ada).");
        } catch (\Exception $e) {
            $this->dispatch('gajikaryawan-delete-error', message: 'Gagal generate gaji: '.$e->getMessage());
        }
    }

    public function render()
    {
        $isSearching = ! empty($this->search);

        $gajikaryawan = $this->buildFilteredQuery()
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(10);

        // Total gaji per status mengikuti scope aktif (pencarian / periode)
        // visibleTo() menjaga total juga tidak membocorkan data karyawan lain.
        $totalQuery = GajiKaryawans::visibleTo()->select('status', DB::raw('SUM(total) as total_gaji'));
        if ($isSearching) {
            $this->applySearch($totalQuery);
        } else {
            $this->applyPeriode($totalQuery);
        }
        $totalGaji = $totalQuery->groupBy('status')->get();

        return view('livewire.pages.admin.gaji-karyawans.gaji-karyawans-list', [
            'gajikaryawan' => $gajikaryawan,
            'totalGaji' => $totalGaji,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ])->layout('livewire.layout.templateindex');
    }

    /**
     * Query data gaji sesuai state aktif:
     * - sedang mencari -> seluruh data yang cocok pencarian (lintas periode)
     * - tanpa mencari  -> periode (bulan/tahun) terpilih
     */
    protected function buildFilteredQuery()
    {
        // visibleTo() = pondasi keamanan: tabel, pencarian, dan export PDF
        // semuanya dibangun dari query ini sehingga otomatis ter-scope.
        $query = GajiKaryawans::visibleTo()->with('karyawan');

        if (! empty($this->search)) {
            $this->applySearch($query);
        } else {
            $this->applyPeriode($query);
        }

        return $query;
    }

    /**
     * Terapkan pencarian ke seluruh kolom penting (lintas semua data).
     */
    protected function applySearch($query): void
    {
        $term = '%'.$this->search.'%';
        $dateTerm = '%'.$this->normalizeDateSearch($this->search).'%';

        $query->where(function ($q) use ($term, $dateTerm) {
            $q->where('id_transaksi', 'like', $term)
                ->orWhere('bank', 'like', $term)
                ->orWhere('no_rek', 'like', $term)
                ->orWhere('deskripsi', 'like', $term)
                ->orWhere('status', 'like', $term)
                ->orWhere('gaji_pokok', 'like', $term)
                ->orWhere('bonus_kinerja', 'like', $term)
                ->orWhere('bonus_lainnya', 'like', $term)
                ->orWhere('tunjangan_kesehatan', 'like', $term)
                ->orWhere('tunjangan_thr', 'like', $term)
                ->orWhere('tunjangan_ketenagakerjaan', 'like', $term)
                ->orWhere('tunjangan_lainnya', 'like', $term)
                ->orWhere('potongan', 'like', $term)
                ->orWhere('pph21', 'like', $term)
                ->orWhere('total', 'like', $term)
                ->orWhereHas('karyawan', function ($q) use ($term) {
                    $q->where('name', 'like', $term);
                })
                // Tanggal transaksi (tanggal bulan tahun)
                ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%d %m %Y') LIKE ?", [$dateTerm])
                ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%Y-%m-%d') LIKE ?", [$dateTerm])
                // Waktu data dibuat (tanggal bulan tahun jam)
                ->orWhereRaw("DATE_FORMAT(created_at, '%d %m %Y %H:%i') LIKE ?", [$dateTerm])
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

        return preg_replace('/\s+/', ' ', $hasil);
    }

    protected function applyPeriode($query): void
    {
        // Filter berdasarkan PERIODE GAJI (bukan tanggal pembayaran)
        if ($this->tahun) {
            $query->where('periode_tahun', $this->tahun);
        }
        if ($this->bulan) {
            $query->where('periode_bulan', $this->bulan);
        }
    }

    /**
     * Unduh laporan PDF mengikuti data yang sedang tampil:
     * - sedang search -> data hasil pencarian
     * - filter periode -> data periode tersebut
     * - tanpa keduanya -> data default (periode berjalan)
     */
    public function downloadPdf()
    {
        $records = $this->buildFilteredQuery()
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();

        $isSearching = ! empty($this->search);

        $rows = $records->map(function (GajiKaryawans $g) {
            return [
                'id_transaksi' => $g->id_transaksi,
                'nama' => $g->karyawan->name ?? '-',
                'periode' => $g->periode_label,
                'tanggal' => $g->tanggal_transaksi_formatted,
                'gaji_pokok' => (float) $g->gaji_pokok,
                'total' => (float) $g->total,
                'status' => $g->status,
            ];
        })->toArray();

        $konteks = $isSearching
            ? 'Hasil Pencarian: "'.$this->search.'"'
            : $this->periodeLabel();

        $summary = [
            'count' => $records->count(),
            'total' => $records->sum(fn (GajiKaryawans $g) => (float) $g->total),
            'completed' => $records->where('status', 'completed')->sum(fn (GajiKaryawans $g) => (float) $g->total),
            'pending' => $records->where('status', 'pending')->sum(fn (GajiKaryawans $g) => (float) $g->total),
        ];

        $pdf = Pdf::loadView('livewire.pages.admin.gaji-karyawans.gaji-report-pdf', [
            'konteks' => $konteks,
            'rows' => $rows,
            'summary' => $summary,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-gaji-'.now()->format('Ymd-His').'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    /**
     * Unduh slip gaji (PDF) untuk satu transaksi. Dirender langsung dari data
     * terkini, sehingga otomatis mengikuti hasil generate maupun update.
     */
    public function downloadSlip($id)
    {
        // visibleTo() mencegah IDOR: karyawan tidak bisa mengunduh slip milik
        // orang lain dengan menebak id, karena query tidak akan menemukannya.
        $gaji = GajiKaryawans::visibleTo()->with('karyawan')->findOrFail($id);

        $pdf = Pdf::loadView('livewire.pages.admin.gaji-karyawans.slip-gaji-pdf', [
            'g' => $gaji,
        ])->setPaper('a4', 'portrait');

        $filename = 'slip-gaji-'.$gaji->id_transaksi.'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    protected function periodeLabel(): string
    {
        $namaBulan = $this->bulan ? ($this->daftarBulan()[(int) $this->bulan] ?? '') : '';

        if ($this->bulan && $this->tahun) {
            return $namaBulan.' '.$this->tahun;
        }
        if ($this->tahun) {
            return 'Tahun '.$this->tahun;
        }
        if ($this->bulan) {
            return 'Bulan '.$namaBulan;
        }

        return 'Semua Periode';
    }

    protected function daftarBulan(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    protected function daftarTahun(): array
    {
        $tahunSekarang = (int) now()->year;

        return range($tahunSekarang, $tahunSekarang - 5);
    }
}
