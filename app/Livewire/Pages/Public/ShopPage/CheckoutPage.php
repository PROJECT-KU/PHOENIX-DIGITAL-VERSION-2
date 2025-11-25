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

    // Properties baru untuk poin
    public $showPointsOption = false;

    public $usePoints = false;

    public $availablePoints = 0;

    public $pointsValue = 0;

    public $discount = 0;

    public $finalTotal = 0;

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

    public function updatedUsePoints()
    {
        $this->calculateTotal();
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
        }

        $this->isLoadingCustomer = false;
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
        $this->discount = 0;
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

    // Method baru untuk distribute diskon ke cart items
    private function distributeDiscountToCart()
    {
        if ($this->discount <= 0) {
            return $this->cart; // Tidak ada diskon
        }

        $cartWithDiscount = [];
        $remainingDiscount = $this->discount;
        $totalItems = count($this->cart);

        foreach ($this->cart as $index => $item) {
            $cartItem = $item;

            // Hitung proporsi diskon untuk item ini
            if ($index === $totalItems - 1) {
                // Item terakhir dapat sisa diskon untuk menghindari pembulatan
                $itemDiscount = $remainingDiscount;
            } else {
                // Diskon proporsional berdasarkan subtotal
                $discountRatio = $item['subtotal'] / $this->total;
                $itemDiscount = floor($this->discount * $discountRatio);
            }

            // Hitung harga setelah diskon
            $discountedSubtotal = max(0, $item['subtotal'] - $itemDiscount);
            $discountedPrice = $item['quantity'] > 0 ? floor($discountedSubtotal / $item['quantity']) : 0;

            // Update item dengan harga setelah diskon
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

        // Validasi ulang cart
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang Anda kosong');

            return redirect()->route('shop.index');
        }

        // Validasi jika menggunakan poin tapi total jadi 0
        if ($this->finalTotal <= 0 && ! $this->usePoints) {
            session()->flash('error', 'Total pembayaran tidak valid');

            return;
        }

        try {
            DB::beginTransaction();

            // 1. Create or Update Customer
            $customer = Customer::updateOrCreate(
                ['no_hp' => $this->no_hp],
                [
                    'nama' => $this->nama,
                    'email' => $this->email,
                ]
            );

            // 2. Jika menggunakan poin, kurangi poin customer
            if ($this->usePoints && $customer->status_member === 'active' && $customer->point > 0) {
                $customer->usePoints();
            }

            // 3. Generate Order Number
            $orderNumber = $this->generateOrderNumber();

            // 4. Distribute diskon ke cart items jika menggunakan poin
            $finalCart = $this->usePoints ? $this->distributeDiscountToCart() : $this->cart;

            // 5. Create Order
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
            ]);

            // 6. Create Order Items dengan harga yang sudah didiskon
            foreach ($finalCart as $item) {
                OrderItem::create([
                    'id' => Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_image' => $item['product_image'],
                    'duration_type' => $item['duration_type'],
                    'duration_value' => $item['duration_value'],
                    'price' => $item['price'], // Harga sudah terdiskon
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'], // Subtotal sudah terdiskon
                ]);
            }

            DB::commit();

            // 7. Clear Cart
            session()->forget('cart');
            $this->dispatch('cart-updated');

            // 8. Redirect
            if ($this->finalTotal > 0) {
                session()->flash('success', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.');

                return redirect()->route('payment', ['order' => $order->id]);
            } else {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'points',
                ]);

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
