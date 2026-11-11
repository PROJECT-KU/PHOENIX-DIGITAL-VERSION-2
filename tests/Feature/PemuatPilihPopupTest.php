<?php

use Illuminate\Support\Facades\File;

/**
 * Pemilih berbentuk jendela di layar Orcha.
 *
 * Yang dijaga di sini satu golongan bug yang sama: markahnya ada, fungsinya
 * tidak — dan layarnya tetap tergambar rapi tanpa satu pun galat.
 *
 * Itulah yang terjadi pada borang Master Harga sebelum ini. Markahnya
 * memanggil pasangSelectCari() di tiga tempat, tetapi yang menyertakan
 * pustakanya cuma halaman DAFTAR. Dibuka langsung, ketiga kotak itu tetap
 * kotak pilih bawaan peramban; dicapai lewat wire:navigate dari daftar,
 * skripnya terlanjur hidup di memori sementara lembar gayanya tidak ikut
 * terbawa — dan yang terlihat kotak pilih asli DITAMBAH salinan telanjang
 * nilainya di bawahnya.
 */
function sumberOrcha(string $jalur): string
{
    $jalur = str_replace('.', '/', $jalur);

    return file_get_contents(resource_path("views/livewire/pages/admin/orcha/{$jalur}.blade.php"));
}

test('tiap halaman yang memakai pilihPopup ikut memuat partialnya', function () {
    /*
     | Pemakainya di satu berkas, pemuatnya di berkas lain — dan tidak ada
     | yang menyatakan hubungannya. Penjaga ini mengikuti @include sampai
     | ke dalam, karena yang memanggil pilihPopup() memang partial isian,
     | sementara yang menyertakan pustakanya halamannya.
     */
    $akar = resource_path('views/livewire/pages/admin/orcha');

    $baca = function (string $nama) use ($akar): string {
        $berkas = $akar.'/'.str_replace('.', '/', $nama).'.blade.php';

        return is_file($berkas) ? file_get_contents($berkas) : '';
    };

    // Isi sebuah halaman BESERTA seluruh partial Orcha yang disertakannya.
    $utuh = function (string $isi) use ($baca, &$utuh): string {
        preg_match_all(
            "/@include\('livewire\.pages\.admin\.orcha\.([a-z0-9._-]+)'/i",
            $isi,
            $cocok
        );

        foreach ($cocok[1] as $anak) {
            $isi .= "\n".$baca($anak);
        }

        return $isi;
    };

    $nakal = [];

    foreach (File::files($akar.'/rab') as $berkas) {
        $isi = $utuh(file_get_contents($berkas->getPathname()));

        if (! str_contains($isi, 'pilihPopup(')) {
            continue;
        }

        if (! str_contains($isi, "@include('livewire.pages.admin.orcha.partials.pilih-popup')")) {
            $nakal[] = $berkas->getFilename();
        }
    }

    expect($nakal)->toBe([]);
});

test('partial pemilih memuat dirinya sendiri, bukan lewat stack', function () {
    /*
     | templateindex tidak punya @stack('scripts'), jadi apa pun yang
     | didorong ke sana dibuang diam-diam — alasan yang sama dengan
     | partial select-cari.
     */
    $partial = sumberOrcha('partials.pilih-popup');

    expect($partial)->toContain('window.pilihPopup')
        ->and($partial)->toContain('@once')
        ->and($partial)->not->toContain('@push(');

    // SweetAlert2 datang dari layout, bukan dari partial ini — jadi
    // ketiadaannya harus ditangani, bukan diandaikan.
    expect($partial)->toContain("typeof Swal === 'undefined'");

    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($layout)->toContain('sweetalert2@11');
});

test('pembungkus pemilih TIDAK memakai wire:ignore', function () {
    /*
     | Kebalikan dari aturan Select2, dan gampang tertukar.
     |
     | Select2 membangun DOM-nya sendiri di sebelah <select> aslinya, jadi
     | morph Livewire harus ditahan. Pemilih ini tidak membangun apa pun —
     | yang digambar cuma tombol biasa yang membawa nilainya sebagai teks.
     | Ditahan morph, tombolnya akan terus menampilkan nilai lama sesudah
     | server menggantinya, tanpa satu pun galat.
     */
    $isi = sumberOrcha('rab.partials.master-harga-borang');

    preg_match_all('/<div[^>]*class="op-pilih"[^>]*>/', $isi, $cocok);

    expect($cocok[0])->toHaveCount(4);

    foreach ($cocok[0] as $pembungkus) {
        expect($pembungkus)->not->toContain('wire:ignore')
            // wire:key tetap wajib: daftarnya berubah mengikuti provinsi dan
            // mengikuti apa yang sudah masuk tiket.
            ->and($pembungkus)->toContain('wire:key=');
    }
});

test('tiap pemicu pemilih membawa tujuan, nilai, dan daftarnya', function () {
    /*
     | Ketiganya dibaca dari data-*, dan yang hilang tidak menimbulkan
     | galat apa pun: tanpa data-ke pilihannya tidak pernah sampai ke
     | server, tanpa data-terpilih tidak ada baris yang bercentang, dan
     | tanpa data-daftar jendelanya terbuka kosong.
     */
    $isi = sumberOrcha('rab.partials.master-harga-borang');

    preg_match_all('/<button[^>]*onclick="pilihPopup\(this\)"(?:.|\n)*?>/', $isi, $cocok);

    expect($cocok[0])->toHaveCount(4);

    foreach ($cocok[0] as $tombol) {
        expect($tombol)->toContain('data-ke=')
            ->and($tombol)->toContain('data-terpilih=')
            ->and($tombol)->toContain('data-daftar=');
    }

    // Keempat medannya memang yang dimaksud, bukan empat tombol mana saja.
    foreach (['isian.destinasi', 'isian.vendor_id', 'isian.provinsi', 'destinasiLainPilih'] as $ke) {
        expect($isi)->toContain('data-ke="'.$ke.'"');
    }
});

test('hanya pemilih destinasi yang menawarkan tombol buang', function () {
    /*
     | Yang dibuang di baris "tempat lain" adalah keanggotaan di tiket ini,
     | dan itu dikerjakan lewat tanda × pada pilnya. Tombol buang di dalam
     | jendelanya akan berarti membuang namanya dari KATALOG — dua perbuatan
     | berbeda yang kebetulan bersebelahan.
     |
     | Provinsi & vendor punya menunya sendiri, dan menghapus vendor dari
     | sini akan melewati penjaga yang hidup di layar itu.
     */
    $isi = sumberOrcha('rab.partials.master-harga-borang');

    expect(substr_count($isi, 'data-hapus-aksi='))->toBe(1)
        ->and($isi)->toContain('data-hapus-aksi="hapusDestinasiKatalog"');

    // Dan aksinya memang ada — nama metode yang salah ketik diam saja
    // sampai tombolnya ditekan.
    expect(method_exists(
        \App\Livewire\Pages\Admin\Orcha\Rab\OrchaMasterHargaForm::class,
        'hapusDestinasiKatalog'
    ))->toBeTrue();
});
