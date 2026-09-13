<?php

use App\Livewire\Pages\Public\Blog\BlogIndex;
use App\Models\BlogPost;
use App\Support\KategoriBeranda;
use App\Support\RagamBlog;
use Livewire\Livewire;

/**
 * /blog — kartu artikel tanpa sampul.
 *
 * Tidak satu pun artikel punya sampul, jadi dulu kedelapan kartunya tampil
 * sebagai kotak persik pucat berikon sama dan tidak bisa dibedakan. Kini
 * bidangnya berwarna kategori artikel, dengan warna yang sama dengan Shop.
 */
$sumberBlog = fn () => file_get_contents(
    resource_path('views/livewire/pages/public/blog/blog-index.blade.php')
);

$warnaShop = fn (string $label) => collect(KategoriBeranda::PETA)->firstWhere('label', $label);

it('kategori blog memakai warna & ikon kategori produk padanannya di Shop', function () use ($warnaShop) {
    foreach ([
        'Turnitin' => 'Cek Plagiasi',
        'AI & Tools Digital' => 'AI Tools',
        'Riset & Publikasi' => 'Jurnal & Riset',
    ] as $kategoriBlog => $labelShop) {
        $shop = $warnaShop($labelShop);

        expect(RagamBlog::untuk($kategoriBlog))
            ->toBe(['warna' => $shop['warna'], 'ikon' => $shop['ikon']]);
    }
});

it('cocok lewat kata kunci, jadi nama kategori boleh diubah admin', function () use ($warnaShop) {
    expect(RagamBlog::untuk('Tips Cek TURNITIN')['warna'])->toBe($warnaShop('Cek Plagiasi')['warna'])
        ->and(RagamBlog::untuk('  riset  ')['warna'])->toBe($warnaShop('Jurnal & Riset')['warna']);
});

it('"ai" dicocokkan sebagai kata utuh, bukan potongan kata', function () use ($warnaShop) {
    // "pemakaian" & "rangkaian" mengandung huruf a-i berurutan.
    expect(RagamBlog::untuk('Panduan Pemakaian'))->toBe(RagamBlog::BAWAAN)
        ->and(RagamBlog::untuk('Rangkaian Webinar'))->toBe(RagamBlog::BAWAAN)
        ->and(RagamBlog::untuk('Tools AI'))->toBe([
            'warna' => $warnaShop('AI Tools')['warna'],
            'ikon' => $warnaShop('AI Tools')['ikon'],
        ]);
});

it('kategori kosong atau tak dikenal jatuh ke warna bawaan, bukan galat', function () {
    expect(RagamBlog::untuk(null))->toBe(RagamBlog::BAWAAN)
        ->and(RagamBlog::untuk(''))->toBe(RagamBlog::BAWAAN)
        ->and(RagamBlog::untuk('Kabar Perusahaan'))->toBe(RagamBlog::BAWAAN);
});

it('sampul dijaga dua lapis: diperiksa di server, dan onerror bila tetap rusak', function () use ($sumberBlog) {
    $sumber = $sumberBlog();

    // Kartu utama, baris "Baru Terbit", dan kartu grid — ketiganya.
    expect(substr_count($sumber, "Storage::disk('public')->exists("))->toBe(3)
        ->and(substr_count($sumber, "onerror=\"this.parentNode.classList.add('is-kosong'); this.remove();\""))->toBe(3)
        ->and($sumber)->toContain("style=\"--kb: {{ \$rbF['warna'] }}\"")
        ->and($sumber)->toContain("style=\"--kb: {{ \$rbS['warna'] }}\"")
        ->and($sumber)->toContain("style=\"--kb: {{ \$rb['warna'] }}\"")
        // Chip topik ikut warna kategorinya.
        ->and($sumber)->toContain("style=\"--kb: {{ \$rbC['warna'] }}\"");
});

it('ubin ikon hanya tampil saat sampulnya kosong, tidak menindih foto', function () use ($sumberBlog) {
    $sumber = $sumberBlog();

    expect($sumber)->toMatch('/\.ph-blog \.blg-ubin\s*\{[^}]*display:\s*none/')
        ->and($sumber)->toMatch('/\.ph-blog \.blg-sampul\.is-kosong \.blg-ubin\s*\{[^}]*display:\s*flex/');
});

it('lebar badan tidak dipatok angka, jadi sejajar kartu kepala di semua layar', function () use ($sumberBlog) {
    /*
     | Dulu `<div class="container" style="max-width: 1140px;">` membuat badan
     | 90px lebih sempit di kiri-kanan dari kartu kepala. Kartu kepala adalah
     | ::before pada .container yang menjorok 12px — sama dengan padding
     | .container — jadi .container polos otomatis sejajar di tiap breakpoint.
     */
    $sumber = $sumberBlog();

    expect($sumber)->toContain('class="page-title ph-page-title"')
        ->and($sumber)->not->toMatch('/class="container"[^>]*max-width/')
        ->and($sumber)->not->toMatch('/\.blg-(alat|sorot|grid|hasil)\s*\{[^}]*max-width/');
});

it('animasi menghormati prefers-reduced-motion', function () use ($sumberBlog) {
    expect($sumberBlog())->toContain('prefers-reduced-motion: reduce');
});

function artikelBlog(int $urutan, string $kategori = 'AI & Tools Digital'): BlogPost
{
    return BlogPost::create([
        'title' => 'Artikel Uji '.$urutan,
        'slug' => 'artikel-uji-'.$urutan,
        'category' => $kategori,
        'excerpt' => 'Ringkasan artikel '.$urutan,
        'body' => 'Isi artikel',
        'status' => 'published',
        'published_at' => now()->subDays($urutan),
    ]);
}

it('artikel utama & "Baru Terbit" tidak diulang di grid', function () {
    foreach (range(1, 8) as $i) {
        artikelBlog($i);
    }

    $html = Livewire::test(BlogIndex::class)->html();

    // Tiap judul muncul tepat sekali sebagai tautan artikel.
    foreach (range(1, 8) as $i) {
        expect(substr_count($html, 'href="'.route('blog.show', 'artikel-uji-'.$i).'"'))->toBe(1);
    }

    expect(substr_count($html, 'class="blg-utama"'))->toBe(1)
        ->and(substr_count($html, 'class="blg-baris"'))->toBe(3)
        ->and(substr_count($html, 'class="blg-kartu"'))->toBe(4);
});

it('halaman pertama memuat 12 artikel: 1 utama + 3 baru terbit + 8 kartu grid', function () {
    // Delapan kartu = dua baris penuh grid 4 kolom, tanpa kartu yatim.
    foreach (range(1, 14) as $i) {
        artikelBlog($i);
    }

    $html = Livewire::test(BlogIndex::class)->html();

    expect(substr_count($html, 'class="blg-kartu"'))->toBe(8);
});

it('saat topik dipilih tidak ada sorotan; semua hasil masuk grid', function () {
    artikelBlog(1, 'Turnitin');
    artikelBlog(2, 'Turnitin');
    artikelBlog(3, 'Riset & Publikasi');

    Livewire::test(BlogIndex::class)
        ->call('filterCategory', 'Turnitin')
        ->assertDontSeeHtml('class="blg-utama"')
        ->assertDontSeeHtml('class="blg-baru"')
        ->assertSeeHtml('<h2>Turnitin</h2>')
        ->assertSee('Artikel Uji 1')
        ->assertDontSee('Artikel Uji 3');
});
