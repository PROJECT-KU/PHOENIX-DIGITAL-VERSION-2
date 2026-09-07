<?php

use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Livewire\Pages\Public\ShopPage\ProductDetail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\JedaLayanan;
use Livewire\Livewire;

/**
 * Menjeda layanan menutup PEMBELIAN BARU tanpa menyembunyikan produknya:
 * halamannya tetap terbuka, hanya pintu checkout-nya yang ditutup.
 */
function produkPlagiasi(): Product
{
    return Product::create([
        'nama_akun' => 'Cek Plagiasi Turnitin',
        'butuh_file' => true,
        'pakai_exclude' => true,
        'harga_perbulan' => 5000,
    ]);
}

function produkCekAi(): Product
{
    return Product::create([
        'nama_akun' => 'Cek Plagiasi AI',
        'butuh_file' => true,
        'cek_ai' => true,
        'harga_perbulan' => 10000,
    ]);
}

function produkBiasa(): Product
{
    return Product::create([
        'nama_akun' => 'Microsoft Office 365',
        'harga_perbulan' => 25000,
    ]);
}

afterEach(function () {
    \App\Models\Setting::query()->delete();
});

it('jenis layanan berdiri sendiri — menjeda plagiasi tidak menyentuh cek ai', function () {
    JedaLayanan::setel('plagiasi', true);

    expect(JedaLayanan::dijeda('plagiasi'))->toBeTrue()
        ->and(JedaLayanan::dijeda('ai'))->toBeFalse()
        ->and(JedaLayanan::dijeda('parafrase'))->toBeFalse();
});

it('produk plagiasi terjeda sementara produk cek ai tetap boleh dibeli', function () {
    JedaLayanan::setel('plagiasi', true);

    expect(JedaLayanan::produkDijeda(produkPlagiasi()))->toBeTrue()
        ->and(JedaLayanan::produkDijeda(produkCekAi()))->toBeFalse();
});

it('produk non-jasa tidak pernah ikut terjeda', function () {
    foreach (array_keys(JedaLayanan::JENIS) as $jenis) {
        JedaLayanan::setel($jenis, true);
    }

    // Alur produk biasa sengaja tidak disentuh sama sekali oleh fitur ini.
    expect(JedaLayanan::produkDijeda(produkBiasa()))->toBeFalse();
});

it('tombol tambah ke keranjang ditolak saat layanannya dijeda', function () {
    $produk = produkPlagiasi();
    JedaLayanan::setel('plagiasi', true, 'Groupy sedang perbaikan.');

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->call('addToCart')
        ->assertDispatched('cart-error', message: 'Groupy sedang perbaikan.');

    expect(session('cart'))->toBeNull();
});

it('halaman produknya tetap terbuka, bukan 404', function () {
    $produk = produkPlagiasi();
    JedaLayanan::setel('plagiasi', true);

    // Inti permintaannya: produk MASIH BISA DILIHAT, hanya tak bisa dibeli.
    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->assertOk()
        ->assertSee('Cek Plagiasi Turnitin');
});

it('layanan yang dibuka kembali bisa dibeli lagi', function () {
    $produk = produkPlagiasi();
    JedaLayanan::setel('plagiasi', true);
    JedaLayanan::setel('plagiasi', false);

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->call('addToCart')
        ->assertNotDispatched('cart-error');
});

it('keranjang lama tidak menerobos ketika layanan dijeda setelah barang masuk', function () {
    $produk = produkPlagiasi();

    // Barang masuk keranjang SEBELUM dijeda.
    session()->put('cart', ["p_{$produk->id}" => [
        'product_id' => $produk->id,
        'product_name' => $produk->nama_akun,
        'product_image' => null,
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => 5000,
        'quantity' => 1,
        'subtotal' => 5000,
    ]]);

    JedaLayanan::setel('plagiasi', true, 'Sedang perbaikan.');

    Livewire::test(CheckoutPage::class)
        ->set('no_hp', '081200000901')
        ->set('nama', 'Pembeli')
        ->set('email', 'jeda@contoh.test')
        ->call('checkout')
        ->assertDispatched('cart-error', message: 'Sedang perbaikan.');

    expect(Order::count())->toBe(0);
});

it('checkout produk biasa tidak terhalang meski semua jasa dijeda', function () {
    $produk = produkBiasa();

    foreach (array_keys(JedaLayanan::JENIS) as $jenis) {
        JedaLayanan::setel($jenis, true);
    }

    session()->put('cart', ["p_{$produk->id}" => [
        'product_id' => $produk->id,
        'product_name' => $produk->nama_akun,
        'product_image' => null,
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => 25000,
        'quantity' => 1,
        'subtotal' => 25000,
    ]]);

    Livewire::test(CheckoutPage::class)
        ->set('no_hp', '081200000902')
        ->set('nama', 'Pembeli Biasa')
        ->set('email', 'biasa@contoh.test')
        ->call('checkout');

    expect(Order::count())->toBe(1);
});

it('pesan bawaan dipakai bila admin tidak menulis keterangan', function () {
    JedaLayanan::setel('ai', true, '   ');

    expect(JedaLayanan::pesan('ai'))->toBe(JedaLayanan::PESAN_BAWAAN);
});

it('pelanggan yang sudah bayar tetap bisa mengunggah sisa kuotanya', function () {
    $produk = produkPlagiasi();
    JedaLayanan::setel('plagiasi', true);

    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-JEDA-0001',
        'customer_id' => Customer::create([
            'nama' => 'Sudah Bayar',
            'no_hp' => '081200000903',
            'email' => 'sudah@contoh.test',
        ])->id,
        'subtotal' => 5000,
        'total' => 5000,
        'unique_code' => 0,
        'status' => 'paid',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
        'share_token' => 'TOKENJEDA1',
    ]);

    // Haknya sudah dibayar, jadi tidak dicabut saat layanan dijeda.
    expect($order->fresh()->share_token)->toBe('TOKENJEDA1')
        ->and(JedaLayanan::produkDijeda($produk))->toBeTrue();
});

/* ===================== Halaman Jeda Layanan ===================== */

function adminJeda(array $izin = ['view_jeda_layanan', 'manage_jeda_layanan']): \App\Models\User
{
    $peran = \App\Models\Role::create([
        'name' => 'uji-jeda-'.uniqid(),
        'description' => 'Peran uji jeda layanan',
    ]);

    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'jasa', 'description' => 'uji']
        );
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id])->fresh();
}

function halamanJeda(?\App\Models\User $user = null)
{
    return Livewire::actingAs($user ?: adminJeda())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class);
}

it('halaman jeda layanan tampil dengan ketiga jenisnya', function () {
    halamanJeda()
        ->assertOk()
        ->assertSee('Jeda Layanan')
        ->assertSee('Cek Plagiasi')
        ->assertSee('Cek AI')
        ->assertSee('Parafrase')
        ->assertSee('Semua layanan menerima pesanan');
});

it('kartu menyebut produk mana saja yang ikut terjeda', function () {
    produkPlagiasi();
    produkCekAi();

    // Diambil dari data, bukan daftar manual: produk jasa baru langsung muncul.
    halamanJeda()
        ->assertSee('Cek Plagiasi Turnitin')
        ->assertSee('Cek Plagiasi AI');
});

it('ringkasan di kepala halaman menyebut jenis yang sedang dijeda', function () {
    JedaLayanan::setel('plagiasi', true);

    halamanJeda()->assertSee('Dijeda: Cek Plagiasi');
});

it('admin bisa menjeda satu jenis tanpa menyentuh jenis lain', function () {
    halamanJeda()
        ->call('alihkanJeda', 'plagiasi')
        ->assertDispatched('swal-success');

    expect(JedaLayanan::dijeda('plagiasi'))->toBeTrue()
        ->and(JedaLayanan::dijeda('ai'))->toBeFalse()
        ->and(JedaLayanan::dijeda('parafrase'))->toBeFalse();
});

it('admin bisa membuka kembali layanan yang dijeda', function () {
    JedaLayanan::setel('ai', true);

    halamanJeda()->call('alihkanJeda', 'ai');

    expect(JedaLayanan::dijeda('ai'))->toBeFalse();
});

it('keterangan untuk pembeli tersimpan tanpa mengubah status jeda', function () {
    JedaLayanan::setel('plagiasi', true);

    halamanJeda()
        ->set('jeda.plagiasi.pesan', 'Groupy sedang perbaikan.')
        ->call('simpanPesan', 'plagiasi')
        ->assertDispatched('swal-success');

    expect(JedaLayanan::pesan('plagiasi'))->toBe('Groupy sedang perbaikan.')
        ->and(JedaLayanan::dijeda('plagiasi'))->toBeTrue();
});

it('hanya bisa melihat bila tak punya izin kelola', function () {
    $pengintip = adminJeda(['view_jeda_layanan']);

    // Ditandai lewat data-action, bukan teks tombol: teksnya ikut berubah saat
    // tampilan dirapikan, sehingga asersi berbasis teks diam-diam jadi tumpul.
    halamanJeda($pengintip)
        ->assertOk()
        ->assertSee('Kelola Jeda Layanan')
        ->assertDontSee('data-action="alihkanJeda"', false);
});

it('yang berizin kelola melihat sakelarnya', function () {
    halamanJeda()->assertSee('data-action="alihkanJeda"', false);
});

it('tanpa izin kelola, sakelarnya ditolak server', function () {
    $pengintip = adminJeda(['view_jeda_layanan']);

    // Tombol yang disembunyikan bukan pengaman; server tetap memeriksa.
    halamanJeda($pengintip)
        ->call('alihkanJeda', 'plagiasi')
        ->assertDispatched('swal-error');

    expect(JedaLayanan::dijeda('plagiasi'))->toBeFalse();
});

it('jenis yang tidak dikenal diabaikan, bukan menimbulkan galat', function () {
    halamanJeda()->call('alihkanJeda', 'jenis-karangan')->assertOk();

    expect(\App\Models\Setting::where('key', 'like', 'jeda_layanan_%')->count())->toBe(0);
});
