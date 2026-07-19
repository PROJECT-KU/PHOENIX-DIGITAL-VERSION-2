<?php

namespace App\Livewire\Pages\Admin\ProductBundlings;

use App\Models\ProductBundlings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

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
        if (! auth()->user()->hasPermission('delete_bundlings')) {
            $this->dispatch('delete-error', message: 'Anda tidak memiliki izin menghapus bundling.');

            return;
        }

        $ProductBundlings = ProductBundlings::find($id);

        if (! $ProductBundlings) {
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

    public function render()
    {
        $search = trim($this->searchProductBundlings);

        $ProductBundlings = ProductBundlings::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_paket', 'like', "%{$search}%")
                        ->orWhere('harga_awal', 'like', "%{$search}%")
                        ->orWhere('harga_bundling', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('product1', fn ($p) => $p->where('nama_akun', 'like', "%{$search}%"))
                        ->orWhereHas('product2', fn ($p) => $p->where('nama_akun', 'like', "%{$search}%"))
                        ->orWhereHas('product3', fn ($p) => $p->where('nama_akun', 'like', "%{$search}%"))
                        ->orWhereHas('product4', fn ($p) => $p->where('nama_akun', 'like', "%{$search}%"))
                        ->orWhereHas('product5', fn ($p) => $p->where('nama_akun', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.ProductBundlings.ProductBundlings-list', [
            'ProductBundlings' => $ProductBundlings,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
