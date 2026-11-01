<?php

use Illuminate\Support\Facades\File;

/**
 * Dua cara gaya halaman Orcha hilang TANPA satu pun galat.
 *
 * Keduanya sudah pernah terjadi, dan keduanya meninggalkan halaman yang
 * tetap 200: yang rusak cuma rupanya, dan kerusakannya tidak menyebut
 * sebabnya sendiri.
 */
function berkasOrchaBergaya(): array
{
    return collect(File::allFiles(resource_path('views/livewire/pages/admin/orcha')))
        // Salinan kembar buatan OneDrive ("form.blade 2.php") tidak pernah
        // dirender Blade — melaporkannya hanya kebisingan.
        ->reject(fn ($f) => preg_match('/ \d+\.php$/', $f->getFilename()))
        ->values()->all();
}

test('gaya halaman Orcha tidak didorong ke stack yang tidak ada', function () {
    /*
     | templateindex tidak punya satu pun @stack. Gaya yang di-push ke sana
     | hilang tanpa jejak — halamannya tetap 200, cuma tampil polos. Pernah
     | terjadi di layar Hari Libur, terbawa dari konvensi proyek sebelah.
     */
    expect(file_get_contents(resource_path('views/livewire/layout/templateindex.blade.php')))
        ->not->toContain('@stack(');

    /*
     | Cacat LAMA yang sudah ada sebelum penjaga ini, dan sengaja belum
     | dibetulkan di sini: orcha-blog-form mendorong scripts-head, scripts,
     | dan styles ke stack yang tidak ada, jadi ketiganya tidak pernah
     | tergambar. Memindahkannya ke dalam badan komponen bukan sekadar
     | memindah teks — skrip sebaris di dalam akar komponen Livewire ikut
     | jalan lagi tiap kali DOM-nya di-morph, jadi perbaikannya butuh
     | pemeriksaan tersendiri. Disebut namanya di sini supaya tidak hilang
     | dari ingatan, bukan supaya dimaafkan.
     */
    $lamaBelumDibetulkan = ['orcha-blog-form.blade.php'];

    $nakal = [];

    foreach (berkasOrchaBergaya() as $berkas) {
        // Komentar Blade dibuang lebih dulu: beberapa berkas MENYEBUT
        // direktif ini justru untuk menjelaskan kenapa tidak memakainya,
        // dan penjaga yang menuduh komentar akan dimatikan orang.
        $isi = preg_replace('/\{\{--.*?--\}\}/s', '', (string) file_get_contents($berkas->getPathname()));

        if (str_contains($isi, '@push(') && ! in_array($berkas->getFilename(), $lamaBelumDibetulkan, true)) {
            $nakal[] = $berkas->getFilename();
        }
    }

    expect($nakal)->toBe([]);
});

test('satu nama kelas tidak dipakai untuk dua hal yang display-nya bentrok', function () {
    /*
     | ".mh-muat" sempat berarti DUA hal di berkas yang sama: "muat"
     | (kapasitas satu kendaraan, display:flex) dan "memuat" (pita sedang
     | mengambil data, display:none). Aturan yang ditulis belakangan menang,
     | jadi kotak kapasitas ikut tersembunyi — ada di DOM, tidak terlihat,
     | dan validasinya tetap menuntut diisi. Harga bersatuan per unit tidak
     | bisa disimpan sama sekali.
     |
     | Yang dijaga hanya BENTROK display: kelas yang ditulis dua kali untuk
     | menambah properti lain (atau di dalam @media) memang wajar.
     */
    $nakal = [];

    foreach (berkasOrchaBergaya() as $berkas) {
        $isi = (string) file_get_contents($berkas->getPathname());

        // Hanya blok <style> di luar @media: aturan di dalam media query
        // memang menimpa dengan sengaja.
        if (! preg_match_all('/<style>(.*?)<\/style>/s', $isi, $blok)) {
            continue;
        }

        $gaya = preg_replace('/@media[^{]*\{(?:[^{}]|\{[^{}]*\})*\}/s', '', implode("\n", $blok[1]));

        preg_match_all('/(^|\})\s*(\.[\w-]+)\s*\{([^{}]*)\}/m', $gaya, $m, PREG_SET_ORDER);

        $display = [];

        foreach ($m as $aturan) {
            if (preg_match('/(?:^|;)\s*display\s*:\s*([\w-]+)/', $aturan[3], $d)) {
                $display[$aturan[2]][] = $d[1];
            }
        }

        foreach ($display as $kelas => $nilai) {
            if (count(array_unique($nilai)) > 1) {
                $nakal[] = $berkas->getFilename().': '.$kelas.' → '.implode(' vs ', array_unique($nilai));
            }
        }
    }

    expect($nakal)->toBe([]);
});
