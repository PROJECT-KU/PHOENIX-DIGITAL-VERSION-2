<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class OrderForm extends Component
{
    public $customer_id = null;

    public $nama = '';

    public $email = '';

    public $items = [];

    public $no_hp = '';

    public $customerFound = false;

    public $foundCustomer = null;

    public $isLoadingCustomer = false;

    public $customer_notes = '';

    public function mount()
    {
        $this->items[] = [
            'product_id' => '',
            'duration_type' => 'bulan',
            'duration_value' => 1,
            'quantity' => 1,
            'price' => 0,
            'subtotal' => 0,
        ];
    }

    protected function formatIndonesianPhone(string $value): string
    {
        $value = preg_replace('/[^0-9+]/', '', $value);

        if (str_starts_with($value, '+62')) {
            return $value;
        }

        if (str_starts_with($value, '62')) {
            return '+' . $value;
        }

        if (str_starts_with($value, '0')) {
            return '+62' . substr($value, 1);
        }

        return $value;
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'duration_type' => 'bulan',
            'duration_value' => 1,
            'quantity' => 1,
            'price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);

        $this->items = array_values($this->items);

        $this->calculateTotals();
    }

    public function updatedItems()
    {
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        foreach ($this->items as $index => $item) {

            if (empty($item['product_id'])) {
                continue;
            }

            $product = Product::find($item['product_id']);

            if (! $product) {
                continue;
            }

            $price = $this->getPrice(
                $product,
                $item['duration_type'],
                (int) $item['duration_value']
            );

            $this->items[$index]['price'] = $price;

            $this->items[$index]['subtotal'] =
                $price * (int) $item['quantity'];
        }
    }

    private function getPrice(
        Product $product,
        string $durationType,
        int $durationValue
    ): int {

        if ($durationType === 'tahun') {
            return (int) ($product->harga_pertahun ?? 0);
        }

        return match ($durationValue) {
            1 => (int) ($product->harga_perbulan ?? 0),
            5 => (int) ($product->harga_5_perbulan ?? 0),
            10 => (int) ($product->harga_10_perbulan ?? 0),
            default => (int) ($product->harga_awal ?? 0),
        };
    }

    public function getGrandTotalProperty()
    {
        return collect($this->items)->sum('subtotal');
    }

    private function generateOrderNumber(): string
    {
        $count = Order::count() + 1;

        return 'INV-' .
            now()->format('Ymd') .
            '-' .
            str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        $this->validate([
            'no_hp' => 'required|string|max:20',
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:140',

            'items' => 'required|array|min:1',

            'items.*.product_id' => 'required|exists:products,id',

            'items.*.duration_type' =>
            'required|in:bulan,tahun',

            'items.*.duration_value' =>
            'required|integer|min:1',

            'items.*.quantity' =>
            'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            if ($this->customer_id) {

                $customer = Customer::findOrFail($this->customer_id);

                $customer->update([
                    'nama' => $this->nama,
                    'email' => $this->email,
                ]);
            } else {

                $customer = Customer::create([
                    'nama' => $this->nama,
                    'email' => $this->email,
                    'no_hp' => $this->no_hp,
                ]);
            }

            $this->customer_id = $customer->id;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),

                'customer_id' => $this->customer_id,

                'subtotal' => $this->grandTotal,

                'total' => $this->grandTotal,

                'status' => 'pending',

                'customer_notes' => $this->customer_notes,
            ]);

            foreach ($this->items as $item) {

                $product = Product::findOrFail(
                    $item['product_id']
                );

                OrderItem::create([
                    'order_id' => $order->id,

                    'product_id' => $product->id,

                    'product_name' => $product->nama_akun,

                    'product_description' => $product->deskripsi,

                    'product_image' => $product->image,

                    'duration_type' => $item['duration_type'],

                    'duration_value' => $item['duration_value'],

                    'price' => $item['price'],

                    'quantity' => $item['quantity'],

                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();

            session()->flash(
                'success',
                'Pesanan berhasil dibuat'
            );

            return redirect()->route(
                'admin.pesanantoko.detail',
                $order
            );
        } catch (\Exception $e) {

            DB::rollBack();

            session()->flash(
                'error',
                'Gagal membuat pesanan : ' . $e->getMessage()
            );
        }
    }

    public function updatedNoHp($value)
    {
        $this->no_hp = $this->formatIndonesianPhone($value);

        if (strlen($this->no_hp) >= 10) {
            $this->searchCustomer();
        } else {
            $this->resetCustomerData();
        }
    }

    public function searchCustomer()
    {
        $this->isLoadingCustomer = true;

        $customer = Customer::where('no_hp', $this->no_hp)
            ->orWhere('no_hp', str_replace('+62', '0', $this->no_hp))
            ->first();

        if ($customer) {

            $this->foundCustomer = $customer;

            $this->customer_id = $customer->id;

            $this->nama = $customer->nama;

            $this->email = $customer->email;

            $this->customerFound = true;
        } else {

            $this->resetCustomerData();
        }

        $this->isLoadingCustomer = false;
    }

    private function resetCustomerData()
    {
        $this->customer_id = null;

        $this->nama = '';

        $this->email = '';

        $this->customerFound = false;

        $this->foundCustomer = null;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view(
            'livewire.pages.admin.order.order-form',
            [
                'products' => Product::orderBy('nama_akun')->get(),
            ]
        );
    }
}
