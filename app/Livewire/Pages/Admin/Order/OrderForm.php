<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\PromoService;
use App\Models\Promo;

class OrderForm extends Component
{
    protected PromoService $promoService;

    public $customer_id = null;

    public $nama = '';

    public $email = '';

    public $items = [];

    public $no_hp = '';

    public $customerFound = false;

    public $foundCustomer = null;

    public $isLoadingCustomer = false;

    public $customerPoint = 0;

    public $customerStatus = null;

    public $customerReferralCode = null;

    public $customer_notes = '';

    public $promoDiscount = 0;

    public $totalDiscount = 0;

    public $appliedPromos = [];

    public $kodePromo = '';

    public $promoValid = false;

    public $promoMessage = '';

    public $showPointsOption = false;

    public $usePoints = false;

    public $availablePoints = 0;

    public $pointsValue = 0;

    public $pointsDiscount = 0;

    public $selectedPromoId = null;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

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
        $this->calculateTotals();
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

                $this->items[$index]['price'] = 0;
                $this->items[$index]['subtotal'] = 0;

                continue;
            }

            $product = Product::find($item['product_id']);

            if (! $product) {

                $this->items[$index]['price'] = 0;
                $this->items[$index]['subtotal'] = 0;

                continue;
            }

            $price = $this->getPrice(
                $product,
                $item['duration_type'],
                (int) $item['duration_value']
            );

            if ($price <= 0) {
                continue;
            }

            $this->items[$index]['price'] = $price;

            $qty = max(1, (int) ($item['quantity'] ?? 1));

            if ($item['duration_type'] === 'bulan') {

                $this->items[$index]['subtotal'] =
                    $price *
                    (int) $item['duration_value'] *
                    $qty;
            } else {

                $this->items[$index]['subtotal'] =
                    $price * $qty;
            }
        }
        $this->calculateDiscounts();
    }

    private function getPrice(
        Product $product,
        string $durationType,
        int $durationValue
    ): int {

        if ($durationType === 'paket') {

            return match ($durationValue) {
                5  => (int) ($product->harga_5_perbulan ?? 0),
                10 => (int) ($product->harga_10_perbulan ?? 0),
                12 => (int) ($product->harga_pertahun ?? 0),
                default => 0,
            };
        }

        return (int) ($product->harga_perbulan ?? 0);
    }

    public function getGrandTotalProperty()
    {
        return max(
            0,
            $this->subTotal
                - $this->promoDiscount
                - $this->pointsDiscount
        );
    }

    private function generateOrderNumber(): string
    {
        $count = Order::count();

        while (
            Order::where(
                'order_number',
                'INV-' . now()->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT)
            )->exists()
        ) {
            $count++;
        }

        return 'INV-'
            . now()->format('Ymd')
            . '-'
            . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getSubTotalProperty()
    {
        return collect($this->items)->sum('subtotal');
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
            'required|in:bulan,paket',

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

            if (
                $this->usePoints &&
                $customer->status_member === 'active' &&
                $customer->point > 0
            ) {
                $customer->usePoints();
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),

                'customer_id' => $this->customer_id,

                'subtotal' => $this->subTotal,

                'promo_discount' => $this->promoDiscount,

                'points_discount' => $this->pointsDiscount,

                'total_discount' => $this->totalDiscount,

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

                    'duration_type' => $item['duration_type'] === 'paket'
                        ? 'tahun'
                        : 'bulan',

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

            dd($e->getMessage());
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

            $this->customerPoint = $customer->point ?? 0;

            $this->customerStatus = $customer->status_member;

            $this->customerReferralCode = $customer->kode_ref;

            $this->customerFound = true;
            if (
                $customer->status_member === 'active'
                && $customer->point > 0
            ) {

                $this->showPointsOption = true;

                $this->availablePoints = $customer->point;

                $this->pointsValue =
                    $customer->getPointValue();
            } else {

                $this->showPointsOption = false;

                $this->usePoints = false;

                $this->availablePoints = 0;

                $this->pointsValue = 0;
            }
        } else {

            $this->resetCustomerData();
        }

        $this->isLoadingCustomer = false;
        $this->calculateDiscounts();
    }

    private function buildCart(): array
    {
        $cart = [];

        foreach ($this->items as $item) {

            if (empty($item['product_id'])) {
                continue;
            }

            $product = Product::find($item['product_id']);

            if (! $product) {
                continue;
            }

            $cart[] = [
                'product_id'   => $product->id,
                'product_name' => $product->nama_akun,
                'price'        => $item['price'],
                'quantity'     => $item['quantity'] ?? 1,
                'subtotal'     => $item['subtotal'],
            ];
        }

        return $cart;
    }

    private function calculateDiscounts()
    {
        $cart = $this->buildCart();

        if (empty($cart)) {

            $this->promoDiscount = 0;
            $this->pointsDiscount = 0;
            $this->totalDiscount = 0;
            $this->appliedPromos = [];

            return;
        }

        $result = $this->promoService->calculateDiscount(
            $cart,
            $this->foundCustomer,
            $this->promoValid
                ? $this->kodePromo
                : null,
            false,
            false
        );

        $this->promoDiscount =
            $result['promo_discount'];

        $this->appliedPromos =
            $result['applied_promos'];

        $tempTotal =
            $result['subtotal']
            - $this->promoDiscount;

        if (
            $this->usePoints &&
            $this->pointsValue > 0
        ) {

            $this->pointsDiscount = min(
                $this->pointsValue,
                $tempTotal
            );
        } else {

            $this->pointsDiscount = 0;
        }

        $this->totalDiscount =
            $this->promoDiscount +
            $this->pointsDiscount;
    }

    public function updatedUsePoints()
    {
        $this->calculateDiscounts();
    }

    private function resetCustomerData()
    {
        $this->customer_id = null;
        $this->nama = '';
        $this->email = '';

        $this->customerPoint = 0;
        $this->customerStatus = null;
        $this->customerReferralCode = null;

        $this->customerFound = false;
        $this->foundCustomer = null;

        $this->showPointsOption = false;
        $this->usePoints = false;
        $this->availablePoints = 0;
        $this->pointsValue = 0;
        $this->pointsDiscount = 0;

        $this->isLoadingCustomer = false;

        $this->calculateDiscounts();
    }

    public function checkPromo()
    {
        $result = $this->promoService->validateKodePromo(
            $this->kodePromo,
            $this->foundCustomer,
            $this->subTotal
        );

        $this->promoValid = $result['valid'];

        $this->promoMessage = $result['message'];

        $this->calculateDiscounts();
    }

    public function removePromo()
    {
        $this->kodePromo = '';

        $this->promoValid = false;

        $this->promoMessage = '';

        $this->calculateDiscounts();
    }

    public function updatedKodePromo()
    {
        $this->promoValid = false;
        $this->promoMessage = '';

        $this->promoDiscount = 0;
        $this->appliedPromos = [];

        $this->calculateDiscounts();
    }

    public function updatedSelectedPromoId($value)
    {
        if (!$value) {
            $this->removePromo();

            return;
        }

        $promo = Promo::find($value);

        if (!$promo) {
            return;
        }

        $this->kodePromo = $promo->kode_promo;

        $this->checkPromo();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view(
            'livewire.pages.admin.order.order-form',
            [
                'products' => Product::orderBy('nama_akun')->get(),
                'activePromos' => Promo::active()
                    ->orderBy('nama_promo')
                    ->get(),
            ]
        );
    }
}
