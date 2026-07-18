<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
use App\Services\PromoService;
use Illuminate\Support\Facades\Cookie;
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

    public $pointsExpireLabel = '';

    public $pointsDiscount = 0;

    // Nama promo terpasang yang melarang penggabungan — null bila tidak ada.
    // Dipakai view utk menonaktifkan input & menjelaskan alasannya ke pembeli.
    public $promoBlokirGabung = null;

    public $promoBlokirReferral = null;

    public $promoBlokirPoin = null;

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

    // unik kode
    public $uniqueCode = 0;

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
        $cart = session()->get('cart', []);

        // Akun digital: setiap baris selalu 1 item — samakan dengan keranjang
        // agar sesi lama yang sempat menumpuk jumlah tidak menggelembungkan subtotal.
        foreach ($cart as $key => $item) {
            $cart[$key]['quantity'] = 1;
            $cart[$key]['subtotal'] = (int) ($item['price'] ?? $item['subtotal'] ?? 0);
        }
        session()->put('cart', $cart);

        $this->cart = $cart;

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
            // Pastikan poin tahun lalu sudah kadaluarsa sebelum ditampilkan.
            $customer->applyYearlyExpiry();

            $this->foundCustomer = $customer;
            $this->nama = $customer->nama;
            $this->email = $customer->email;
            $this->customerFound = true;

            $this->checkReferralEligibility();

            if ($customer->status_member === 'active' && $customer->point > 0) {
                $this->showPointsOption = true;
                $this->availablePoints = $customer->point;
                $this->pointsValue = $customer->getPointValue();
                $this->pointsExpireLabel = $customer->pointsExpireLabel();
            } else {
                $this->showPointsOption = false;
                $this->usePoints = false;
                $this->pointsExpireLabel = '';
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

        // Batasi percobaan agar kode tidak bisa ditebak (brute-force). Tidak mengubah
        // logika validasi/perhitungan — hanya menahan bila terlalu sering mencoba.
        $rlKey = 'promo-check:'.request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rlKey, 8)) {
            $this->isCheckingPromo = false;
            $this->promoMessage = 'Terlalu banyak percobaan. Coba lagi dalam '.\Illuminate\Support\Facades\RateLimiter::availableIn($rlKey).' detik.';

            return;
        }
        \Illuminate\Support\Facades\RateLimiter::hit($rlKey, 60);

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

        // Batasi percobaan agar kode referral tidak bisa ditebak (brute-force).
        $rlKey = 'referral-check:'.request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rlKey, 8)) {
            $this->isCheckingReferral = false;
            $this->referralMessage = 'Terlalu banyak percobaan. Coba lagi dalam '.\Illuminate\Support\Facades\RateLimiter::availableIn($rlKey).' detik.';

            return;
        }
        \Illuminate\Support\Facades\RateLimiter::hit($rlKey, 60);

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
        $this->subtotal = array_sum(array_column($this->cart, 'subtotal'));

        $promoResult = $this->promoService->calculateDiscount(
            $this->cart,
            $this->foundCustomer,
            $this->promoValid ? $this->kodePromo : null,
            $this->referralValid,
            false
        );

        $this->promoDiscount = $promoResult['promo_discount'];
        $this->referralDiscount = $promoResult['referral_discount'];
        $this->appliedPromos = $promoResult['applied_promos'];

        $tempTotal = $this->subtotal - $this->promoDiscount - $this->referralDiscount;

        // Promo terpasang bisa melarang penggabungan. Nama promo pelarangnya
        // disimpan supaya UI bisa menonaktifkan input SEKALIGUS menyebut alasannya
        // — kalau cuma diam-diam tidak berlaku, pembeli awam akan mengira bisa.
        $this->promoBlokirGabung = $this->promoService->promoPelarang($this->appliedPromos, 'can_stack_with_other');
        $this->promoBlokirReferral = $this->promoService->promoPelarang($this->appliedPromos, 'can_stack_with_referral');
        $this->promoBlokirPoin = $this->promoService->promoPelarang($this->appliedPromos, 'can_stack_with_points');

        if ($this->usePoints && $this->pointsValue > 0 && ! $this->promoBlokirPoin) {
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

    /**
     * Simpan keranjang tertinggal (email + isi keranjang) untuk reminder.
     * Terisolasi & try/catch — TIDAK memengaruhi perhitungan/alur checkout.
     */
    public function saveAbandonedCart($email = null): void
    {
        try {
            $email = trim((string) $email);
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return;
            }
            $cart = session()->get('cart', []);
            if (empty($cart)) {
                return;
            }
            // Batasi agar tidak bisa dipakai spam email ke alamat sembarangan.
            $rlKey = 'abandoned-cart:'.request()->ip();
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rlKey, 12)) {
                return;
            }
            \Illuminate\Support\Facades\RateLimiter::hit($rlKey, 300);
            \App\Models\AbandonedCart::updateOrCreate(
                ['email' => $email],
                [
                    'items' => array_values(array_map(fn ($i) => [
                        'name' => $i['product_name'] ?? '',
                        'qty' => (int) ($i['quantity'] ?? 1),
                    ], $cart)),
                    'total' => (int) array_sum(array_column($cart, 'subtotal')),
                    'recovered_at' => null,
                    'reminded_at' => null,
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function checkout()
    {
        $this->validate();

        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang Anda kosong');

            return redirect()->route('shop.index');
        }

        // Email UNIQUE antar pelanggan. Pelanggan dikenali via no_hp (updateOrCreate),
        // jadi bila email yang diketik sudah dipakai pelanggan LAIN (no_hp berbeda),
        // penyimpanan akan gagal diam-diam karena constraint unik — tanpa keterangan.
        // Cegah lebih awal dengan pesan jelas di kolom email.
        $pemilikEmail = Customer::where('email', $this->email)->first();
        if ($pemilikEmail && $pemilikEmail->no_hp !== $this->no_hp) {
            $this->addError('email', 'Email ini sudah terdaftar dengan nomor HP lain. Gunakan email lain, atau isi nomor HP yang terdaftar dengan email ini.');

            return;
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
                'guest_token' => Cookie::get('guest_token'),
                'total' => $this->finalTotal,
                'unique_code' => $this->uniqueCode,
                'status' => 'pending',
                // Pemesanan dari PUBLIC (jasa maupun non-jasa) selalu QRIS dinamis.
                // Bila akhirnya lunas penuh dengan poin, ditimpa jadi 'points' di bawah.
                'payment_method' => 'qris_dinamis',
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
                $orderItem = OrderItem::create([
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
                    // Khusus JASA (kosong/null untuk produk biasa).
                    'addons' => $item['addons'] ?? null,
                    'addons_total' => (int) ($item['addons_total'] ?? 0),
                    'jumlah_halaman' => $item['jumlah_halaman'] ?? null,
                    'halaman_dikecualikan' => $item['halaman_dikecualikan'] ?? null,
                    'halaman_dihitung' => $item['halaman_dihitung'] ?? null,
                ]);

                // Jasa per halaman: file yang diunggah SEBELUM bayar dipindahkan
                // menjadi pengecekan pesanan ini, siap diproses admin.
                if (! empty($item['draft_upload_id'])) {
                    $this->pindahkanDraftUpload($item['draft_upload_id'], $order->id);
                }
            }

            // Save promo usage to pivot table
            foreach ($this->appliedPromos as $promoData) {
                $promo = Promo::find($promoData['promo_id']);

                // Penjaga kuota. Promo dipasang di keranjang jauh sebelum bayar,
                // jadi slot terakhir bisa saja diambil orang lain di sela itu.
                // lockForUpdate() menahan checkout lain yg memakai promo SAMA
                // sampai transaksi ini selesai, sehingga dua pembeli tidak bisa
                // sama-sama lolos merebut slot terakhir.
                // Promo tanpa kuota (kuota NULL) tidak dikunci -> alur lama utuh.
                if ($promo && $promo->kuota !== null) {
                    $promo = Promo::whereKey($promo->id)->lockForUpdate()->first();

                    if ($promo->kuotaHabis()) {
                        throw new \RuntimeException(
                            'Kuota promo "'.$promo->nama_promo.'" baru saja habis. Silakan muat ulang halaman dan pesan kembali.'
                        );
                    }
                }

                $order->promos()->attach($promoData['promo_id'], [
                    'id' => Str::uuid(),
                    'kode_promo' => $promoData['kode_promo'] ?? null,
                    'tipe_diskon' => $promoData['tipe_diskon'],
                    'nilai_diskon' => $promoData['nilai_diskon'],
                    'jumlah_diskon' => $promoData['jumlah_diskon'],
                ]);

                // Increment promo usage
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

            // Bila lolos pre-check tapi email tetap bentrok (mis. balapan antar
            // pembeli), tampilkan pesan ramah di kolom email, bukan SQL mentah.
            if (str_contains($e->getMessage(), 'customers_email_unique')) {
                $this->addError('email', 'Email ini sudah terdaftar dengan nomor HP lain. Gunakan email lain, atau isi nomor HP yang terdaftar dengan email ini.');

                return;
            }

            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Pindahkan file draft (diunggah sebelum bayar, jasa per halaman) menjadi
     * OrderUpload milik pesanan — langsung berstatus 'menunggu' agar terpantau
     * admin lewat badge. Terisolasi: kegagalan di sini tak membatalkan checkout.
     */
    private function pindahkanDraftUpload(string $draftId, string $orderId): void
    {
        try {
            $draft = \App\Models\JasaDraftUpload::find($draftId);
            if (! $draft) {
                return;
            }

            $tujuan = 'order-uploads/'.$orderId.'/masuk/'.basename($draft->path);
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($draft->path)) {
                \Illuminate\Support\Facades\Storage::disk('local')->move($draft->path, $tujuan);
            }

            \App\Models\OrderUpload::create([
                'order_id' => $orderId,
                'path' => $tujuan,
                'nama_asli' => $draft->nama_asli,
                'ukuran' => $draft->ukuran,
                'mime' => $draft->mime,
                'status' => 'menunggu',
            ]);

            $draft->delete();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function generateOrderNumber()
    {
        $date = now()->format('Ymd');

        // Tambahkan lockForUpdate() menghindari race condition di database
        $lastOrder = Order::whereDate('created_at', now())
            ->latest()
            ->lockForUpdate()
            ->first();

        if (! $lastOrder) {
            $number = 'INV-'.$date.'-0001';
            if (Order::where('order_number', $number)->exists()) {
                return 'INV-'.$date.'-0002';
            }

            return $number;
        }

        $parts = explode('-', $lastOrder->order_number);
        $lastSequence = intval(end($parts));

        $increment = $lastSequence + 1;

        $newOrderNumber = 'INV-'.$date.'-'.str_pad($increment, 4, '0', STR_PAD_LEFT);

        while (Order::where('order_number', $newOrderNumber)->exists()) {
            $increment++;
            $newOrderNumber = 'INV-'.$date.'-'.str_pad($increment, 4, '0', STR_PAD_LEFT);
        }

        return $newOrderNumber;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.checkout-page');
    }
}
