<?php

namespace App\Livewire\Pages\Admin\Product;

use App\Models\Product;
use Livewire\Component;

class ProductForm extends Component
{
    public ?Product $product = null;
    public $nama_akun = '';
    public $username_akun = '';
    public $password_akun = '';
    public $link_login_akun = '';
    public $pj_akun = '';
    public $deskripsi = '';
    public $harga_satuan = '';

    public $mode = 'create';

    public function mount($product = null)
    {
        if ($product) {
            $this->product = $product;
            $this->nama_akun = $this->product->nama_akun;
            $this->username_akun = $this->product->username_akun;
            $this->password_akun = $this->product->password_akun;
            $this->link_login_akun = $this->product->link_login_akun;
            $this->pj_akun = $this->product->pj_akun;
            $this->harga_satuan = $this->product->harga_satuan;
            $this->deskripsi = $this->product->deskripsi;
            $this->mode = 'edit';
        }
    }

    public function save()
    {
        $this->validate([
            'nama_akun'      => 'required|min:3',
            'username_akun'  => 'required',
            'password_akun'  => 'required|min:6',
            'link_login_akun' => 'nullable|url',
            'pj_akun'        => 'required',
            'deskripsi'      => 'nullable|string',
            'harga_satuan'   => 'required|numeric|min:1000',
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
                'nama_akun'      => $this->nama_akun,
                'username_akun'  => $this->username_akun,
                'password_akun'  => $this->password_akun,
                'link_login_akun' => $this->link_login_akun,
                'pj_akun'        => $this->pj_akun,
                'deskripsi'      => $this->deskripsi,
                'harga_satuan'   => $this->harga_satuan,
            ]);

            $this->dispatch('product-created');
            $this->resetForm();
            redirect()->route('admin.product.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan product: ' . $e->getMessage());
            $this->dispatch('failed-create-data-product');
        }
    }

    private function updateProduct()
    {
        try {
            $this->product->update([
                'nama_akun'      => $this->nama_akun,
                'username_akun'  => $this->username_akun,
                'password_akun'  => $this->password_akun,
                'link_login_akun' => $this->link_login_akun,
                'pj_akun'        => $this->pj_akun,
                'deskripsi'      => $this->deskripsi,
                'harga_satuan'   => $this->harga_satuan,
            ]);

            $this->dispatch('product-updated');
            $this->resetForm();
            redirect()->route('admin.product.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate product: ' . $e->getMessage());
            $this->dispatch('failed-update-data-product');
        }
    }

    private function resetForm()
    {
        $this->nama_akun      = '';
        $this->username_akun  = '';
        $this->password_akun  = '';
        $this->link_login_akun = '';
        $this->pj_akun        = '';
        $this->deskripsi      = '';
        $this->harga_satuan   = '';
    }
    public function render()
    {
        return view('livewire.pages.admin.product.product-form');
    }
}
