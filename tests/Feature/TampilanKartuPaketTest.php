<?php

/**
 * Kartu paket bundling (partials/kartu-paket.blade.php) — dipakai bersama
 * beranda dan halaman /bundling.
 */
$sumberKartuPaket = fn () => file_get_contents(resource_path('views/partials/kartu-paket.blade.php'));

it('medianya memakai KartuPaket, satu sumber dengan /bundling/product', function () use ($sumberKartuPaket) {
    /*
     | Kartu ini dulu menghitung sendiri — dan hasilnya menyimpang dari halaman
     | daftar paket: lencana hematnya memakai 'potongan' (promo tambahan, hampir
     | selalu 0) sehingga tidak pernah muncul, padahal /bundling/product
     | menampilkannya dari selisih harga coret. Helper KartuPaket adalah satu
     | sumber untuk warna aksen, tumpukan isi, penjaga berkas, harga, dan hemat.
     */
    $sumber = $sumberKartuPaket();

    expect($sumber)->toContain('KartuPaket::data($item)')
        // Tidak boleh menghitung sendiri lagi.
        ->and($sumber)->not->toContain('HargaPaket::untuk($item)')
        ->and($sumber)->not->toContain('phoenix-mark.png');
});

it('tanpa gambar, isinya digambar sebagai tumpukan ubin per produk', function () use ($sumberKartuPaket) {
    // Ubin tunggal berikon kotak membuat keempat kartu terlihat sama persis;
    // tumpukan ini memberi tahu isi paketnya tanpa satu kata pun.
    $sumber = $sumberKartuPaket();

    expect($sumber)->toContain('class="pkt-tumpuk"')
        ->and($sumber)->toMatch('/\.pkt-media\.is-kosong\s+\.pkt-tumpuk\s*\{[^}]*display:\s*flex/')
        // Tiap ubin berwarna kategorinya sendiri & dimiringkan bergantian.
        ->and($sumber)->toContain("--p: {{ \$t['warna'] }}; --r: {{ \$t['putar'] }}deg")
        // Berlapis dua, sama dengan /shop & /bundling/product.
        ->and($sumber)->toContain("onerror=\"this.parentNode.classList.add('is-kosong'); this.remove();\"");
});

it('medianya sebangun dengan kartu di /bundling/product', function () use ($sumberKartuPaket) {
    $paket = $sumberKartuPaket();
    $daftar = file_get_contents(resource_path('views/livewire/pages/public/bundling/product-bundlings.blade.php'));

    // Rasio & sapuan warna kategori harus sama; kalau salah satu berubah
    // sendiri, kedua halaman berhenti terbaca sebagai satu toko.
    foreach ([['/aspect-ratio:\s*16\s*\/\s*11/', 'rasio media'],
        ['/linear-gradient\(160deg, color-mix\(in srgb, var\(--c\) 11%, #fff\)/', 'sapuan warna kategori']] as [$pola, $apa]) {
        expect($paket)->toMatch($pola, "kartu paket kehilangan $apa");
        expect($daftar)->toMatch($pola, "/bundling/product kehilangan $apa");
    }
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
        // Tautan "Lihat" kini memakai url dari helper, bukan route() sendiri.
        ->and($sumber)->toContain("href=\"{{ \$k['url'] }}\"");
});

it('url dari helper memang menuju halaman detail paketnya', function () {
    // Uji tautannya di tingkat PERILAKU: memeriksa teks route() di blade tidak
    // lagi mungkin sejak helper yang membangunnya, dan yang penting bukan cara
    // menulisnya melainkan ke mana ia bermuara.
    $paket = \App\Models\ProductBundlings::create([
        'nama_paket' => 'Uji Tautan Kartu',
        'harga_awal' => 'Rp100.000',
        'harga_bundling' => 'Rp70.000',
        'status' => 'active',
    ]);

    expect(\App\Support\KartuPaket::data($paket)['url'])
        ->toBe(route('bundling.detail', $paket->id));
});
