<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Product;
use App\Services\PromoService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProductDetail extends Component
{
    public $product;

    public int $quantity = 1;

    public ?string $durationType = null;

    public ?int $durationValue = null;

    public int $pickCustomMonths = 3;

    public bool $isCustom = false;

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount($id)
    {
        $this->product = Product::findOrFail($id);

        // Pilih paket pertama sebagai default
        $rows = $this->product->daftarHarga();
        if ($rows->isNotEmpty()) {
            $this->durationType = $rows->first()['durasi_type'];
            $this->durationValue = (int) $rows->first()['durasi_value'];
        }
    }

    #[Computed]
    public function bestDiscount()
    {
        return $this->promoService->getBestProductDiscount($this->product->id, null);
    }

    public function applyDiscount(int $harga): int
    {
        $best = $this->bestDiscount;
        if (! $best || empty($best['value'])) {
            return $harga;
        }
        if (($best['type'] ?? '') === 'persen') {
            // floor pada nilai diskon — sama persis dengan PromoService (tanpa pembulatan)
            return (int) ($harga - floor($harga * $best['value'] / 100));
        }

        return (int) max(0, $harga - $best['value']);
    }

    public function selectedHarga(): int
    {
        if ($this->isCustom) {
            return $this->customPricing()['base'];
        }

        foreach ($this->product->daftarHarga() as $r) {
            if ($r['durasi_type'] === $this->durationType && (int) $r['durasi_value'] === (int) $this->durationValue) {
                return (int) $r['harga'];
            }
        }

        return (int) ($this->product->harga_awal ?? 0);
    }

    /**
     * Harga durasi custom. Bila jumlah bulan cocok dengan paket "bulan" yang sudah ada,
     * ikuti harga paket itu; selain itu = bulan × harga per bulan.
     */
    public function customPricing(): array
    {
        $months = (int) $this->pickCustomMonths;

        foreach ($this->product->daftarHarga() as $r) {
            if ($r['durasi_type'] === 'bulan' && (int) $r['durasi_value'] === $months) {
                $base = (int) $r['harga'];
                $disc = $this->applyDiscount($base);

                return ['base' => $base, 'discounted' => $disc, 'savings' => max(0, $base - $disc), 'matched' => true];
            }
        }

        // Hitung per bulan lalu dikali agar hemat konsisten (mis. 7.631 × 4 = 30.524),
        // tidak ada selisih akibat pembulatan pada total.
        $perBulan = (int) ($this->product->harga_perbulan ?? 0);
        $discPerBulan = $this->applyDiscount($perBulan);
        $base = $months * $perBulan;
        $disc = $months * $discPerBulan;

        return ['base' => $base, 'discounted' => $disc, 'savings' => max(0, $base - $disc), 'matched' => false];
    }

    public function selectPackage(string $type, int $value)
    {
        $this->isCustom = false;
        $this->durationType = $type;
        $this->durationValue = $value;
    }

    public function chooseCustom()
    {
        if ((int) ($this->product->harga_perbulan ?? 0) <= 0) {
            return;
        }
        $this->isCustom = true;
        $this->durationType = 'bulan';
        $this->durationValue = (int) $this->pickCustomMonths;
    }

    public function incCustom()
    {
        $this->pickCustomMonths = min(60, $this->pickCustomMonths + 1);
        $this->chooseCustom();
    }

    public function decCustom()
    {
        $this->pickCustomMonths = max(1, $this->pickCustomMonths - 1);
        $this->chooseCustom();
    }

    public function addToCart()
    {
        if (! $this->durationType || ! $this->durationValue) {
            $this->dispatch('cart-error', message: 'Silakan pilih paket harga terlebih dahulu.');

            return;
        }

        $this->quantity = max(1, (int) $this->quantity);

        $price = $this->getPrice($this->product, $this->durationType, $this->durationValue);

        // Durasi custom (admin belum set harga) → bulan × harga per bulan
        if (! $price && $this->durationType === 'bulan') {
            $perBulan = (int) ($this->product->harga_perbulan ?? 0);
            if ($perBulan > 0 && $this->durationValue > 0) {
                $price = $perBulan * $this->durationValue;
            }
        }

        if (! $price) {
            $this->dispatch('cart-error', message: 'Paket tidak valid.');

            return;
        }

        $cart = session()->get('cart', []);
        $cartKey = "{$this->product->id}_{$this->durationType}_{$this->durationValue}";
        $imageName = $this->product->image ? basename($this->product->image) : null;

        if (isset($cart[$cartKey])) {
            // Akun digital: 1 baris = 1 item, tidak menumpuk jumlah.
            $cart[$cartKey]['quantity'] = 1;
            $cart[$cartKey]['subtotal'] = $cart[$cartKey]['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $this->product->id,
                'product_name' => $this->product->nama_akun,
                'product_image' => $imageName,
                'duration_type' => $this->durationType,
                'duration_value' => $this->durationValue,
                'price' => $price,
                'quantity' => $this->quantity,
                'subtotal' => $price * $this->quantity,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated', count: $this->getCartCount());
        $this->dispatch('cart-success', message: 'Produk berhasil ditambahkan ke keranjang!');
    }

    private function getPrice(Product $product, string $durationType, int $durationValue)
    {
        $harga = $product->hargaUntuk($durationValue, $durationType);

        return $harga > 0 ? $harga : null;
    }

    private function getCartCount(): int
    {
        $cart = session()->get('cart', []);

        return count($cart);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.product-detail');
    }
}
