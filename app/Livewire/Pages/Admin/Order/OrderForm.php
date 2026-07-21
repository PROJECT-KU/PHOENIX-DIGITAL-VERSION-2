<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\Promo;
use App\Services\PromoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderForm extends Component
{
    use WithFileUploads;

    // ===== Bukti pembayaran manual (transfer / QRIS statis) =====
    public bool $showBuktiModal = false;

    public $bukti; // file bukti yang diunggah

    // ===== Customer =====
    public $customer_id = null;

    public $nama = '';

    public $email = '';

    public $no_hp = '';

    public $customerFound = false;

    public $foundCustomer = null;

    public $isLoadingCustomer = false;

    public $customer_notes = '';

    // ===== Items (boleh lebih dari satu akun / paket) =====
    public $items = [];

    public $selectedBundleId = '';

    // ===== Pembayaran =====
    public $payment_method = '';

    // ===== Promo =====
    public $kodePromo = '';

    public $promoValid = false;

    public $promoMessage = '';

    // ===== Referral =====
    public $referralCode = '';

    public $referralValid = false;

    public $referralMessage = '';

    public $referrerId = null;

    public $showReferralInput = false;

    // ===== Points =====
    public $usePoints = false;

    public $pointsValue = 0;

    // ===== Totals (computed) =====
    public $subtotal = 0;

    public $promoDiscount = 0;

    public $referralDiscount = 0;

    public $pointsDiscount = 0;

    public $totalDiscount = 0;

    public $uniqueCode = 0;

    public $finalTotal = 0;

    public $appliedPromos = [];

    protected PromoService $promoService;

    public function boot(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    public function mount()
    {
        $this->items[] = $this->blankItem();
    }

    private function blankItem(): array
    {
        return [
            'type' => 'product',
            'product_id' => '',
            'duration_type' => 'bulan',
            'duration_value' => 1,
            'quantity' => 1,
            'price' => 0,
            'subtotal' => 0,
        ];
    }

    // Tambah paket bundling → dipecah jadi beberapa produk (durasi per produk)
    public function addBundle()
    {
        if (! $this->selectedBundleId) {
            return;
        }

        $b = ProductBundlings::find($this->selectedBundleId);
        if (! $b) {
            return;
        }

        $hargaBundling = (int) preg_replace('/[^0-9]/', '', (string) $b->harga_bundling);

        // Durasi tiap produk sudah diset di paket → admin tinggal pilih nama paket
        $products = [];
        foreach ($b->bundleProducts() as $bp) {
            $p = Product::find($bp['product_id']);
            if (! $p) {
                continue;
            }
            $products[] = [
                'product_id' => $p->id,
                'product_name' => $p->nama_akun,
                'duration_type' => $bp['duration_type'],
                'duration_value' => $bp['duration_value'],
                'normal' => 0,
                'distributed' => 0,
            ];
        }

        if (empty($products)) {
            return;
        }

        // Hilangkan baris produk kosong (mis. baris default) supaya order paket saja rapi
        $this->items = array_values(array_filter($this->items, function ($item) {
            return ($item['type'] ?? 'product') === 'bundle' || ! empty($item['product_id']);
        }));

        $this->items[] = [
            'type' => 'bundle',
            'bundling_id' => $b->id,
            'bundling_name' => $b->nama_paket,
            'harga_bundling' => $hargaBundling,
            'products' => $products,
            'subtotal' => $hargaBundling,
        ];

        $this->selectedBundleId = '';
        $this->calculateTotals();
    }

    // ===================== CUSTOMER =====================
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
            $this->pointsValue = ($customer->status_member === 'active' && $customer->point > 0)
                ? $customer->getPointValue() : 0;
        } else {
            $this->resetCustomerData();
        }

        $this->checkReferralEligibility();
        $this->calculateTotals();
        $this->isLoadingCustomer = false;
    }

    private function resetCustomerData()
    {
        $this->customer_id = null;
        $this->nama = '';
        $this->email = '';
        $this->customerFound = false;
        $this->foundCustomer = null;
        $this->pointsValue = 0;
        $this->usePoints = false;
    }

    private function checkReferralEligibility()
    {
        $this->showReferralInput = ! ($this->foundCustomer && $this->foundCustomer->hasTransactions());

        if (! $this->showReferralInput) {
            $this->referralValid = false;
            $this->referralCode = '';
            $this->referralMessage = '';
            $this->referrerId = null;
        }
    }

    // ===================== ITEMS =====================
    public function addItem()
    {
        $this->items[] = $this->blankItem();
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

    // Dipanggil dari SweetAlert picker produk (data banyak → mudah dicari)
    public function setItemProduct($index, $productId)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['product_id'] = $productId;
            $this->calculateTotals();
        }
    }

    // Dipanggil dari SweetAlert picker paket bundling
    public function addBundleById($id)
    {
        $this->selectedBundleId = $id;
        $this->addBundle();
    }

    private function getPrice(Product $product, string $durationType, int $durationValue): int
    {
        $harga = $product->hargaUntuk($durationValue, $durationType);
        if ($harga > 0) {
            return $harga;
        }

        // Durasi non-katalog (mis. 2/3/4 bulan) tidak punya harga khusus →
        // hitung dari harga per bulan × durasi. Sama seperti fallback di sisi
        // publik (ProductDetail::addToCart). Tanpa ini durasi >1 berharga Rp 0.
        if ($durationType === 'bulan' && (int) ($product->harga_perbulan ?? 0) > 0 && $durationValue > 0) {
            return (int) $product->harga_perbulan * $durationValue;
        }

        return $harga;
    }

    // ===================== TOTAL / DISKON =====================
    public function calculateTotals()
    {
        foreach ($this->items as $i => $item) {
            if (($item['type'] ?? 'product') === 'bundle') {
                // Distribusi harga paket proporsional terhadap harga normal tiap produk
                $harga = (int) $item['harga_bundling'];
                $normals = [];
                foreach ($item['products'] as $j => $sub) {
                    $p = Product::find($sub['product_id']);
                    $normal = $p ? $this->getPrice($p, $sub['duration_type'], (int) $sub['duration_value']) : 0;
                    $this->items[$i]['products'][$j]['normal'] = $normal;
                    $normals[$j] = $normal;
                }
                $sumNormal = array_sum($normals);
                $count = count($item['products']);
                $running = 0;
                $lastKey = array_key_last($item['products']);
                foreach ($item['products'] as $j => $sub) {
                    if ($j === $lastKey) {
                        $dist = $harga - $running; // sisa agar total pas
                    } else {
                        $weight = $sumNormal > 0 ? ($normals[$j] / $sumNormal) : (1 / max(1, $count));
                        $dist = (int) round($harga * $weight);
                        $running += $dist;
                    }
                    $this->items[$i]['products'][$j]['distributed'] = max(0, $dist);
                }
                $this->items[$i]['subtotal'] = $harga;

                continue;
            }

            // Produk satuan
            if (empty($item['product_id'])) {
                $this->items[$i]['price'] = 0;
                $this->items[$i]['subtotal'] = 0;

                continue;
            }
            $product = Product::find($item['product_id']);
            if (! $product) {
                continue;
            }

            $price = $this->getPrice($product, $item['duration_type'], (int) $item['duration_value']);
            $this->items[$i]['price'] = $price;
            $this->items[$i]['subtotal'] = $price * (int) $item['quantity'];
        }

        // Subtotal penuh (termasuk paket) untuk total akhir.
        // Promo cart HANYA produk satuan — item di dalam paket bundling
        // dikecualikan dari flash sale / promo (paket sudah punya harga diskon).
        $fullSubtotal = 0;
        $promoCart = [];
        foreach ($this->items as $item) {
            if (($item['type'] ?? 'product') === 'bundle') {
                $fullSubtotal += (int) ($item['subtotal'] ?? 0);

                continue; // tidak masuk promo cart
            }
            if (empty($item['product_id'])) {
                continue;
            }
            $fullSubtotal += (int) $item['subtotal'];
            $promoCart[] = [
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'quantity' => (int) $item['quantity'],
                'subtotal' => $item['subtotal'],
            ];
        }

        $this->subtotal = $fullSubtotal;

        if ($fullSubtotal <= 0) {
            $this->resetTotals();

            return;
        }

        // Promo (flash sale/auto/kode) hanya untuk produk satuan
        $this->promoDiscount = 0;
        $this->appliedPromos = [];
        if (! empty($promoCart)) {
            $result = $this->promoService->calculateDiscount(
                $promoCart,
                $this->foundCustomer,
                $this->promoValid ? $this->kodePromo : null,
                false, // referral dihitung terpisah di bawah (atas subtotal penuh)
                false
            );
            $this->promoDiscount = $result['promo_discount'];
            $this->appliedPromos = $result['applied_promos'];
        }

        // Referral (flat 2000) berlaku atas seluruh order, kecuali ada promo yang melarang stacking
        $this->referralDiscount = 0;
        if ($this->referralValid) {
            $blocked = false;
            foreach ($this->appliedPromos as $pd) {
                $promo = Promo::find($pd['promo_id'] ?? null);
                if ($promo && ! $promo->can_stack_with_referral) {
                    $blocked = true;
                    break;
                }
            }
            $this->referralDiscount = $blocked ? 0 : (int) min(2000, $this->subtotal);
        }

        $tempTotal = $this->subtotal - $this->promoDiscount - $this->referralDiscount;

        // Sama dgn checkout publik: promo yang melarang penggabungan dgn poin
        // membatalkan pemakaian poin.
        if ($this->usePoints && $this->pointsValue > 0
            && $this->promoService->poinBolehDipakai($this->appliedPromos)) {
            $this->pointsDiscount = min($this->pointsValue, max(0, $tempTotal));
        } else {
            $this->pointsDiscount = 0;
        }

        $this->totalDiscount = $this->promoDiscount + $this->referralDiscount + $this->pointsDiscount;
        $netTotal = max(0, $this->subtotal - $this->totalDiscount);

        if ($netTotal > 0) {
            if ($this->uniqueCode === 0) {
                $this->uniqueCode = rand(500, 999);
            }
            $this->finalTotal = $netTotal + $this->uniqueCode;
        } else {
            $this->uniqueCode = 0;
            $this->finalTotal = 0;
        }
    }

    private function resetTotals()
    {
        $this->promoDiscount = 0;
        $this->referralDiscount = 0;
        $this->pointsDiscount = 0;
        $this->totalDiscount = 0;
        $this->uniqueCode = 0;
        $this->finalTotal = 0;
        $this->appliedPromos = [];
    }

    // ===================== PROMO =====================
    public function applyPromo()
    {
        $this->promoValid = false;
        $this->promoMessage = '';

        if (empty($this->kodePromo)) {
            $this->promoMessage = 'Masukkan kode promo';

            return;
        }

        $result = $this->promoService->validateKodePromo(
            trim($this->kodePromo),
            $this->foundCustomer,
            $this->subtotal
        );

        if ($result['valid'] ?? false) {
            $this->promoValid = true;
            $this->promoMessage = '✓ '.($result['message'] ?? 'Kode promo dipakai');
        } else {
            $this->promoMessage = $result['message'] ?? 'Kode promo tidak valid';
        }

        $this->calculateTotals();
    }

    public function removePromo()
    {
        $this->kodePromo = '';
        $this->promoValid = false;
        $this->promoMessage = '';
        $this->calculateTotals();
    }

    // ===================== REFERRAL =====================
    public function checkReferralCode()
    {
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;

        if (empty($this->referralCode)) {
            $this->referralMessage = 'Masukkan kode referral';

            return;
        }

        $this->referralCode = strtoupper(trim($this->referralCode));

        if (! preg_match('/^PDW_\d{4}$/', $this->referralCode)) {
            $this->referralMessage = 'Format tidak valid (contoh: PDW_1234).';

            return;
        }

        $referrer = Customer::where('kode_ref', $this->referralCode)
            ->where('status_member', 'active')
            ->first();

        if (! $referrer) {
            $this->referralMessage = 'Kode referral tidak ditemukan / tidak aktif';

            return;
        }

        if (! $this->showReferralInput) {
            $this->referralMessage = 'Referral hanya untuk pembelian pertama';

            return;
        }

        $this->referralValid = true;
        $this->referrerId = $referrer->id;
        $this->referralMessage = '✓ Valid! Direferensikan oleh '.$referrer->nama;
        $this->calculateTotals();
    }

    public function removeReferral()
    {
        $this->referralCode = '';
        $this->referralValid = false;
        $this->referralMessage = '';
        $this->referrerId = null;
        $this->calculateTotals();
    }

    // ===================== POINTS =====================
    public function updatedUsePoints()
    {
        $this->calculateTotals();
    }

    // ===================== SAVE =====================
    private function generateOrderNumber(): string
    {
        $count = Order::count() + 1;

        return 'INV-'.now()->format('Ymd').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        // Buang baris produk kosong (mis. baris default) agar tidak menghalangi
        // pemesanan paket bundling saja.
        $this->items = array_values(array_filter($this->items, function ($item) {
            return ($item['type'] ?? 'product') === 'bundle' || ! empty($item['product_id']);
        }));

        $this->validate([
            'no_hp' => 'required|string|max:20',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:140',
            'payment_method' => 'required|in:transfer,qris_statis,qris_dinamis',
            'items' => 'required|array|min:1',
        ], [
            'items.required' => 'Tambahkan minimal 1 produk atau paket bundling.',
            'items.min' => 'Tambahkan minimal 1 produk atau paket bundling.',
        ], [
            'no_hp' => 'nomor HP',
            'payment_method' => 'metode pembayaran',
        ]);

        $this->calculateTotals();

        // Transfer & QRIS statis = pembayaran manual -> WAJIB unggah bukti dulu
        // (lewat popup) sebelum pesanan dibuat & masuk ke detail.
        if (in_array($this->payment_method, ['transfer', 'qris_statis'], true) && ! $this->bukti) {
            $this->showBuktiModal = true;

            return;
        }

        return $this->persistAndRedirect();
    }

    /** Konfirmasi dari popup bukti: validasi bukti lalu simpan pesanan. */
    public function konfirmasiBukti()
    {
        $this->validate(
            ['bukti' => 'required|image|max:4096'],
            ['bukti.required' => 'Bukti pembayaran wajib diunggah.', 'bukti.image' => 'Berkas harus gambar.', 'bukti.max' => 'Ukuran maksimal 4 MB.'],
            ['bukti' => 'bukti pembayaran']
        );

        return $this->persistAndRedirect();
    }

    protected function persistAndRedirect()
    {
        // Simpan file bukti (untuk transfer / qris_statis).
        $buktiPath = null;
        if ($this->bukti) {
            // Disk PRIVAT: bukti bayar memuat data rekening customer.
            $buktiPath = $this->bukti->store('bukti_pembayaran', 'local');
        }

        DB::beginTransaction();
        try {
            $customer = Customer::updateOrCreate(
                ['no_hp' => $this->no_hp],
                ['nama' => $this->nama, 'email' => $this->email ?: null]
            );

            $canUseReferral = $this->referralValid && $this->referrerId
                && ! $customer->hasTransactions();

            if ($this->usePoints && $customer->status_member === 'active' && $customer->point > 0) {
                $customer->usePoints();
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'subtotal' => $this->subtotal,
                'total' => $this->finalTotal,
                'unique_code' => $this->uniqueCode,
                'status' => 'pending',
                'payment_method' => $this->payment_method,
                'bukti_pembayaran' => $buktiPath,
                'customer_notes' => $this->customer_notes,
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

            foreach ($this->items as $item) {
                // Paket bundling → pecah jadi 1 order item per produk
                if (($item['type'] ?? 'product') === 'bundle') {
                    foreach ($item['products'] as $sub) {
                        $product = Product::findOrFail($sub['product_id']);
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name' => '['.$item['bundling_name'].'] '.$product->nama_akun,
                            'product_description' => $product->deskripsi ?? null,
                            'product_image' => $product->image ?? null,
                            'duration_type' => $sub['duration_type'],
                            'duration_value' => $sub['duration_value'],
                            'price' => $sub['distributed'] ?? 0,
                            'quantity' => 1,
                            'subtotal' => $sub['distributed'] ?? 0,
                        ]);
                    }

                    continue;
                }

                if (empty($item['product_id'])) {
                    continue;
                }
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->nama_akun,
                    'product_description' => $product->deskripsi ?? null,
                    'product_image' => $product->image ?? null,
                    'duration_type' => $item['duration_type'],
                    'duration_value' => $item['duration_value'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            foreach ($this->appliedPromos as $promoData) {
                if (empty($promoData['promo_id'])) {
                    continue;
                }
                // Penjaga kuota — sama dgn checkout publik: kunci baris promo agar
                // slot terakhir tidak bisa direbut dua pesanan sekaligus. Promo
                // tanpa kuota (NULL) tidak dikunci, jadi alur lama tidak berubah.
                $promo = Promo::find($promoData['promo_id']);
                if ($promo && $promo->kuota !== null) {
                    $promo = Promo::whereKey($promo->id)->lockForUpdate()->first();

                    if ($promo->kuotaHabis()) {
                        throw new \RuntimeException(
                            'Kuota promo "'.$promo->nama_promo.'" sudah habis.'
                        );
                    }
                }

                $order->promos()->attach($promoData['promo_id'], [
                    'id' => (string) Str::uuid(),
                    'kode_promo' => $promoData['kode_promo'] ?? null,
                    'tipe_diskon' => $promoData['tipe_diskon'] ?? null,
                    'nilai_diskon' => $promoData['nilai_diskon'] ?? 0,
                    'jumlah_diskon' => $promoData['jumlah_diskon'] ?? 0,
                ]);
                if ($promo) {
                    $promo->incrementUsage($promoData['jumlah_diskon'] ?? 0);
                }
            }

            DB::commit();

            // QRIS Dinamis → layar QR (generate, countdown, share WA, draft)
            if ($this->payment_method === 'qris_dinamis') {
                return redirect()->route('admin.pesanantoko.qris', $order);
            }

            session()->flash('successCreated', 'Pesanan berhasil dibuat! Silakan proses tiap akun (data akun, bonus, ebook) dari halaman detail.');

            return redirect()->route('admin.pesanantoko.detail', $order);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showBuktiModal = false;
            $this->dispatch('swal-error', message: 'Gagal membuat pesanan: '.$e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-form', [
            'products' => Product::orderBy('nama_akun')->get(),
            'bundlings' => ProductBundlings::where('status', 'active')->orderBy('nama_paket')->get(),
        ]);
    }
}
