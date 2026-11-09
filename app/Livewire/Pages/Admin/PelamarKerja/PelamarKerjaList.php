<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Models\JobApplication;
use App\Models\Lowongan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PelamarKerjaList extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public $filterMonth = '';

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
