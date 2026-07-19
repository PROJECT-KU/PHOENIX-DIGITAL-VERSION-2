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

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $query = JobApplication::with('job')->latest();

        // Pencarian: nama, email, telepon, atau posisi lowongan
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhereHas('job', fn ($j) => $j->where('title', 'like', "%{$this->search}%"));
            });
        }

        // Filter berdasarkan bulan
        if ($this->filterMonth) {
            $query->whereMonth('created_at', $this->filterMonth);
        }

        // Filter berdasarkan posisi lowongan
        if ($this->filterJob) {
            $query->where('job_id', $this->filterJob);
        }

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

            if ($cvPath && Storage::disk('public')->exists($cvPath)) {
                Storage::disk('public')->delete($cvPath);
            }

            if ($clPath && Storage::disk('public')->exists($clPath)) {
                Storage::disk('public')->delete($clPath);
            }

            $folder = dirname($cvPath ?? $clPath ?? '');
            if ($folder && Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->deleteDirectory($folder);
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

    public function resetFilters()
    {
        $this->reset(['search', 'filterMonth', 'filterJob']);
        $this->resetPage();
    }
}
