<?php

namespace App\Livewire\Pages\Admin\Product;

use Livewire\Component;
use App\Models\Product;


class ProductForm extends Component
{
    public ?Product $product = null;

    public $nama_akun = '';
    public $image = '';
    public $harga_perbulan = '';
    public $harga_5_perbulan = '';
    public $harga_10_perbulan = '';
    public $harga_pertahun = '';
    public $deskripsi = '';

    public $mode = 'create';

    public function mount($product = null)
    {
        if ($product) {
            $this->product = $product;
            $this->nama_akun       = $product->nama_akun;
            $this->image           = $product->image;
            $this->harga_perbulan  = $product->harga_perbulan;
            $this->harga_5_perbulan = $product->harga_5_perbulan;
            $this->harga_10_perbulan = $product->harga_10_perbulan;
            $this->harga_pertahun  = $product->harga_pertahun;
            $this->deskripsi       = $product->deskripsi;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $this->validate([
            'nama_akun'        => 'required|min:3',
            'image'            => 'nullable|string', // bisa diubah kalau nanti upload file
            'harga_perbulan'   => 'nullable|numeric',
            'harga_5_perbulan' => 'nullable|numeric',
            'harga_10_perbulan' => 'nullable|numeric',
            'harga_pertahun'   => 'nullable|numeric',
            'deskripsi'        => 'nullable|string',
        ]);

        if ($this->mode === 'create') {
            $this->createProduct();
        } else {
            $this->updateProduct();
        }
    }

    private function createProduct()
    {
        try {
            Product::create([
                'nama_akun'        => $this->nama_akun,
                'image'            => $this->image,
                'harga_perbulan'   => $this->harga_perbulan,
                'harga_5_perbulan' => $this->harga_5_perbulan,
                'harga_10_perbulan' => $this->harga_10_perbulan,
                'harga_pertahun'   => $this->harga_pertahun,
                'deskripsi'        => $this->deskripsi,
            ]);

            session()->flash('success', 'Product berhasil ditambahkan!');
            $this->dispatch('product-created');
            $this->resetForm();
            return redirect()->route('admin.product.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Product: ' . $e->getMessage());
            $this->dispatch('failed-create-product');
        }
    }

    private function updateProduct()
    {
        try {
            $this->product->update([
                'nama_akun'        => $this->nama_akun,
                'image'            => $this->image,
                'harga_perbulan'   => $this->harga_perbulan,
                'harga_5_perbulan' => $this->harga_5_perbulan,
                'harga_10_perbulan' => $this->harga_10_perbulan,
                'harga_pertahun'   => $this->harga_pertahun,
                'deskripsi'        => $this->deskripsi,
            ]);

            session()->flash('success', 'Product berhasil diperbarui!');
            $this->dispatch('product-updated');
            $this->resetForm();
            return redirect()->route('admin.product.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal update Product: ' . $e->getMessage());
            $this->dispatch('failed-update-product');
        }
    }

    private function resetForm()
    {
        $this->nama_akun       = '';
        $this->image           = '';
        $this->harga_perbulan  = '';
        $this->harga_5_perbulan = '';
        $this->harga_10_perbulan = '';
        $this->harga_pertahun  = '';
        $this->deskripsi       = '';
    }

    public function render()
    {

        return view('livewire.pages.admin.product.product-form');
    }
}
