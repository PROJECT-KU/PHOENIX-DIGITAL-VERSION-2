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

        $productIds = $this->flashSale->products->pluck('id')->toArray();

        if (empty($productIds)) {
            $this->featuredProducts = Product::inRandomOrder()->take(4)->get();
        } else {
            $this->featuredProducts = Product::whereIn('id', $productIds)
                ->inRandomOrder()
                ->take(4)
                ->get();
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

            return $originalPrice - ($originalPrice * ($percentage / 100));
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

    public function render()
    {
        return view('livewire.components.flash-saletimer');
    }
}
