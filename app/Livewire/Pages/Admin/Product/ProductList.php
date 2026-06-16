<?php

namespace App\Livewire\Pages\Admin\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
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
        try {
            $product = Product::findOrFail($id);

            if (! empty($product->image) && Storage::disk('public')->exists('img/Product/' . $product->image)) {
                Storage::disk('public')->delete('img/Product/' . $product->image);
            }

            $product->delete();

            $this->dispatch('product-deleted');
        } catch (\Exception $e) {
            $this->dispatch('delete-product-error', message: 'Gagal menghapus produk!');
        }
    }

    public function render()
    {
        $Dataproduct = Product::latest()
            ->where('nama_akun', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_awal', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_perbulan', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_5_perbulan', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_10_perbulan', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('harga_pertahun', 'like', "%{$this->searchDataProduct}%")
            ->orWhere('deskripsi', 'like', "%{$this->searchDataProduct}%")
            ->paginate(10);

        return view('livewire.pages.admin.product.product-list', [
            'DataProduct' => $Dataproduct,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
