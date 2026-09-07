<?php

namespace App\Livewire\Pages\Admin\Product;

use App\Models\Product;
use App\Support\JedaLayanan;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public $searchDataProduct = '';

    /**
     * Panel jeda layanan.
     *
     * Diletakkan di sini, bukan di menu setelan tersendiri, mengikuti kebiasaan
     * proyek: setelan diatur dari halaman tempat admin memang sudah bekerja
     * (lihat pool bonus di Penyelesaian Task dan target modal di Modal).
     */
    public bool $panelJeda = false;

    /** @var array<string, array{dijeda:bool, pesan:string}> */
    public array $jeda = [];

    public function mount(): void
    {
        $this->muatJeda();
    }

    private function muatJeda(): void
    {
        $this->jeda = [];

        foreach (JedaLayanan::daftar() as $jenis => $info) {
            $this->jeda[$jenis] = [
                'dijeda' => $info['dijeda'],
                'pesan' => $info['pesan'],
            ];
        }
    }

    /**
     * Buka/tutup satu jenis layanan. Tiap jenis berdiri sendiri — menjeda
     * plagiasi tidak menyentuh cek AI maupun parafrase.
     */
    public function alihkanJeda(string $jenis): void
    {
        if (! auth()->user()->hasPermission('edit_product')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah layanan.');

            return;
        }

        if (! array_key_exists($jenis, JedaLayanan::JENIS)) {
            return;
        }

        $jadiDijeda = ! ($this->jeda[$jenis]['dijeda'] ?? false);

        JedaLayanan::setel($jenis, $jadiDijeda, $this->jeda[$jenis]['pesan'] ?? null);
        $this->muatJeda();

        $this->dispatch('swal-success', message: $jadiDijeda
            ? JedaLayanan::JENIS[$jenis].' dijeda — pesanan baru ditutup.'
            : JedaLayanan::JENIS[$jenis].' dibuka kembali.');
    }

    /** Simpan keterangan yang dilihat pembeli, tanpa mengubah status jeda. */
    public function simpanPesanJeda(string $jenis): void
    {
        if (! auth()->user()->hasPermission('edit_product')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah layanan.');

            return;
        }

        if (! array_key_exists($jenis, JedaLayanan::JENIS)) {
            return;
        }

        JedaLayanan::setel($jenis, (bool) ($this->jeda[$jenis]['dijeda'] ?? false), $this->jeda[$jenis]['pesan'] ?? '');
        $this->muatJeda();

        $this->dispatch('swal-success', message: 'Keterangan disimpan.');
    }

    public function updatedSearchDataProduct()
    {
        $this->resetPage();
    }

    public function deleteDataProduct($id)
    {
        if (! auth()->user()->hasPermission('delete_product')) {
            $this->dispatch('delete-product-error', message: 'Anda tidak memiliki izin menghapus produk.');

            return;
        }

        try {
            $product = Product::findOrFail($id);

            if (! empty($product->image) && Storage::disk('public')->exists('img/Product/'.$product->image)) {
                Storage::disk('public')->delete('img/Product/'.$product->image);
            }

            $product->delete();

            $this->dispatch('product-deleted');
        } catch (\Exception $e) {
            $this->dispatch('delete-product-error', message: 'Gagal menghapus produk!');
        }
    }

    public function render()
    {
        $Dataproduct = Product::with('prices', 'addons')->latest()
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
            'labelJeda' => JedaLayanan::JENIS,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
