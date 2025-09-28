<?php

namespace App\Livewire\Pages\Admin\Product;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithFileUploads;


class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public $nama_akun = '';
    public $image;
    public $oldImage;
    public $harga_perbulan = '';
    public $harga_5_perbulan = '';
    public $harga_10_perbulan = '';
    public $harga_pertahun = '';
    public $deskripsi = '';

    public $mode = 'create';

    public function mount($product = null)
    {
        if ($product) {
            $this->product          = $product;
            $this->nama_akun        = $product->nama_akun;
            $this->image            = null;
            $this->oldImage         = $product->image;
            $this->harga_perbulan   = $product->harga_perbulan;
            $this->harga_5_perbulan = $product->harga_5_perbulan;
            $this->harga_10_perbulan = $product->harga_10_perbulan;
            $this->harga_pertahun   = $product->harga_pertahun;
            $this->deskripsi        = $product->deskripsi;
            $this->mode             = 'edit';
        }
    }

    public function save()
    {
        $this->validate([
            'nama_akun'        => 'required|min:3',
            'image'            => $this->mode === 'create'
                ? 'required|image|max:2048'
                : 'nullable|image|max:2048',
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
            $imagePath = $this->image ? $this->image->store('products', 'public') : null;

            Product::create([
                'nama_akun'        => $this->nama_akun,
                'image'            => $imagePath,
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
            $imagePath = $this->oldImage;

            if ($this->image) {
                $imagePath = $this->image->store('products', 'public');
            }

            $this->product->update([
                'nama_akun'        => $this->nama_akun,
                'image'            => $imagePath,
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
        $this->nama_akun        = '';
        $this->image            = null;
        $this->oldImage         = null;
        $this->harga_perbulan   = '';
        $this->harga_5_perbulan = '';
        $this->harga_10_perbulan = '';
        $this->harga_pertahun   = '';
        $this->deskripsi        = '';
    }

    public function render()
    {

        return view('livewire.pages.admin.product.product-form');
    }
}
