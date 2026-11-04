<?php

/**
 * Layar yang mengirim event harus memuat yang mendengarkannya.
 *
 * `orcha-sukses-pindah` dan `toast-error` ditangani di satu partial skrip
 * Orcha. Layar yang mengirim salah satunya tanpa memuat partial itu akan
 * terlihat seperti tidak menekan apa pun: tidak ada toast, tidak ada
 * perpindahan, tidak ada galat apa pun yang bisa dicurigai.
 *
 * Yang paling berbahaya bukan diamnya, melainkan bahwa pekerjaannya SUDAH
 * tersimpan di Orcha. Admin mengira gagal, menekan berkali-kali, lalu
 * menyerah dan mengetik ulang.
 *
 * Uji komponen tidak bisa menangkapnya: assertDispatched justru hijau, karena
 * yang diperiksa pengirimannya — bukan ada tidaknya yang mendengarkan.
 */
function layarPengirimEvent(): array
{
    $hasil = [];

    $berkas = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path('Livewire/Pages/Admin/Orcha'))
    );

    foreach ($berkas as $f) {
        if ($f->getExtension() !== 'php') {
            continue;
        }

        // Salinan ganda OneDrive ("... 2.php") tidak terlacak git dan bukan
        // layar sungguhan — lihat [[berkas-ganda-onedrive]].
        if (str_contains($f->getFilename(), ' 2.php')) {
            continue;
        }

        $sumber = file_get_contents($f->getPathname());

        if (! str_contains($sumber, 'orcha-sukses-pindah') && ! str_contains($sumber, "'toast-error'")) {
            continue;
        }

        if (! preg_match("/view\('(livewire\.[a-z0-9._-]+)'/", $sumber, $c)) {
            continue;
        }

        $hasil[$f->getFilename()] = resource_path('views/'.str_replace('.', '/', $c[1]).'.blade.php');
    }

    return $hasil;
}

test('setiap layar Orcha yang mengirim event memuat penangannya', function () {
    $layar = layarPengirimEvent();

    expect($layar)->not->toBeEmpty('pemindainya tidak menemukan layar apa pun — polanya berubah?');

    $tanpa = [];

    foreach ($layar as $komponen => $tampilan) {
        if (! file_exists($tampilan)) {
            $tanpa[] = "{$komponen}: tampilannya tidak ada ({$tampilan})";

            continue;
        }

        if (! str_contains(file_get_contents($tampilan), 'partials.skrip')) {
            $tanpa[] = "{$komponen}: {$tampilan}";
        }
    }

    expect($tanpa)->toBe([], 'layar ini mengirim event tanpa memuat penangannya: '.implode(' | ', $tanpa));
});
