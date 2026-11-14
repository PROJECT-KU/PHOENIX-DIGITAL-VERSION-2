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

test('tiap aksi yang disebut pemicu pemilih memang ada metodenya', function () {
    /*
     | Nama metode yang salah ketik di data-* DIAM SAJA sampai tombolnya
     | ditekan — dan yang terjadi saat itu bukan galat, melainkan tidak ada
     | apa-apa. Jadi tiap nama aksi diperiksa di sini, bukan dipercaya.
     */
    $isi = sumberOrcha('rab.partials.master-harga-borang');
    $kelas = \App\Livewire\Pages\Admin\Orcha\Rab\OrchaMasterHargaForm::class;

    preg_match_all('/data-(?:hapus|sunting|tambah)-aksi="([a-zA-Z]+)"/', $isi, $cocok);

    expect($cocok[1])->not->toBeEmpty();

    foreach (array_unique($cocok[1]) as $aksi) {
        expect(method_exists($kelas, $aksi))->toBeTrue("Aksi '{$aksi}' disebut pemicu tetapi metodenya tidak ada.");
    }
});

test('destinasi bawaan tidak menawarkan pensil, hanya yang kustom', function () {
    /*
     | Pensil harus duduk di baris yang SAMA dengan tombol buang: destinasi
     | kustom. Destinasi bawaan hidup di katalog situs publik Orcha, dan
     | menggantinya dari borang harga berarti mengubah isi situs tanpa
     | disadari — tanpa galat, dan tanpa ada yang melihat.
     */
    $isi = sumberOrcha('rab.partials.master-harga-borang');

    expect($isi)->toContain("'sunting' => \$b['katalog_id'] ?? null,")
        ->and($isi)->toContain("'hapus' => \$b['katalog_id'] ?? null,");
});

test('provinsi hanya boleh diketik, tidak disunting atau dibuang', function () {
    // Provinsi tidak punya rekaman sendiri: daftarnya diturunkan dari yang
    // sudah dipakai. Tombol ubah di sana akan menjanjikan sesuatu yang tidak
    // ada barisnya.
    $isi = sumberOrcha('rab.partials.master-harga-borang');

    $blokProvinsi = substr($isi, strpos($isi, 'data-judul="Pilih Provinsi"'));
    $blokProvinsi = substr($blokProvinsi, 0, strpos($blokProvinsi, '</button>'));

    expect($blokProvinsi)->toContain('data-ketik-baru="1"')
        ->and($blokProvinsi)->not->toContain('data-sunting-aksi')
        ->and($blokProvinsi)->not->toContain('data-hapus-aksi');
});

test('menambah destinasi & vendor dikerjakan di dalam jendela, bukan tombol terpisah', function () {
    /*
     | Yang sedang dicari admin saat ia menyadari namanya belum ada adalah
     | DAFTARNYA. Menutup jendela dulu untuk menekan tombol lain di bawah
     | kotak pilih berarti kehilangan tempatnya, dan dua jalan menuju satu
     | perbuatan membuat yang satu tertinggal saat yang lain disentuh.
     */
    $isi = sumberOrcha('rab.partials.master-harga-borang');

    expect($isi)->toContain('data-tambah-aksi="tambahDestinasiDariPopup"')
        ->and($isi)->toContain('data-tambah-aksi="tambahVendorDariPopup"')
        // Tombol lama di luar jendela sudah tidak ada.
        ->and($isi)->not->toContain("\$set('destinasiBaru', true)")
        ->and($isi)->not->toContain("\$set('vendorBaru', true)");
});
