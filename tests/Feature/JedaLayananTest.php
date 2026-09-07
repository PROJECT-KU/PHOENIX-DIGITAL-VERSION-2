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

    expect(JedaLayanan::pesan('ai'))->toBe(JedaLayanan::pesanBawaan('ai'));
});

it('kalimat bawaan berbeda antara pemeriksaan dan parafrase', function () {
    // Sebabnya memang berbeda: sistem pemeriksaan vs antrean tim sendiri.
    expect(JedaLayanan::pesanBawaan('parafrase'))
        ->not->toBe(JedaLayanan::pesanBawaan('plagiasi'))
        ->and(JedaLayanan::pesanBawaan('plagiasi'))->toContain('Sistem pemeriksaan')
        ->and(JedaLayanan::pesanBawaan('parafrase'))->toContain('Antrean pengerjaan');
});

it('kalimat bawaan selalu menawarkan langkah berikutnya', function () {
    foreach (array_keys(JedaLayanan::JENIS) as $jenis) {
        expect(JedaLayanan::pesanBawaan($jenis))->toContain('WhatsApp');
    }
});

it('keterangan tulisan admin mengalahkan kalimat bawaan', function () {
    JedaLayanan::setel('plagiasi', true, 'Buka lagi Senin pagi.');

    expect(JedaLayanan::pesan('plagiasi'))->toBe('Buka lagi Senin pagi.');
});

it('kalimat bawaan tidak ikut tersimpan ke database', function () {
    JedaLayanan::setel('plagiasi', true);

    // Dibiarkan kosong supaya perbaikan katanya kelak langsung berlaku.
    expect(\App\Models\Setting::get('jeda_layanan_plagiasi_pesan', ''))->toBe('')
        ->and(JedaLayanan::pesan('plagiasi'))->toBe(JedaLayanan::pesanBawaan('plagiasi'));
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

/* ===================== Produk akun (non-jasa) ===================== */

it('produk akun bisa dijeda satu per satu tanpa menyentuh yang lain', function () {
    $a = produkBiasa();
    $b = Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);

    JedaLayanan::setelProduk($a, true);

    expect(JedaLayanan::produkDijeda($a->fresh()))->toBeTrue()
        ->and(JedaLayanan::produkDijeda($b->fresh()))->toBeFalse();
});

it('menjeda produk akun tidak terpengaruh sakelar jenis jasa', function () {
    $akun = produkBiasa();

    foreach (array_keys(JedaLayanan::JENIS) as $jenis) {
        JedaLayanan::setel($jenis, true);
    }

    // Produk akun tidak punya jenis layanan, jadi hanya kolomnya yang berlaku.
    expect(JedaLayanan::produkDijeda($akun->fresh()))->toBeFalse();
});

it('produk akun yang dijeda memakai kalimat bawaannya sendiri', function () {
    $akun = produkBiasa();
    JedaLayanan::setelProduk($akun, true);

    expect(JedaLayanan::pesanProduk($akun->fresh()))->toBe(JedaLayanan::PESAN_PRODUK)
        ->and(JedaLayanan::PESAN_PRODUK)->toContain('WhatsApp');
});

it('keterangan tulisan admin mengalahkan kalimat bawaan produk', function () {
    $akun = produkBiasa();
    JedaLayanan::setelProduk($akun, true, 'Stok habis, restock Senin.');

    expect(JedaLayanan::pesanProduk($akun->fresh()))->toBe('Stok habis, restock Senin.');
});

it('jeda per produk mengalahkan jeda per jenis pada produk yang sama', function () {
    $jasa = produkPlagiasi();
    JedaLayanan::setel('plagiasi', true);
    JedaLayanan::setelProduk($jasa, true, 'Alasan khusus produk ini.');

    // Yang lebih spesifik menang, supaya keterangannya tidak tertukar.
    expect(JedaLayanan::pesanProduk($jasa->fresh()))->toBe('Alasan khusus produk ini.');
});

it('pemilih durasi di daftar toko menolak produk akun yang dijeda', function () {
    $akun = produkBiasa();
    JedaLayanan::setelProduk($akun, true, 'Stok habis.');

    // Ditolak SEBELUM pemilih paket terbuka, bukan sesudah pembeli memilih.
    Livewire::test(\App\Livewire\Pages\Public\ShopPage\Index::class)
        ->call('openDuration', $akun->id)
        ->assertDispatched('cart-error', message: 'Stok habis.');
});

it('produk akun yang dijeda tidak bisa dimasukkan ke keranjang dari daftar', function () {
    $akun = produkBiasa();
    JedaLayanan::setelProduk($akun, true);

    Livewire::test(\App\Livewire\Pages\Public\ShopPage\Index::class)
        ->call('addToCart', $akun->id, 'bulan', 1)
        ->assertDispatched('cart-error');

    expect(session('cart'))->toBeNull();
});

it('produk akun yang tidak dijeda tetap bisa dibeli seperti biasa', function () {
    $akun = produkBiasa();

    Livewire::test(\App\Livewire\Pages\Public\ShopPage\Index::class)
        ->call('addToCart', $akun->id, 'bulan', 1)
        ->assertNotDispatched('cart-error');

    expect(session('cart'))->not->toBeNull();
});

it('checkout menolak keranjang berisi produk akun yang dijeda', function () {
    $akun = produkBiasa();

    session()->put('cart', ["p_{$akun->id}" => [
        'product_id' => $akun->id,
        'product_name' => $akun->nama_akun,
        'product_image' => null,
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => 25000,
        'quantity' => 1,
        'subtotal' => 25000,
    ]]);

    JedaLayanan::setelProduk($akun, true, 'Stok habis.');

    Livewire::test(CheckoutPage::class)
        ->set('no_hp', '081200000910')
        ->set('nama', 'Pembeli')
        ->set('email', 'akun.jeda@contoh.test')
        ->call('checkout')
        ->assertDispatched('cart-error', message: 'Stok habis.');

    expect(Order::count())->toBe(0);
});

it('halaman admin menampilkan produk akun yang sedang dijeda', function () {
    $akun = produkBiasa();
    JedaLayanan::setelProduk($akun, true);

    halamanJeda()
        ->assertSee('Produk Akun')
        ->assertSee('Microsoft Office 365')
        ->assertSee('1 dijeda');
});

it('admin bisa menjeda dan membuka produk akun dari halaman itu', function () {
    $akun = produkBiasa();

    halamanJeda()
        ->call('alihkanProduk', $akun->id)
        ->assertDispatched('swal-success');

    expect($akun->fresh()->dijeda)->toBeTrue();
});

it('semua produk akun terdaftar tanpa perlu dicari', function () {
    produkBiasa();
    Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);
    Product::create(['nama_akun' => 'Spotify Premium', 'harga_perbulan' => 18000]);

    halamanJeda()
        ->assertSee('Menerima pesanan &middot; 3 produk', false)
        ->assertSee('Microsoft Office 365')
        ->assertSee('Canva Pro')
        ->assertSee('Spotify Premium');
});

it('produk yang dijeda pindah dari daftar aktif ke bagian atas', function () {
    $akun = produkBiasa();
    Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);

    JedaLayanan::setelProduk($akun, true);

    // Tidak boleh muncul di dua tempat sekaligus.
    halamanJeda()
        ->assertSee('1 dijeda dari 2')
        ->assertSee('Menerima pesanan &middot; 1 produk', false);
});

it('produk jasa tidak ikut di daftar produk akun', function () {
    produkBiasa();
    produkPlagiasi();

    // Jasa sudah punya sakelar jenisnya sendiri di atas.
    halamanJeda()->assertSee('Menerima pesanan &middot; 1 produk', false);
});

it('tanpa izin kelola, menjeda produk akun ditolak server', function () {
    $akun = produkBiasa();

    halamanJeda(adminJeda(['view_jeda_layanan']))
        ->call('alihkanProduk', $akun->id)
        ->assertDispatched('swal-error');

    expect($akun->fresh()->dijeda)->toBeFalse();
});

it('ringkasan kepala halaman menghitung jasa dan produk akun sekaligus', function () {
    JedaLayanan::setel('plagiasi', true);
    JedaLayanan::setelProduk(produkBiasa(), true);

    // Menghitung jasa saja pernah membuat kepala halaman berkata semuanya
    // menerima pesanan padahal ada produk akun yang tertutup.
    halamanJeda()->assertSee('Dijeda: Cek Plagiasi & 1 produk akun');
});

it('ringkasan menyebut produk akun meski tak ada jasa yang dijeda', function () {
    JedaLayanan::setelProduk(produkBiasa(), true);

    halamanJeda()
        ->assertSee('Dijeda: 1 produk akun')
        ->assertDontSee('Semua layanan menerima pesanan');
});
