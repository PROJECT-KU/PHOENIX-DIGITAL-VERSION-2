<?php

use App\Livewire\Pages\Public\Blog\BlogShow;
use App\Models\BlogPost;
use App\Support\DaftarIsiArtikel;
use Livewire\Livewire;

/**
 * /blog/{slug} — halaman baca artikel.
 */
$sumberArtikel = fn () => file_get_contents(
    resource_path('views/livewire/pages/public/blog/blog-show.blade.php')
);

function artikelBaca(array $isian = []): BlogPost
{
    return BlogPost::create(array_merge([
        'title' => 'Cara Uji Artikel',
        'slug' => 'cara-uji-artikel-'.uniqid(),
        'category' => 'Turnitin',
        'excerpt' => 'Ringkasan uji',
        'body' => '<p>Pembuka.</p><h2>1. Langkah Pertama</h2><p>a</p><h2>2. Langkah Kedua</h2><p>b</p>',
        'status' => 'published',
        'published_at' => now()->setDate(2026, 8, 31),
        'author' => 'Nama Karyawan Asli',
    ], $isian));
}

it('daftar isi disusun dari <h2>: id unik, nomor penulis tidak diulang, "Daftar Isi" dilewati', function () {
    $hasil = DaftarIsiArtikel::susun(
        '<h2>Daftar Isi</h2><h2 class="ql-align-center">1. Satu &amp; Dua</h2><h2>1. Satu &amp; Dua</h2><h2> </h2>'
    );

    expect($hasil['daftar'])->toBe([
        ['id' => 'bagian-1-satu-dua', 'teks' => 'Satu & Dua'],
        ['id' => 'bagian-1-satu-dua-2', 'teks' => 'Satu & Dua'],
    ])
        // Atribut bawaan penyaring tetap; judul di artikel TIDAK dipotong nomornya.
        ->and($hasil['html'])->toContain('<h2 id="bagian-1-satu-dua" class="ql-align-center">1. Satu &amp; Dua</h2>')
        ->and($hasil['html'])->toContain('<h2>Daftar Isi</h2>');
});

it('artikel tanpa subjudul tidak menampilkan daftar isi', function () {
    Livewire::test(BlogShow::class, ['post' => artikelBaca(['body' => '<p>Hanya paragraf.</p>'])])
        ->assertDontSeeHtml('id="blgd-toc"')
        ->assertDontSeeHtml('class="blgd-isi-hp');
});

it('jangkar daftar isi sampai ke halaman dan menunjuk subjudul yang ada', function () {
    Livewire::test(BlogShow::class, ['post' => artikelBaca()])
        ->assertSeeHtml('<h2 id="bagian-1-langkah-pertama">1. Langkah Pertama</h2>')
        ->assertSeeHtml('href="#bagian-1-langkah-pertama" data-bagian="bagian-1-langkah-pertama"')
        ->assertSeeHtml('href="#bagian-2-langkah-kedua"');
});

it('isi artikel TETAP disaring: script & atribut on* tidak lolos walau alurnya berubah', function () {
    /*
     | Isinya kini lewat DaftarIsiArtikel sesudah HtmlSanitizer. Kalau urutannya
     | tertukar, atau penyaringnya terlepas dari alur, penulis blog bisa lagi
     | menjalankan skrip di browser semua pengunjung.
     */
    $html = Livewire::test(BlogShow::class, ['post' => artikelBaca([
        'body' => '<p>Aman</p><script>alert(1)</script><h2 onclick="alert(2)">Judul</h2><img src="x" onerror="alert(3)">',
    ])])->html();

    expect($html)->not->toContain('alert(1)')
        ->and($html)->not->toContain('alert(2)')
        ->and($html)->not->toContain('alert(3)')
        ->and($html)->toContain('<h2 id="bagian-judul">Judul</h2>');
});

it('nama penulis asli tidak tampil, bulan berbahasa Indonesia', function () {
    Livewire::test(BlogShow::class, ['post' => artikelBaca()])
        ->assertDontSee('Nama Karyawan Asli')
        ->assertSee('31 Agustus 2026')
        ->assertDontSee('August');
});

it('kepala baku berwarna kategori & badan tidak dipatok lebar', function () use ($sumberArtikel) {
    $sumber = $sumberArtikel();

    expect($sumber)->toContain('class="page-title ph-page-title" style="--c: {{ $ragam[\'warna\'] }}"')
        ->and($sumber)->toContain('class="ph-sec-eyebrow"><i class="bi {{ $ragam[\'ikon\'] }}"></i>')
        // Dulu .container art-shell { max-width: 1120px } — badan lebih sempit dari kepala.
        ->and($sumber)->not->toContain('art-shell')
        ->and($sumber)->not->toMatch('/class="container[^"]*"[^>]*max-width/')
        ->and($sumber)->not->toMatch('/\.blgd-(badan|grid|lain)\s*\{[^}]*max-width/');

    Livewire::test(BlogShow::class, ['post' => artikelBaca()])
        ->assertSeeHtml('style="--c: #2563eb"')
        ->assertSeeHtml('<i class="bi bi-search"></i> Turnitin');
});

it('sampul dijaga dua lapis & ubinnya hanya tampil saat sampul kosong', function () use ($sumberArtikel) {
    $sumber = $sumberArtikel();

    // Sampul artikel + kartu bacaan lainnya.
    expect(substr_count($sumber, "Storage::disk('public')->exists("))->toBe(2)
        ->and(substr_count($sumber, "onerror=\"this.parentNode.classList.add('is-kosong'); this.remove();\""))->toBe(2)
        ->and($sumber)->toMatch('/\.ph-article \.blgd-ubin\s*\{[^}]*display:\s*none/')
        ->and($sumber)->toMatch('/\.ph-article \.blgd-sampul\.is-kosong \.blgd-ubin\s*\{[^}]*display:\s*flex/');

    // Berkas sampul tidak ada di disk uji: <img> tidak dipasang sama sekali.
    Livewire::test(BlogShow::class, ['post' => artikelBaca(['cover' => 'tidak-ada.webp'])])
        ->assertSeeHtml('class="blgd-sampul is-kosong"')
        ->assertDontSeeHtml('storage/img/blog/tidak-ada.webp');
});

it('skrip progres & daftar isi dipasang sekali dan tahan wire:navigate', function () use ($sumberArtikel) {
    $sumber = $sumberArtikel();

    expect($sumber)->toContain('window.__blgdTerpasang')
        ->and($sumber)->toContain("document.addEventListener('livewire:navigated', minta)")
        ->and($sumber)->toContain('{ passive: true }')
        ->and($sumber)->toContain('prefers-reduced-motion: reduce');
});

it('bacaan lainnya tidak memuat artikel yang sedang dibaca', function () {
    $dibaca = artikelBaca();
    $lain = artikelBaca(['title' => 'Artikel Tetangga', 'slug' => 'artikel-tetangga']);

    $html = Livewire::test(BlogShow::class, ['post' => $dibaca])->html();

    expect($html)->toContain('href="'.route('blog.show', $lain->slug).'"')
        ->and(substr_count($html, 'class="blgd-kartu-lain"'))->toBe(1);
});
