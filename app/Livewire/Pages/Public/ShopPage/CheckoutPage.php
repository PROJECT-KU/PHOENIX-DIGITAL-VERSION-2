<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Str;

class CheckoutPage extends Component
{
    #[Validate('required|numeric|digits_between:10,15')]
    public $no_hp = '';

    #[Validate('required|string|max:255')]
    public $nama = '';

    #[Validate('required|email|max:255')]
    public $email = '';

    public $customer_notes = '';

    public $isLoadingCustomer = false;
    public $customerFound = false;
    public $cart = [];
    public $total = 0;

    public function mount()
    {
        // Redirect jika cart kosong
        $this->cart = session()->get('cart', []);

        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang Anda kosong');
            return redirect()->route('shop.index');
        }

        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->total = array_sum(array_column($this->cart, 'subtotal'));
    }

    public function updatedNoHp($value)
    {
        // Auto search customer ketika no_hp diubah (minimal 10 digit)
        if (strlen($value) >= 10) {
            $this->searchCustomer();
        } else {
            $this->resetCustomerData();
        }
    }

    public function searchCustomer()
    {
        $this->isLoadingCustomer = true;

        // Simulasi delay (hapus di production)
        sleep(1);

        $customer = Customer::where('no_hp', $this->no_hp)->first();

        if ($customer) {
            $this->nama = $customer->nama;
            $this->email = $customer->email;
            $this->customerFound = true;

            session()->flash('info', 'Data pelanggan ditemukan dan diisi otomatis');
        } else {
            $this->resetCustomerData();
            $this->customerFound = false;
        }

        $this->isLoadingCustomer = false;
    }

    private function resetCustomerData()
    {
        $this->nama = '';
        $this->email = '';
        $this->customerFound = false;
    }

    public function checkout()
    {
        $this->validate();

        // Validasi ulang cart
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang Anda kosong');
            return redirect()->route('shop.index');
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

            // 2. Generate Order Number
            $orderNumber = $this->generateOrderNumber();

            // 3. Create Order
            $order = Order::create([
                'id' => Str::uuid(),
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'subtotal' => $this->total,
                'total' => $this->total,
                'status' => 'pending',
                'customer_notes' => $this->customer_notes,
                'expired_at' => now()->addHours(24), // Expired 24 jam
            ]);

            // 4. Create Order Items
            foreach ($this->cart as $item) {
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

            DB::commit();

            // 5. Clear Cart
            session()->forget('cart');
            $this->dispatch('cart-updated');

            // 6. Redirect ke halaman payment
            session()->flash('success', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.');
            return redirect()->route('payment', ['order' => $order->id]);
        } catch (\Exception $e) {
            DB::rollBack();

            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', now())
            ->latest()
            ->first();

        $increment = $lastOrder ? intval(substr($lastOrder->order_number, -4)) + 1 : 1;

        return 'INV-' . $date . '-' . str_pad($increment, 4, '0', STR_PAD_LEFT);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.checkout-page');
    }
}
