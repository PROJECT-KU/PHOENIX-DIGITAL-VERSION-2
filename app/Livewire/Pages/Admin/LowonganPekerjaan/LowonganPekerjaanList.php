<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use App\Models\Job;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LowonganPekerjaanList extends Component
{
    use WithPagination;
    public $perPage = 10;
    public $search = '';

    // Properties untuk form
    public $jobId;
    public $title;
    public $is_active = true;

    #[Layout('layouts.app')]
    public function render()
    {
        $dataLowongan = Job::latest()
            ->where('title', 'like', "%{$this->search}%")
            ->paginate($this->perPage);

        return view('livewire.pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-list', [
            'dataLowongan' => $dataLowongan
        ]);
    }

    #[On('open-create-form')]
    public function openCreateForm()
    {
        $this->resetForm();
        $this->dispatch('show-create-modal');
    }

    #[On('open-edit-form')]
    public function openEditForm($id)
    {
        $job = Job::findOrFail($id);
        $this->jobId = $job->id;
        $this->title = $job->title;
        $this->is_active = $job->is_active;

        $this->dispatch('show-edit-modal', [
            'id' => $this->jobId,
            'title' => $this->title,
            'is_active' => $this->is_active
        ]);
    }

    public function store()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ], [
            'title.required' => 'Nama lowongan harus diisi',
            'title.max' => 'Nama lowongan maksimal 255 karakter'
        ]);

        try {
            Job::create([
                'title' => $this->title,
                'is_active' => $this->is_active
            ]);

            $this->resetForm();
            $this->dispatch('job-created');
        } catch (\Exception $e) {
            $this->dispatch('job-error', ['message' => 'Gagal menambahkan lowongan']);
        }
    }

    public function update()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ], [
            'title.required' => 'Nama lowongan harus diisi',
            'title.max' => 'Nama lowongan maksimal 255 karakter'
        ]);

        try {
            $job = Job::findOrFail($this->jobId);
            $job->update([
                'title' => $this->title,
                'is_active' => $this->is_active
            ]);

            $this->resetForm();
            $this->dispatch('job-updated');
        } catch (\Exception $e) {
            $this->dispatch('job-error', ['message' => 'Gagal mengupdate lowongan']);
        }
    }

    #[On('confirm-delete')]
    public function delete($id)
    {
        try {
            Job::findOrFail($id)->delete();
            $this->dispatch('job-deleted');
        } catch (\Exception $e) {
            $this->dispatch('job-error', ['message' => 'Gagal menghapus lowongan']);
        }
    }

    private function resetForm()
    {
        $this->reset(['jobId', 'title', 'is_active']);
        $this->is_active = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
