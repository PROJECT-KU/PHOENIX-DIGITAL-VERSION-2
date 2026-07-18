<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Product;
use App\Services\PromoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 12;

    public $search = '';

    // Filter & urutkan (opsional). Bila kosong → perilaku daftar produk IDENTIK seperti semula.
    public $tipe = '';

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
    }

    #[On('search-updated')]
    public function updateSearch($search)
    {
        $this->search = $search;

        if (! empty(trim($search))) {
            $this->redirect('/shop?search=' . urlencode($search));
        } else {
            $this->redirect('/shop', navigate: true);
        }
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

    public function addToCart($productId, $durationType, $durationValue)
    {
        $product = Product::findOrFail($productId);

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
        ]);
    }
}
