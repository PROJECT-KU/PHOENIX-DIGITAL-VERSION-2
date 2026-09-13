<?php

use App\Support\KategoriBeranda;
use App\Support\RagamBlog;

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

    // Kartu unggulan dan kartu grid sama-sama.
    expect(substr_count($sumber, "Storage::disk('public')->exists("))->toBeGreaterThanOrEqual(2)
        ->and(substr_count($sumber, "onerror=\"this.parentNode.classList.add('is-kosong'); this.remove();\""))->toBe(2)
        ->and(substr_count($sumber, 'RagamBlog::untuk('))->toBe(2)
        ->and($sumber)->toContain("style=\"--kb: {{ \$rbF['warna'] }}\"")
        ->and($sumber)->toContain("style=\"--kb: {{ \$rb['warna'] }}\"");
});

it('ubin ikon hanya tampil saat sampulnya kosong, tidak menindih foto', function () use ($sumberBlog) {
    $sumber = $sumberBlog();

    expect($sumber)->toMatch('/\.ph-blog \.thumb \.fb\s*\{[^}]*display:\s*none/')
        ->and($sumber)->toMatch('/\.ph-blog \.thumb\.is-kosong \.fb\s*\{[^}]*display:\s*flex/')
        // Aturan lama ini memaksa ikon tampil DI ATAS foto sampul — dan karena
        // spesifisitasnya lebih tinggi, ia mengalahkan display:none di atas.
        ->and($sumber)->not->toMatch('/\.ph-blog \.bcard \.thumb \.fb\s*\{[^}]*display:\s*flex/');
});

it('latar warna kategori tidak dikalahkan latar persik bawaan kartu grid', function () use ($sumberBlog) {
    /*
     | ".ph-blog .bcard .thumb" (latar persik) ditulis LEBIH BAWAH. Kalau
     | aturan warnanya cuma ".ph-blog .thumb.is-kosong", spesifisitas keduanya
     | sama dan yang terakhir menang — kartu grid kembali persik tanpa galat.
     */
    $sumber = $sumberBlog();

    expect($sumber)->toMatch('/\.ph-blog \.bcard \.thumb\.is-kosong\s*\{[^}]*var\(--kb\)/')
        ->and($sumber)->toMatch('/\.ph-blog \.feat \.thumb\.is-kosong,/');
});

it('animasi ubin menghormati prefers-reduced-motion', function () use ($sumberBlog) {
    expect($sumberBlog())->toContain('prefers-reduced-motion: reduce');
});
