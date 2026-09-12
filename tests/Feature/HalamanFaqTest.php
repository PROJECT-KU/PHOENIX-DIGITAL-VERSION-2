<?php

use App\Livewire\Pages\Public\Legal\FaqPage;
use Livewire\Livewire;

/**
 * Halaman /faq: seragam dengan Shop, Bundling, Layanan, Tentang & Kontak.
 * Isi pertanyaan dipakai bersama oleh tampilan dan data terstruktur Google.
 */
it('halaman faq terbuka dengan kartu judul yang sama seperti shop', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('<div class="page-title ph-page-title">', false)
        ->assertSeeText('Pertanyaan Umum')
        ->assertSeeText('Berapa batas perangkat per akun?');
});

it('tiap pertanyaan punya warna, ikon, dan penanda tautannya sendiri', function () {
    $daftar = collect(FaqPage::daftar());
    $warna = $daftar->pluck('warna');

    expect($daftar)->toHaveCount(7)
        ->and($warna->unique())->toHaveCount(7);

    $html = Livewire::test(FaqPage::class);

    // Anchor #fq-1..#fq-7 dipertahankan: tautan lama dari halaman/chat tetap hidup.
    foreach (range(1, 7) as $n) {
        $html->assertSeeHtml('id="fq-'.$n.'"')->assertSeeHtml('href="#fq-'.$n.'"');
    }

    $html->assertSeeHtml('<span class="fq-ubin"><i class="bi bi-shield-check"></i></span>')
        ->assertSeeHtml('style="--c: #2563eb"');
});

it('pertanyaan pertama terbuka, sisanya tertutup', function () {
    Livewire::test(FaqPage::class)
        ->assertSeeHtml('id="fq-1" style="--c: #2563eb" open')
        ->assertSeeHtml('id="fq-2" style="--c: #16a34a" >');
});

it('data terstruktur google memakai jawaban yang sama dengan yang tampil', function () {
    $ld = FaqPage::dataTerstruktur();
    $daftar = FaqPage::daftar();

    expect($ld['@type'])->toBe('FAQPage')
        ->and($ld['mainEntity'])->toHaveCount(count($daftar));

    foreach ($ld['mainEntity'] as $i => $q) {
        expect($q['name'])->toBe($daftar[$i]['tanya'])
            // Teks jawaban tanpa tag HTML, tetapi isinya sama.
            ->and($q['acceptedAnswer']['text'])->not->toContain('<')
            ->and($q['acceptedAnswer']['text'])->toContain(strip_tags(explode('<', $daftar[$i]['jawab'])[0]));
    }

    // "&amp;" ikut diurai, jadi Google tidak membaca entitas mentah.
    expect(json_encode($ld))->not->toContain('&amp;');
});

it('jawaban menautkan halaman terkait', function () {
    Livewire::test(FaqPage::class)
        ->assertSeeHtml('href="'.route('shop.index').'"')
        ->assertSeeHtml('href="'.route('terms').'"')
        ->assertSeeHtml('href="'.route('privacy').'"');
});
