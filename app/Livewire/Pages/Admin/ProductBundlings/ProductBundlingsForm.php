<?php

namespace App\Livewire\Pages\Admin\ProductBundlings;

use App\Models\ProductBundlings;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ProductBundlingsForm extends Component
{
    use WithFileUploads;

    public ?ProductBundlings $product_bundlings = null;

    public $nama_paket = '';
    public $product_1 = '';
    public $product_2 = '';
    public $product_3 = '';
    public $product_4 = '';
    public $product_5 = '';
    public $harga_awal = '';
    public $harga_bundling = '';
    public $gambar;
    public $existingImage = null;
    public $deskripsi = '';
    public $status = '';

    public $mode = 'create';
    public $showProductsContainer = true;

    public $products;

    public function mount()
    {
        $this->products = Product::all();
        if ($this->product_bundlings) {
            $this->nama_paket           = $this->product_bundlings->nama_paket;
            $this->product_1            = $this->product_bundlings->product_1;
            $this->product_2            = $this->product_bundlings->product_2;
            $this->product_3            = $this->product_bundlings->product_3;
            $this->product_4            = $this->product_bundlings->product_4;
            $this->product_5            = $this->product_bundlings->product_5;
            $this->harga_awal           = $this->product_bundlings->harga_awal;
            $this->harga_bundling       = $this->product_bundlings->harga_bundling;
            $this->existingImage        = $this->product_bundlings->gambar;
            $this->deskripsi            = $this->product_bundlings->deskripsi;
            $this->status               = $this->product_bundlings->status;
            $this->mode                 = 'edit';
        }
    }

    public function save()
    {
        $rules = [
            'nama_paket'        => 'required|min:3',
            'harga_awal'        => 'required',
            'harga_bundling'    => 'required',
            'deskripsi'         => 'nullable|string',
            'status'            => 'required|in:active,non-active',
        ];

        if ($this->mode === 'create') {
            $rules['gambar'] = 'required|image|mimes:png,jpg,jpeg|max:5120';
        } else {
            $rules['gambar'] = 'nullable|image|mimes:png,jpg,jpeg|max:5120';
        }

        $this->validate($rules);

        $selectedProducts = array_filter([
            $this->product_1,
            $this->product_2,
            $this->product_3,
            $this->product_4,
            $this->product_5,
        ]);

        if (count($selectedProducts) !== count(array_unique($selectedProducts))) {
            $this->addError('products_selected', 'Produk dalam bundling tidak boleh sama.');
            return;
        }

        if ($this->mode === 'create') {
            $this->createProductBundlings();
        } else {
            $this->updateProductBundlings();
        }
    }

    private function createProductBundlings()
    {
        try {
            $random   = rand(10000, 99999);
            $filename = 'ProductBundlings_' . $random . '.' . $this->gambar->getClientOriginalExtension();

            $this->gambar->storeAs('img/ProductBundlings', $filename, 'public');

            ProductBundlings::create([
                'nama_paket'         => $this->nama_paket,
                'product_1'          => $this->product_1,
                'product_2'          => $this->product_2,
                'product_3'          => $this->product_3,
                'product_4'          => $this->product_4,
                'product_5'          => $this->product_5,
                'harga_awal'         => $this->harga_awal,
                'harga_bundling'     => $this->harga_bundling,
                'gambar'             => $filename,
                'deskripsi'          => $this->deskripsi,
                'status'             => $this->status,
            ]);

            session()->flash('success', 'Data Bundling berhasil ditambahkan!');
            $this->dispatch('ProductBundlings-created');
            $this->resetForm();

            return redirect()->route('admin.Bundlings.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Data Bundling: ' . $e->getMessage());
        }
    }

    private function updateProductBundlings()
    {
        try {
            $data = [
                'nama_paket'         => $this->nama_paket,
                'product_1'          => $this->product_1,
                'product_2'          => $this->product_2,
                'product_3'          => $this->product_3,
                'product_4'          => $this->product_4,
                'product_5'          => $this->product_5,
                'harga_awal'         => $this->harga_awal,
                'harga_bundling'     => $this->harga_bundling,
                'deskripsi'          => $this->deskripsi,
                'status'             => $this->status
            ];

            if ($this->gambar && is_object($this->gambar)) {
                if ($this->existingImage && Storage::disk('public')->exists('img/ProductBundlings/' . $this->existingImage)) {
                    Storage::disk('public')->delete('img/ProductBundlings/' . $this->existingImage);
                }

                $random   = rand(10000, 99999);
                $filename = 'ProductBundlings_' . $random . '.' . $this->gambar->getClientOriginalExtension();
                $this->gambar->storeAs('img/ProductBundlings', $filename, 'public');
                $data['gambar'] = $filename;
            } else {
                $data['gambar'] = $this->existingImage;
            }


            if ($this->product_bundlings) {
                $this->product_bundlings->update($data);
            }

            session()->flash('success', 'Perubahan Data Bundling berhasil disimpan!');
            $this->dispatch('ProductBundlings-updated');
            $this->resetForm();

            return redirect()->route('admin.Bundlings.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate Data Bundling: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->nama_paket = '';
        $this->product_1 = '';
        $this->product_2 = '';
        $this->product_3 = '';
        $this->product_4 = '';
        $this->product_5 = '';
        $this->harga_awal = '';
        $this->harga_bundling = '';
        $this->gambar = '';
        $this->deskripsi = '';
        $this->status = '';
    }

    public function render()
    {
        $products = Product::select('id', 'nama_akun')->orderBy('nama_akun')->get();
        return view('livewire.pages.admin.ProductBundlings.ProductBundlings-form', [
            'product_bundlings' => $this->product_bundlings,
            'products' => $products,

        ]);
    }
}
