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

        // 1. Flash Sale Promos (otomatis apply untuk semua produk yang eligible)
        $flashSaleDiscount = $this->applyFlashSalePromos($cart, $customer, $isMember);
        if ($flashSaleDiscount['total'] > 0) {
            $appliedPromos = array_merge($appliedPromos, $flashSaleDiscount['promos']);
            $totalPromoDiscount += $flashSaleDiscount['total'];
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

        // 4. Points Discount (dihitung terpisah, di handle di CheckoutPage)

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
     * Apply flash sale promos
     */
    private function applyFlashSalePromos(array $cart, ?Customer $customer, bool $isMember): array
    {
        $promos = [];
        $totalDiscount = 0;

        // Get all active flash sale promos
        $flashSales = Promo::active()
            ->flashSale()
            ->orderBy('prioritas', 'desc')
            ->get();

        foreach ($cart as $item) {
            // Find applicable flash sales for this product
            $productPromos = $flashSales->filter(function ($promo) use ($item, $customer) {
                // Check if promo applies to this product
                if ($promo->products->isEmpty() || $promo->products->contains('id', $item['product_id'])) {
                    return $promo->canBeUsedBy($customer);
                }

                return false;
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
                        'tipe_diskon' => $promo->tipe_diskon,
                        'nilai_diskon' => $promo->getDiskonValue($isMember),
                        'jumlah_diskon' => $discount,
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
                'tipe_diskon' => $promo->tipe_diskon,
                'nilai_diskon' => $promo->getDiskonValue($isMember),
                'jumlah_diskon' => $discount,
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
     * Get applicable promos for a product
     */
    public function getProductPromos(string $productId, ?Customer $customer = null): Collection
    {
        return Promo::active()
            ->forProduct($productId)
            ->get()
            ->filter(function ($promo) use ($customer) {
                return $promo->canBeUsedBy($customer);
            });
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
