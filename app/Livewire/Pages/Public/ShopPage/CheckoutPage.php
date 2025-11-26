<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
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

    public $total = 0;

    public $showPointsOption = false;

    public $usePoints = false;

    public $availablePoints = 0;

    public $pointsValue = 0;

    public $discount = 0;

    public $finalTotal = 0;

    // Referral properties
    public $referralCode = '';

    public $referralValid = false;

    public $referralMessage = '';

    public $referrerId = null;

    public $showReferralInput = false;

    public $isCheckingReferral = false;

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
        // Cek eligibility untuk referral code saat email berubah
        $this->checkReferralEligibility();
    }

    public function updatedUsePoints()
    {
        $this->calculateTotal();
    }

    public function updatedReferralCode()
    {
        // Reset validasi saat kode berubah
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

            // Cek eligibility referral
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
            // Customer baru, bisa pakai referral
            $this->showReferralInput = true;
        }

        $this->isLoadingCustomer = false;
        $this->calculateTotal();
    }

    private function checkReferralEligibility()
    {
        // Reset referral status
        $this->showReferralInput = false;
        $this->referralCode = '';
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;

        if (! $this->no_hp || ! $this->email) {
            return;
        }

        // Cek apakah customer sudah terdaftar
        $existingCustomer = Customer::where(function ($query) {
            $query->where('no_hp', $this->no_hp)
                ->orWhere('email', $this->email);
        })->first();

        if ($existingCustomer) {
            // Jika sudah member, tidak bisa pakai referral
            if ($existingCustomer->status_member === 'active') {
                $this->showReferralInput = false;

                return;
            }

            // Jika sudah pernah transaksi, tidak bisa pakai referral
            if ($existingCustomer->hasTransactions()) {
                $this->showReferralInput = false;

                return;
            }

            // Non-member dan belum pernah transaksi, bisa pakai referral
            $this->showReferralInput = true;
        } else {
            // Customer baru, bisa pakai referral
            $this->showReferralInput = true;
        }
    }

    public function checkReferralCode()
    {
        $this->isCheckingReferral = true;
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;

        // Validasi input kosong
        if (empty($this->referralCode)) {
            $this->referralMessage = 'Silakan masukkan kode referral';
            $this->isCheckingReferral = false;

            return;
        }

        // Format kode ke uppercase
        $this->referralCode = strtoupper(trim($this->referralCode));

        // Validasi format kode
        if (! preg_match('/^PDW_\d{4}$/', $this->referralCode)) {
            $this->referralMessage = 'Format kode referral tidak valid.';
            $this->isCheckingReferral = false;

            return;
        }

        // Cek apakah kode referral ada dan aktif
        $referrer = Customer::where('kode_ref', $this->referralCode)
            ->where('status_member', 'active')
            ->first();

        if (! $referrer) {
            $this->referralMessage = 'Kode referral tidak ditemukan atau sudah tidak aktif';
            $this->isCheckingReferral = false;

            return;
        }

        // Cek apakah customer masih eligible untuk pakai referral
        if (! $this->showReferralInput) {
            $this->referralMessage = 'Anda tidak bisa menggunakan kode referral';
            $this->isCheckingReferral = false;

            return;
        }

        // Double check: pastikan no_hp dan email belum pernah transaksi
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

        // Validasi berhasil
        $this->referralValid = true;
        $this->referrerId = $referrer->id;
        $this->referralMessage = '✓ Kode referral valid! Direferensikan oleh '.$referrer->nama;

        $this->isCheckingReferral = false;
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
        $this->discount = 0;

        // Reset referral
        $this->showReferralInput = false;
        $this->referralCode = '';
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;
    }

    private function calculateTotal()
    {
        $subtotal = 0;
        foreach ($this->cart as $item) {
            $subtotal += $item['subtotal'];
        }

        $this->total = $subtotal;

        if ($this->usePoints && $this->pointsValue > 0) {
            $this->discount = min($this->pointsValue, $this->total);
        } else {
            $this->discount = 0;
        }

        $this->finalTotal = max(0, $this->total - $this->discount);
    }

    private function distributeDiscountToCart()
    {
        if ($this->discount <= 0) {
            return $this->cart;
        }

        $cartWithDiscount = [];
        $remainingDiscount = $this->discount;
        $totalItems = count($this->cart);

        foreach ($this->cart as $index => $item) {
            $cartItem = $item;

            if ($index === $totalItems - 1) {
                $itemDiscount = $remainingDiscount;
            } else {
                $discountRatio = $item['subtotal'] / $this->total;
                $itemDiscount = floor($this->discount * $discountRatio);
            }

            $discountedSubtotal = max(0, $item['subtotal'] - $itemDiscount);
            $discountedPrice = $item['quantity'] > 0 ? floor($discountedSubtotal / $item['quantity']) : 0;

            $cartItem['original_price'] = $item['price'];
            $cartItem['original_subtotal'] = $item['subtotal'];
            $cartItem['price'] = $discountedPrice;
            $cartItem['subtotal'] = $discountedSubtotal;
            $cartItem['discount_amount'] = $itemDiscount;

            $cartWithDiscount[] = $cartItem;
            $remainingDiscount -= $itemDiscount;
        }

        return $cartWithDiscount;
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

            // Cek existing customer untuk validasi referral
            $existingCustomer = Customer::where('no_hp', $this->no_hp)
                ->orWhere('email', $this->email)
                ->first();

            // Tentukan apakah bisa menggunakan referral
            $canUseReferral = false;
            if ($this->referralCode && $this->referralValid && $this->referrerId) {
                // Hanya bisa pakai referral jika belum pernah transaksi
                if (! $existingCustomer || ! $existingCustomer->hasTransactions()) {
                    $canUseReferral = true;
                }
            }

            // Create atau update customer
            $customer = Customer::updateOrCreate(
                [
                    'no_hp' => $this->no_hp,
                    'nama' => $this->nama,
                    'email' => $this->email,
                ]
            );

            // Gunakan poin jika dipilih
            if ($this->usePoints && $customer->status_member === 'active' && $customer->point > 0) {
                $customer->usePoints();
            }

            $orderNumber = $this->generateOrderNumber();
            $finalCart = $this->usePoints ? $this->distributeDiscountToCart() : $this->cart;

            // Create order dengan data referral
            $order = Order::create([
                'id' => Str::uuid(),
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'subtotal' => $this->total,
                'total' => $this->finalTotal,
                'status' => 'pending',
                'customer_notes' => $this->customer_notes,
                'expired_at' => now()->addHours(24),
                'used_points' => $this->usePoints,
                'points_discount' => $this->discount,
                'points_calculated' => false,
                'referral_code' => $canUseReferral ? $this->referralCode : null,
                'referrer_id' => $canUseReferral ? $this->referrerId : null,
            ]);

            // Create order items
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

            // Jika menggunakan referral yang valid, tidak langsung berikan poin
            // Poin akan diberikan saat order statusnya 'paid'
            // Ini akan ditangani oleh Observer atau saat update status order

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

                // Berikan poin referral jika order langsung paid (dibayar dengan poin)
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
