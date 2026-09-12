<?php

use App\Livewire\Pages\Public\About\AboutPage;
use Livewire\Livewire;

/**
 * Halaman /about: seragam dengan Shop, Bundling & Layanan (kartu judul
 * bersama), tiap kartu berwarna & berikon sendiri, ajakan lewat WhatsApp.
 */
it('halaman tentang terbuka dengan kartu judul yang sama seperti shop', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('<div class="page-title ph-page-title">', false)
        ->assertSeeText('Tentang Phoenix Digital')
        ->assertSeeText('Alasan pelanggan mempercayai kami');
});

it('tiap kartu nilai punya warna dan ubin ikonnya sendiri', function () {
    $warna = collect(AboutPage::nilai())->pluck('warna');

    expect($warna)->toHaveCount(4)
        ->and($warna->unique())->toHaveCount(4);

    Livewire::test(AboutPage::class)
        ->assertSeeHtml('<article class="tk-kartu" style="--c: #2563eb">')
        ->assertSeeHtml('<span class="tk-ubin is-padat"><i class="bi bi-tags-fill"></i></span>')
        ->assertSee('Terpercaya & Amanah');
});

it('angka tetap terbaca walau javascript penghitungnya tidak jalan', function () {
    // purecounter menganimasikan dari 0; angka akhirnya tetap tercetak di HTML.
    Livewire::test(AboutPage::class)
        ->assertSeeHtml('data-purecounter-end="1500"')
        ->assertSeeHtml('class="purecounter">1500</span>')
        ->assertSee('Transaksi Selesai');
});

it('tombol mengarah ke whatsapp dengan pesan yang sudah terisi', function () {
    Livewire::test(AboutPage::class)
        ->assertSeeHtml('href="'.AboutPage::wa('Halo Phoenix Digital, saya ingin bertanya.').'"')
        ->assertSeeHtml('href="'.AboutPage::wa('Halo Phoenix Digital, saya ingin booking untuk kampus/instansi.').'"')
        ->assertSeeHtml('href="'.route('shop.index').'"');
});

it('ilustrasi beserta animasinya tetap dipakai', function () {
    // .abt-visual/.abt-illus digayakan CSS beku server — sengaja dipertahankan.
    Livewire::test(AboutPage::class)
        ->assertSeeHtml('<div class="abt-visual">')
        ->assertSeeHtml('class="abt-illus"')
        ->assertSeeHtml('class="abt-badge abt-b1"');
});
