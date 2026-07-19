<?php

namespace App\Livewire\Pages\Admin\Ebook;

use App\Models\Ebook;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EbookForm extends Component
{
    use WithFileUploads;

    public ?Ebook $ebook = null;

    public $judul = '';

    public $deskripsi = '';

    public $file;            // file upload baru

    public $existingFile = null;

    public $status = 'active';

    public $mode = 'create';

    public function mount()
    {
        if ($this->ebook) {
            $this->judul = $this->ebook->judul;
            $this->deskripsi = $this->ebook->deskripsi;
            $this->existingFile = $this->ebook->file;
            $this->status = $this->ebook->status;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $rules = [
            'judul' => 'required|min:3|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:active,non-active',
            // Hanya PDF (untuk proteksi viewer view-only), maks 2MB
            'file' => ($this->mode === 'create' ? 'required' : 'nullable') . '|file|mimes:pdf|max:2048',
        ];

        $this->validate($rules, [
            'file.mimes' => 'File ebook harus berformat PDF.',
        ]);

        try {
            $filename = $this->existingFile;

            if ($this->file && is_object($this->file)) {
                // Simpan di disk PRIVAT (storage/app/ebooks) — tidak bisa diakses langsung via URL
                if ($this->existingFile && Storage::disk('local')->exists('ebooks/' . $this->existingFile)) {
                    Storage::disk('local')->delete('ebooks/' . $this->existingFile);
                }
                $filename = 'ebook_' . rand(10000, 99999) . '_' . time() . '.pdf';
                $this->file->storeAs('ebooks', $filename, 'local');
            }

            if ($this->mode === 'create') {
                Ebook::create([
                    'judul' => $this->judul,
                    'deskripsi' => $this->deskripsi,
                    'file' => $filename,
                    'status' => $this->status,
                ]);
                session()->flash('successCreated', 'Data Ebook berhasil ditambahkan!');
            } else {
                $this->ebook->update([
                    'judul' => $this->judul,
                    'deskripsi' => $this->deskripsi,
                    'file' => $filename,
                    'status' => $this->status,
                ]);
                session()->flash('successUpdated', 'Perubahan Data Ebook berhasil disimpan!');
            }

            return redirect()->route('admin.ebook.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menyimpan ebook: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ebook.ebook-form');
    }
}
