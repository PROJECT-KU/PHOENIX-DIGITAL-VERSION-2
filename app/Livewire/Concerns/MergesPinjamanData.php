<?php

namespace App\Livewire\Concerns;

use App\Models\Loan;
use App\Models\Pengembalian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Logika bersama untuk tab Peminjaman & Pengembalian.
 *
 * Saat search ATAU filter periode aktif, tabel menampilkan data GABUNGAN
 * (Peminjaman + Pengembalian) lengkap dengan kolom "Jenis", tidak peduli
 * tab mana yang sedang aktif. Status mengikuti perhitungan otomatis.
 *
 * Komponen pemakai wajib punya properti publik: $search, $bulan, $tahun.
 */
trait MergesPinjamanData
{
    /**
     * Hapus baris dari tabel gabungan. Karena satu tabel memuat Peminjaman &
     * Pengembalian sekaligus, deteksi otomatis jenis data dari id-nya lalu hapus
     * model yang tepat dan kirim event sukses yang sesuai.
     */
    public function delete($id): void
    {
        try {
            if ($loan = Loan::find($id)) {
                $loan->delete();
                $this->dispatch('loan-deleted');

                return;
            }

            if ($pengembalian = Pengembalian::find($id)) {
                $pengembalian->delete();
                $this->dispatch('pengembalian-deleted');

                return;
            }

            $this->dispatch('delete-loan-error', message: 'Data tidak ditemukan!');
        } catch (\Exception $e) {
            $this->dispatch('delete-loan-error', message: 'Terjadi kesalahan saat menghapus data!');
        }
    }

    /**
     * Bangun daftar untuk tabel.
     * - Saat SEARCH aktif  -> gabungkan Peminjaman + Pengembalian (lintas tab).
     * - Tanpa search       -> hanya data tab aktif ($jenisAktif) sesuai filter periode.
     * Terurut tanggal terbaru & dipaginasi manual agar kompatibel Livewire.
     */
    protected function buildMergedRows(string $jenisAktif, int $perPage = 10): LengthAwarePaginator
    {
        $merged = $this->collectRows($jenisAktif);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $merged->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $merged->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'pageName' => 'page']
        );
    }

    /**
     * Kumpulkan baris data (tanpa paginasi) sesuai state aktif.
     * Dipakai bersama oleh tabel (dipaginasi) dan export PDF (semua data).
     */
    protected function collectRows(string $jenisAktif, bool $gabungSemua = false): Collection
    {
        $isSearching = ! empty($this->search);
        $statusMap = Loan::statusMap();
        $merged = collect();

        // ---- Peminjaman: tampil bila sedang search, gabung-semua (export), ATAU tab aktif Peminjaman ----
        if ($isSearching || $gabungSemua || $jenisAktif === 'peminjaman') {
            $loanQuery = Loan::with('penginput');
            if ($isSearching) {
                $this->applySearch($loanQuery, 'nama_peminjam', 'tanggal_peminjam', $statusMap);
            } else {
                $this->applyPeriode($loanQuery, 'tanggal_peminjam');
            }
            $merged = $merged->concat($loanQuery->get()->map(function (Loan $l) use ($statusMap) {
                return [
                    'jenis' => 'peminjaman',
                    'jenis_label' => 'Peminjaman',
                    'id' => $l->id,
                    'id_transaksi' => $l->id_transaksi,
                    'nama' => $l->nama_peminjam,
                    'tanggal' => $l->tanggal_peminjam_formatted,
                    'tanggal_sort' => optional($l->tanggal_peminjam)->timestamp ?? 0,
                    'nominal' => (float) $l->nominal,
                    'nominal_formatted' => $l->nominal_formatted,
                    'deskripsi' => $l->deskripsi,
                    'status' => $statusMap[$l->nama_peminjam] ?? 'pending',
                    'penginput' => $l->namaPenginput,
                    'created_at' => $l->created_at_formatted,
                    'edit_url' => route('admin.loan.edit', $l->id),
                    'delete_class' => 'delete-Loan-btn',
                ];
            }));
        }

        // ---- Pengembalian: tampil bila sedang search, gabung-semua (export), ATAU tab aktif Pengembalian ----
        if ($isSearching || $gabungSemua || $jenisAktif === 'pengembalian') {
            $pQuery = Pengembalian::with('penginput');
            if ($isSearching) {
                $this->applySearch($pQuery, 'nama_pengembalian', 'tanggal_pengembalian', $statusMap);
            } else {
                $this->applyPeriode($pQuery, 'tanggal_pengembalian');
            }
            $merged = $merged->concat($pQuery->get()->map(function (Pengembalian $p) use ($statusMap) {
                return [
                    'jenis' => 'pengembalian',
                    'jenis_label' => 'Pengembalian',
                    'id' => $p->id,
                    'id_transaksi' => $p->id_transaksi,
                    'nama' => $p->nama_pengembalian,
                    'tanggal' => $p->tanggal_pengembalian_formatted,
                    'tanggal_sort' => optional($p->tanggal_pengembalian)->timestamp ?? 0,
                    'nominal' => (float) $p->nominal,
                    'nominal_formatted' => $p->nominal_formatted,
                    'deskripsi' => $p->deskripsi,
                    'status' => $statusMap[$p->nama_pengembalian] ?? 'pending',
                    'penginput' => $p->namaPenginput,
                    'created_at' => $p->created_at_formatted,
                    'edit_url' => route('admin.pengembalian.edit', $p->id),
                    'delete_class' => 'delete-pengembalian-btn',
                ];
            }));
        }

        return $merged->sortByDesc('tanggal_sort')->values();
    }

    /**
     * Unduh laporan PDF mengikuti data yang sedang tampil:
     * - sedang search -> data hasil pencarian (Peminjaman + Pengembalian)
     * - tanpa search  -> data tab aktif sesuai filter periode
     */
    protected function generatePdf(string $jenisAktif)
    {
        // PDF SELALU menggabungkan Peminjaman + Pengembalian (meski tidak sedang
        // search) agar pembaca laporan tidak bingung — keduanya tampil utuh.
        $rows = $this->collectRows($jenisAktif, true)->values()->all();
        $isSearching = ! empty($this->search);

        $totalPeminjaman = collect($rows)->where('jenis', 'peminjaman')->sum('nominal');
        $totalPengembalian = collect($rows)->where('jenis', 'pengembalian')->sum('nominal');

        $judul = 'LAPORAN PEMINJAMAN & PENGEMBALIAN';
        $konteks = $isSearching
            ? 'Hasil Pencarian: "'.$this->search.'"'
            : $this->periodeLabel();

        $pdf = Pdf::loadView('livewire.pages.admin._shared.pinjaman-report-pdf', [
            'judul' => $judul,
            'konteks' => $konteks,
            'rows' => $rows,
            'summary' => [
                'count' => count($rows),
                'peminjaman' => $totalPeminjaman,
                'pengembalian' => $totalPengembalian,
                'selisih' => $totalPeminjaman - $totalPengembalian,
            ],
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-pinjaman-'.now()->format('Ymd-His').'.pdf';

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

    /**
     * Total peminjaman per peminjam (pinjaman periode terpilih, pengembalian lintas waktu).
     * Sama untuk kedua tab.
     */
    protected function buildTotalLoans()
    {
        $totalQuery = Loan::query()
            ->select('nama_peminjam', DB::raw('SUM(nominal) as total_pinjaman'));

        if ($this->tahun) {
            $totalQuery->whereYear('tanggal_peminjam', $this->tahun);
        }
        if ($this->bulan) {
            $totalQuery->whereMonth('tanggal_peminjam', $this->bulan);
        }

        return $totalQuery->groupBy('nama_peminjam')
            ->orderByDesc('total_pinjaman')
            ->get()
            ->map(function ($item) {
                $totalPengembalian = (float) DB::table('pengembalians')
                    ->where('nama_pengembalian', $item->nama_peminjam)
                    ->sum('nominal');

                $item->total_pinjaman = (float) $item->total_pinjaman;
                $item->total_pengembalian = $totalPengembalian;
                $item->sisa_peminjaman = $item->total_pinjaman - $totalPengembalian;

                return $item;
            });
    }

    /**
     * Terapkan kondisi pencarian ke query (lintas semua data, tanpa batas periode).
     */
    protected function applySearch($query, string $kolomNama, string $kolomTanggal, array $statusMap): void
    {
        $term = '%'.$this->search.'%';
        $dateTerm = '%'.$this->normalizeDateSearch($this->search).'%';
        $statusNames = $this->namaCocokStatus($statusMap);

        $query->where(function ($q) use ($term, $dateTerm, $statusNames, $kolomNama, $kolomTanggal) {
            $q->where($kolomNama, 'like', $term)
                ->orWhere('deskripsi', 'like', $term)
                ->orWhere('id_transaksi', 'like', $term)
                ->orWhere('nominal', 'like', $term)
                ->orWhereHas('penginput', function ($q) use ($term) {
                    $q->where('name', 'like', $term);
                })
                // Tanggal transaksi (tanggal bulan tahun)
                ->orWhereRaw("DATE_FORMAT($kolomTanggal, '%d %m %Y') LIKE ?", [$dateTerm])
                ->orWhereRaw("DATE_FORMAT($kolomTanggal, '%Y-%m-%d') LIKE ?", [$dateTerm])
                // Waktu data dibuat (tanggal bulan tahun jam)
                ->orWhereRaw("DATE_FORMAT(created_at, '%d %m %Y %H:%i') LIKE ?", [$dateTerm])
                ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", [$dateTerm]);

            // Status otomatis (mis. ketik "lunas" / "berjalan" / "pending")
            if (! empty($statusNames)) {
                $q->orWhereIn($kolomNama, $statusNames);
            }
        });
    }

    protected function applyPeriode($query, string $kolomTanggal): void
    {
        if ($this->tahun) {
            $query->whereYear($kolomTanggal, $this->tahun);
        }
        if ($this->bulan) {
            $query->whereMonth($kolomTanggal, $this->bulan);
        }
    }

    /**
     * Nama yang status OTOMATIS-nya cocok dengan kata pencarian.
     */
    protected function namaCocokStatus(array $statusMap): array
    {
        $kata = mb_strtolower(trim($this->search));

        if ($kata === '') {
            return [];
        }

        return collect($statusMap)
            ->filter(fn ($status) => str_contains($status, $kata))
            ->keys()
            ->all();
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
