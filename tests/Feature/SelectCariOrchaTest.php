<?php

/**
 * Kotak pilih yang bisa dicari (Select2) di layar Orcha.
 *
 * Yang dijaga di sini bukan rupanya, melainkan tiga aturan yang kalau
 * dilanggar TIDAK menimbulkan galat apa pun — layarnya tetap tergambar,
 * dan yang salah baru terasa saat dipakai.
 */
function sumberLayar(string $jalur): string
{
    return file_get_contents(resource_path("views/livewire/pages/admin/orcha/{$jalur}.blade.php"));
}

test('partial select-cari memuat pustakanya langsung, bukan lewat stack', function () {
    /*
     | Layout templateindex — yang dipakai seluruh halaman Orcha — tidak
     | punya @stack('scripts'), jadi apa pun yang didorong ke sana dibuang
     | diam-diam. Pustakanya tidak pernah termuat dan kotak pilihnya tetap
     | polos, tanpa satu pun galat di layar maupun di konsol.
     */
    $partial = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/partials/select-cari.blade.php')
    );

    expect($partial)->toContain('select2.min.js')
        ->and($partial)->toContain('jquery-3.7.1.min.js')
        ->and($partial)->toContain('@once')
        // Tidak didorong ke stack yang tidak dirender siapa pun.
        ->and($partial)->not->toContain('@push(');

    $layout = file_get_contents(resource_path('views/livewire/layout/templateindex.blade.php'));

    expect($layout)->not->toContain("@stack('scripts')");
});

test('tiap pembungkus select-cari memakai wire:ignore dan wire:key', function () {
    /*
     | wire:ignore: Select2 membangun DOM-nya sendiri di sebelah <select>
     | aslinya; tanpa ini, morph Livewire membuangnya tiap kali halaman
     | digambar ulang.
     |
     | wire:key: wire:ignore menahan morph, sehingga nilai yang diubah dari
     | SERVER tidak akan tercermin — kecuali nodenya diganti utuh karena
     | kuncinya berbeda.
     */
    $isi = sumberLayar('rab/master-harga');

    preg_match_all('/<div[^>]*x-init="pasangSelectCari\(/', $isi, $cocok);

    expect($cocok[0])->not->toBeEmpty();

    foreach ($cocok[0] as $pembungkus) {
        expect($pembungkus)->toContain('wire:ignore')
            ->and($pembungkus)->toContain('wire:key=');
    }
});

test('tidak ada wire:model di dalam pembungkus select-cari', function () {
    /*
     | Isi wire:ignore diabaikan Livewire, jadi wire:model di dalamnya tidak
     | pernah bekerja: nilainya tidak terbaca saat disimpan, dan tidak ada
     | galat apa pun yang mengatakannya. Nilai awalnya harus ditandai
     | @selected(), dan perubahannya didorong lewat $wire.set().
     */
    $isi = sumberLayar('rab/master-harga');

    // Potong tiap pembungkus select-cari sampai </div> penutupnya.
    preg_match_all(
        '/<div[^>]*x-init="pasangSelectCari\((?:.|\n)*?<\/select>/',
        $isi,
        $blok
    );

    expect($blok[0])->not->toBeEmpty();

    foreach ($blok[0] as $b) {
        expect($b)->not->toContain('wire:model')
            // Dan nilainya memang ditandai dari server.
            ->and($b)->toContain('@selected(');
    }
});

test('kotak pilih berdaftar panjang memang dipasangi pencarian', function () {
    /*
     | Yang dipasangi bukan semua kotak pilih — pada daftar enam baris,
     | kotak cari justru menambah satu benda yang harus diabaikan mata.
     | Yang dipasangi yang daftarnya memang bisa panjang.
     */
    $isi = sumberLayar('rab/master-harga');

    foreach ([
        "pasangSelectCari(\$el, 'isian.destinasi'",   // puluhan destinasi katalog
        "pasangSelectCari(\$el, 'isian.vendor_id'",   // tumbuh terus
        "pasangSelectCari(\$el, 'isian.provinsi'",    // 38 provinsi
        "pasangSelectCari(\$el, 'filterProvinsi'",
        "pasangSelectCari(\$el, 'filterVendor'",
        "pasangSelectCari(\$el, 'pasangKeVendor'",
    ] as $penanda) {
        expect($isi)->toContain($penanda);
    }
});

test('daftar select-cari tidak ditempelkan ke dalam kartu', function () {
    /*
     | dropdownParent yang menunjuk pembungkusnya terlihat lebih rapi di
     | kode dan rusak di layar: .dsb-kartu memakai overflow:hidden untuk
     | sudut membulatnya, jadi daftar yang terbuka dipotong tepi kartu —
     | yang terlihat cuma satu-dua baris pertama. Tidak ada galat; kotaknya
     | tetap terbuka, isinya saja yang hilang.
     |
     | Dibiarkan menempel ke <body> (bawaan Select2), dan karena itu butuh
     | z-index sendiri supaya tidak tertimbun kartu atau sidebar.
     */
    $partial = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/partials/select-cari.blade.php')
    );

    // Kata "dropdownParent" boleh muncul di keterangan, asal tidak dipasang.
    expect($partial)->not->toMatch('/dropdownParent\s*:/')
        ->and($partial)->toContain('.select2-container--open { z-index:');

    // Alasannya masih berlaku: kartunya memang memotong isinya.
    $gaya = file_get_contents(
        resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php')
    );

    expect($gaya)->toMatch('/\.dsb-kartu\s*\{[^}]*overflow:\s*hidden/');
});
