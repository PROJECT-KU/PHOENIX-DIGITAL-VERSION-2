<?php

namespace App\Livewire\Pages\Public\Bundling;

use App\Livewire\Concerns\MengirimPixel;
use App\Models\ProductBundlings as ModelsProductBundlings;
use App\Support\HargaPaket;
use App\Support\KategoriBeranda;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Data satu kartu paket, siap cetak.
     *
     * Harga dari HargaPaket::untuk() — sumber yang sama dengan keranjang, jadi
     * angka di kartu tidak mungkin berbeda dari yang ditagih. Semua
     * perbandingan dilakukan di sini supaya Blade tidak memuat ">" di sela
     * direktif blok (jebakan penanda morph Livewire).
     *
     * @return array<string, mixed>
     */
    private function dataKartu(ModelsProductBundlings $item): array
    {
        $hp = HargaPaket::untuk($item);
        $durasi = collect($item->bundleProducts())->keyBy('product_id');

        $isi = collect([1, 2, 3, 4, 5])
            ->map(fn ($i) => $item->{'product'.$i})
            ->filter()
            ->values()
            ->map(function ($p) use ($durasi) {
                $kat = KategoriBeranda::untukProduk($p->nama_akun);
                $d = $durasi->get($p->id);

                return [
                    'nama' => $p->nama_akun,
                    'warna' => $kat['warna'] ?? '#f26522',
                    'ikon' => $kat['ikon'] ?? 'bi-box-seam',
                    'durasi' => $d ? $d['duration_value'].' '.ucfirst($d['duration_type']) : null,
                ];
            });

        // Tumpukan ubin di area gambar: paling banyak empat, sedikit miring
        // bergantian supaya terbaca sebagai "beberapa barang dalam satu paket".
        $putar = [-7, 5, -3, 6];
        $tumpuk = $isi->take(4)->values()->map(fn ($p, $i) => $p + ['putar' => $putar[$i]])->all();

        $hemat = $hp['coret'] > $hp['bayar'] ? $hp['coret'] - $hp['bayar'] : 0;
        $berkas = $item->gambar ? basename($item->gambar) : null;

        return [
            'id' => $item->id,
            'nama' => $item->nama_paket,
            'url' => route('bundling.detail', $item->id),
            'gambar' => $berkas && Storage::disk('public')->exists('img/ProductBundlings/'.$berkas)
                ? asset('storage/img/ProductBundlings/'.$berkas)
                : null,
            // Aksen kartu = warna kategori produk pertama di dalam paket.
            'warna' => $isi->first()['warna'] ?? '#f26522',
            'tumpuk' => $tumpuk,
            'jumlahIsi' => $isi->count(),
            'isiTampil' => $isi->take(3)->all(),
            'isiLain' => max(0, $isi->count() - 3),
            'harga' => $hp['bayar'],
            'hargaAsli' => $hemat ? $hp['coret'] : null,
            'hemat' => $hemat ? 'Hemat Rp'.number_format($hemat, 0, ',', '.') : null,
            'diskon' => $hemat && $hp['coret'] ? '-'.round($hemat / $hp['coret'] * 100).'%' : null,
            // Promo berkode tidak berlaku sendiri: kodenya WAJIB terlihat.
            'kode' => $hp['butuh_kode'] ? ($hp['promo']->kode_promo ?? null) : null,
            'jadwal' => $item->jadwalLabel(),
        ];
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

        $pilihanIsi = $this->pilihanIsi();

        return view('livewire.pages.public.bundling.product-bundlings', [
            'bundlings' => $bundlings,
            'pilihanIsi' => $pilihanIsi,
            'kartu' => $bundlings->getCollection()->map(fn ($b) => $this->dataKartu($b))->all(),
            // Chip "isi paket": produk yang dipakai paket tayang, berwarna
            // kategorinya — bahasa yang sama dengan chip kategori di /shop.
            'chipIsi' => $pilihanIsi->map(function ($nama, $id) {
                $kat = KategoriBeranda::untukProduk($nama);

                return ['id' => (string) $id, 'nama' => $nama, 'warna' => $kat['warna'] ?? '#f26522', 'ikon' => $kat['ikon'] ?? 'bi-box-seam'];
            })->values()->all(),
            'adaFilter' => (bool) ($this->isi || $this->sortBy),
        ]);
    }
}
