<?php

namespace App\Livewire\Pages\Admin\Promo;

use Livewire\Component;
use App\Models\Promo;

class PromoForm extends Component
{
    public ?Promo $dataPromo = null;
    public $nama_promo = '';
    public $diskon_rupiah = '';
    public $diskon_persen = '';

    public $mode = 'create';

    public function mount($dataPromo = null)
    {
        if ($dataPromo) {
            $this->dataPromo = $dataPromo;
            $this->nama_promo = $this->dataPromo->nama_promo;
            $this->diskon_rupiah = $this->dataPromo->diskon_rupiah;
            $this->diskon_persen = $this->dataPromo->diskon_persen ;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $this->validate([
            'nama_promo'     => 'required|string|max:255',
        ]);

        if (!$this->diskon_rupiah && !$this->diskon_persen) {
            $this->addError('diskon', 'Isi salah satu: Diskon Rupiah atau Diskon Persen');
            return;
        }

        if ($this->diskon_rupiah) {
            $this->validate([
                'diskon_rupiah' => 'numeric|min:1',
            ]);
            $this->diskon_persen = null;
        }

        if ($this->diskon_persen) {
            $this->validate([
                'diskon_persen' => 'numeric|min:1|max:100',
            ]);
            $this->diskon_rupiah = null;
        }

        if ($this->mode === 'create') {
            $this->createDataPromo();
        } else {
            $this->updateDataPromo();
        }
    }
    private function createDataPromo()
    {
        try {
            Promo::create([
                'nama_promo'      => $this->nama_promo,
                'diskon_rupiah'  => $this->diskon_rupiah,
                'diskon_persen'  => $this->diskon_persen,
            ]);

            session()->flash('success', 'Data Promo berhasil ditambahkan!');
            $this->dispatch('DataPromo-created');
            $this->resetForm();
            return redirect()->route('admin.promo.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Data Promo: ' . $e->getMessage());
            $this->dispatch('failed-create-data-DataPromo');
        }
    }

    private function updateDataPromo()
    {
        try {
            $this->dataPromo->update([
                'nama_promo'      => $this->nama_promo,
                'diskon_rupiah'  => $this->diskon_rupiah,
                'diskon_persen'  => $this->diskon_persen,
            ]);

            session()->flash('success', 'Perubahan Data Promo berhasil disimpan!');
            $this->dispatch('DataPromo-updated');
            $this->resetForm();
            return redirect()->route('admin.promo.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate Data Promo: ' . $e->getMessage());
            $this->dispatch('failed-update-data-DataPromo');
        }
    }

    private function resetForm()
    {
        $this->nama_promo       = '';
        $this->diskon_rupiah    = '';
        $this->diskon_persen    = '';
    }

    public function render()
    {
        return view('livewire.pages.admin.promo.promo-form');
    }
}
