<?php

namespace App\Livewire\Pages\Admin\Ebook;

use App\Models\Ebook;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class EbookList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteEbook($id)
    {
        if (! auth()->user()->hasPermission('delete_ebook')) {
            $this->dispatch('Ebook-deleteError', message: 'Anda tidak memiliki izin menghapus ebook.');

            return;
        }

        $ebook = Ebook::find($id);

        if (! $ebook) {
            $this->dispatch('Ebook-deleteError', message: 'Data ebook tidak ditemukan!');

            return;
        }

        if ($ebook->file && Storage::disk('local')->exists('ebooks/' . $ebook->file)) {
            Storage::disk('local')->delete('ebooks/' . $ebook->file);
        }

        $ebook->delete();

        $this->dispatch('Ebook-deleted');
    }

    public function render()
    {
        $ebooks = Ebook::query()
            ->when($this->search, function ($q) {
                $q->where('judul', 'like', "%{$this->search}%")
                    ->orWhere('deskripsi', 'like', "%{$this->search}%")
                    ->orWhere('status', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.ebook.ebook-list', [
            'ebooks' => $ebooks,
        ])->layout('livewire.layout.templateindex');
    }
}
