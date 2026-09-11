<?php

use App\Livewire\Pages\Public\ShopPage\ProductDetail;
use App\Models\Product;
use App\Support\JedaLayanan;
use Livewire\Livewire;

/**
 * Tampilan detail produk saat pemesanannya dijeda: kotak pemberitahuan
 * berikon, jalan keluar (WhatsApp & layanan lain), tombol beli nonaktif.
 */
function produkJedaTampil(bool $dijeda = true): Product
{
    $produk = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);
    JedaLayanan::setelProduk($produk, $dijeda, 'Sedang ada perawatan sistem.');

    return $produk->fresh();
}

it('produk dijeda menampilkan kotak berikon dengan pesan admin dan jalan keluarnya', function () {
    $produk = produkJedaTampil();

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->assertSeeHtml('<div class="pd-jeda" role="status">')
        ->assertSeeHtml('<span class="pd-jeda-ic"><i class="bi bi-pause-fill"></i></span>')
        ->assertSee('Sedang tidak menerima pesanan baru')
        ->assertSee('Sedang ada perawatan sistem.')
        // Pesan WhatsApp menyebut produknya — hanya nama produk, tanpa data pembeli.
        ->assertSeeHtml('https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya%20tentang%20Canva%20Premium')
        ->assertSeeHtml('href="'.route('shop.index').'"')
        ->assertSeeHtml('class="pd-add is-jeda"')
        ->assertSee('Pesanan Ditutup Sementara');
});

it('produk yang buka tidak menampilkan kotak jeda', function () {
    $produk = produkJedaTampil(false);

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->assertDontSeeHtml('class="pd-jeda"')
        // Bukan sekadar 'is-jeda': kata itu juga ada di CSS inline halaman.
        ->assertDontSeeHtml('class="pd-add is-jeda"')
        ->assertSee('Tambah ke Keranjang');
});
