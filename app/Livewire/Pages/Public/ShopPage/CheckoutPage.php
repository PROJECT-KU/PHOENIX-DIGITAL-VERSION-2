<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
use App\Services\PromoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CheckoutPage extends Component
{
    #[Validate('required')]
    public $no_hp = '';

    #[Validate('required|string|max:255')]
    public $nama = '';

    #[Validate('required|email|max:255')]
    public $email = '';

    public $customer_notes = '';

    public $isLoadingCustomer = false;

    public $customerFound = false;

    public $foundCustomer = null;

    public $cart = [];

    public $subtotal = 0;

    // Points
    public $showPointsOption = false;

    public $usePoints = false;

    public $availablePoints = 0;

    public $pointsValue = 0;

    public $pointsDiscount = 0;

    // Promo
    public $kodePromo = '';

    public $promoValid = false;

    public $promoMessage = '';

    public $isCheckingPromo = false;

    public $promoDiscount = 0;

    public $appliedPromos = [];

    // Referral
    public $referralCode = '';

    public $referralValid = false;

    public $referralMessage = '';

    public $referrerId = null;

    public $showReferralInput = false;

    public $isCheckingReferral = false;

    public $referralDiscount = 0;

    // Total
    public $totalDiscount = 0;

    public $finalTotal = 0;

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount()
    {
        $this->cart = session()->get('cart', []);

        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang Anda kosong');

            return redirect()->route('shop.index');
        }

        $this->calculateTotal();
    }

    protected function formatIndonesianPhone(string $value): string
    {
        $value = preg_replace('/[^0-9+]/', '', $value);

        if (str_starts_with($value, '+62')) {
            return $value;
        }
        if (str_starts_with($value, '62')) {
            return '+'.$value;
        }
        if (str_starts_with($value, '0')) {
            return '+62'.substr($value, 1);
        }

        return $value;
    }

    public function updatedNoHp($value)
    {
        $this->no_hp = $this->formatIndonesianPhone($value);
        if (strlen($value) >= 10) {
            $this->searchCustomer();
        } else {
            $this->resetCustomerData();
        }
    }

    public function updatedEmail()
    {
        $this->checkReferralEligibility();
        $this->calculateTotal();
    }

    public function updatedUsePoints()
    {
        $this->calculateTotal();
    }

    public function updatedKodePromo()
    {
        $this->promoValid = false;
        $this->promoMessage = '';
        $this->calculateTotal();
    }

    public function updatedReferralCode()
    {
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;
    }

    public function searchCustomer()
    {
        $this->isLoadingCustomer = true;

        $customer = Customer::where('no_hp', $this->no_hp)->first();

        if ($customer) {
            $this->foundCustomer = $customer;
            $this->nama = $customer->nama;
            $this->email = $customer->email;
            $this->customerFound = true;

            $this->checkReferralEligibility();

            if ($customer->status_member === 'active' && $customer->point > 0) {
                $this->showPointsOption = true;
                $this->availablePoints = $customer->point;
                $this->pointsValue = $customer->getPointValue();
            } else {
                $this->showPointsOption = false;
                $this->usePoints = false;
            }

            session()->flash('info', 'Data pelanggan ditemukan dan diisi otomatis');
        } else {
            $this->resetCustomerData();
            $this->showReferralInput = true;
        }

        $this->isLoadingCustomer = false;
        $this->calculateTotal();
    }

    public function checkPromo()
    {
        $this->isCheckingPromo = true;
        $this->promoValid = false;
        $this->promoMessage = '';

        if (empty($this->kodePromo)) {
            $this->promoMessage = 'Silakan masukkan kode promo';
            $this->isCheckingPromo = false;

            return;
        }

        $customer = $this->foundCustomer;
        $result = $this->promoService->validateKodePromo(
            $this->kodePromo,
            $customer,
            $this->subtotal
        );

        if ($result['valid']) {
            $this->promoValid = true;
            $this->promoMessage = $result['message'];
            $this->calculateTotal();
        } else {
            $this->promoMessage = $result['message'];
        }

        $this->isCheckingPromo = false;
    }

    public function removePromo()
    {
        $this->kodePromo = '';
        $this->promoValid = false;
        $this->promoMessage = '';
        $this->calculateTotal();
    }

    private function checkReferralEligibility()
    {
        $this->showReferralInput = false;
        $this->referralCode = '';
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;

        if (! $this->no_hp || ! $this->email) {
            return;
        }

        $existingCustomer = Customer::where(function ($query) {
            $query->where('no_hp', $this->no_hp)
                ->orWhere('email', $this->email);
        })->first();

        if ($existingCustomer) {
            if ($existingCustomer->status_member === 'active') {
                $this->showReferralInput = false;

                return;
            }

            if ($existingCustomer->hasTransactions()) {
                $this->showReferralInput = false;

                return;
            }

            $this->showReferralInput = true;
        } else {
            $this->showReferralInput = true;
        }
    }

    public function checkReferralCode()
    {
        $this->isCheckingReferral = true;
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;

        if (empty($this->referralCode)) {
            $this->referralMessage = 'Silakan masukkan kode referral';
            $this->isCheckingReferral = false;

            return;
        }

        $this->referralCode = strtoupper(trim($this->referralCode));

        if (! preg_match('/^PDW_\d{4}$/', $this->referralCode)) {
            $this->referralMessage = 'Format kode referral tidak valid.';
            $this->isCheckingReferral = false;

            return;
        }

        $referrer = Customer::where('kode_ref', $this->referralCode)
            ->where('status_member', 'active')
            ->first();

        if (! $referrer) {
            $this->referralMessage = 'Kode referral tidak ditemukan atau sudah tidak aktif';
            $this->isCheckingReferral = false;

            return;
        }

        if (! $this->showReferralInput) {
            $this->referralMessage = 'Anda tidak bisa menggunakan kode referral';
            $this->isCheckingReferral = false;

            return;
        }

        $existingCustomer = Customer::where(function ($query) {
            $query->where('no_hp', $this->no_hp)
                ->orWhere('email', $this->email);
        })->first();

        if ($existingCustomer && $existingCustomer->hasTransactions()) {
            $this->referralMessage = 'Kode referral hanya berlaku untuk pembelian pertama';
            $this->showReferralInput = false;
            $this->isCheckingReferral = false;

            return;
        }

        $this->referralValid = true;
        $this->referrerId = $referrer->id;
        $this->referralMessage = '✓ Kode referral valid! Direferensikan oleh '.$referrer->nama;

        $this->isCheckingReferral = false;
        $this->calculateTotal();
    }

    private function resetCustomerData()
    {
        $this->nama = '';
        $this->email = '';
        $this->customerFound = false;
        $this->foundCustomer = null;
        $this->showPointsOption = false;
        $this->usePoints = false;
        $this->availablePoints = 0;
        $this->pointsValue = 0;

        $this->showReferralInput = false;
        $this->referralCode = '';
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;
    }

    private function calculateTotal()
    {
        // Calculate subtotal
        $this->subtotal = array_sum(array_column($this->cart, 'subtotal'));

        // Calculate promo discount using PromoService
        $promoResult = $this->promoService->calculateDiscount(
            $this->cart,
            $this->foundCustomer,
            $this->promoValid ? $this->kodePromo : null,
            $this->referralValid,
            false // points handled separately
        );

        $this->promoDiscount = $promoResult['promo_discount'];
        $this->referralDiscount = $promoResult['referral_discount'];
        $this->appliedPromos = $promoResult['applied_promos'];

        // Calculate points discount
        if ($this->usePoints && $this->pointsValue > 0) {
            $totalAfterPromo = $this->subtotal - $this->promoDiscount - $this->referralDiscount;
            $this->pointsDiscount = min($this->pointsValue, $totalAfterPromo);
        } else {
            $this->pointsDiscount = 0;
        }

        // Calculate total discount and final total
        $this->totalDiscount = $this->promoDiscount + $this->referralDiscount + $this->pointsDiscount;
        $this->finalTotal = max(0, $this->subtotal - $this->totalDiscount);
    }

    public function checkout()
    {
        $this->validate();

        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang Anda kosong');

            return redirect()->route('shop.index');
        }

        if ($this->finalTotal <= 0 && ! $this->usePoints) {
            session()->flash('error', 'Total pembayaran tidak valid');

            return;
        }

        try {
            DB::beginTransaction();

            $existingCustomer = Customer::where('no_hp', $this->no_hp)
                ->orWhere('email', $this->email)
                ->first();

            $canUseReferral = false;
            if ($this->referralCode && $this->referralValid && $this->referrerId) {
                if (! $existingCustomer || ! $existingCustomer->hasTransactions()) {
                    $canUseReferral = true;
                }
            }

            $customer = Customer::updateOrCreate(
                ['no_hp' => $this->no_hp],
                [
                    'nama' => $this->nama,
                    'email' => $this->email,
                ]
            );

            if ($this->usePoints && $customer->status_member === 'active' && $customer->point > 0) {
                $customer->usePoints();
            }

            $orderNumber = $this->generateOrderNumber();

            // Distribute all discounts to cart
            $finalCart = $this->promoService->distributeDiscountToCart(
                $this->cart,
                $this->totalDiscount
            );

            $order = Order::create([
                'id' => Str::uuid(),
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'subtotal' => $this->subtotal,
                'total' => $this->finalTotal,
                'status' => 'pending',
                'customer_notes' => $this->customer_notes,
                'expired_at' => now()->addHours(24),
                'used_points' => $this->usePoints,
                'points_discount' => $this->pointsDiscount,
                'points_calculated' => false,
                'promo_discount' => $this->promoDiscount,
                'referral_discount' => $this->referralDiscount,
                'total_discount' => $this->totalDiscount,
                'applied_promos' => $this->appliedPromos,
                'referral_code' => $canUseReferral ? $this->referralCode : null,
                'referrer_id' => $canUseReferral ? $this->referrerId : null,
            ]);

            foreach ($finalCart as $item) {
                OrderItem::create([
                    'id' => Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_image' => $item['product_image'],
                    'duration_type' => $item['duration_type'],
                    'duration_value' => $item['duration_value'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Save promo usage to pivot table
            foreach ($this->appliedPromos as $promoData) {
                $order->promos()->attach($promoData['promo_id'], [
                    'id' => Str::uuid(),
                    'kode_promo' => $promoData['kode_promo'] ?? null,
                    'tipe_diskon' => $promoData['tipe_diskon'],
                    'nilai_diskon' => $promoData['nilai_diskon'],
                    'jumlah_diskon' => $promoData['jumlah_diskon'],
                ]);

                // Increment promo usage
                $promo = Promo::find($promoData['promo_id']);
                if ($promo) {
                    $promo->incrementUsage($promoData['jumlah_diskon']);
                }
            }

            DB::commit();

            session()->forget('cart');
            $this->dispatch('cart-updated');

            if ($this->finalTotal > 0) {
                $message = 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.';
                if ($canUseReferral) {
                    $message .= ' Terima kasih telah menggunakan kode referral!';
                }
                session()->flash('success', $message);

                return redirect()->route('payment', ['order' => $order->id]);
            } else {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'points',
                ]);

                if ($canUseReferral && $this->referrerId) {
                    $referrer = Customer::find($this->referrerId);
                    if ($referrer && $referrer->status_member === 'active') {
                        $referrer->addReferralPoints(2);
                    }
                }

                session()->flash('success', 'Pesanan berhasil dibuat dan dibayar dengan poin!');

                return redirect()->route('shop.index');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    private function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', now())
            ->latest()
            ->first();

        $increment = $lastOrder ? intval(substr($lastOrder->order_number, -4)) + 1 : 1;

        return 'INV-'.$date.'-'.str_pad($increment, 4, '0', STR_PAD_LEFT);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.checkout-page');
    }
}
