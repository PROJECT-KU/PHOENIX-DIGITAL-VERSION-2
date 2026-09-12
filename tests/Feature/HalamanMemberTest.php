<?php

use App\Livewire\Pages\Public\Legal\MemberPage;
use Livewire\Livewire;

/**
 * Halaman /member: seragam dengan Shop, Bundling, Layanan, Tentang, Kontak
 * & FAQ. Angka poin tetap diturunkan dari rumusnya, bukan diketik di view.
 */
it('halaman member terbuka dengan kartu judul yang sama seperti shop', function () {
    $this->get(route('member.info'))
        ->assertOk()
        ->assertSee('<div class="page-title ph-page-title">', false)
        ->assertSeeText('Jadi Member Phoenix')
        ->assertSeeText('Caranya cuma 2 langkah');
});

it('anchor lama mb-1 sampai mb-4 dan daftar isinya dipertahankan', function () {
    $html = Livewire::test(MemberPage::class);

    foreach (range(1, 4) as $n) {
        $html->assertSeeHtml('id="mb-'.$n.'"')->assertSeeHtml('href="#mb-'.$n.'"');
    }
});

it('contoh hitungan poin diturunkan dari rumusnya, bukan diketik tangan', function () {
    // 170.000 : 50.000 = 3 poin; 3 x 500 = 1.500; sisa 20.000 tidak hangus.
    Livewire::test(MemberPage::class)
        ->assertSee('Rp 170.000')
        ->assertSee('3 poin')
        ->assertSee('Rp 1.500')
        ->assertSee('Rp 20.000')
        ->assertSee('tidak hangus');

    expect(intdiv(MemberPage::CONTOH_BELANJA, MemberPage::PER_POIN))->toBe(3)
        ->and(MemberPage::CONTOH_BELANJA % MemberPage::PER_POIN)->toBe(20000);
});

it('tiap keuntungan, langkah, dan syarat punya warna serta ikonnya sendiri', function () {
    expect(MemberPage::langkah())->toHaveCount(2)
        ->and(MemberPage::keuntungan())->toHaveCount(3)
        ->and(MemberPage::syarat())->toHaveCount(6);

    Livewire::test(MemberPage::class)
        ->assertSeeHtml('<span class="mbr-ubin is-padat"><i class="bi bi-coin"></i></span>')
        // Langkah memakai pola "Cara Pesan" di beranda: bulatan bernomor pada
        // jalur, dengan ikon di pojok berhadapan.
        ->assertSeeHtml('<span class="mbr-nomor">1</span>')
        ->assertSeeHtml('<span class="mbr-ikon-pojok"><i class="bi bi-bag-check"></i></span>')
        // Nilai balik dihitung dari rumus poin: 500 / 50.000 = 1%.
        ->assertSee('≈ 1% belanja kembali')
        ->assertSee('Poin belanja');
});

it('tombol kembali menunjuk langsung ke checkout', function () {
    // Sengaja rute checkout, bukan halaman sebelumnya: tombolnya bertuliskan
    // "Kembali ke Checkout", jadi tujuannya tidak boleh berubah-ubah.
    Livewire::test(MemberPage::class)
        ->assertSeeHtml('href="'.route('checkout').'"')
        ->assertSee('Kembali ke Checkout');
});
