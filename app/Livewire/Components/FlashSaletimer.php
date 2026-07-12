<?php

namespace App\Livewire\Components;

use App\Models\Product;
use App\Services\PromoService;
use Livewire\Component;

class FlashSaletimer extends Component
{
    public $flashSale = null;

    public $timeRemaining = [];

    public $featuredProducts = [];

    public $customer = null;

    // ---- Modal pilih durasi (sebelum masuk keranjang) ----
    public bool $showDurationModal = false;

    public $pickProductId = null;

    public string $pickProductName = '';

    public $pickProductImage = null;

    public array $pickPackages = [];

    public $pickType = null;

    public $pickValue = null;

    public int $pickPerBulan = 0;      // harga per bulan (untuk durasi custom)

    public int $pickCustomMonths = 3;  // jumlah bulan pilihan customer

    public bool $pickIsCustom = false; // sedang memilih durasi custom?

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount()
    {
        $this->loadFlashSale();
    }

    public function loadFeaturedProducts()
    {
        if (! $this->flashSale) {
            return;
        }

        $products = $this->flashSale->products;

        if ($products->isEmpty()) {
            // Tidak ada produk khusus → tampilkan produk terbaru (stabil, tidak acak)
            $this->featuredProducts = Product::latest()->take(4)->get();
        } else {
            // Pertahankan urutan yang diatur admin di flash sale (stabil, tidak berubah-ubah)
            $this->featuredProducts = $products->take(4)->values();
        }
    }

    public function loadFlashSale()
    {
        $flashSales = $this->promoService->getActiveFlashSales();

        if ($flashSales->isNotEmpty()) {
            $this->flashSale = $flashSales->first();
            $this->timeRemaining = $this->flashSale->getTimeRemaining();

            $this->loadFeaturedProducts();
        }
    }

    public function updateTimer()
    {
        if ($this->flashSale) {
            $this->timeRemaining = $this->flashSale->getTimeRemaining();

            // Reload flash sale if expired
            if ($this->flashSale->isExpired()) {
                $this->loadFlashSale();
            }
        }
    }

    public function getDiscountedPrice($originalPrice)
    {
        if (! $this->flashSale) {
            return $originalPrice;
        }

        $isMember = $this->customer && $this->customer->status_member === 'active';

        if ($this->flashSale->tipe_diskon === 'persen') {
            $percentage = $this->flashSale->getDiskonValue($isMember);

            // floor pada nilai diskon — sama persis dengan PromoService (tanpa pembulatan)
            return $originalPrice - floor($originalPrice * $percentage / 100);
        }

        $discount = $this->flashSale->getDiskonValue($isMember);

        return max(0, $originalPrice - $discount);
    }

    public function getDiscountPercentage()
    {
        if (! $this->flashSale) {
            return 0;
        }

        $isMember = $this->customer && $this->customer->status_member === 'active';

        if ($this->flashSale->tipe_diskon === 'persen') {
            return $this->flashSale->getDiskonValue($isMember);
        }

        $avgPercentage = 0;
        $count = 0;

        foreach ($this->featuredProducts as $product) {
            if ($product->harga_perbulan > 0) {
                $discount = $this->flashSale->getDiskonValue($isMember);
                $percentage = ($discount / $product->harga_perbulan) * 100;
                $avgPercentage += $percentage;
                $count++;
            }
        }

        return $count > 0 ? round($avgPercentage / $count) : 0;
    }

    /**
     * Buka modal pilih durasi untuk sebuah produk (seperti di halaman Shop).
     */
    public function openDuration($productId)
    {
        $product = Product::find($productId);
        if (! $product) {
            $this->dispatch('cart-error', message: 'Produk tidak ditemukan.');

            return;
        }

        // Pakai daftarHarga() (tabel harga fleksibel) — SAMA seperti halaman Shop,
        // jadi durasi apa pun yang di-set admin (mis. 2 bulan) ikut muncul.
        $rows = $product->daftarHarga();

        if ($rows->isEmpty()) {
            $this->pushToCart($productId, 'bulan', 1);   // tidak ada harga → fallback

            return;
        }

        $perBulan = (int) ($product->harga_perbulan ?? 0);

        $packages = $rows->map(function ($r) {
            $harga = (int) $r['harga'];
            $val = (int) $r['durasi_value'];
            $type = $r['durasi_type'];

            // Harga setelah diskon promo/flash sale
            $discounted = (int) $this->getDiscountedPrice($harga);

            // "Hemat" = selisih promo (tampil di SEMUA durasi jika produk kena promo)
            $savings = max(0, $harga - $discounted);

            return [
                'duration_type' => $type,
                'duration_value' => $val,
                'price' => $harga,
                'label' => $val.' '.ucfirst($type),
                'savings' => $savings,
                'discounted' => $discounted,
            ];
        })->values()->all();

        $this->pickProductId = $productId;
        $this->pickProductName = $product->nama_akun;
        $this->pickProductImage = $product->image;
        $this->pickPackages = $packages;
        $this->pickPerBulan = $perBulan;
        $this->pickIsCustom = false;
        $this->pickType = $packages[0]['duration_type'];
        $this->pickValue = (int) $packages[0]['duration_value'];
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
        $disc = (int) $this->getDiscountedPrice($base);

        return [
            'base' => $base,
            'discounted' => $disc,
            'savings' => max(0, $base - $disc),
            'matched' => false,
        ];
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

        $this->pushToCart($this->pickProductId, $this->pickType, (int) $this->pickValue);
        $this->showDurationModal = false;
    }

    /**
     * Masukkan ke keranjang (struktur sama dengan halaman Shop).
     * Harga tersimpan = harga dasar durasi (diskon promo diterapkan saat checkout).
     */
    private function pushToCart($productId, $durationType, $durationValue)
    {
        $product = Product::find($productId);
        if (! $product) {
            $this->dispatch('cart-error', message: 'Produk tidak ditemukan.');

            return;
        }

        $price = (int) $product->hargaUntuk((int) $durationValue, $durationType);
        // Durasi custom (admin belum set harga) → bulan × harga per bulan
        if ($price <= 0 && $durationType === 'bulan') {
            $perBulan = (int) ($product->harga_perbulan ?? 0);
            if ($perBulan > 0 && (int) $durationValue > 0) {
                $price = $perBulan * (int) $durationValue;
            }
        }
        if ($price <= 0) {
            $this->dispatch('cart-error', message: 'Harga paket belum tersedia.');

            return;
        }

        $cart = session()->get('cart', []);
        $cartKey = "{$productId}_{$durationType}_{$durationValue}";

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
                'duration_value' => (int) $durationValue,
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: count($cart));
        $this->dispatch('cart-success', message: 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Diskon "sampai" terbesar: ambil angka TERBESAR dari 4 nilai
     * (persen & nominal, member & non-member). Tentukan apakah nominal atau persen.
     */
    public function getBestDiscount()
    {
        if (! $this->flashSale) {
            return null;
        }

        $memberNominal = (float) ($this->flashSale->diskon_member_nominal ?? 0);
        $nonMemberNominal = (float) ($this->flashSale->diskon_non_member_nominal ?? 0);

        $vals = [
            (float) ($this->flashSale->diskon_member_persen ?? 0),
            $memberNominal,
            (float) ($this->flashSale->diskon_non_member_persen ?? 0),
            $nonMemberNominal,
        ];

        $max = max($vals);
        if ($max <= 0) {
            return null;
        }

        // Angka terbesar itu nominal bila sama dengan salah satu nilai nominal ( > 0 ).
        $isNominal = ($max == $memberNominal || $max == $nonMemberNominal) && $max > 0;

        return ['value' => $max, 'isNominal' => $isNominal];
    }

    public function render()
    {
        return view('livewire.components.flash-saletimer');
    }
}
