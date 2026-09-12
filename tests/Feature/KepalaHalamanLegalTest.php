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
    // Keduanya kini punya tata letak sendiri dengan jarak atas 22px.
    Livewire::test(TermsPage::class)->assertSeeHtml('<section class="syk-sec">');
    Livewire::test(PrivacyPage::class)->assertSeeHtml('<section class="prv-sec">');
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

it('tiap pasal syarat punya nomor, ikon, dan warnanya sendiri', function () {
    $pasal = collect(TermsPage::pasal());

    expect($pasal)->toHaveCount(9)
        // Judul daftar isi dan judul pasal berasal dari satu daftar yang sama.
        ->and($pasal->pluck('judul')->unique())->toHaveCount(9);

    Livewire::test(TermsPage::class)
        ->assertSeeHtml('<span class="syk-ubin is-kecil"><i class="bi bi-credit-card-2-front"></i></span>')
        ->assertSeeHtml('<span class="syk-no">5</span>')
        ->assertSee('Maksimal 2 perangkat');
});

it('pasal batas perangkat dan refund ditandai penting', function () {
    $sorot = collect(TermsPage::pasal())->filter(fn ($p) => $p['sorot'])->pluck('judul')->values()->all();

    expect($sorot)->toBe(['Batas Perangkat & Blokir Otomatis', 'Kebijakan Refund']);

    Livewire::test(TermsPage::class)
        ->assertSeeHtml('id="sk-5" style="--c: #e11d48"')
        ->assertSeeHtml('<i class="bi bi-exclamation-triangle-fill"></i> Penting');
});

it('tiap pasal privasi punya nomor, ikon, dan warnanya sendiri', function () {
    $pasal = collect(PrivacyPage::pasal());

    expect($pasal)->toHaveCount(6)
        ->and($pasal->pluck('judul')->unique())->toHaveCount(6);

    Livewire::test(PrivacyPage::class)
        ->assertSeeHtml('<span class="prv-ubin is-kecil"><i class="bi bi-shield-lock-fill"></i></span>')
        ->assertSeeHtml('<span class="prv-no">4</span>')
        ->assertSee('Data tidak dijual');
});

it('pasal berbagi data dan hak pengguna ditandai penting', function () {
    $sorot = collect(PrivacyPage::pasal())->filter(fn ($p) => $p['sorot'])->pluck('judul')->values()->all();

    expect($sorot)->toBe(['Berbagi Data', 'Hak Anda']);

    Livewire::test(PrivacyPage::class)
        ->assertSeeHtml('id="pv-4" style="--c: #e11d48"')
        ->assertSee('tidak menjual');
});

it('daftar isi melompat sendiri, tidak mengandalkan tanda pagar', function () {
    // Di halaman ini navigasi tanda pagar tidak menggulir sama sekali —
    // membuka /terms#sk-5 langsung pun berhenti di puncak halaman.
    // Diuji lewat halaman utuh: skrip halaman ada di @push('scripts'),
    // yang hanya ikut dirender bersama layout.
    foreach (['terms' => '.syk-toc-list a', 'privacy' => '.prv-toc-list a'] as $rute => $pemilih) {
        $this->get(route($rute))
            ->assertOk()
            ->assertSee("document.querySelectorAll('".$pemilih."')", false)
            ->assertSee('scrollIntoView', false);
    }
});
