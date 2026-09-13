<?php

/**
 * Kartu paket bundling (partials/kartu-paket.blade.php) — dipakai bersama
 * beranda dan halaman /bundling.
 */
$sumberKartuPaket = fn () => file_get_contents(resource_path('views/partials/kartu-paket.blade.php'));

it('gambar dipakai hanya bila berkasnya ada, cadangannya ubin kategori', function () use ($sumberKartuPaket) {
    /*
     | Dulu cadangannya (logo Phoenix) hanya dipakai bila kolom `gambar`
     | bernilai NULL, padahal yang biasa terjadi adalah gambar TERISI tetapi
     | berkasnya tidak ada. Karena alt-nya memuat nama paket, yang tampil justru
     | teks alt mentah — "Combo Riset Hemat" sebagai gambar rusak.
     */
    $sumber = $sumberKartuPaket();

    expect($sumber)->toContain("Storage::disk('public')->exists('img/ProductBundlings/'")
        ->and($sumber)->toContain('class="pkt-cadangan"')
        ->and($sumber)->toMatch('/\.pkt-pelat\.is-kosong\s+\.pkt-cadangan\s*\{[^}]*display:\s*flex/')
        // Berlapis dua, sama dengan /shop.
        ->and($sumber)->toContain("onerror=\"this.parentNode.classList.add('is-kosong'); this.remove();\"")
        // Cadangan lama tidak boleh kembali.
        ->and($sumber)->not->toContain('phoenix-mark.png');
});

it('awalan kelasnya pkt-, tidak menabrak kartu Kategori Populer', function () {
    /*
     | .kp-kartu dan .kp-nama SUDAH dipakai kategori-populer.blade.php di
     | beranda. Versi pertama kartu paket ini memakai kp- juga, dan gayanya
     | langsung bocor ke sembilan kartu kategori di halaman yang sama —
     | tingginya berubah dari 142px jadi ikut aturan kartu paket. Tidak ada
     | galat, hanya halaman yang diam-diam berubah.
     */
    $paket = file_get_contents(resource_path('views/partials/kartu-paket.blade.php'));
    $kategori = file_get_contents(resource_path('views/livewire/pages/public/homepage/partials/kategori-populer.blade.php'));

    // Kelas yang DIDEFINISIKAN kartu paket tidak boleh ada yang juga
    // didefinisikan kartu kategori.
    preg_match_all('/\.((?:pkt|kp)-[a-z-]+)\s*[{,:]/', $paket, $mPaket);
    preg_match_all('/\.(kp-[a-z-]+)\s*[{,:]/', $kategori, $mKategori);

    $tabrakan = array_intersect(array_unique($mPaket[1]), array_unique($mKategori[1]));

    expect($tabrakan)->toBe([], 'kelas ini dipakai kedua kartu: '.implode(', ', $tabrakan));
});

it('tidak lagi menumpang kerangka .fs-card dari CSS beku', function () use ($sumberKartuPaket) {
    $sumber = $sumberKartuPaket();

    expect($sumber)->not->toContain('class="fs-card"')
        ->and($sumber)->not->toContain('class="fs-card-media"')
        ->and($sumber)->not->toContain('class="fs-card-body"');
});

it('ikon tombol sebaris dengan teksnya', function () use ($sumberKartuPaket) {
    // Isi tombol dibungkus <span> oleh wire:loading; span polos bukan flex,
    // jadi ikon display:block akan berdiri DI ATAS teksnya.
    $sumber = $sumberKartuPaket();

    expect($sumber)->toMatch('/\.pkt-btn\s*>\s*span\s*\{[^}]*display:\s*inline-flex/');
});

it('kabel keranjang & tautan detail tetap utuh', function () use ($sumberKartuPaket) {
    $sumber = $sumberKartuPaket();

    expect($sumber)->toContain("wire:click=\"addToCart('{{ \$item->id }}')\"")
        ->and($sumber)->toContain("wire:target=\"addToCart('{{ \$item->id }}')\"")
        ->and($sumber)->toContain("route('bundling.detail', \$item->id)");
});
