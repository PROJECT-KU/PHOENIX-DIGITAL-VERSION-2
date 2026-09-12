<?php

use App\Livewire\Pages\Public\Legal\PrivacyPage;
use App\Livewire\Pages\Public\Legal\TermsPage;
use Livewire\Livewire;

/**
 * Syarat & Ketentuan dan Kebijakan Privasi memakai KARTU JUDUL BERSAMA
 * (.ph-page-title) seperti Shop, FAQ, dan Member — judulnya berdiri sendiri,
 * terpisah dari kartu isi di bawahnya. Versi lamanya (.legal-hero) menempel
 * hanya 9px sehingga keduanya terbaca sebagai satu blok.
 */
it('halaman syarat memakai kartu judul bersama dengan remah roti', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('<div class="page-title ph-page-title">', false)
        ->assertSee('<li class="current">Syarat &amp; Ketentuan</li>', false)
        ->assertDontSee('<div class="legal-hero">', false)
        ->assertSeeText('Syarat & Ketentuan');
});

it('halaman privasi memakai kartu judul bersama dengan remah roti', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('<div class="page-title ph-page-title">', false)
        ->assertSee('<li class="current">Kebijakan Privasi</li>', false)
        ->assertDontSee('<div class="legal-hero">', false)
        ->assertSeeText('Kebijakan Privasi');
});

it('kartu isi diberi jarak dari kartu judul, tidak menempel', function () {
    Livewire::test(TermsPage::class)->assertSeeHtml('class="container lg-jarak-judul"');
    Livewire::test(PrivacyPage::class)->assertSeeHtml('class="container lg-jarak-judul"');
});

it('anchor pasal lama tetap hidup', function () {
    // Tautan sk-1..sk-9 dan pv-1..pv-6 dipakai dari daftar isi & tautan luar.
    $syarat = Livewire::test(TermsPage::class);
    foreach (range(1, 9) as $n) {
        $syarat->assertSeeHtml('id="sk-'.$n.'"')->assertSeeHtml('href="#sk-'.$n.'"');
    }

    $privasi = Livewire::test(PrivacyPage::class);
    foreach (range(1, 6) as $n) {
        $privasi->assertSeeHtml('id="pv-'.$n.'"')->assertSeeHtml('href="#pv-'.$n.'"');
    }
});
