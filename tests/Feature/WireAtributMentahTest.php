<?php

use Illuminate\Support\Facades\File;

/*
 * Variabel PHP mentah di dalam atribut wire:.
 *
 * Isi atribut BUKAN kode PHP. Ditulis wire:click="keHalaman($nomor)", yang
 * sampai ke peramban adalah tanda dolar itu sendiri: Livewire menilainya
 * sebagai nama yang tak dikenal, lemparannya tertelan, dan kliknya berhenti
 * di situ — tanpa satu pun pesan di layar.
 *
 * Rusaknya mahal justru karena tidak kelihatan: halaman tergambar sempurna,
 * tombolnya tampak hidup, bahkan alamat tujuannya muncul di bilah status
 * peramban saat disinggung. Yang tidak terjadi cuma satu: apa-apa. Dan pada
 * penomoran halaman Orcha, .prevent sudah terlanjur membatalkan tautannya —
 * dua jalan keluar, dua-duanya mati. Seluruh daftar Orcha terkunci di
 * halaman pertama berbulan-bulan tanpa ada yang bisa menyebutkan sebabnya.
 *
 * Diuji pada SUMBER view: yang terkompilasi di-cache dan dipakai bersama.
 */
test('tidak ada atribut wire: yang memuat variabel PHP mentah', function () {
    $berkas = collect(File::allFiles(resource_path('views')))
        ->filter(fn ($f) => str_ends_with($f->getFilename(), '.blade.php'));

    expect($berkas)->not->toBeEmpty();

    /*
     | Yang BUKAN pelanggaran:
     | - {{ ... }} dan @js(...) — keduanya memang dikompilasi;
     | - sihir Alpine/Livewire yang hidup di sisi peramban ($wire, $event,
     |   $refresh, $set, $toggle, $parent, $dispatch, $js, $el, $watch, $store).
     */
    $sihir = '$wire|$event|$refresh|$set|$toggle|$parent|$dispatch|$js|$el|$watch|$store|$nextTick|$data';

    $temuan = [];

    foreach ($berkas as $f) {
        $isi = File::get($f->getPathname());

        preg_match_all('/\bwire:[a-zA-Z0-9.:_-]+="([^"]*)"/', $isi, $cocok, PREG_SET_ORDER);

        foreach ($cocok as [$utuh, $nilai]) {
            // Buang yang sah lebih dulu, lalu lihat apakah masih ada dolar.
            $sisa = preg_replace('/\{\{.*?\}\}/s', '', $nilai);
            $sisa = preg_replace('/@js\([^)]*\)/', '', $sisa);
            $sisa = preg_replace('/'.preg_quote($sihir, '/').'/', '', str_replace(
                explode('|', $sihir), '', $sisa
            ));

            if (str_contains($sisa, '$')) {
                $temuan[] = str_replace(resource_path('views').'/', '', $f->getPathname()).' → '.trim($utuh);
            }
        }
    }

    expect($temuan)->toBe([], "atribut wire: memuat variabel PHP mentah:\n".implode("\n", $temuan));
});

test('nomor halaman Orcha sampai ke peramban sebagai angka', function () {
    /*
     | Penjaga yang lebih dekat ke gejalanya: bukan "tidak ada dolar", tetapi
     | "ada angkanya". Sebuah partial yang diganti isinya pun tetap tertangkap.
     */
    $sumber = File::get(resource_path('views/livewire/pages/admin/orcha/partials/paginasi.blade.php'));

    // Dicari di ATRIBUTnya, bukan di seluruh berkas: catatan di kepala partial
    // mengutip bentuk yang salah untuk menjelaskan sebabnya, dan pencarian
    // polos akan menuduh catatan itu sendiri.
    preg_match_all('/\bwire:click[a-z.]*="([^"]*)"/', $sumber, $cocok);
    $atribut = implode(' | ', $cocok[1]);

    expect($atribut)->toContain('keHalaman({{ $nomor }})')
        ->and($atribut)->not->toContain('keHalaman($');
});
