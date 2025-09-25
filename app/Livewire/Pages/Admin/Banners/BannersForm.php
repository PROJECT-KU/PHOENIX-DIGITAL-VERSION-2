<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class BannersForm extends Component
{
    use WithFileUploads;
    public ?Banners $Banners = null;
    public $judul = '';
    public $gambar;
    public $deskripsi = '';
    public $status = '';

    public $mode = 'create';

    public function mount($Banners = null)
    {
        if ($Banners) {
            $this->Banners = $Banners;
            $this->judul = $this->Banners->judul;
            $this->gambar = $this->Banners->gambar;
            $this->deskripsi = $this->Banners->deskripsi;
            $this->status = $Banners->status;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $this->validate([
            'judul'      => 'required|min:3',
            'gambar'     => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'deskripsi'  => 'nullable|string',
            'status'     => 'required|in:active,non-active',
        ]);
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
            $random = rand(10000, 99999);
            $filename = 'Banners_' . $random . '.' . $this->gambar->getClientOriginalExtension();

            // simpan file fisik ke folder public/assets/img/banner
            $this->gambar->storeAs('assets/img/banner', $filename, 'public');

            // simpan hanya path di DB
            Banners::create([
                'judul'     => $this->judul,
                'gambar'    => '/url/Banners/' . $filename, // <<=== hasil akhir di DB
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

            if ($this->gambar) {
                $random = rand(10000, 99999);
                $filename = 'Banners_' . $random . '.' . $this->gambar->getClientOriginalExtension();
                $this->gambar->storeAs('assets/img/banner', $filename, 'public');
                $data['gambar'] = '/url/Banners/' . $filename; // simpan path baru
            }

            $this->Banners->update($data);

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

        return view('livewire.pages.admin.Banners.Banners-form', [
            'Banners' => $this->Banners,
        ]);
    }
}
