<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabForm;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Harga open trip dipatok dari jumlah peserta yang MANA.
 *
 * Biaya rombongan — satu HiAce Rp 8.500.000 — dibagi ke jumlah ini, bukan ke
 * jumlah peserta yang terlihat di sebelahnya. Itu sebabnya open trip 13 orang
 * bisa menampilkan untung 99%: harganya dipatok untuk 5, lalu datang 13.
 *
 * Pilihannya SUDAH ada di basis data sejak awal (`harga_dasar`), diterima
 * Orcha, dan sudah dibaca balik oleh perulangan kunci borang. Yang tidak
 * pernah ada: cara memilihnya. Layar memaksanya 'minimum' untuk setiap open
 * trip, dan admin tidak punya jalan mengubahnya.
 *
 * Uji di bawah menjaga perilaku yang sudah benar itu agar tidak hilang saat
 * perulangan kunci borang disentuh — bukan menambal cacat yang sedang hidup.
 */
function rekamanDasar(array $ubah = []): array
{
    $data = json_decode(
        file_get_contents(base_path('tests/Fixtures/orcha-rab/show.json')), true
    )['data'];

    return ['data' => array_merge($data, $ubah)];
}

function palsukanDasar(array $ubah = []): void
{
    Http::fake(function (Request $r) use ($ubah) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            return Http::response(
                json_decode(file_get_contents(base_path('tests/Fixtures/orcha-rab/katalog.json')), true)
            );
        }

        return Http::response(rekamanDasar($ubah));
    });
}

test('dasar harga dibaca balik saat borang dibuka', function () {
    // RAB yang sengaja dipatok pada target peserta.
    palsukanDasar(['jenis' => 'open', 'harga_dasar' => 'peserta']);

    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabForm::class, ['rab' => 3]);

    expect($komponen->get('isian')['harga_dasar'])->toBe('peserta');
});

test('membuka lalu menyimpan tidak diam-diam mengembalikannya ke minimum', function () {
    palsukanDasar(['jenis' => 'open', 'harga_dasar' => 'peserta']);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabForm::class, ['rab' => 3])
        // Admin cuma membetulkan judulnya; tidak menyentuh harga sama sekali.
        ->set('isian.judul', 'Open Trip Dieng Nataru')
        ->call('simpan');

    /*
     | Kalau pembacaan baliknya hilang, nilainya tinggal bawaan 'minimum'
     | lalu terkirim — dan HARGA JUAL open trip ini berubah tanpa ada yang
     | menyentuh angka mana pun. Itu yang dijaga di sini.
     */
    // Menuntut PATCH-nya ADA dan benar. Bentuk "bukan PATCH ATAU benar"
    // hijau begitu saja karena permintaan GET pemuat borang ikut cocok.
    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && $r['harga_dasar'] === 'peserta');
});

test('pilihan dasar harga bisa diubah dan terkirim', function () {
    palsukanDasar(['jenis' => 'open', 'harga_dasar' => 'minimum']);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabForm::class, ['rab' => 3])
        ->set('isian.harga_dasar', 'peserta')
        ->call('simpan');

    // Menuntut PATCH-nya ADA dan benar. Bentuk "bukan PATCH ATAU benar"
    // hijau begitu saja karena permintaan GET pemuat borang ikut cocok.
    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && $r['harga_dasar'] === 'peserta');
});

test('private trip tetap dipatok dari jumlah pesertanya', function () {
    palsukanDasar(['jenis' => 'private', 'harga_dasar' => 'minimum']);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabForm::class, ['rab' => 3])
        ->call('simpan');

    /*
     | Rombongan private sudah pasti jumlahnya; "peserta minimum" tidak punya
     | arti di sana, dan kolom yang terisi tapi tak berarti akan dibaca
     | sebagai keadaan nyata kelak.
     */
    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && $r['harga_dasar'] === 'peserta' && $r['peserta_minimum'] === null);
});

test('pilihannya hanya muncul untuk open trip', function () {
    palsukanDasar(['jenis' => 'open']);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabForm::class, ['rab' => 3])
        ->assertSee('Harga dipatok dari')
        ->assertSee('Peserta minimum')
        ->assertSee('Target peserta');
});

test('pilihannya tidak muncul untuk private trip', function () {
    palsukanDasar(['jenis' => 'private']);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabForm::class, ['rab' => 3])
        ->assertDontSee('Harga dipatok dari');
});

test('keterangannya menyebut angka yang benar-benar dipakai membagi', function () {
    palsukanDasar(['jenis' => 'open', 'harga_dasar' => 'minimum', 'peserta_minimum' => 5]);

    /*
     | Yang membingungkan di layar RAB adalah angka mana yang membagi biaya
     | rombongan. Borangnya harus menyebutnya, bukan membiarkannya ditebak.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabForm::class, ['rab' => 3])
        ->assertSee('Biaya rombongan (armada, pemandu, sewa) dibagi ke jumlah ini.', false)
        ->assertSee('5 peserta minimum');
});
