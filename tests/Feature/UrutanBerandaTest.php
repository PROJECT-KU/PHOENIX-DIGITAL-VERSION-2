<?php

use App\Livewire\Pages\Public\Homepage\Index;
use Livewire\Livewire;

/**
 * Urutan bagian di beranda.
 *
 * Flash sale punya BATAS WAKTU, sedangkan produk terlaris ada setiap hari.
 * Menaruh yang berbatas waktu di bawah berarti sebagian pengunjung menutup
 * halaman sebelum sempat melihatnya, dan penawarannya keburu habis.
 *
 * Diuji lewat penanda di markup, bukan lewat mata: urutan @include mudah
 * tertukar lagi saat bagian lain ditambahkan, dan tertukarnya tidak memunculkan
 * galat apa pun — hanya penjualan yang diam-diam berkurang.
 */
it('flash sale dirender sebelum produk terlaris', function () {
    $html = Livewire::test(Index::class)->html();

    // id="call-to-action" adalah akar komponen flash sale, dan tetap ada
    // meski tidak sedang ada flash sale — jadi urutannya bisa diuji kapan pun,
    // bukan hanya saat kebetulan ada promo berjalan.
    $flash = strpos($html, 'id="call-to-action"');
    $terlaris = strpos($html, 'id="promo-cards"');

    expect($terlaris)->not->toBeFalse('bagian Produk Terlaris tidak ditemukan');
    expect($flash)->not->toBeFalse('bagian Flash Sale tidak ditemukan');
    expect($flash)->toBeLessThan($terlaris);
});
