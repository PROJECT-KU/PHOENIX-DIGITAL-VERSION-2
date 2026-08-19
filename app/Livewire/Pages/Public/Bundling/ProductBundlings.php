<?php

namespace App\Livewire\Pages\Public\Bundling;

use App\Livewire\Concerns\MengirimPixel;
use App\Models\ProductBundlings as ModelsProductBundlings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductBundlings extends Component
{
    use MengirimPixel;
    use WithPagination;

    /** Halaman paket tersendiri: 8 per halaman. */
    public $perPage = 8;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    /**
     * Filter & urutkan, meniru halaman shop. Bila keduanya kosong, daftar paket
     * tampil persis seperti sebelumnya.
     *
     * $isi menyaring berdasarkan produk yang ADA DI DALAM paket — padanan
     * "kategori" di shop, karena paket sendiri tidak punya tipe.
     */
    public $isi = '';

    public $sortBy = '';

    /** Harga tersimpan sebagai teks berformat ("Rp 160.000"), jadi angkanya
     *  dibersihkan dulu sebelum diurutkan. Ditulis dengan REPLACE + 0 supaya
     *  jalan di MySQL maupun SQLite (yang dipakai pengujian). */
    private const ANGKA_HARGA = "REPLACE(REPLACE(REPLACE(harga_bundling,'Rp',''),'.',''),' ','') + 0";

    public function updatedIsi()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->isi = '';
        $this->sortBy = '';
        $this->resetPage();
    }

    public function mount()
    {
        $this->search = request('search', '');
    }

    #[On('search-updated')]
    public function updateSearch($search)
    {
        // $this->search = $search;

        // if (!empty(trim($search))) {
        //     $this->redirect('/shop?search=' . urlencode($search));
        // } else {
        //     $this->redirect('/shop', navigate: true);
        // }
    }

    public function clearSearch()
    {
        // $this->search = '';
        // $this->redirect('/shop', navigate: true);
    }

    public function addToCart($bundlingId)
    {
        // dd($bundlingId);
        $bundling = ModelsProductBundlings::findOrFail($bundlingId);

        $cart = session()->get('cart', []);
        $cartKey = "bundling_{$bundling->id}";
        $imageName = $bundling->gambar ? basename($bundling->gambar) : null;
        // Harga dasar mengikuti keadaan promo: harga awal bila paket kena
        // promo, harga paket bila tidak (lihat HargaPaket::dasarKeranjang).
        $price = \App\Support\HargaPaket::dasarKeranjang($bundling);

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

        $this->dispatch('cart-updated', count: $this->getCartCount());
        $this->kirimPixel('AddToCart', $this->pixelDariBarisKeranjang($cart[$cartKey]));
        $this->dispatch('cart-success', message: 'Bundling berhasil ditambahkan ke keranjang!');
    }

    private function getCartCount(): int
    {
        $cart = session()->get('cart', []);

        return count($cart);
    }

    /**
     * Produk yang benar-benar dipakai oleh paket yang sedang tayang — dipakai
     * sebagai isi dropdown, supaya tidak ada pilihan yang hasilnya kosong.
     */
    private function pilihanIsi()
    {
        $ids = ModelsProductBundlings::tayang()
            ->get(['product_1', 'product_2', 'product_3', 'product_4', 'product_5'])
            ->flatMap(fn ($p) => [$p->product_1, $p->product_2, $p->product_3, $p->product_4, $p->product_5])
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return \App\Models\Product::whereIn('id', $ids)
            ->orderBy('nama_akun')
            ->pluck('nama_akun', 'id');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $bundlings = ModelsProductBundlings::with([
            'product1',
            'product2',
            'product3',
            'product4',
            'product5',
        ])
            // tayang(): status aktif DAN di dalam rentang tanggalnya.
            // Sebelumnya hanya memeriksa status, sehingga paket musiman yang
            // sudah lewat jadwalnya tetap muncul dan bisa dibeli di sini.
            ->tayang()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_paket', 'like', "%{$this->search}%")
                        ->orWhere('deskripsi', 'like', "%{$this->search}%");
                });
            })
            ->when($this->isi, function ($q) {
                // Paket menyimpan isinya di lima kolom terpisah.
                $q->where(function ($cari) {
                    foreach ([1, 2, 3, 4, 5] as $i) {
                        $cari->orWhere('product_'.$i, $this->isi);
                    }
                });
            })
            ->when($this->sortBy, function ($q) {
                match ($this->sortBy) {
                    'termurah' => $q->orderByRaw(self::ANGKA_HARGA.' asc'),
                    'termahal' => $q->orderByRaw(self::ANGKA_HARGA.' desc'),
                    'nama' => $q->orderBy('nama_paket', 'asc'),
                    'terlama' => $q->oldest(),
                    default => $q->latest(),
                };
            }, fn ($q) => $q->latest())
            ->paginate($this->perPage);

        return view('livewire.pages.public.bundling.product-bundlings', [
            'bundlings' => $bundlings,
            'pilihanIsi' => $this->pilihanIsi(),
        ]);
    }
}
