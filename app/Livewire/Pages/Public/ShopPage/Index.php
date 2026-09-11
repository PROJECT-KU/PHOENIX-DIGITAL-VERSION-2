<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Livewire\Concerns\MengirimPixel;
use App\Models\Product;
use App\Services\PromoService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use MengirimPixel;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 12;

    public $search = '';

    // Filter & urutkan (opsional). Bila kosong → perilaku daftar produk IDENTIK seperti semula.
    public $tipe = '';

    // Kategori dari beranda (?kategori=ai-tools) atau dari chip kategori di
    // halaman ini. Kosong → tidak menyaring apa pun, jadi /shop tanpa parameter
    // berperilaku persis seperti sebelumnya. Kunci yang tidak dikenal juga
    // diperlakukan sebagai kosong. Disinkronkan ke alamat supaya daftar yang
    // tersaring bisa dibagikan dan tombol Kembali mengembalikannya.
    #[Url(as: 'kategori', except: '')]
    public $kategori = '';

    public $sortBy = '';

    public function updatedTipe()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->tipe = '';
        $this->sortBy = '';
        $this->kategori = '';
        $this->resetPage();
    }

    /**
     * Pilih kategori dari chip di papan saring. Kunci yang tidak dikenal
     * diperlakukan sebagai "Semua" — nilainya datang dari peramban.
     */
    public function pilihKategori(string $kunci = ''): void
    {
        $this->kategori = \App\Support\KategoriBeranda::kata($kunci) ? $kunci : '';
        $this->resetPage();
    }

    // ---- Modal pilih durasi (seragam dengan Flash Sale) ----
    public bool $showDurationModal = false;

    public $pickProductId = null;

    public string $pickProductName = '';

    public $pickProductImage = null;

    public array $pickPackages = [];

    public $pickType = null;

    public $pickValue = null;

    public int $pickPerBulan = 0;      // harga per bulan (durasi custom)

    public int $pickCustomMonths = 3;  // jumlah bulan pilihan customer

    public bool $pickIsCustom = false; // sedang memilih durasi custom?

    public ?array $pickBest = null;    // diskon promo terbaik produk terpilih

    public bool $pickIsFlash = false;  // produk terpilih sedang flash sale?

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount()
    {
        $this->search = request('search', '');
        $this->kategori = \App\Support\KategoriBeranda::kata(request('kategori')) ? request('kategori') : '';
    }

    #[On('search-updated')]
    public function updateSearch($search)
    {
        $this->search = $search;

        if (! empty(trim($search))) {
            $this->redirect('/shop?search='.urlencode($search));
        } else {
            $this->redirect('/shop', navigate: true);
        }
    }

    /**
     * Lepas penyaring kategori yang datang dari beranda.
     *
     * Tanpa jalan keluar ini, pengunjung yang mengklik kartu "AI Tools"
     * terkurung di daftar yang tersaring: penyaringnya tidak terlihat di
     * halaman, dan satu-satunya cara keluar adalah menyunting alamat sendiri.
     */
    public function clearKategori()
    {
        $this->kategori = '';
        $this->resetPage();
        $this->redirect('/shop', navigate: true);
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->redirect('/shop', navigate: true);
    }

    /**
     * Buka modal pilih durasi (seragam dengan Flash Sale).
     * Bila produk tidak punya durasi tetap → langsung mode custom (bulan × harga per bulan).
     */
    public function openDuration($productId)
    {
        $product = Product::find($productId);
        if (! $product) {
            $this->dispatch('cart-error', message: 'Produk tidak ditemukan.');

            return;
        }

        // Dijeda: dijawab SEBELUM pemilih durasi terbuka, supaya pembeli tidak
        // memilih paket lebih dulu lalu baru ditolak.
        if (\App\Support\JedaLayanan::produkDijeda($product)) {
            $this->dispatch('cart-error', message: \App\Support\JedaLayanan::pesanProduk($product));

            return;
        }

        // Produk JASA tak bisa dibeli langsung dari daftar: harganya bergantung
        // pada dokumen yang diunggah (per halaman) dan/atau add-on yang dipilih.
        // Arahkan ke halaman produk tempat semua itu ditentukan.
        if ($product->butuh_file) {
            return $this->redirectRoute('shop.detail-product', ['id' => $product->id], navigate: true);
        }

        $best = $this->promoService->getBestProductDiscount($productId, null);
        $rows = $product->daftarHarga();

        $packages = $rows->map(function ($r) use ($best) {
            $harga = (int) $r['harga'];
            $val = (int) $r['durasi_value'];
            $type = $r['durasi_type'];
            $discounted = $this->applyDiscount($harga, $best);

            return [
                'duration_type' => $type,
                'duration_value' => $val,
                'price' => $harga,
                'label' => $val.' '.ucfirst($type),
                'savings' => max(0, $harga - $discounted),
                'discounted' => $discounted,
            ];
        })->values()->all();

        $perBulan = (int) ($product->harga_perbulan ?? 0);

        $this->pickProductId = $productId;
        $this->pickProductName = $product->nama_akun;
        $this->pickProductImage = $product->image;
        $this->pickPackages = $packages;
        $this->pickPerBulan = $perBulan;
        $this->pickBest = $best ? ['type' => $best['type'], 'value' => (float) $best['value']] : null;
        $this->pickIsFlash = $best ? (($best['promo']->tipe_promo ?? null) === 'flash_sale') : false;
        $this->pickCustomMonths = 3;

        if (empty($packages)) {
            // Tidak ada durasi tetap → mode custom (seperti flash sale)
            if ($perBulan <= 0) {
                $this->dispatch('cart-error', message: 'Harga paket belum tersedia.');

                return;
            }
            $this->pickIsCustom = true;
            $this->pickType = 'bulan';
            $this->pickValue = $this->pickCustomMonths;
        } else {
            $this->pickIsCustom = false;
            $this->pickType = $packages[0]['duration_type'];
            $this->pickValue = (int) $packages[0]['duration_value'];
        }

        $this->showDurationModal = true;
    }

    public function selectPackage($type, $value)
    {
        $this->pickIsCustom = false;
        $this->pickType = $type;
        $this->pickValue = (int) $value;
    }

    /** Pilih durasi custom (jumlah bulan bebas): harga = bulan × harga per bulan. */
    public function chooseCustom()
    {
        if ($this->pickPerBulan <= 0) {
            return;
        }
        $this->pickIsCustom = true;
        $this->pickType = 'bulan';
        $this->pickValue = (int) $this->pickCustomMonths;
    }

    public function incCustom()
    {
        $this->pickCustomMonths = min(60, (int) $this->pickCustomMonths + 1);
        $this->chooseCustom();
    }

    public function decCustom()
    {
        $this->pickCustomMonths = max(1, (int) $this->pickCustomMonths - 1);
        $this->chooseCustom();
    }

    public function updatedPickCustomMonths($value)
    {
        $v = (int) $value;
        $this->pickCustomMonths = max(1, min(60, $v ?: 1));
        if ($this->pickIsCustom) {
            $this->pickValue = $this->pickCustomMonths;
        }
    }

    public function closeDuration()
    {
        $this->showDurationModal = false;
    }

    public function confirmAddToCart()
    {
        if (! $this->pickProductId || ! $this->pickType || ! $this->pickValue) {
            return;
        }

        $this->addToCart($this->pickProductId, $this->pickType, (int) $this->pickValue);
        $this->showDurationModal = false;
    }

    /** Terapkan diskon promo terbaik ke sebuah harga. */
    public function applyDiscount(int $harga, ?array $best): int
    {
        if (! $best || empty($best['value'])) {
            return $harga;
        }
        if (($best['type'] ?? '') === 'persen') {
            return (int) ($harga - floor($harga * $best['value'] / 100));
        }

        return (int) max(0, $harga - $best['value']);
    }

    /** Harga setelah diskon untuk pratinjau durasi custom di modal. */
    public function previewDiscount($amount): int
    {
        return $this->applyDiscount((int) $amount, $this->pickBest);
    }

    /**
     * Harga durasi custom. Bila jumlah bulan cocok dengan paket yang sudah
     * di-set admin (mis. 5 bulan), IKUTI harga paket itu agar seragam.
     * Selain itu → bulan × harga per bulan.
     */
    public function customPricing(): array
    {
        $months = (int) $this->pickCustomMonths;

        foreach ($this->pickPackages as $p) {
            if (($p['duration_type'] ?? null) === 'bulan' && (int) ($p['duration_value'] ?? 0) === $months) {
                return [
                    'base' => (int) $p['price'],
                    'discounted' => (int) ($p['discounted'] ?? $p['price']),
                    'savings' => (int) ($p['savings'] ?? 0),
                    'matched' => true,
                ];
            }
        }

        $base = $months * (int) $this->pickPerBulan;
        $disc = $this->previewDiscount($base);

        return [
            'base' => $base,
            'discounted' => $disc,
            'savings' => max(0, $base - $disc),
            'matched' => false,
        ];
    }

    /**
     * Data jendela pilih durasi, siap cetak.
     *
     * Hanya MERAKIT tampilan dari state yang sudah ada (pickPackages,
     * customPricing, pickBest) — harga, promo, dan isi keranjang tidak diubah.
     * Semua perbandingan dilakukan di sini supaya Blade tidak memuat ">" di
     * sela direktif blok (jebakan penanda morph Livewire).
     *
     * @return array<string, mixed>
     */
    public function dataModal(): array
    {
        $kat = \App\Support\KategoriBeranda::untukProduk($this->pickProductName);
        $rupiah = fn ($v) => 'Rp'.number_format((int) $v, 0, ',', '.');
        $bulanDari = fn ($tipe, $nilai) => match (strtolower((string) $tipe)) {
            'bulan' => (int) $nilai,
            'tahun' => (int) $nilai * 12,
            default => null,
        };

        $opsi = [];
        $perBulan = [];
        foreach ($this->pickPackages as $i => $p) {
            $akhir = (int) ($p['discounted'] ?? $p['price']);
            $asli = (int) $p['price'];
            $bln = $bulanDari($p['duration_type'], $p['duration_value']);
            $perBulan[$i] = $bln ? $akhir / $bln : null;

            $opsi[$i] = [
                'kunci' => $p['duration_type'].'-'.$p['duration_value'],
                'tipe' => $p['duration_type'],
                'nilai' => (int) $p['duration_value'],
                'label' => $p['label'],
                'akhir' => $rupiah($akhir),
                'asli' => $akhir < $asli ? $rupiah($asli) : null,
                'hemat' => ! empty($p['savings']) ? 'Hemat '.$rupiah($p['savings']) : null,
                // Setara per bulan hanya untuk paket lebih dari sebulan — itulah
                // yang sulit dibandingkan pembeli di kepalanya sendiri.
                'perBulan' => $bln && $bln > 1 ? '≈ '.$rupiah(round($akhir / $bln)).'/bulan' : null,
                'aktif' => ! $this->pickIsCustom
                    && $this->pickType === $p['duration_type']
                    && (int) $this->pickValue === (int) $p['duration_value'],
                'terhemat' => false,
            ];
        }

        // "Paling hemat" hanya bila benar-benar ada selisih per bulan di antara
        // setidaknya dua paket — label yang menempel ke semua paket tak berarti.
        $sah = array_filter($perBulan, fn ($v) => $v !== null);
        if (count($sah) >= 2 && min($sah) < max($sah)) {
            $opsi[array_search(min($sah), $sah, true)]['terhemat'] = true;
        }

        $custom = null;
        if ($this->pickPerBulan > 0) {
            $cp = $this->customPricing();
            $bulan = (int) $this->pickCustomMonths;
            $custom = [
                'bulan' => $bulan,
                'sub' => $cp['matched'] ? 'Sesuai paket '.$bulan.' bulan' : $rupiah($this->pickPerBulan).'/bulan',
                'akhir' => $cp['discounted'],
                'asli' => $cp['discounted'] < $cp['base'] ? $cp['base'] : null,
                'bisaKurang' => $bulan > 1,
                'bisaTambah' => $bulan < 60,
            ];
        }

        $terpilih = collect($opsi)->firstWhere('aktif', true);
        if ($this->pickIsCustom && $custom) {
            $total = ['label' => $custom['bulan'].' bulan', 'akhir' => $rupiah($custom['akhir']), 'asli' => $custom['asli'] ? $rupiah($custom['asli']) : null];
        } elseif ($terpilih) {
            $total = ['label' => $terpilih['label'], 'akhir' => $terpilih['akhir'], 'asli' => $terpilih['asli']];
        } else {
            $total = null;
        }

        $diskon = null;
        if ($this->pickBest && ! empty($this->pickBest['value'])) {
            $diskon = ($this->pickBest['type'] ?? '') === 'persen'
                ? number_format($this->pickBest['value'], 0).'%'
                : $rupiah($this->pickBest['value']);
        }

        return [
            'warna' => $kat['warna'] ?? '#f26522',
            'ikon' => $kat['ikon'] ?? 'bi-box-seam',
            'kategori' => $kat['label'] ?? null,
            'gambar' => $this->pickProductImage && Storage::disk('public')->exists('img/Product/'.$this->pickProductImage)
                ? asset('storage/img/Product/'.$this->pickProductImage)
                : null,
            'diskon' => $diskon,
            'flash' => $this->pickIsFlash,
            'opsi' => array_values($opsi),
            'custom' => $custom,
            'total' => $total,
            'aktifKunci' => $this->pickIsCustom ? 'custom' : ($terpilih['kunci'] ?? ''),
        ];
    }

    public function addToCart($productId, $durationType, $durationValue)
    {
        $product = Product::findOrFail($productId);

        // Penjaga: produk JASA hanya boleh masuk keranjang lewat halaman produk
        // (butuh unggah dokumen dan/atau pilihan add-on agar harganya benar).
        if ($product->butuh_file) {
            return $this->redirectRoute('shop.detail-product', ['id' => $product->id], navigate: true);
        }

        // Produk akun masuk keranjang LANGSUNG dari kartu di daftar, tanpa
        // melewati halaman detail — jadi penjaga jedanya harus ada di sini juga,
        // bukan hanya di halaman detail.
        if (\App\Support\JedaLayanan::produkDijeda($product)) {
            $this->dispatch('cart-error', message: \App\Support\JedaLayanan::pesanProduk($product));

            return;
        }

        // Tentukan harga berdasarkan durasi
        $price = $this->getPrice($product, $durationType, $durationValue);

        // Durasi custom (admin belum set harga) → bulan × harga per bulan
        if (! $price && $durationType === 'bulan') {
            $perBulan = (int) ($product->harga_perbulan ?? 0);
            if ($perBulan > 0 && (int) $durationValue > 0) {
                $price = $perBulan * (int) $durationValue;
            }
        }

        if (! $price) {
            $this->dispatch('cart-error', message: 'Paket yang dipilih tidak tersedia');

            return;
        }

        // Get cart dari session atau buat array kosong
        $cart = session()->get('cart', []);

        // Generate unique key untuk cart item
        $cartKey = "{$productId}_{$durationType}_{$durationValue}";

        // Cek apakah item sudah ada di cart
        if (isset($cart[$cartKey])) {
            // Akun digital: 1 baris = 1 item, tidak menumpuk jumlah.
            $cart[$cartKey]['quantity'] = 1;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $productId,
                'product_name' => $product->nama_akun,
                'product_image' => $product->image,
                'duration_type' => $durationType,
                'duration_value' => $durationValue,
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ];
        }
        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: $this->getCartCount());
        // $this->dispatch('success-add-to-cart');
        $this->kirimPixel('AddToCart', $this->pixelDariBarisKeranjang($cart[$cartKey]));
        $this->dispatch('cart-success', message: 'Produk berhasil ditambahkan ke keranjang!');
    }

    private function getPrice($product, $durationType, $durationValue)
    {
        $harga = $product->hargaUntuk((int) $durationValue, $durationType);

        return $harga > 0 ? $harga : null;
    }

    private function getCartCount()
    {
        $cart = session()->get('cart', []);

        return count($cart);
    }

    public function getProductPromos($productId)
    {
        return $this->promoService->getProductPromos($productId, null);
    }

    public function getBestDiscount($productId)
    {
        return $this->promoService->getBestProductDiscount($productId, null);
    }

    /**
     * Data satu kartu produk, siap cetak.
     *
     * Aturan harga & promonya SAMA PERSIS dengan yang sebelumnya dihitung di
     * Blade — hanya dipindah ke sini. Halaman ini komponen Livewire, dan
     * perbandingan ">" di sela direktif blok membuat Livewire melewati penanda
     * morph-nya sehingga pembaruan daftar (saring, urut, halaman) bisa rusak.
     *
     * @return array<string, mixed>
     */
    private function dataKartu(Product $item): array
    {
        $best = $this->getBestDiscount($item->id);
        $isFlash = $best && ($best['promo']->tipe_promo ?? null) === 'flash_sale';

        // Produk JASA harga per bulannya 0: ditagih per pengecekan atau per halaman.
        $isJasa = (bool) $item->butuh_file;
        $perHalaman = $isJasa && $item->jasaPerHalaman();
        $asli = $perHalaman
            ? (int) $item->hargaPerHalaman()
            : ($isJasa ? (int) ($item->hargaSekali() ?? 0) : (int) $item->harga_perbulan);

        $akhir = $asli;
        if ($best) {
            $akhir = $best['type'] === 'persen'
                ? (int) round($asli - ($asli * $best['value']) / 100)
                : (int) max(0, $asli - $best['value']);
        }

        // Lencana diskon diringkas: "Diskon s.d. 30%" di setiap kartu memanjang
        // melintasi kartu dan menabrak label kategori di seberangnya.
        $diskon = null;
        if ($best) {
            $nilai = fn ($v) => number_format($v, 0);
            $rupiah = fn ($v) => 'Rp'.number_format($v, 0, ',', '.');
            if ($isFlash) {
                // Flash sale menyebut nilai TERBESARNYA, seperti lencana lama.
                $diskon = 's.d. '.($best['type'] === 'persen' ? $nilai($best['value']).'%' : $rupiah($best['value']));
            } elseif ($best['type'] === 'persen') {
                // Rentang member/non-member selalu dari kecil ke besar — urutan
                // data promo tidak menjamin mana yang lebih besar.
                $bawah = min($best['member_value'], $best['non_member_value']);
                $atas = max($best['member_value'], $best['non_member_value']);
                $diskon = $bawah != $atas ? $nilai($bawah).'–'.$nilai($atas).'%' : '-'.$nilai($best['value']).'%';
            } else {
                $diskon = '-'.$rupiah($best['value']);
            }
        }

        $kat = \App\Support\KategoriBeranda::untukProduk($item->nama_akun);

        return [
            'id' => $item->id,
            'nama' => $item->nama_akun,
            'url' => route('shop.detail-product', $item->id),
            // Hanya bila berkasnya ADA: gambar rusak menampilkan teks alt mentah
            // yang terpotong di balik lencana — terbaca seperti toko tak terurus.
            'gambar' => $item->image && Storage::disk('public')->exists('img/Product/'.$item->image)
                ? asset('storage/img/Product/'.$item->image)
                : null,
            'kategori' => $kat['label'] ?? null,
            'warna' => $kat['warna'] ?? '#f26522',
            'ikon' => $kat['ikon'] ?? 'bi-box-seam',
            'jenis' => $isJasa ? 'Layanan' : ucfirst((string) ($item->tipe_akun ?: 'Akun')),
            'harga' => $akhir,
            'hargaAsli' => $akhir < $asli ? $asli : null,
            'satuan' => $perHalaman ? '/halaman' : ($isJasa ? '/cek' : '/bln'),
            'mulai' => $isJasa,
            'jasa' => $isJasa,
            'diskon' => $diskon,
            'flash' => $isFlash,
            'dijeda' => \App\Support\JedaLayanan::produkDijeda($item),
        ];
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $products = Product::with('prices')->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nama_akun', 'like', "%{$this->search}%")
                    ->orWhere('deskripsi', 'like', "%{$this->search}%");
            });
        })
            ->when($this->tipe, fn ($q) => $q->where('tipe_akun', $this->tipe))
            ->when(\App\Support\KategoriBeranda::kata($this->kategori),
                fn ($q, $kata) => \App\Support\KategoriBeranda::saring($q, $kata))
            ->when($this->sortBy, function ($q) {
                match ($this->sortBy) {
                    'termurah' => $q->orderBy('harga_perbulan', 'asc'),
                    'termahal' => $q->orderBy('harga_perbulan', 'desc'),
                    'nama' => $q->orderBy('nama_akun', 'asc'),
                    'terlama' => $q->oldest(),
                    default => $q->latest(),
                };
            }, fn ($q) => $q->latest()) // tanpa sort → tetap ->latest() (identik seperti semula)
            ->paginate($this->perPage);

        $categories = Product::query()->whereNotNull('tipe_akun')
            ->where('tipe_akun', '!=', '')->distinct()->orderBy('tipe_akun')->pluck('tipe_akun');

        return view('livewire.pages.public.shop-page.index', [
            'products' => $products,
            'categories' => $categories,
            'kartu' => $products->getCollection()->map(fn ($p) => $this->dataKartu($p))->all(),
            // Chip kategori: hanya yang ada isinya (KategoriBeranda). "Paket
            // Bundling" dilewati — ia halaman tersendiri, bukan penyaring katalog.
            'daftarKategori' => array_values(array_filter(
                \App\Support\KategoriBeranda::tersedia(),
                fn ($k) => $k['kunci'] !== 'bundling',
            )),
            'kategoriAktif' => \App\Support\KategoriBeranda::kata($this->kategori) ? $this->kategori : '',
            'adaFilter' => (bool) ($this->tipe || $this->sortBy || \App\Support\KategoriBeranda::kata($this->kategori)),
        ]);
    }
}
