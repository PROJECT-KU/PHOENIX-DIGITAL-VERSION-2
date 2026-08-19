<?php

use App\Livewire\Pages\Public\Bundling\Detail;
use App\Models\ProductBundlings;
use App\Models\Promo;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(function () {
    HargaPaket::lupakan();
});

/** Paket contoh: harga satuan 160.000, harga paket 125.000. */
function paketUji(): ProductBundlings
{
    return ProductBundlings::create([
        'nama_paket' => 'Paket Uji Detail',
        'harga_awal' => 160000,
        'harga_bundling' => 125000,
        'status' => 'active',
    ]);
}

function flashSale(int $persen = 10): Promo
{
    return Promo::create([
        'nama_promo' => 'Flash Uji',
        'tipe_promo' => 'flash_sale',
        'tipe_diskon' => 'persen',
        'diskon_member_persen' => $persen,
        'diskon_non_member_persen' => $persen,
        'is_active' => true,
        'mulai_promo' => now()->subDay(),
        'selesai_promo' => now()->addDay(),
        'prioritas' => 99,
    ]);
}

/** Blok harga saja; angka di kartu "Paket Lainnya" bukan harga paket ini. */
function blokHarga(string $html): string
{
    preg_match('#<div class="pd-price">.*?</div>#s', $html, $m);

    return $m[0] ?? '';
}

it('tanpa promo: mencoret harga awal dan menagih harga paket', function () {
    $paket = paketUji();

    $hp = HargaPaket::untuk($paket);

    expect($hp['bayar'])->toBe(125000)
        ->and($hp['coret'])->toBe(160000)
        ->and($hp['potongan'])->toBe(0);
});

it('kena flash sale: potongan dihitung dari harga awal, yang dicoret harga awal', function () {
    $paket = paketUji();
    flashSale(10)->bundlings()->attach($paket->id);
    HargaPaket::lupakan();

    $hp = HargaPaket::untuk($paket->fresh());

    // Dasar hitungnya harga awal: 160.000 x 10% = 16.000,
    // dibayar 160.000 - 16.000 = 144.000. Harga paket diabaikan.
    expect($hp['bayar'])->toBe(144000)
        ->and($hp['coret'])->toBe(160000)
        ->and($hp['potongan'])->toBe(16000);
});

it('halaman detail menampilkan harga coret dari harga awal', function () {
    $paket = paketUji();
    flashSale(10)->bundlings()->attach($paket->id);
    HargaPaket::lupakan();

    $blok = blokHarga(Livewire::test(Detail::class, ['id' => $paket->id])->html());

    expect($blok)->toContain('Rp 144.000')
        ->and($blok)->toContain('Rp 160.000')
        ->and($blok)->not->toContain('125.000');
});

it('halaman detail memakai kerangka yang sama dengan detail produk shop', function () {
    $html = Livewire::test(Detail::class, ['id' => paketUji()->id])->html();

    expect($html)->toContain('pd-section')
        ->and($html)->toContain('pd-price-old')
        ->and($html)->toContain('pd-add');
});

it('paket bisa dimasukkan keranjang dari halaman detail', function () {
    $paket = paketUji();

    Livewire::test(Detail::class, ['id' => $paket->id])->call('addToCart');

    expect(session('cart'))->toHaveKey("bundling_{$paket->id}");
});

it('halaman ikut hilang ketika jadwal paket sudah berakhir', function () {
    $paket = paketUji();
    $paket->update(['selesai_tayang' => now()->subHour()]);

    Livewire::test(Detail::class, ['id' => $paket->id])->assertStatus(404);
});

it('menolak paket yang tidak dikenal', function () {
    Livewire::test(Detail::class, ['id' => (string) Str::uuid()])->assertStatus(404);
});

it('tombol Lihat di daftar paket menuju halaman detail, bukan halaman itu sendiri', function () {
    $paket = paketUji();

    foreach ([
        App\Livewire\Pages\Public\Bundling\Index::class,
        App\Livewire\Pages\Public\Bundling\ProductBundlings::class,
    ] as $kelas) {
        $html = Livewire::test($kelas)->html();

        expect($html)->toContain('/bundling/paket/'.$paket->id);
    }
});

it('angka yang ditampilkan sama dengan yang ditagih keranjang', function () {
    // Contoh nyata: harga awal 125.000, harga paket 99.000, flash sale 10%.
    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Uji Tagihan',
        'harga_awal' => 125000,
        'harga_bundling' => 99000,
        'status' => 'active',
    ]);
    flashSale(10)->bundlings()->attach($paket->id);
    HargaPaket::lupakan();

    $hp = HargaPaket::untuk($paket->fresh());

    // 125.000 x 10% = 12.500 potongan, dibayar 125.000 - 12.500 = 112.500.
    expect($hp['potongan'])->toBe(12500)
        ->and($hp['coret'])->toBe(125000)
        ->and($hp['bayar'])->toBe(112500);

    $cart = ["bundling_{$paket->id}" => [
        'product_id' => $paket->id,
        'product_name' => $paket->nama_paket,
        'type' => 'bundling',
        'harga_awal' => 125000,
        // Sengaja diisi harga paket seperti sesi lama: PromoService harus
        // menyetelnya ulang sendiri mengikuti promo yang berlaku.
        'price' => 99000,
        'quantity' => 1,
        'subtotal' => 99000,
    ]];

    $hasil = app(App\Services\PromoService::class)->calculateDiscount($cart, null);

    // Yang ditagih harus sama dengan yang tertulis di kartu dan halaman detail.
    expect((int) $hasil['final_total'])->toBe($hp['bayar']);
});

it('paket tanpa promo tetap ditagih harga paket, bukan harga awal', function () {
    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Tanpa Promo',
        'harga_awal' => 125000,
        'harga_bundling' => 99000,
        'status' => 'active',
    ]);

    $hp = HargaPaket::untuk($paket);

    expect($hp['bayar'])->toBe(99000)
        ->and($hp['coret'])->toBe(125000)
        ->and($hp['potongan'])->toBe(0);

    $cart = ["bundling_{$paket->id}" => [
        'product_id' => $paket->id,
        'product_name' => $paket->nama_paket,
        'type' => 'bundling',
        'harga_awal' => 125000,
        'price' => 99000,
        'quantity' => 1,
        'subtotal' => 99000,
    ]];

    $hasil = app(App\Services\PromoService::class)->calculateDiscount($cart, null);

    expect((int) $hasil['final_total'])->toBe(99000);
});

it('promo yang berakhir setelah paket masuk keranjang tidak menagih harga awal', function () {
    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Promo Habis',
        'harga_awal' => 125000,
        'harga_bundling' => 99000,
        'status' => 'active',
    ]);
    $promo = flashSale(10);
    $promo->bundlings()->attach($paket->id);
    HargaPaket::lupakan();

    // Masuk keranjang saat promo masih jalan: dasarnya harga awal.
    $cart = ["bundling_{$paket->id}" => [
        'product_id' => $paket->id,
        'product_name' => $paket->nama_paket,
        'type' => 'bundling',
        'harga_awal' => 125000,
        'price' => 125000,
        'quantity' => 1,
        'subtotal' => 125000,
    ]];

    // Promo berakhir sebelum pelanggan membayar.
    $promo->update(['is_active' => false]);
    HargaPaket::lupakan();

    $hasil = app(App\Services\PromoService::class)->calculateDiscount($cart, null);

    // Kembali ke harga paket, bukan tertinggal di harga awal 125.000.
    expect((int) $hasil['final_total'])->toBe(99000);
});
