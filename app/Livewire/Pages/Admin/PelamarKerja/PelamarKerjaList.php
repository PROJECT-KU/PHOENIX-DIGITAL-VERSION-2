<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Models\JobApplication;
use App\Models\Lowongan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PelamarKerjaList extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public $filterMonth = '';

    /**
     * Lowongan yang sedang disaring.
     *
     * Terikat URL supaya layar Lowongan bisa menautkannya langsung:
     * /admin/pelamar?filterJob=7. Tanpa itu, angka "7 orang" di daftar
     * lowongan tidak bisa ditindaklanjuti — admin harus membuka layar ini
     * lalu mencari lowongannya lagi di kotak pilih.
     */
    #[Url(as: 'lowongan', except: '')]
    public $filterJob = '';

    public $filterStatus = '';

    /**
     * Kueri bersama layar & ekspor: berkas Excel berisi persis yang sedang
     * disaring di layar.
     */
    private function kueri()
    {
        $query = JobApplication::with('job')->latest();

        // Pencarian: nama, email, telepon, nomor lamaran, atau posisi lowongan
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('nomor_lamaran', 'like', "%{$this->search}%")
                    ->orWhereHas('job', fn ($j) => $j->where('title', 'like', "%{$this->search}%"));
            });
        }

        // Filter berdasarkan bulan
        if ($this->filterMonth) {
            $query->whereMonth('created_at', $this->filterMonth);
        }

        // Filter berdasarkan posisi lowongan
        // 'umum' = lamaran umum (talent pool) tanpa posisi.
        if ($this->filterJob === 'umum') {
            $query->whereNull('job_id');
        } elseif ($this->filterJob) {
            $query->where('job_id', $this->filterJob);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query;
    }

    /**
     * Berapa pelamar di tiap tahap, mengikuti saringan yang SEDANG hidup.
     *
     * Dihitung dari kueri yang sama dengan daftarnya, hanya tanpa batas
     * tahapnya sendiri. Dua kueri yang ditulis terpisah akan berselisih
     * begitu saringannya bertambah, dan angka di tab lalu menjanjikan isi
     * yang tidak ada saat ditekan.
     */
    public function jumlahTahap(): array
    {
        $tahap = $this->filterStatus;
        $this->filterStatus = '';
        $dasar = $this->kueri();
        $this->filterStatus = $tahap;

        $jumlah = ['' => (clone $dasar)->count()];

        foreach (array_keys(JobApplication::STATUS) as $kunci) {
            $jumlah[$kunci] = (clone $dasar)->where('status', $kunci)->count();
        }

        return $jumlah;
    }

    public function setTahap(string $tahap): void
    {
        $this->filterStatus = array_key_exists($tahap, JobApplication::STATUS) ? $tahap : '';
        $this->resetPage();
    }

    public function adaSaringan(): bool
    {
        return trim((string) $this->search) !== ''
            || $this->filterMonth !== ''
            || $this->filterJob !== ''
            || $this->filterStatus !== '';
    }

    public function ekspor()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PelamarKerjaExport($this->kueri()->get()),
            'pelamar-kerja-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $query = $this->kueri();

        $dataPelamar = $query->paginate($this->perPage);

        // Get data untuk filter dropdown (is_active = enum string 'active')
        $jobList = Lowongan::where('is_active', 'active')
            ->orderBy('title')
            ->get();

        // Generate bulan untuk filter (12 bulan terakhir)
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months->push([
                'value' => $date->format('m'),
                'label' => $date->locale('id')->isoFormat('MMMM YYYY'),
            ]);
        }

        return view('livewire.pages.admin.pelamar-kerja.pelamar-kerja-list', [
            'dataPelamar' => $dataPelamar,
            'jobList' => $jobList,
            'months' => $months,
            'jumlahTahap' => $this->jumlahTahap(),
            // Dipakai kepala layar: lowongan mana yang sedang dibaca, bila
            // layar ini dibuka lewat tautan dari daftar Lowongan.
            'lowonganDisaring' => $this->filterJob && $this->filterJob !== 'umum'
                ? Lowongan::find($this->filterJob)
                : null,
        ]);
    }

    #[On('confirm-delete')]
    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_pelamar')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus data pelamar.');

            return;
        }

        try {
            $application = JobApplication::findOrFail($id);

            $cvPath = $application->cv_path;
            $clPath = $application->cover_letter_path;
            $lampiranPath = $application->lampiran_path;

            // Lamaran baru disimpan di disk PRIVAT 'local'; yang lama mungkin
            // masih di 'public'. Dulu hanya 'public' yang dibersihkan, jadi CV
            // pelamar yang dihapus tetap tertinggal di server.
            foreach (['local', 'public'] as $disk) {
                foreach ([$cvPath, $clPath, $lampiranPath] as $jalur) {
                    if ($jalur && Storage::disk($disk)->exists($jalur)) {
                        Storage::disk($disk)->delete($jalur);
                    }
                }

                // Folder per lamaran: applications/{tahun}/{bulan}/{uuid}.
                // Dijaga ketat supaya dirname() dari jalur aneh ('.' atau
                // 'applications') tidak pernah menghapus folder bersama.
                $folder = dirname($cvPath ?? $clPath ?? '');
                if (preg_match('#^applications/\d{4}/\d{2}/[0-9a-f-]{36}$#', $folder)
                    && Storage::disk($disk)->exists($folder)
                    && Storage::disk($disk)->files($folder) === []) {
                    Storage::disk($disk)->deleteDirectory($folder);
                }
            }

            $application->delete();

            $this->dispatch('swal-success', message: 'Data pelamar berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('swal-error', message: 'Gagal menghapus data pelamar.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterJob()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterMonth', 'filterJob', 'filterStatus']);
        $this->resetPage();
    }
}
