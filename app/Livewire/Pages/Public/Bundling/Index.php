<?php

namespace App\Livewire\Pages\Public\Bundling;

use App\Livewire\Concerns\MengirimPixel;
use App\Models\ProductBundlings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use MengirimPixel;

    public $perPage = 8;

    // Modal detail bundling
    public bool $showBundleDetail = false;

    public ?array $detailBundle = null;

    use WithPagination;

    public function addToCart($bundlingId)
    {
        $bundling = ProductBundlings::findOrFail($bundlingId);

        if (! $bundling) {
            $this->dispatch('cart-error', message: 'Bundling tidak ditemukan');

            return;
        }

        $cart = session()->get('cart', []);
        $cartKey = "bundling_{$bundling->id}";
        $imageName = $bundling->gambar ? basename($bundling->gambar) : null;
        $price = (int) preg_replace('/[^0-9]/', '', $bundling->harga_bundling);

        if (isset($cart[$cartKey])) {
            // Akun digital: 1 baris = 1 item, tidak menumpuk jumlah.
            $cart[$cartKey]['quantity'] = 1;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $bundling->id,
                'product_name' => $bundling->nama_paket,
                'product_image' => $imageName,
                'duration_type' => null,
                'duration_value' => null,
                // Harga coret, dibawa serta supaya PromoService tidak perlu
                // membaca database tiap kali menghitung. Inilah dasar hitung
                // diskon paket (lihat PromoService::hargaAwalPaket).
                'harga_awal' => (int) preg_replace('/[^0-9]/', '', (string) $bundling->harga_awal),
                'type' => 'bundling',
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ];
        }
        // session()->put('cart', $cart);

        // $this->dispatch('cart-updated', count: $this->getCartCount());
        // $this->dispatch('cart-success', message: 'Bundling berhasil ditambahkan ke keranjang!');
        // $this->dispatch('redirect-home');

        session()->put('cart', $cart);

        // Bila ditambah dari popup detail → tutup popup (kembali ke homepage bundling)
        $this->showBundleDetail = false;

        $this->dispatch('cart-updated', count: $this->getCartCount());
        $this->kirimPixel('AddToCart', $this->pixelDariBarisKeranjang($cart[$cartKey]));
        $this->dispatch('cart-success', message: 'Bundling berhasil ditambahkan ke keranjang!');
    }

    /** Buka modal detail bundling. */
    public function openDetail($bundlingId)
    {
        $bundling = ProductBundlings::find($bundlingId);
        if (! $bundling) {
            $this->dispatch('cart-error', message: 'Bundling tidak ditemukan.');

            return;
        }

        $durs = $bundling->durations ?? [];
        $products = [];
        foreach ([1, 2, 3, 4, 5] as $i) {
            $p = $bundling->{'product'.$i};
            if ($p) {
                $dur = $durs['product_'.$i] ?? null;
                $products[] = [
                    'nama' => $p->nama_akun,
                    'dur_value' => (int) ($dur['value'] ?? 1),
                    'dur_type' => ucfirst($dur['type'] ?? 'bulan'),
                ];
            }
        }

        $this->detailBundle = [
            'id' => $bundling->id,
            'nama' => $bundling->nama_paket,
            'gambar' => $bundling->gambar,
            'deskripsi' => $bundling->deskripsi,
            'produk' => $products,
            'harga_awal' => $bundling->harga_awal,
            'harga_bundling' => $bundling->harga_bundling,
        ];
        $this->showBundleDetail = true;
    }

    public function closeDetail()
    {
        $this->showBundleDetail = false;
    }

    private function getCartCount(): int
    {
        $cart = session()->get('cart', []);

        return count($cart);
    }

    public function loadMore()
    {
        $this->perPage += 12;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        // Tayang(): status aktif DAN berada di dalam rentang tanggalnya. Paket
        // musiman jadi hilang sendiri saat lewat, tanpa admin perlu mematikannya.
        $bundlings = ProductBundlings::tayang()->latest()->paginate($this->perPage);

        return view('livewire.pages.public.bundling.index', [
            'bundlings' => $bundlings,
        ]);
    }
}
