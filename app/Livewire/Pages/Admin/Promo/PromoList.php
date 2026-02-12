<?php

namespace App\Livewire\Pages\Admin\Promo;

use App\Models\Promo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PromoList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchDataPromo = '';

    protected $listeners = ['promoDeleted' => '$refresh'];

    public function updatingSearchDataPromo()
    {
        $this->resetPage();
    }

    #[On('delete-promo-data')]
    public function delete($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            $promo->delete();

            session()->flash('success', 'Promo berhasil dihapus');
            $this->dispatch('promoDeleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus promo: '.$e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $promos = Promo::query()
            ->when($this->searchDataPromo, function ($query) {
                $query->where('nama_promo', 'like', '%'.$this->searchDataPromo.'%')
                    ->orWhere('kode_promo', 'like', '%'.$this->searchDataPromo.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.promo.promo-list', ['promos' => $promos]);
    }
}
