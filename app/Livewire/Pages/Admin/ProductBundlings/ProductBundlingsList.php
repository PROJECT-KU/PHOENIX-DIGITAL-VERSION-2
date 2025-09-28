<?php

namespace App\Livewire\Pages\Admin\ProductBundlings;

use App\Models\ProductBundlings;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class ProductBundlingsList extends Component
{
    use WithPagination;

    public $searchProductBundlings = '';

    // Reset page ketika search berubah
    public function updatedSearchProductBundlings()
    {
        $this->resetPage();
    }

    public function deleteProductBundlings($id)
    {
        $ProductBundlings = ProductBundlings::find($id);

        if (!$ProductBundlings) {
            $this->dispatch('delete-error', message: 'Data Bundling tidak ditemukan!');
            return;
        }

        // Hapus file fisik jika ada
        if ($ProductBundlings->gambar) {
            $filePath = storage_path('app/public/img/ProductBundlings/' . $ProductBundlings->gambar);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Hapus record dari DB
        $ProductBundlings->delete();

        $this->dispatch('ProductBundlings-deleted', id: $id);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        $ProductBundlings = ProductBundlings::query()
            ->where('nama_paket', 'like', "%{$this->searchProductBundlings}%")
            ->where('product_1', 'like', "%{$this->searchProductBundlings}%")
            ->where('product_2', 'like', "%{$this->searchProductBundlings}%")
            ->where('product_3', 'like', "%{$this->searchProductBundlings}%")
            ->where('product_4', 'like', "%{$this->searchProductBundlings}%")
            ->where('product_5', 'like', "%{$this->searchProductBundlings}%")
            ->where('harga_awal', 'like', "%{$this->searchProductBundlings}%")
            ->where('harga_bundling', 'like', "%{$this->searchProductBundlings}%")
            ->orWhere('deskripsi', 'like', "%{$this->searchProductBundlings}%")
            ->orWhere('status', 'like', "%{$this->searchProductBundlings}%")
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.ProductBundlings.ProductBundlings-list', [
            'ProductBundlings' => $ProductBundlings,
        ]);
    }
}
