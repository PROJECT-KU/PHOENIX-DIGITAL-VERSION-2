<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use App\Models\Lowongan;
use Exception;
use Illuminate\Support\Str;
use Livewire\Component;

class LowonganPekerjaanForm extends Component
{
    public ?Lowongan $lowongan = null;

    public string $title;

    public string $isActive = 'active';

    public string $slug;

    public string $requirements;

    public string $descriptions;

    public $lowonganId = null;

    public $mode = 'create';

    public function mount($lowongan = null)
    {
        if ($lowongan) {
            $this->lowongan = $lowongan;
            $this->title = $this->lowongan->title;
            $this->isActive = $this->lowongan->is_active ?? '';
            $this->descriptions = $this->lowongan->descriptions ?? '';
            $this->requirements = $this->lowongan->requirements ?? '';
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
        ];

        if ($this->mode == 'edit' && $this->lowongan) {
            $rules['slug'] = 'required|string|max:255|unique:tbl_jobs,slug,'.$this->lowongan->id;
        } else {
            $rules['slug'] = 'required|string|max:255|unique:tbl_jobs,slug,';
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
            ]);
            $this->resetForm();
            session()->flash('success', 'berhasil menambah data lowongan');
            $this->redirectRoute('admin.lowongan.index', navigate: true);
        } catch (Exception $e) {
            dump($e->getMessage());
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
            ]);
            $this->resetForm();
            session()->flash('success', 'berhasil update data lowongan');
            $this->redirectRoute('admin.lowongan.index', navigate: true);
        } catch (Exception $e) {
            dump($e->getMessage());
        }
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
