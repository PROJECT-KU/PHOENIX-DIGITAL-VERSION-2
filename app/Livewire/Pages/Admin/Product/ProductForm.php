<?php

namespace App\Livewire\Pages\Admin\Product;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;


class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public $nama_akun = '';
    public $image;
    public $existingImage = null; // nama file lama di DB
    public $harga_awal = '';
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
            $this->existingImage    = $product->image;
            $this->harga_awal   = $product->harga_awal;
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
        $rules = [
            'nama_akun'        => 'required|min:3',
            'harga_awal'   => 'nullable|numeric',
            'harga_perbulan'   => 'nullable|numeric',
            'harga_5_perbulan' => 'nullable|numeric',
            'harga_10_perbulan' => 'nullable|numeric',
            'harga_pertahun'   => 'nullable|numeric',
            'deskripsi'        => 'nullable|string',
        ];

        if ($this->mode === 'create') {
            $rules['image'] = 'required|image|mimes:png,jpg,jpeg|max:5120';
        } else {
            $rules['image'] = 'nullable|image|mimes:png,jpg,jpeg|max:5120';
        }

        $this->validate($rules);

        if ($this->mode === 'create') {
            $this->createProduct();
        } else {
            $this->updateProduct();
        }
    }

    private function createProduct()
    {
        try {
            $random   = rand(10000, 99999);
            $filename = 'Product_' . $random . '.' . $this->image->getClientOriginalExtension();

            // simpan file fisik ke folder storage/app/public/img/banners
            $this->image->storeAs('img/Product', $filename, 'public');

            Product::create([
                'nama_akun'        => $this->nama_akun,
                'image'            => $filename,
                'harga_awal'   => $this->harga_awal,
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
            $data = [
                'nama_akun'         => $this->nama_akun,
                'harga_awal'    => $this->harga_awal,
                'harga_perbulan'    => $this->harga_perbulan,
                'harga_5_perbulan'  => $this->harga_5_perbulan,
                'harga_10_perbulan' => $this->harga_10_perbulan,
                'harga_pertahun'    => $this->harga_pertahun,
                'deskripsi'         => $this->deskripsi,
            ];


            if ($this->image && is_object($this->image)) {
                if (!empty($this->existingImage) && Storage::disk('public')->exists('img/Product/' . $this->existingImage)) {
                    Storage::disk('public')->delete('img/Product/' . $this->existingImage);
                }

                $random   = rand(10000, 99999);
                $filename = 'Product_' . $random . '.' . $this->image->getClientOriginalExtension();
                $this->image->storeAs('img/Product', $filename, 'public');

                $data['image'] = $filename;
            } else {
                $data['image'] = $this->existingImage;
            }

            $this->product->update($data);

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
        $this->harga_awal   = '';
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
