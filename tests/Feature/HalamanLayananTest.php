<?php

use App\Livewire\Pages\Public\Services\ServicesPage;
use Livewire\Livewire;

/**
 * Halaman /layanan: seragam dengan Shop & Bundling (kartu judul bersama),
 * tiap kartu berwarna & berikon sendiri, semua pemesanan lewat WhatsApp.
 */
it('halaman layanan terbuka dengan kartu judul yang sama seperti shop', function () {
    $this->get(route('services'))
        ->assertOk()
        ->assertSee('<div class="page-title ph-page-title">', false)
        ->assertSeeText('Layanan Teknologi')
        ->assertSeeText('Apa yang bisa kami kerjakan');
});

it('tiap layanan punya warna dan ubin ikonnya sendiri', function () {
    $warna = collect(ServicesPage::layanan())->pluck('warna');

    expect($warna)->toHaveCount(6)
        ->and($warna->unique())->toHaveCount(6);

    Livewire::test(ServicesPage::class)
        ->assertSeeHtml('<article class="ly-kartu" style="--c: #2563eb">')
        ->assertSeeHtml('<span class="ly-ubin is-padat is-besar"><i class="bi bi-code-slash"></i></span>')
        ->assertSeeHtml('<span class="ly-ubin is-padat is-besar"><i class="bi bi-robot"></i></span>');
});

it('tombol pesan membuka WhatsApp dengan pesan layanannya', function () {
    Livewire::test(ServicesPage::class)
        ->assertSeeHtml('href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20tertarik%20dengan%20layanan%20Pengembangan%20Website."')
        ->assertSeeHtml('href="'.ServicesPage::wa('Halo Phoenix Digital, saya tertarik dengan Paket Ecommerce (website).').'"')
        ->assertSeeHtml('href="'.ServicesPage::wa('Halo Phoenix Digital, saya ingin konsultasi layanan teknologi.').'"');
});

it('hemat paket website dihitung dari harga coretnya dan fitur panjang dilipat', function () {
    $paket = collect(ServicesPage::paketWebsite())->keyBy('nama');

    expect($paket['Paket Starter Company']['hemat'])->toBe(67)
        ->and($paket['Paket Ecommerce']['hemat'])->toBe(73)
        ->and($paket['Paket Custom']['hemat'])->toBe(47)
        ->and($paket['Paket Custom']['fiturUtama'])->toHaveCount(ServicesPage::FITUR_TAMPIL)
        ->and($paket['Paket Custom']['fiturLain'])->toHaveCount(6);

    Livewire::test(ServicesPage::class)
        ->assertSee('Hemat 67%')
        ->assertSee('Lihat 6 fitur lainnya')
        ->assertSeeHtml('class="ly-pkt is-populer" style="--c: #f26522"')
        ->assertSeeHtml('<span class="ly-pkt-lencana"><i class="bi bi-star-fill"></i> Terpopuler</span>');
});

it('tabel perbandingan tetap lengkap dan punya label untuk tampilan hp', function () {
    Livewire::test(ServicesPage::class)
        ->assertSeeHtml('role="table"')
        ->assertSee('Sepenuhnya milik Anda')
        ->assertSeeHtml('<em class="ly-bd-label">Vendor umumnya</em>');
});
