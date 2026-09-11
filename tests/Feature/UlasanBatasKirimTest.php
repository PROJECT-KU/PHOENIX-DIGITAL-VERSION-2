<?php

use App\Livewire\Components\ProductReviews;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\ProductReview;
use Livewire\Livewire;

/**
 * Batas kiriman ulasan per alamat IP: 3 per jam untuk satu produk/paket, dan
 * 10 per jam untuk semua produk & paket digabung. Hanya kiriman yang benar-
 * benar tersimpan yang dihitung.
 */
function kirimUlasanUji(string $id, string $jenis = 'produk', string $ulasan = 'Mantap sekali layanannya')
{
    return Livewire::test(ProductReviews::class, ['productId' => $id, 'jenis' => $jenis])
        ->set('nama', 'Sari')
        ->set('rating', 5)
        ->set('ulasan', $ulasan)
        ->call('submit');
}

function produkBatasUlasan(): Product
{
    return Product::create(['nama_akun' => 'Produk Uji '.uniqid(), 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);
}

function paketBatasUlasan(): ProductBundlings
{
    return ProductBundlings::create([
        'nama_paket' => 'Combo Uji Batas',
        'harga_awal' => 'Rp 150.000',
        'harga_bundling' => 'Rp 100.000',
        'status' => 'active',
    ]);
}

it('satu perangkat hanya bisa mengirim 3 ulasan per jam untuk paket yang sama', function () {
    $paket = paketBatasUlasan();

    foreach (range(1, ProductReviews::BATAS_PER_TARGET) as $i) {
        kirimUlasanUji($paket->id, 'paket')->assertHasNoErrors();
    }

    kirimUlasanUji($paket->id, 'paket')
        ->assertHasErrors('ulasan')
        ->assertSee('Terlalu banyak ulasan untuk paket ini dari perangkat Anda.');

    expect(ProductReview::count())->toBe(3);
});

it('satu perangkat paling banyak 10 ulasan per jam untuk semua produk dan paket', function () {
    // Satu ulasan paket + sembilan produk berbeda = 10: tiap target baru
    // terkirim sekali, jadi yang menolak kiriman ke-11 adalah batas total.
    kirimUlasanUji(paketBatasUlasan()->id, 'paket')->assertHasNoErrors();

    foreach (range(1, 9) as $i) {
        kirimUlasanUji(produkBatasUlasan()->id)->assertHasNoErrors();
    }

    kirimUlasanUji(produkBatasUlasan()->id)
        ->assertHasErrors('ulasan')
        ->assertSee('Anda sudah mengirim 10 ulasan dalam satu jam terakhir.');

    expect(ProductReview::count())->toBe(10);
});

it('pesan batas menyebut berapa menit lagi boleh mencoba', function () {
    $produk = produkBatasUlasan();

    foreach (range(1, 3) as $i) {
        kirimUlasanUji($produk->id);
    }

    kirimUlasanUji($produk->id)->assertSeeText('Coba lagi dalam 60 menit.');
});

it('kiriman yang gagal validasi tidak menghabiskan jatah', function () {
    $produk = produkBatasUlasan();

    // Ulasan minimal 5 huruf: lima kiriman "ok" ditolak validasi.
    foreach (range(1, 5) as $i) {
        kirimUlasanUji($produk->id, 'produk', 'ok')->assertHasErrors('ulasan');
    }

    foreach (range(1, 3) as $i) {
        kirimUlasanUji($produk->id)->assertHasNoErrors();
    }

    expect(ProductReview::count())->toBe(3);
});
