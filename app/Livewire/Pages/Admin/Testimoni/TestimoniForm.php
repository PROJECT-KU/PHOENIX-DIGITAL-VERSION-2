<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Models\Testimoni;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TestimoniForm extends Component
{
    use WithFileUploads;

    public ?Testimoni $testimoni = null;

    public $nama = '';

    public $peran = '';

    public $pesan = '';

    public $rating = 5;

    public $foto;

    public $existingImage = null; // nama file lama di DB

    public $status = '';

    public $mode = 'create';

    public function mount()
    {
        if ($this->testimoni) {
            $this->nama = $this->testimoni->nama;
            $this->peran = $this->testimoni->peran;
            $this->pesan = $this->testimoni->pesan;
            $this->rating = $this->testimoni->rating;
            $this->existingImage = $this->testimoni->foto;
            $this->status = $this->testimoni->status;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $rules = [
            'nama' => 'required|min:3',
            'peran' => 'nullable|string|max:100',
            'pesan' => 'required|string|min:5',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:active,non-active',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ];

        $this->validate($rules);

        if ($this->mode === 'create') {
            $this->createTestimoni();
        } else {
            $this->updateTestimoni();
        }
    }

    private function createTestimoni()
    {
        try {
            $filename = null;
            if ($this->foto && is_object($this->foto)) {
                $random = rand(10000, 99999);
                $filename = 'Testimoni_' . $random . '.' . $this->foto->getClientOriginalExtension();
                $this->foto->storeAs('img/testimoni', $filename, 'public');
            }

            Testimoni::create([
                'nama' => $this->nama,
                'peran' => $this->peran,
                'pesan' => $this->pesan,
                'rating' => $this->rating,
                'foto' => $filename,
                'status' => $this->status,
            ]);

            session()->flash('successCreated', 'Data Testimoni berhasil ditambahkan!');
            $this->dispatch('testimoni-created');
            $this->resetForm();

            return redirect()->route('admin.testimoni.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan Data Testimoni: ' . $e->getMessage());
        }
    }

    private function updateTestimoni()
    {
        try {
            $data = [
                'nama' => $this->nama,
                'peran' => $this->peran,
                'pesan' => $this->pesan,
                'rating' => $this->rating,
                'status' => $this->status,
            ];

            if ($this->foto && is_object($this->foto)) {
                if ($this->existingImage && Storage::disk('public')->exists('img/testimoni/' . $this->existingImage)) {
                    Storage::disk('public')->delete('img/testimoni/' . $this->existingImage);
                }

                $random = rand(10000, 99999);
                $filename = 'Testimoni_' . $random . '.' . $this->foto->getClientOriginalExtension();
                $this->foto->storeAs('img/testimoni', $filename, 'public');
                $data['foto'] = $filename;
            } else {
                $data['foto'] = $this->existingImage;
            }

            $this->testimoni->update($data);

            session()->flash('successUpdated', 'Perubahan Data Testimoni berhasil disimpan!');
            $this->dispatch('testimoni-updated');
            $this->resetForm();

            return redirect()->route('admin.testimoni.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate Data Testimoni: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->nama = '';
        $this->peran = '';
        $this->pesan = '';
        $this->rating = 5;
        $this->foto = '';
        $this->status = '';
    }

    public function render()
    {
        return view('livewire.pages.admin.testimoni.testimoni-form');
    }
}
