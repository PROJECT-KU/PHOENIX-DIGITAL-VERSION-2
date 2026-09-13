<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

/**
 * Kartu "Produk Terlaris" di beranda.
 *
 * Cadangannya dulu hanya dipakai bila kolom `image` bernilai NULL — padahal
 * yang biasa terjadi adalah image TERISI tetapi berkasnya tidak ada. Ditambah
 * alt="" pada <img>-nya, kegagalan itu senyap total: peramban tidak menampilkan
 * apa pun, bahkan ikon rusak. Yang terlihat cuma lubang kosong tanpa petunjuk.
 */
$sumberTerlaris = fn () => file_get_contents(
    resource_path('views/livewire/pages/public/homepage/partials/produk-terlaris.blade.php')
);

it('gambar dipakai hanya bila berkasnya benar-benar ada', function () use ($sumberTerlaris) {
    $sumber = $sumberTerlaris();

    // Storage::exists, bukan is_file — mengikuti cara /shop memeriksanya.
    expect($sumber)->toContain("Storage::disk('public')->exists('img/Product/'")
        // Cadangan lama yang hanya jalan saat image NULL tidak boleh kembali.
        ->and($sumber)->not->toContain('niceshop/assets/img/product/scopus.png');
});

it('cadangannya ubin ikon KATEGORI, sama dengan kartu produk di /shop', function () use ($sumberTerlaris) {
    $sumber = $sumberTerlaris();

    expect($sumber)->toContain('KategoriBeranda::untukProduk')
        ->and($sumber)->toContain('class="pt-cadangan"')
        ->and($sumber)->toMatch('/\.pt-pelat\.is-kosong\s+\.pt-cadangan\s*\{[^}]*display:\s*flex/')
        // Ubinnya bergradasi warna kategori, seperti .sk-cadangan di /shop.
        ->and($sumber)->toContain('linear-gradient(140deg, var(--pc)');
});

it('berlapis dua: penjaga di server dan onerror di peramban', function () use ($sumberTerlaris) {
    // Berkas bisa ada saat dirender lalu gagal diambil peramban; onerror
    // menutup celah itu tanpa menunggu render ulang.
    $sumber = $sumberTerlaris();

    expect($sumber)->toContain("onerror=\"this.parentNode.classList.add('is-kosong'); this.remove();\"");
});

it('produk yang berkasnya hilang memang jatuh ke cadangan', function () {
    // Diuji lewat pemeriksa yang sama dengan yang dipakai Blade, bukan sekadar
    // membaca teksnya: kalau kelak Storage-nya diganti disk lain, uji ini ikut
    // memberi tahu.
    $p = Product::create([
        'nama_akun' => 'Cek Plagiasi Turnitin Uji',
        'tipe_akun' => 'sharing',
        'harga_perbulan' => 5000,
        'image' => 'Product_tidak_ada_'.uniqid().'.webp',
    ]);

    expect(Storage::disk('public')->exists('img/Product/'.$p->image))->toBeFalse();

    // Dan kategorinya memberi warna + ikon untuk ubin cadangannya.
    $kat = \App\Support\KategoriBeranda::untukProduk($p->nama_akun);
    expect($kat)->not->toBeNull()
        ->and($kat['warna'])->toBe('#2563eb')
        ->and($kat['ikon'])->toBe('bi-search');
});
