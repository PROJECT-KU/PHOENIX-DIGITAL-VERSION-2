<?php

namespace App\Livewire\Pages\Admin\Product;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;
    public $searchProduct = '';

    public function updatedSearchProduct()
    {
        $this->resetPage();
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {
            $this->dispatch('delete-error', ['message' => 'Produk tidak ditemukan!'], browserEvent: true);
            return;
        }

        $product->delete();

        $this->dispatch('product-deleted', ['id' => $id], browserEvent: true);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        $products = Product::latest()
            ->where('nama_akun', 'like', "%{$this->searchProduct}%")
            ->orWhere('username_akun', 'like', "%{$this->searchProduct}%")
            ->orWhere('link_login_akun', 'like', "%{$this->searchProduct}%")
            ->orWhere('pj_akun', 'like', "%{$this->searchProduct}%")
            ->orWhere('deskripsi', 'like', "%{$this->searchProduct}%")
            ->orWhere('harga_satuan', 'like', "%{$this->searchProduct}%")
            ->paginate(10);

        return view('livewire.pages.admin.product.product-list', [
            'product' => $products
        ]);
    }
}
