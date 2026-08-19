<?php

use App\Livewire\Components\GlobalSearch;
use App\Livewire\Pages\Public\ShopPage\Index as HalamanShop;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\ProductPrice;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(fn () => HargaPaket::lupakan());

/** Jasa yang ditagih per pengecekan (durasi_type 'kali'). */
function jasaPerCek(): Product
{
    $p = Product::create([
        'nama_akun' => 'Cek Plagiasi Uji',
        'harga_perbulan' => 0,
        'butuh_file' => true,
        'jasa_mode' => 'kali',
    ]);

    ProductPrice::create([
        'product_id' => $p->id, 'durasi_type' => 'kali', 'durasi_value' => 1, 'harga' => 5000,
    ]);

    return $p->fresh();
}

/** Jasa yang ditagih per halaman. */
function jasaPerHalaman(): Product
{
    $p = Product::create([
        'nama_akun' => 'Parafrase Uji',
        'harga_perbulan' => 0,
        'butuh_file' => true,
        'jasa_mode' => 'halaman',
    ]);

    ProductPrice::create([
        'product_id' => $p->id, 'durasi_type' => 'halaman', 'durasi_value' => 1, 'harga' => 15000,
    ]);

    return $p->fresh();
}

it('pencarian tidak menampilkan Rp 0 untuk jasa per pengecekan', function () {
    jasaPerCek();

    $html = Livewire::test(GlobalSearch::class)->set('searchQuery', 'Cek Plagiasi Uji')->html();

    expect($html)->toContain('Rp 5.000')
        ->and($html)->toContain('/cek')
        ->and($html)->not->toMatch('#Rp 0\s*<small>#');
});

it('pencarian menampilkan harga per halaman untuk jasa per halaman', function () {
    jasaPerHalaman();

    $html = Livewire::test(GlobalSearch::class)->set('searchQuery', 'Parafrase Uji')->html();

    // Ini yang dulu tampil "Rp 0": harganya bukan di paket 'kali', tapi per halaman.
    expect($html)->toContain('Rp 15.000')
        ->and($html)->toContain('/halaman');
});

it('kartu shop juga memakai harga per halaman, bukan nol', function () {
    jasaPerHalaman();

    $html = Livewire::test(HalamanShop::class)->set('search', 'Parafrase Uji')->html();

    expect($html)->toContain('15.000')
        ->and($html)->toContain('/halaman');
});

it('produk biasa tetap memakai harga per bulan', function () {
    Product::create(['nama_akun' => 'Akun Biasa Uji', 'harga_perbulan' => 25000]);

    $html = Livewire::test(GlobalSearch::class)->set('searchQuery', 'Akun Biasa Uji')->html();

    expect($html)->toContain('Rp 25.000')
        ->and($html)->toContain('/bln');
});

it('paket berjadwal yang sudah berakhir tidak muncul di pencarian', function () {
    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Kedaluwarsa Uji',
        'harga_awal' => 'Rp 125.000',
        'harga_bundling' => 'Rp 99.000',
        'status' => 'active',
        'selesai_tayang' => now()->subHour(),
    ]);

    $html = Livewire::test(GlobalSearch::class)->set('searchQuery', 'Paket Kedaluwarsa Uji')->html();

    // Disasar ke tautannya: nama paket juga dipantulkan di pesan
    // "tidak ada hasil", jadi keberadaan namanya bukan bukti ia terdaftar.
    expect($html)->not->toContain('/bundling/paket/'.$paket->id);
});

it('harga paket di pencarian sama dengan sumber harga bersama', function () {
    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Cari Uji',
        'harga_awal' => 'Rp 125.000',
        'harga_bundling' => 'Rp 99.000',
        'status' => 'active',
    ]);

    $html = Livewire::test(GlobalSearch::class)->set('searchQuery', 'Paket Cari Uji')->html();

    expect($html)->toContain(number_format(HargaPaket::untuk($paket)['bayar'], 0, ',', '.'));
});
