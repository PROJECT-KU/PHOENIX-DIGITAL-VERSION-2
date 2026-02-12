<?php

namespace App\Livewire\Components;

use App\Services\PromoService;
use Livewire\Component;

class PromoBadge extends Component
{
    public $productId;

    public $customer = null;

    public $activePromos = [];

    public $bestDiscount = 0;

    public $discountType = 'persen';

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount($productId, $customer = null)
    {
        $this->productId = $productId;
        $this->customer = $customer;
        $this->loadPromos();
    }

    public function loadPromos()
    {
        $this->activePromos = $this->promoService
            ->getProductPromos($this->productId, $this->customer)
            ->filter(function ($promo) {
                return $promo->tipe_promo === 'flash_sale'; // Only show flash sales on product card
            });

        if ($this->activePromos->isNotEmpty()) {
            $isMember = $this->customer && $this->customer->status_member === 'active';

            // Find best discount
            $bestPromo = $this->activePromos->sortByDesc(function ($promo) use ($isMember) {
                return $promo->getDiskonValue($isMember);
            })->first();

            $this->bestDiscount = $bestPromo->getDiskonValue($isMember);
            $this->discountType = $bestPromo->tipe_diskon;
        }
    }

    public function render()
    {
        return view('livewire.components.promo-badge');
    }
}
