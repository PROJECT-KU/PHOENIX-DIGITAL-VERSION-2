<?php

use App\Livewire\Pages\Admin\Order\OrderForm;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\Promo;
use App\Services\PromoService;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(function () {
    HargaPaket::lupakan();
});

/** Paket contoh: harga satuan 125.000, harga paket 99.000. */
function paketBanding(): ProductBundlings
{
    $produk = Product::create(['nama_akun' => 'Produk Uji', 'harga_perbulan' => 60000]);

    return ProductBundlings::create([
        'nama_paket' => 'Paket Banding',
        'harga_awal' => 'Rp 125.000',
        'harga_bundling' => 'Rp 99.000',
        'status' => 'active',
        'product_1' => $produk->id,
        'durations' => ['product_1' => ['value' => 1, 'type' => 'bulan']],
    ]);
}

function flashSalePaket(ProductBundlings $paket, int $persen = 10): void
{
    Promo::create([
        'nama_promo' => 'Flash Banding',
        'tipe_promo' => 'flash_sale',
        'tipe_diskon' => 'persen',
        'diskon_member_persen' => $persen,
        'diskon_non_member_persen' => $persen,
        'is_active' => true,
        'mulai_promo' => now()->subDay(),
        'selesai_promo' => now()->addDay(),
        'prioritas' => 99,
    ])->bundlings()->attach($paket->id);

    HargaPaket::lupakan();
}

/** Yang benar-benar ditagih form admin. */
function totalAdmin(ProductBundlings $paket): int
{
    $t = Livewire::test(OrderForm::class)
        ->set('selectedBundleId', $paket->id)
        ->call('addBundle');

    return (int) $t->get('subtotal') - (int) $t->get('promoDiscount');
}

/** Yang benar-benar ditagih keranjang publik. */
function totalPublik(ProductBundlings $paket): int
{
    $cart = ["bundling_{$paket->id}" => [
        'product_id' => $paket->id,
        'product_name' => $paket->nama_paket,
        'type' => 'bundling',
        'harga_awal' => 125000,
        'price' => 99000,
        'quantity' => 1,
        'subtotal' => 99000,
    ]];

    return (int) app(PromoService::class)->calculateDiscount($cart, null)['final_total'];
}

it('tanpa promo: harga paket di admin sama dengan publik', function () {
    $paket = paketBanding();

    expect(totalAdmin($paket))->toBe(99000)
        ->and(totalPublik($paket))->toBe(99000)
        ->and(HargaPaket::untuk($paket)['bayar'])->toBe(99000);
});

it('kena promo: harga paket di admin sama dengan publik', function () {
    $paket = paketBanding();
    flashSalePaket($paket, 10);

    // 125.000 - 12.500 = 112.500, bukan 99.000.
    $publik = totalPublik($paket);
    HargaPaket::lupakan();
    $admin = totalAdmin($paket);

    expect($admin)->toBe($publik)
        ->and($admin)->toBe(112500);
});

it('admin memakai harga awal sebagai dasar saat paket kena promo', function () {
    $paket = paketBanding();
    flashSalePaket($paket, 10);

    $t = Livewire::test(OrderForm::class)
        ->set('selectedBundleId', $paket->id)
        ->call('addBundle');

    // Dasar hitungnya harga awal, lalu dipotong promo — bukan harga paket mentah.
    expect((int) $t->get('subtotal'))->toBe(125000)
        ->and((int) $t->get('promoDiscount'))->toBe(12500);
});

it('paket ikut dihitung promo di admin, tidak lagi dikecualikan', function () {
    $paket = paketBanding();
    flashSalePaket($paket, 10);

    $t = Livewire::test(OrderForm::class)
        ->set('selectedBundleId', $paket->id)
        ->call('addBundle');

    expect((int) $t->get('promoDiscount'))->toBeGreaterThan(0);
});

it('popup pemilih paket di admin menampilkan harga yang sama dengan publik', function () {
    $paket = paketBanding();
    flashSalePaket($paket, 10);

    $html = Livewire::test(OrderForm::class)->html();

    // Yang tampil di popup harus harga yang dibayar pembeli (112.500),
    // bukan harga paket mentah (99.000).
    expect($html)->toContain('Rp 112.500')
        ->and($html)->toContain('hemat Rp 12.500');
});

it('popup tanpa promo menampilkan harga paket apa adanya', function () {
    paketBanding();

    $html = Livewire::test(OrderForm::class)->html();

    expect($html)->toContain('Rp 99.000')
        ->and($html)->not->toContain('hemat Rp');
});

it('baris paket terpilih memperlihatkan potongan dan harga akhir', function () {
    $paket = paketBanding();
    flashSalePaket($paket, 10);

    $html = Livewire::test(OrderForm::class)
        ->set('selectedBundleId', $paket->id)
        ->call('addBundle')
        ->html();

    // Dasar 125.000, potongan 12.500, akhir 112.500 — ketiganya terlihat.
    expect($html)->toContain('Rp 125.000')
        ->and($html)->toContain('promo Rp 12.500')
        ->and($html)->toContain('Rp 112.500');
});
