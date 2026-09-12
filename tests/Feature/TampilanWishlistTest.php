<?php

use App\Livewire\Pages\Public\ShopPage\WishlistPage;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(fn () => HargaPaket::lupakan());

/**
 * Tampilan /wishlist: kartu memakai bahasa yang sama dengan Shop — warna
 * mengikuti kategori produk, dan ubin ikon dipakai bila gambarnya tak ada.
 */
it('produk tersimpan tampil berwarna kategori dengan ubin cadangan', function () {
    $p = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    Livewire::test(WishlistPage::class)
        ->call('load', [$p->id])
        ->assertSeeHtml('<article class="wl-kartu" style="--c: #db2777">')
        ->assertSeeHtml('<span class="wl-cadangan"><i class="bi bi-palette"></i></span>')
        ->assertSee('Desain & Kreatif')
        ->assertSee('Canva Premium')
        ->assertSee('Rp 15.000')
        // Berkas gambarnya tidak ada: <img> TIDAK dipasang sama sekali,
        // supaya teks alt tidak tampil sebagai gambar rusak.
        ->assertDontSeeHtml('storage/img/Product/');
});

it('paket bundling tersimpan memakai kartu yang sama', function () {
    $pk = ProductBundlings::create([
        'nama_paket' => 'Combo Riset Hemat', 'harga_awal' => 'Rp 100.000',
        'harga_bundling' => 'Rp 70.000', 'status' => 'active',
    ]);

    Livewire::test(WishlistPage::class)
        ->call('load', [$pk->id])
        ->assertSee('Combo Riset Hemat')
        ->assertSee('Paket Bundling')
        ->assertSee('Rp 70.000')
        ->assertSeeHtml('<span class="wl-cadangan"><i class="bi bi-box2-heart-fill"></i></span>');
});

it('ringkasan menghitung produk dan paket sekaligus', function () {
    $p = Product::create(['nama_akun' => 'Kahoot Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 20000]);
    $pk = ProductBundlings::create([
        'nama_paket' => 'Combo Uji', 'harga_awal' => 'Rp 90.000',
        'harga_bundling' => 'Rp 60.000', 'status' => 'active',
    ]);

    Livewire::test(WishlistPage::class)
        ->call('load', [$p->id, $pk->id])
        ->assertSee('2 item')
        ->assertSee('tersimpan di perangkat ini');
});

it('wishlist kosong menawarkan jalan keluar', function () {
    Livewire::test(WishlistPage::class)
        ->assertSee('Wishlist masih kosong')
        ->assertSeeHtml('href="'.route('shop.index').'"');
});

it('tombol hapus tetap memakai penyimpanan di perangkat', function () {
    // Wishlist disimpan di localStorage; tombolnya harus tetap menulis ke sana
    // dan mengabari header lewat event yang sama.
    $p = Product::create(['nama_akun' => 'Grammarly Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 25000]);

    Livewire::test(WishlistPage::class)
        ->call('load', [$p->id])
        ->assertSeeHtml('ph_wishlist')
        ->assertSeeHtml('ph-wishlist-changed')
        ->assertSeeHtml('class="wl-hapus"');
});
