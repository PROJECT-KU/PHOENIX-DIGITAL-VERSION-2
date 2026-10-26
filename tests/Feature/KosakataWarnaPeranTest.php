<?php

/**
 * Kosakata warna peran — acuan untuk SELURUH menu dasbor.
 *
 * Dua hal yang dijaga di sini, dan keduanya pernah rusak diam-diam:
 *
 *   1. Aturan yang MEMBACA --c. Layar Orcha menulis style="--c: …" di
 *      delapan tempat sementara tidak ada satu pun aturan yang membacanya;
 *      statusnya tergambar tanpa warna, dan markahnya terlihat benar
 *      sehingga tidak ada yang curiga.
 *
 *   2. Nama peran, bukan nama warna. "is-hijau" memaksa setiap layar
 *      memutuskan ulang hijau itu artinya apa; "is-aman" membuat
 *      keputusannya diambil sekali.
 */
function gayaDasbor(): string
{
    return file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));
}

test('kosakata warna peran tersedia sebagai variabel bernama', function () {
    $gaya = gayaDasbor();

    foreach ([
        '--dsb-utama' => 'aksi pokok halaman',
        '--dsb-aman' => 'menghubungi & menyelesaikan',
        '--dsb-telaah' => 'melihat & mengambil',
        '--dsb-awas' => 'menahan tanpa merusak',
        '--dsb-bahaya' => 'menghapus & membatalkan',
        '--dsb-netral' => 'tanpa peran khusus',
    ] as $token => $arti) {
        expect($gaya)->toContain($token.':');
    }
});

test('tombol punya varian untuk tiap peran', function () {
    $gaya = gayaDasbor();

    foreach (['is-utama', 'is-aman', 'is-telaah', 'is-awas', 'is-bahaya', 'is-lembut'] as $peran) {
        expect($gaya)->toContain(".dsb-tombol.{$peran}");
    }
});

test('lencana dan tombol tabel benar-benar membaca --c', function () {
    /*
     | Inti seluruh berkas ini. Tanpa aturan ini, style="--c: …" cuma
     | menuliskan variabel yang tidak pernah dibaca siapa pun — dan status
     | yang seharusnya hijau tergambar sewarna teks biasa.
     */
    $gaya = gayaDasbor();

    expect($gaya)->toContain('.dsb-lencana[style*="--c"]')
        ->and($gaya)->toContain('.dsb-tabel-btn[style*="--c"]');
});

test('layar vendor memakai nama peran, bukan kode heksa', function () {
    /*
     | Vendor adalah acuannya. Kalau di sini pun warnanya ditulis sebagai
     | heksa, layar berikutnya akan menyalin heksa itu — dan kosakatanya
     | mati sebelum dipakai.
     */
    foreach (['vendor/index', 'vendor/detail'] as $layar) {
        $isi = file_get_contents(resource_path("views/livewire/pages/admin/orcha/{$layar}.blade.php"));

        // Tidak ada lagi --ikon/--c berisi heksa di markahnya.
        expect($isi)->not->toContain('style="--ikon');

        preg_match_all('/style="--c: (#[0-9a-fA-F]{3,8})"/', $isi, $cocok);
        expect($cocok[1])->toBe([], "{$layar} masih memakai heksa di --c: ".implode(', ', $cocok[1]));
    }
});

test('tiap peran tombol terpakai di layar acuan', function () {
    // Kosakata yang tidak pernah dipakai di layar acuannya tidak akan
    // ditiru layar berikutnya.
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/orcha/vendor/index.blade.php'))
        .file_get_contents(resource_path('views/livewire/pages/admin/orcha/vendor/detail.blade.php'));

    foreach (['is-utama', 'is-aman', 'is-telaah', 'is-awas', 'is-lembut'] as $peran) {
        expect($isi)->toContain("dsb-tombol {$peran}");
    }
});
