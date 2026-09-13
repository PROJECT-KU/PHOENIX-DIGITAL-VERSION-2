<?php

use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(fn () => HargaPaket::lupakan());

function keranjangUji(): void
{
    session()->put('cart', [
        'a' => ['product_id' => 'a', 'product_name' => 'NotebookLM', 'product_image' => null,
            'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 50000, 'quantity' => 1, 'subtotal' => 50000],
        'b' => ['product_id' => 'b', 'product_name' => 'Canva Premium', 'product_image' => null,
            'duration_type' => 'bulan', 'duration_value' => 3, 'price' => 45000, 'quantity' => 1, 'subtotal' => 45000],
    ]);
}

/**
 * Tampilan /checkout: kartunya memakai bahasa yang sama dengan Keranjang,
 * Shop, dan Wishlist — ubin ikon berwarna di kepala tiap bagian, dan warna
 * kategori pada ringkasan.
 */
it('tiap bagian berkepala ubin ikon berwarna', function () {
    keranjangUji();

    Livewire::test(CheckoutPage::class)
        ->assertSeeHtml('<div class="ck-kartu" style="--c: #2563eb">')   // Informasi Pelanggan
        ->assertSeeHtml('<div class="ck-kartu" style="--c: #d97706">')   // Kode Promo
        ->assertSeeHtml('<span class="ck-ubin"><i class="bi bi-person-fill"></i></span>')
        ->assertSeeHtml('<span class="ck-ubin"><i class="bi bi-tag-fill"></i></span>')
        ->assertSee('Informasi Pelanggan')
        ->assertSee('Kode Promo');
});

it('ringkasan memakai warna & ikon kategori produknya', function () {
    keranjangUji();

    Livewire::test(CheckoutPage::class)
        // NotebookLM = AI Tools (ungu), Canva = Desain & Kreatif (merah muda) —
        // taksonomi yang sama dengan Keranjang dan Shop.
        ->assertSeeHtml('<div class="ck-item" style="--c: #7c3aed">')
        ->assertSeeHtml('<div class="ck-item" style="--c: #db2777">')
        ->assertSeeHtml('<i class="bi bi-robot"></i>')
        ->assertSeeHtml('<i class="bi bi-palette"></i>')
        ->assertSee('Rp 95.000');
});

it('jalur empat langkah menandai yang sedang dikerjakan', function () {
    keranjangUji();

    Livewire::test(CheckoutPage::class)
        ->assertSeeHtml('<div class="ck-henti is-lewat">')
        ->assertSeeHtml('<div class="ck-henti is-kini">')
        ->assertSee('Keranjang')
        ->assertSee('Data & Promo')
        ->assertSee('Bayar')
        ->assertSee('Terima');
});

it('kartu referral & poin ikut memakai kepala berubin', function () {
    // Keduanya hanya muncul untuk pelanggan tertentu, jadi tidak pernah
    // terlihat saat halaman dibuka biasa — dinyalakan langsung di sini supaya
    // cabangnya tetap terjaga.
    keranjangUji();

    Livewire::test(CheckoutPage::class)
        ->set('showReferralInput', true)
        ->set('showPointsOption', true)
        ->set('availablePoints', 12)
        ->set('pointsValue', 12000)
        ->assertSeeHtml('<div class="ck-kartu" style="--c: #0d9488">')   // Referral
        ->assertSeeHtml('<div class="ck-kartu" style="--c: #db2777">')   // Poin Member
        ->assertSee('Kode Referral')
        ->assertSee('Poin Member')
        ->assertSee('12 poin');
});

it('kotak nomor telepon tetap dipasang & dijembatani ke Livewire', function () {
    keranjangUji();

    Livewire::test(CheckoutPage::class)
        ->assertSeeHtml('id="co-phone"')
        ->assertSeeHtml('id="co-phone-e164"')
        ->assertSeeHtml('wire:ignore');
});

it('pemilih gaya intl-tel-input menunjuk kelas pembungkus yang dipakai', function () {
    /*
     | Diperiksa di SUMBER, bukan di HTML komponen: aturannya ada di
     | @push('styles') yang naik ke tumpukan layout, jadi tidak pernah ikut
     | dalam html() sebuah uji Livewire.
     |
     | Yang dijaga: kelas pembungkus berganti dari .co-card ke .ck-page saat
     | ditata ulang. Bila pemilihnya tertinggal, kotak nomor kembali ke rupa
     | bawaan pustakanya — tanpa galat, tanpa gejala di log, hanya terlihat
     | oleh mata yang kebetulan membuka halamannya.
     */
    $sumber = file_get_contents(resource_path('views/livewire/pages/public/shop-page/checkout-page.blade.php'));

    expect($sumber)->toContain('class="ck-page"')
        ->and($sumber)->toContain('.ck-page .iti')
        ->and($sumber)->toContain('.ck-page #co-phone')
        // Tidak boleh ada sisa PEMILIH lama yang menunjuk kelas yang sudah
        // tiada. Yang dilarang pemilihnya, bukan penyebutan namanya: komentar
        // di berkas itu sengaja menyimpan nama lamanya sebagai catatan.
        ->and($sumber)->not->toContain('.co-card .iti')
        ->and($sumber)->not->toContain('.co-card #co-phone');
});

it('kabel Livewire yang menentukan uang tidak berubah', function () {
    keranjangUji();

    Livewire::test(CheckoutPage::class)
        ->assertSeeHtml('wire:submit="checkout"')
        ->assertSeeHtml('wire:model="no_hp"')
        ->assertSeeHtml('wire:model="nama"')
        ->assertSeeHtml('wire:model="email"')
        ->assertSeeHtml('wire:model="kodePromo"')
        ->assertSeeHtml('wire:click="checkPromo"')
        ->assertSeeHtml('wire:click="checkout"')
        ->assertSeeHtml('wire:target="checkout"');
});

it('total, diskon, dan hemat tampil saat ada potongan', function () {
    keranjangUji();

    Livewire::test(CheckoutPage::class)
        ->set('promoDiscount', 10000)
        ->set('totalDiscount', 10000)
        ->set('finalTotal', 85000)
        ->assertSee('Diskon Promo')
        ->assertSee('Total Hemat')
        ->assertSee('Rp 85.000')
        ->assertSeeHtml('class="ck-hemat"');
});
