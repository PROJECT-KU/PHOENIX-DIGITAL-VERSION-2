<?php

use App\Livewire\Components\ProductReviews;
use App\Livewire\Pages\Admin\ProductReview\ReviewModeration;
use App\Livewire\Pages\Public\Bundling\Detail;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\ProductReview;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/**
 * Ulasan untuk paket bundling.
 *
 * Tabel ulasan yang sama dengan produk; kolom `jenis` membedakan targetnya.
 */
function paketUntukUlasan(array $lain = []): ProductBundlings
{
    return ProductBundlings::create(array_merge([
        'nama_paket' => 'Combo Uji Ulasan',
        'harga_awal' => 'Rp 150.000',
        'harga_bundling' => 'Rp 100.000',
        'status' => 'active',
    ], $lain));
}

it('ulasan dari halaman paket tersimpan sebagai ulasan paket yang menunggu persetujuan', function () {
    $paket = paketUntukUlasan();

    Livewire::test(ProductReviews::class, ['productId' => $paket->id, 'jenis' => 'paket'])
        ->set('nama', 'Sari')
        ->set('rating', 5)
        ->set('ulasan', 'Paketnya hemat banget')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSeeHtml('class="ul-terima"');

    $ulasan = ProductReview::sole();

    expect($ulasan->jenis)->toBe('paket')
        ->and($ulasan->product_id)->toBe($paket->id)
        ->and($ulasan->status)->toBe('pending');
});

it('halaman paket hanya menampilkan ulasan paketnya, halaman produk hanya ulasan produknya', function () {
    $paket = paketUntukUlasan();
    $produk = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    ProductReview::create(['product_id' => $paket->id, 'jenis' => 'paket', 'nama' => 'A', 'rating' => 5, 'ulasan' => 'Ulasan untuk paket', 'status' => 'approved']);
    ProductReview::create(['product_id' => $produk->id, 'nama' => 'B', 'rating' => 4, 'ulasan' => 'Ulasan untuk produk', 'status' => 'approved']);

    Livewire::test(ProductReviews::class, ['productId' => $paket->id, 'jenis' => 'paket'])
        ->assertSee('Ulasan untuk paket')
        ->assertDontSee('Ulasan untuk produk')
        ->assertSee('memakai paket ini');

    // Ulasan lama tanpa jenis tetap ulasan produk.
    Livewire::test(ProductReviews::class, ['productId' => $produk->id])
        ->assertSee('Ulasan untuk produk')
        ->assertDontSee('Ulasan untuk paket')
        ->assertSee('memakai produk ini');
});

it('jenis dan target ulasan tidak bisa diubah dari peramban', function () {
    $paket = paketUntukUlasan();

    expect(fn () => Livewire::test(ProductReviews::class, ['productId' => $paket->id, 'jenis' => 'paket'])->set('jenis', 'produk'))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    expect(fn () => Livewire::test(ProductReviews::class, ['productId' => $paket->id, 'jenis' => 'paket'])->set('productId', 'lain'))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

it('jenis yang tidak dikenal diperlakukan sebagai ulasan produk', function () {
    $produk = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    Livewire::test(ProductReviews::class, ['productId' => $produk->id, 'jenis' => 'karangan'])
        ->assertSet('jenis', 'produk');
});

it('ulasan untuk paket yang sudah tidak tayang ditolak', function () {
    // Kolom status paket hanya menerima 'active' atau 'non-active'.
    $paket = paketUntukUlasan(['status' => 'non-active']);

    Livewire::test(ProductReviews::class, ['productId' => $paket->id, 'jenis' => 'paket'])
        ->set('nama', 'Sari')
        ->set('rating', 5)
        ->set('ulasan', 'Paketnya hemat banget')
        ->call('submit')
        ->assertHasErrors('ulasan');

    expect(ProductReview::count())->toBe(0);
});

it('halaman detail paket memasang bagian ulasan paket', function () {
    $paket = paketUntukUlasan();

    Livewire::test(Detail::class, ['id' => $paket->id])
        ->assertSeeLivewire(ProductReviews::class);
});

it('moderasi admin menampilkan dan bisa mencari ulasan paket berdasarkan nama paketnya', function () {
    $paket = paketUntukUlasan();
    ProductReview::create(['product_id' => $paket->id, 'jenis' => 'paket', 'nama' => 'Sari', 'rating' => 5, 'ulasan' => 'Mantap', 'status' => 'pending']);

    Livewire::test(ReviewModeration::class)
        ->assertSee('Combo Uji Ulasan')
        ->assertSeeHtml('ms-1">Paket</span>')
        ->set('search', 'Combo Uji')
        ->assertSee('Mantap');
});
