<?php

namespace App\Livewire\Pages\Admin\Promo;

use App\Models\Promo;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class PromoList extends Component
{
    use WithPagination;
    public $searchDataPromo = '';

    public function updatedSearchDataProduct()
    {
        $this->resetPage();
    }

    public function deleteDataProduct($id)
    {
        try {
            $product = Promo::findOrFail($id);

            $product->delete();

            $this->dispatch('promo-deleted');
        } catch (\Exception $e) {
            $this->dispatch('delete-promo-error', message: 'Gagal menghapus promo!');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $Datapromo = Promo::latest()
            ->where('nama_promo', 'like', "%{$this->searchDataPromo}%")
            ->orWhere('diskon_rupiah', 'like', "%{$this->searchDataPromo}%")
            ->orWhere('diskon_persen', 'like', "%{$this->searchDataPromo}%")
            ->paginate(10);

        return view('livewire.pages.admin.promo.promo-list', ['Promo' => $Datapromo]);
    }
}
