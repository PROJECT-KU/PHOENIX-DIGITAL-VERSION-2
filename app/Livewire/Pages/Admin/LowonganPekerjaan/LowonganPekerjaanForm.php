<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use App\Models\Lowongan;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LowonganPekerjaanForm extends Component
{
    public ?Lowongan $lowongan = null;

    public string $title = '';

    public string $isActive = 'active';

    public string $slug = '';

    public string $requirements = '';

    public string $descriptions = '';

    public string $unit = 'asthana';

    public string $jenis = '';

    public string $polaKerja = '';

    public string $lokasi = '';

    public string $batasLamaran = '';

    public string $ringkasan = '';

    public string $gaji = '';

    public $jumlahPosisi = null;

    public $lowonganId = null;

    public $mode = 'create';

    public function mount($lowongan = null)
    {
        if ($lowongan) {
            $this->lowongan = $lowongan;
            $this->title = $this->lowongan->title;
            $this->isActive = $this->lowongan->is_active ?? '';
            $this->slug = $this->lowongan->slug;
            $this->descriptions = $this->lowongan->descriptions ?? '';
            $this->requirements = $this->lowongan->requirements ?? '';
            $this->unit = $this->lowongan->unit ?: 'asthana';
            $this->jenis = $this->lowongan->jenis ?? '';
            $this->polaKerja = $this->lowongan->pola_kerja ?? '';
            $this->lokasi = $this->lowongan->lokasi ?? '';
            $this->batasLamaran = $this->lowongan->batas_lamaran?->format('Y-m-d') ?? '';
            $this->ringkasan = $this->lowongan->ringkasan ?? '';
            $this->gaji = $this->lowongan->gaji ?? '';
            $this->jumlahPosisi = $this->lowongan->jumlah_posisi;

            $this->dispatch('load-quill-content', [
                'descriptions' => $this->descriptions,
                'requirements' => $this->requirements,
            ]);

            $this->mode = 'edit';
        }
    }

    protected function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'isActive' => 'required|in:active,non-active',
            'requirements' => 'required|string',
            'descriptions' => 'required|string',
            'unit' => ['required', Rule::in(array_keys(Lowongan::UNIT))],
            'jenis' => ['nullable', Rule::in(array_keys(Lowongan::JENIS))],
            'polaKerja' => ['nullable', Rule::in(array_keys(Lowongan::POLA_KERJA))],
            'lokasi' => 'nullable|string|max:100',
            'batasLamaran' => 'nullable|date',
            'ringkasan' => 'nullable|string|max:255',
            'gaji' => 'nullable|string|max:100',
            'jumlahPosisi' => 'nullable|integer|min:1|max:999',
        ];

        if ($this->mode == 'edit' && $this->lowongan) {
            $rules['slug'] = 'required|string|max:255|unique:lowongans,slug,'.$this->lowongan->id;
        } else {
            $rules['slug'] = 'required|string|max:255|unique:lowongans,slug,';
        }

        return $rules;
    }

    public function updatingTitle($value)
    {
        $this->slug = Str::slug($value);
    }

    public function save()
    {
        $this->validate();

        if ($this->mode == 'create') {
            $this->createLowongan();
        } else {
            $this->updateLowongan();
        }
    }

    public function createLowongan()
    {
        try {
            Lowongan::create([
                'title' => $this->title,
                'slug' => $this->slug,
                'is_active' => $this->isActive,
                'requirements' => $this->requirements,
                'descriptions' => $this->descriptions,
                ...$this->rincian(),
            ]);
            $this->resetForm();
            session()->flash('success', 'Berhasil menambah data lowongan');
            $this->redirectRoute('admin.lowongan.index', navigate: true);
        } catch (Exception $e) {
            session()->flash('error', 'Gagal menambah data lowongan: '.$e->getMessage());
        }
    }

    public function updateLowongan()
    {
        try {
            $this->lowongan->update([
                'title' => $this->title,
                'slug' => $this->slug,
                'is_active' => $this->isActive,
                'descriptions' => $this->descriptions,
                'requirements' => $this->requirements,
                ...$this->rincian(),
            ]);
            $this->resetForm();
            session()->flash('success', 'Berhasil memperbarui data lowongan');
            $this->redirectRoute('admin.lowongan.index', navigate: true);
        } catch (Exception $e) {
            session()->flash('error', 'Gagal memperbarui data lowongan: '.$e->getMessage());
        }
    }

    /** Isian rincian situs; yang dikosongkan disimpan sebagai NULL. */
    private function rincian(): array
    {
        $kosongJadiNull = fn ($v) => ($v === '' || $v === null) ? null : $v;

        return [
            'unit' => $this->unit ?: 'asthana',
            'jenis' => $kosongJadiNull($this->jenis),
            'pola_kerja' => $kosongJadiNull($this->polaKerja),
            'lokasi' => $kosongJadiNull(trim($this->lokasi)),
            'batas_lamaran' => $kosongJadiNull($this->batasLamaran),
            'ringkasan' => $kosongJadiNull(trim($this->ringkasan)),
            'gaji' => $kosongJadiNull(trim($this->gaji)),
            'jumlah_posisi' => $kosongJadiNull($this->jumlahPosisi),
        ];
    }

    public function resetForm()
    {
        $this->title = '';
        $this->isActive = '';
        $this->descriptions = '';
        $this->requirements = '';
    }

    public function render()
    {
        return view('livewire.pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-form');
    }
}
