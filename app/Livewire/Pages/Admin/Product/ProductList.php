<?php

namespace App\Livewire\Pages\Admin\Product;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;
    public $searchDataProduct = '';

    public function updatedSearchDataProduct()
    {
        $this->resetPage();
    }

    public function deleteDataProduct($id)
    {
        $Dataproduct = Product::find($id);

        if (!$Dataproduct) {
            $this->dispatch('delete-error', ['message' => 'Data Product tidak ditemukan!'], browserEvent: true);
            return;
        }

        $Dataproduct->delete();

        $this->dispatch('DataProduct-deleted', ['id' => $id], browserEvent: true);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $Dataproduct = Product::latest()
            ->where('nama_akun', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_perbulan', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_5_perbulan', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_10_perbulan', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_pertahun', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('deskripsi', 'like', "%{$this->searchDataProduct}%")
            ->paginate(10);

        return view('livewire.pages.admin.product.product-list', [
            'DataProduct' => $Dataproduct
        ]);
    }
}
