<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BannersForm extends Component
{
    use WithFileUploads;

    public ?Banners $banners = null;

    public $judul = '';

    public $gambar;

    public $existingImage = null; // nama file lama di DB

    public $deskripsi = '';

    public $status = '';

    /**
     * Jadwal tayang (opsional). Kosong = tanpa batas waktu, sehingga banner
     * tanpa jadwal berperilaku persis seperti sebelum fitur ini ada.
     */
    public $mulai_tayang = '';

    public $selesai_tayang = '';

    public $mode = 'create';

    public function mount()
    {
        if ($this->banners) {
            $this->judul = $this->banners->judul;
            $this->existingImage = $this->banners->gambar;
            $this->deskripsi = $this->banners->deskripsi;
            $this->status = $this->banners->status;
            // format datetime-local: Y-m-d\TH:i
            $this->mulai_tayang = $this->banners->mulai_tayang?->format('Y-m-d\TH:i') ?? '';
            $this->selesai_tayang = $this->banners->selesai_tayang?->format('Y-m-d\TH:i') ?? '';
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $rules = [
            'judul' => 'required|min:3',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:active,non-active',
            'mulai_tayang' => 'nullable|date',
            // after_or_equal hanya diperiksa bila mulai_tayang diisi; kalau
            // kosong, selesai_tayang berdiri sendiri sbg "tayang sampai".
            'selesai_tayang' => 'nullable|date'.($this->mulai_tayang ? '|after_or_equal:mulai_tayang' : ''),
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
            $random = rand(10000, 99999);
            // Disimpan sebagai WebP (lihat App\Support\GambarWebp). Banner adalah
            // gambar TERBERAT di situs — tiga berkas PNG sempat memakan 5,7 MB
            // atau 78% bobot beranda.
            $filename = \App\Support\GambarWebp::simpan($this->gambar, 'img/banners', 'Banners_'.$random);

            // simpan hanya nama file ke DB
            Banners::create([
                'judul' => $this->judul,
                'gambar' => $filename, // cuma nama file
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
                'mulai_tayang' => $this->mulai_tayang ?: null,
                'selesai_tayang' => $this->selesai_tayang ?: null,
            ]);

            session()->flash('successCreated', 'Data Banner berhasil ditambahkan!');
            $this->dispatch('Banners-created');
            $this->resetForm();

            return redirect()->route('admin.Banners.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan Data Banners: '.$e->getMessage());
        }
    }

    private function updateBanners()
    {
        try {
            $data = [
                'judul' => $this->judul,
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
                'mulai_tayang' => $this->mulai_tayang ?: null,
                'selesai_tayang' => $this->selesai_tayang ?: null,
            ];

            if ($this->gambar && is_object($this->gambar)) {
                // hapus file lama kalau ada
                if ($this->existingImage && Storage::disk('public')->exists('img/banners/'.$this->existingImage)) {
                    Storage::disk('public')->delete('img/banners/'.$this->existingImage);
                }

                // upload baru → replace
                $random = rand(10000, 99999);
                $filename = \App\Support\GambarWebp::simpan($this->gambar, 'img/banners', 'Banners_'.$random);
                $data['gambar'] = $filename;
            } else {
                $data['gambar'] = $this->existingImage; // pakai gambar lama
            }

            $this->banners->update($data);

            session()->flash('successUpdated', 'Perubahan Data Banners berhasil disimpan!');
            $this->dispatch('Banners-updated');
            $this->resetForm();

            return redirect()->route('admin.Banners.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate Data Banners: '.$e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->judul = '';
        $this->gambar = '';
        $this->deskripsi = '';
        $this->status = '';
        $this->mulai_tayang = '';
        $this->selesai_tayang = '';
    }

    public function render()
    {
        return view('livewire.pages.admin.Banners.Banners-form');
    }
}
