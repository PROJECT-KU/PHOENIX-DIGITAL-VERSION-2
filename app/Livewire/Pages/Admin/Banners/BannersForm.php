<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class BannersForm extends Component
{
    use WithFileUploads;
    public ?Banners $banners = null;

    public $judul = '';
    public $gambar;
    public $existingImage = null; // nama file lama di DB
    public $deskripsi = '';
    public $status = '';

    public $mode = 'create';

    public function mount()
    {
        if ($this->banners) {
            $this->judul         = $this->banners->judul;
            $this->existingImage = $this->banners->gambar;
            $this->deskripsi     = $this->banners->deskripsi;
            $this->status        = $this->banners->status;
            $this->mode          = 'edit';
        }
    }

    public function save()
    {
        $rules = [
            'judul'      => 'required|min:3',
            'deskripsi'  => 'nullable|string',
            'status'     => 'required|in:active,non-active',
        ];

        if ($this->mode === 'create') {
            $rules['gambar'] = 'required|image|mimes:png,jpg,jpeg|max:5120';
        } else {
            $rules['gambar'] = 'nullable|image|mimes:png,jpg,jpeg|max:5120';
        }

        $this->validate($rules);

        if ($this->mode === 'create') {
            $this->createBanners();
        } else {
            $this->updateBanners();
        }
    }

    private function createBanners()
    {
        try {
            // generate nama unik dengan angka random
            $random   = rand(10000, 99999);
            $filename = 'Banners_' . $random . '.' . $this->gambar->getClientOriginalExtension();

            // simpan file fisik ke folder storage/app/public/img/banners
            $this->gambar->storeAs('img/banners', $filename, 'public');

            // simpan hanya nama file ke DB
            Banners::create([
                'judul'     => $this->judul,
                'gambar'    => $filename, // cuma nama file
                'deskripsi' => $this->deskripsi,
                'status'    => $this->status,
            ]);

            session()->flash('success', 'Data Banner berhasil ditambahkan!');
            $this->dispatch('Banners-created');
            $this->resetForm();

            return redirect()->route('admin.Banners.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Data Banners: ' . $e->getMessage());
        }
    }

    private function updateBanners()
    {
        try {
            $data = [
                'judul'     => $this->judul,
                'deskripsi' => $this->deskripsi,
                'status'    => $this->status,
            ];

            if ($this->gambar && is_object($this->gambar)) {
                // hapus file lama kalau ada
                if ($this->existingImage && Storage::disk('public')->exists('img/banners/' . $this->existingImage)) {
                    Storage::disk('public')->delete('img/banners/' . $this->existingImage);
                }

                // upload baru → replace
                $random   = rand(10000, 99999);
                $filename = 'Banners_' . $random . '.' . $this->gambar->getClientOriginalExtension();
                $this->gambar->storeAs('img/banners', $filename, 'public');
                $data['gambar'] = $filename;
            } else {
                $data['gambar'] = $this->existingImage; // pakai gambar lama
            }

            $this->banners->update($data);

            session()->flash('success', 'Perubahan Data Banners berhasil disimpan!');
            $this->dispatch('Banners-updated');
            $this->resetForm();

            return redirect()->route('admin.Banners.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate Data Banners: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->judul        = '';
        $this->gambar       = '';
        $this->deskripsi    = '';
        $this->status       = '';
    }

    public function render()
    {
        return view('livewire.pages.admin.banners.banners-form');
    }
}
