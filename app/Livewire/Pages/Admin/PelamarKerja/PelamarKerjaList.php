<?php

namespace App\Livewire\Pages\Admin\PelamarKerja;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PelamarKerjaList extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $filterMonth = '';
    public $filterJob = '';

    #[Layout('layouts.app')]
    public function render()
    {
        $query = JobApplication::with('job')->latest();

        // Filter berdasarkan bulan
        if ($this->filterMonth) {
            $query->whereMonth('created_at', $this->filterMonth);
        }

        // Filter berdasarkan posisi lowongan
        if ($this->filterJob) {
            $query->where('job_id', $this->filterJob);
        }

        $dataPelamar = $query->paginate($this->perPage);

        // Get data untuk filter dropdown
        $jobList = Job::where('is_active', true)
            ->orderBy('title')
            ->get();

        // Generate bulan untuk filter (12 bulan terakhir)
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months->push([
                'value' => $date->format('m'),
                'label' => $date->locale('id')->isoFormat('MMMM YYYY')
            ]);
        }

        return view('livewire.pages.admin.pelamar-kerja.pelamar-kerja-list', [
            'dataPelamar' => $dataPelamar,
            'jobList' => $jobList,
            'months' => $months
        ]);
    }

    #[On('confirm-delete')]
    public function delete($id)
    {
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

            $this->dispatch('application-deleted');
        } catch (\Exception $e) {
            $this->dispatch('application-error', ['message' => 'Gagal menghapus data pelamar']);
        }
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
        $this->reset(['filterMonth', 'filterJob']);
        $this->resetPage();
    }
}
