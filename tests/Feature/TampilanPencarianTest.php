<?php

use App\Livewire\Components\GlobalSearch;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(fn () => HargaPaket::lupakan());

/**
 * Tampilan pencarian global di header: hasilnya memakai bahasa visual yang
 * sama dengan kartu di Beranda/Shop — ubin berwarna menurut KATEGORI produk.
 */
it('hasil produk memakai warna dan ikon kategorinya', function () {
    Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    Livewire::test(GlobalSearch::class)
        ->set('searchQuery', 'canva')
        // Desain & Kreatif: warna dan ikon dari App\Support\KategoriBeranda.
        ->assertSeeHtml('class="cr-item" style="--c: #db2777"')
        ->assertSeeHtml('<i class="bi bi-palette"></i> Desain &amp; Kreatif')
        ->assertSee('Canva Premium')
        ->assertSee('Rp 15.000');
});

it('produk tanpa kategori tetap tampil dengan warna merek', function () {
    Product::create(['nama_akun' => 'Produk Tanpa Merek Dikenal', 'tipe_akun' => 'sharing', 'harga_perbulan' => 20000]);

    Livewire::test(GlobalSearch::class)
        ->set('searchQuery', 'Tanpa Merek')
        ->assertSeeHtml('style="--c: #f26522"')
        ->assertSeeHtml('<i class="bi bi-box-seam"></i>');
});

it('paket bundling tampil dengan labelnya sendiri', function () {
    ProductBundlings::create([
        'nama_paket' => 'Combo Riset Uji', 'harga_awal' => 'Rp 150.000',
        'harga_bundling' => 'Rp 100.000', 'status' => 'active',
    ]);

    Livewire::test(GlobalSearch::class)
        ->set('searchQuery', 'Combo Riset')
        ->assertSee('Paket Bundling')
        ->assertSeeHtml('<i class="bi bi-box-seam"></i> Paket Bundling')
        ->assertSee('Rp 100.000');
});

it('tanpa hasil, pencarian menawarkan jalan keluar', function () {
    Livewire::test(GlobalSearch::class)
        ->set('searchQuery', 'zzqqxx')
        ->assertSee('Belum ada yang cocok')
        ->assertSeeHtml('href="'.route('shop.index').'"')
        ->assertSeeHtml('href="'.route('bundling.product-bundlings').'"');
});

it('kotak pencarian kosong tidak membuka panel hasil', function () {
    Livewire::test(GlobalSearch::class)
        ->assertDontSeeHtml('class="cr-panel"')
        ->assertSeeHtml('class="cr-input"');
});
