<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Promo;
use Illuminate\Support\Collection;

class PromoService
{
    /**
     * Calculate total discount dari semua promo yang berlaku
     */
    public function calculateDiscount(
        array $cart,
        ?Customer $customer = null,
        ?string $kodePromo = null,
        bool $useReferral = false,
        bool $usePoints = false
    ): array {
        $subtotal = array_sum(array_column($cart, 'subtotal'));
        $isMember = $customer && $customer->status_member === 'active';
        $isPembeliPertama = ! $customer || ! $customer->hasTransactions();

        $appliedPromos = [];
        $totalPromoDiscount = 0;
        $referralDiscount = 0;

        // 1. Automatic Promos (Flash Sale + Auto Promo) - Apply otomatis tanpa kode
        $automaticDiscount = $this->applyAutomaticPromos($cart, $customer, $isMember);
        if ($automaticDiscount['total'] > 0) {
            $appliedPromos = array_merge($appliedPromos, $automaticDiscount['promos']);
            $totalPromoDiscount += $automaticDiscount['total'];
        }

        // 2. Kode Promo (jika ada)
        if ($kodePromo) {
            $kodePromoDiscount = $this->applyKodePromo(
                $kodePromo,
                $subtotal,
                $cart,
                $customer,
                $isMember,
                $appliedPromos
            );

            if ($kodePromoDiscount['success']) {
                $appliedPromos[] = $kodePromoDiscount['promo_data'];
                $totalPromoDiscount += $kodePromoDiscount['discount'];
            }
        }

        // 3. Referral Discount (flat 2000 untuk pembeli pertama)
        if ($useReferral && $isPembeliPertama) {
            $referralDiscount = $this->calculateReferralDiscount($subtotal, $appliedPromos);
        }

        $totalDiscount = $totalPromoDiscount + $referralDiscount;
        $finalTotal = max(0, $subtotal - $totalDiscount);

        return [
            'subtotal' => $subtotal,
            'promo_discount' => $totalPromoDiscount,
            'referral_discount' => $referralDiscount,
            'total_discount' => $totalDiscount,
            'final_total' => $finalTotal,
            'applied_promos' => $appliedPromos,
        ];
    }

    /**
     * Apply automatic promos (Flash Sale + Auto Promo)
     * Kedua tipe ini otomatis apply tanpa perlu input kode
     */
    private function applyAutomaticPromos(array $cart, ?Customer $customer, bool $isMember): array
    {
        $promos = [];
        $totalDiscount = 0;

        // Get all active automatic promos (flash_sale + auto_promo)
        $automaticPromos = Promo::active()
            ->automaticPromos()
            ->orderBy('prioritas', 'desc')
            ->orderBy('tipe_promo', 'desc') // flash_sale prioritas lebih tinggi
            ->get();

        foreach ($cart as $item) {
            // Find applicable promos for this product
            $productPromos = $automaticPromos->filter(function ($promo) use ($item, $customer) {
                // Check if promo applies to this product
                // Jika products kosong = berlaku untuk semua produk
                $appliesToProduct = $promo->products->isEmpty()
                    || $promo->products->contains('id', $item['product_id']);

                return $appliesToProduct && $promo->canBeUsedBy($customer);
            });

            foreach ($productPromos as $promo) {
                // Check if can stack
                if (! $promo->can_stack_with_other && ! empty($promos)) {
                    continue;
                }

                $discount = $this->calculatePromoDiscount(
                    $item['subtotal'],
                    $promo,
                    $isMember
                );

                if ($discount > 0) {
                    $totalDiscount += $discount;
                    $promos[] = [
                        'promo_id' => $promo->id,
                        'kode_promo' => $promo->kode_promo,
                        'nama_promo' => $promo->nama_promo,
                        'tipe_promo' => $promo->tipe_promo,
                        'tipe_diskon' => $promo->tipe_diskon,
                        'nilai_diskon' => $promo->getDiskonValue($isMember),
                        'jumlah_diskon' => $discount,
                        'is_automatic' => true,
                    ];

                    // If not stackable, stop after first promo
                    if (! $promo->can_stack_with_other) {
                        break;
                    }
                }
            }
        }

        return [
            'total' => $totalDiscount,
            'promos' => $promos,
        ];
    }

    /**
     * Apply kode promo
     */
    private function applyKodePromo(
        string $kodePromo,
        float $subtotal,
        array $cart,
        ?Customer $customer,
        bool $isMember,
        array $existingPromos
    ): array {
        $promo = Promo::active()
            ->kodePromo()
            ->where('kode_promo', strtoupper(trim($kodePromo)))
            ->first();

        if (! $promo) {
            return [
                'success' => false,
                'message' => 'Kode promo tidak ditemukan atau sudah tidak aktif',
            ];
        }

        if (! $promo->canBeUsedBy($customer)) {
            return [
                'success' => false,
                'message' => 'Anda tidak memenuhi syarat untuk menggunakan promo ini',
            ];
        }

        if ($subtotal < $promo->min_pembelian) {
            return [
                'success' => false,
                'message' => 'Minimum pembelian Rp '.number_format($promo->min_pembelian, 0, ',', '.'),
            ];
        }

        // Check stacking rules
        if (! $promo->can_stack_with_other && ! empty($existingPromos)) {
            return [
                'success' => false,
                'message' => 'Promo ini tidak dapat digabungkan dengan promo lain',
            ];
        }

        $discount = $this->calculatePromoDiscount($subtotal, $promo, $isMember);

        return [
            'success' => true,
            'discount' => $discount,
            'promo_data' => [
                'promo_id' => $promo->id,
                'kode_promo' => $promo->kode_promo,
                'nama_promo' => $promo->nama_promo,
                'tipe_promo' => $promo->tipe_promo,
                'tipe_diskon' => $promo->tipe_diskon,
                'nilai_diskon' => $promo->getDiskonValue($isMember),
                'jumlah_diskon' => $discount,
                'is_automatic' => false,
            ],
            'message' => '✓ Kode promo berhasil diterapkan!',
        ];
    }

    /**
     * Calculate promo discount
     */
    private function calculatePromoDiscount(float $amount, Promo $promo, bool $isMember): float
    {
        if ($promo->tipe_diskon === 'persen') {
            $percentage = $promo->getDiskonValue($isMember, 'persen');

            return floor($amount * ($percentage / 100));
        }

        return min($promo->getDiskonValue($isMember, 'nominal'), $amount);
    }

    /**
     * Calculate referral discount
     */
    private function calculateReferralDiscount(float $subtotal, array $appliedPromos): float
    {
        // Check if any promo doesn't allow stacking with referral
        foreach ($appliedPromos as $promoData) {
            $promo = Promo::find($promoData['promo_id']);
            if ($promo && ! $promo->can_stack_with_referral) {
                return 0;
            }
        }

        // Flat 2000 discount for referral
        return min(2000, $subtotal);
    }

    /**
     * Validate kode promo
     */
    public function validateKodePromo(string $kodePromo, ?Customer $customer = null, float $subtotal = 0): array
    {
        $promo = Promo::active()
            ->kodePromo()
            ->where('kode_promo', strtoupper(trim($kodePromo)))
            ->first();

        if (! $promo) {
            return [
                'valid' => false,
                'message' => 'Kode promo tidak ditemukan atau sudah tidak aktif',
            ];
        }

        if (! $promo->canBeUsedBy($customer)) {
            return [
                'valid' => false,
                'message' => 'Anda tidak memenuhi syarat untuk menggunakan promo ini',
            ];
        }

        if ($subtotal > 0 && $subtotal < $promo->min_pembelian) {
            return [
                'valid' => false,
                'message' => 'Minimum pembelian Rp '.number_format($promo->min_pembelian, 0, ',', '.'),
            ];
        }

        $isMember = $customer && $customer->status_member === 'active';
        $discount = $promo->getDiskonValue($isMember);

        return [
            'valid' => true,
            'promo' => $promo,
            'message' => '✓ Kode promo valid! Diskon: '.
                ($promo->tipe_diskon === 'persen'
                    ? $discount.'%'
                    : 'Rp '.number_format($discount, 0, ',', '.')),
        ];
    }

    /**
     * Get active flash sales for homepage
     *
     * @deprecated Use getHomepagePromos() instead
     */
    public function getActiveFlashSales(): Collection
    {
        return Promo::active()
            ->flashSale()
            ->homepage()
            ->orderBy('prioritas', 'desc')
            ->get();
    }

    /**
     * Get all homepage promos (flash sale + auto promo)
     */
    public function getHomepagePromos(): array
    {
        $promos = Promo::active()
            ->automaticPromos() // flash_sale + auto_promo
            ->where('show_on_homepage', true)
            ->orderBy('prioritas', 'desc')
            ->orderBy('tipe_promo', 'desc')
            ->get();

        return [
            'flash_sales' => $promos->where('tipe_promo', 'flash_sale'),
            'auto_promos' => $promos->where('tipe_promo', 'auto_promo'),
            'all_promos' => $promos,
        ];
    }

    /**
     * Get applicable promos for a product
     * Untuk display di product card
     */
    public function getProductPromos(string $productId, ?Customer $customer = null): Collection
    {
        return Promo::active()
            ->automaticPromos() // Hanya ambil flash_sale + auto_promo
            ->where(function ($query) use ($productId) {
                // Promo berlaku jika:
                // 1. Tidak ada products attached (berlaku untuk semua)
                // 2. Product ID ada di relation
                $query->whereDoesntHave('products')
                    ->orWhereHas('products', function ($q) use ($productId) {
                        $q->where('product_id', $productId);
                    });
            })
            ->orderBy('prioritas', 'desc')
            ->get()
            ->filter(function ($promo) use ($customer) {
                return $promo->canBeUsedBy($customer);
            });
    }

    /**
     * Get best discount untuk display di product card
     * Support untuk guest user
     */
    public function getBestProductDiscount(string $productId, ?Customer $customer = null): ?array
    {
        $promos = $this->getProductPromos($productId, $customer);

        if ($promos->isEmpty()) {
            return null;
        }

        // Cari promo dengan nilai diskon tertinggi
        $bestPromo = $promos->sortByDesc(function ($promo) {
            $memberValue = $promo->getDiskonValue(true);
            $nonMemberValue = $promo->getDiskonValue(false);

            return max($memberValue, $nonMemberValue);
        })->first();

        $memberValue = $bestPromo->getDiskonValue(true);
        $nonMemberValue = $bestPromo->getDiskonValue(false);
        $maxValue = max($memberValue, $nonMemberValue);

        return [
            'promo' => $bestPromo,
            'value' => $maxValue,
            'type' => $bestPromo->tipe_diskon,
            'member_value' => $memberValue,
            'non_member_value' => $nonMemberValue,
            'is_automatic' => $bestPromo->isAutomatic(),
            'requires_code' => $bestPromo->requiresCode(),
        ];
    }

    /**
     * Distribute discount ke cart items (proportional)
     */
    public function distributeDiscountToCart(array $cart, float $totalDiscount): array
    {
        if ($totalDiscount <= 0) {
            return $cart;
        }

        $subtotal = array_sum(array_column($cart, 'subtotal'));
        $distributedCart = [];
        $remainingDiscount = $totalDiscount;
        $totalItems = count($cart);

        foreach ($cart as $index => $item) {
            $cartItem = $item;

            // Last item gets remaining discount to avoid rounding issues
            if ($index === $totalItems - 1) {
                $itemDiscount = $remainingDiscount;
            } else {
                $discountRatio = $item['subtotal'] / $subtotal;
                $itemDiscount = floor($totalDiscount * $discountRatio);
            }

            $discountedSubtotal = max(0, $item['subtotal'] - $itemDiscount);
            $discountedPrice = $item['quantity'] > 0
                ? floor($discountedSubtotal / $item['quantity'])
                : 0;

            $cartItem['original_price'] = $item['price'];
            $cartItem['original_subtotal'] = $item['subtotal'];
            $cartItem['price'] = $discountedPrice;
            $cartItem['subtotal'] = $discountedSubtotal;
            $cartItem['discount_amount'] = $itemDiscount;

            $distributedCart[] = $cartItem;
            $remainingDiscount -= $itemDiscount;
        }

        return $distributedCart;
    }
}
