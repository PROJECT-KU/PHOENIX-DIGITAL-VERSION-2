<?php

use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(function () {
    HargaPaket::lupakan();
});

/**
 * Paket berisi DUA produk — inilah bentuk yang dulu gagal: satu baris pesanan
 * untuk dua akun, sehingga admin tak punya tempat menaruh kredensial kedua
 * dan akunnya tidak bisa dikirim (kejadian INV-20260820-0008).
 */
function paketDuaProduk(int $hargaPaket = 70000): ProductBundlings
{
    $a = Product::create(['nama_akun' => 'Grammarly Premium', 'harga_perbulan' => 50000]);
    $b = Product::create(['nama_akun' => 'Scopus Lisensi', 'harga_perbulan' => 30000]);

    return ProductBundlings::create([
        'nama_paket' => 'Combo Riset Hemat',
        'harga_awal' => 'Rp 85.000',
        'harga_bundling' => 'Rp '.number_format($hargaPaket, 0, ',', '.'),
        'status' => 'active',
        'product_1' => $a->id,
        'product_2' => $b->id,
        'durations' => [
            'product_1' => ['value' => 1, 'type' => 'bulan'],
            'product_2' => ['value' => 1, 'type' => 'bulan'],
        ],
    ]);
}

function keranjangPaket(ProductBundlings $paket, int $harga): void
{
    session()->put('cart', ["bundling_{$paket->id}" => [
        'product_id' => $paket->id,
        'product_name' => $paket->nama_paket,
        'product_image' => null,
        'duration_type' => null,
        'duration_value' => null,
        'harga_awal' => 85000,
        'type' => 'bundling',
        'price' => $harga,
        'quantity' => 1,
        'subtotal' => $harga,
    ]]);
}

function checkoutPaket(ProductBundlings $paket, int $harga, string $nomor): void
{
    keranjangPaket($paket, $harga);

    Livewire::test(CheckoutPage::class)
        ->set('no_hp', $nomor)
        ->set('nama', 'Pembeli Paket')
        ->set('email', 'pembeli.pecah@contoh.test')
        ->call('checkout');
}

it('paket berisi dua produk tersimpan sebagai dua baris pesanan', function () {
    $paket = paketDuaProduk();

    checkoutPaket($paket, 70000, '081200000101');

    $order = Order::latest('created_at')->first();

    expect($order->items()->count())->toBe(2);
});

it('tiap baris menunjuk produk sungguhan agar akunnya bisa diisi', function () {
    $paket = paketDuaProduk();

    checkoutPaket($paket, 70000, '081200000102');

    $items = Order::latest('created_at')->first()->items;

    // product_id kosong dulu membuat kolom PRODUK tampil "-" dan halaman
    // proses tidak tahu akun apa yang harus disiapkan.
    expect($items->pluck('product_id')->filter()->count())->toBe(2)
        ->and($items->pluck('duration_value')->all())->toBe([1, 1])
        ->and($items->pluck('duration_type')->unique()->all())->toBe(['bulan']);
});

it('nama baris menyebut paket asalnya', function () {
    $paket = paketDuaProduk();

    checkoutPaket($paket, 70000, '081200000103');

    $nama = Order::latest('created_at')->first()->items->pluck('product_name')->all();

    expect($nama)->toContain('[Combo Riset Hemat] Grammarly Premium')
        ->and($nama)->toContain('[Combo Riset Hemat] Scopus Lisensi');
});

it('pembagian harga berjumlah persis sama dengan harga paket', function (int $harga) {
    $paket = paketDuaProduk($harga);

    checkoutPaket($paket, $harga, '0812000001'.$harga % 100);

    $order = Order::latest('created_at')->first();

    // Tidak boleh meleset satu rupiah pun dari yang dibayar pelanggan.
    expect((int) $order->items()->sum('subtotal'))->toBe($harga);
})->with([70000, 99001, 12345]);

it('paket tanpa produk penyusun tetap tersimpan agar pesanan tidak hilang', function () {
    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Kosong',
        'harga_awal' => 'Rp 125.000',
        'harga_bundling' => 'Rp 99.000',
        'status' => 'active',
    ]);

    checkoutPaket($paket, 99000, '081200000104');

    $order = Order::latest('created_at')->first();

    expect($order->items()->count())->toBe(1)
        ->and((int) $order->items()->first()->subtotal)->toBe(99000);
});

it('perintah pecah-item memperbaiki pesanan lama yang terlanjur satu baris', function () {
    $paket = paketDuaProduk();

    // Bentuk LAMA: satu baris, product_id menunjuk paket, tanpa durasi.
    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-LAMA-0001',
        'customer_id' => \App\Models\Customer::create([
            'nama' => 'Pembeli Lama',
            'no_hp' => '081200000105',
            'email' => 'lama@contoh.test',
        ])->id,
        'subtotal' => 70000,
        'total' => 70000,
        'unique_code' => 0,
        'status' => 'paid',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    OrderItem::create([
        'id' => Str::uuid(),
        'order_id' => $order->id,
        'product_id' => $paket->id,
        'product_name' => $paket->nama_paket,
        'duration_type' => null,
        'duration_value' => null,
        'price' => 70000,
        'quantity' => 1,
        'subtotal' => 70000,
    ]);

    $this->artisan('paket:pecah-item', ['--order' => 'INV-LAMA-0001', '--terapkan' => true])
        ->assertSuccessful();

    $items = $order->fresh()->items;

    expect($items->count())->toBe(2)
        ->and((int) $items->sum('subtotal'))->toBe(70000)
        ->and($items->pluck('product_id')->filter()->count())->toBe(2);
});

it('perintah tanpa --terapkan tidak mengubah apa pun', function () {
    $paket = paketDuaProduk();

    checkoutPaket($paket, 70000, '081200000106');

    $sebelum = OrderItem::count();

    $this->artisan('paket:pecah-item')->assertSuccessful();

    expect(OrderItem::count())->toBe($sebelum);
});
