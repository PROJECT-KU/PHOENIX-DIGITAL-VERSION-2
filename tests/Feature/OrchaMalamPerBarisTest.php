<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Kotak Malam/Hari per baris biaya, dan spanduk malam yang tidak tertutup.
 *
 * Perhitungannya milik Orcha; yang diuji di sini lemon mengirim ke medan
 * yang benar, membedakan kosong dari nol, dan MENGATAKAN saat akomodasinya
 * kurang semalam — kesalahan yang tidak punya satu pun angka salah.
 */
/**
 * Dasar baris biaya dari rekaman API Orcha yang sebenarnya.
 *
 * Bukan larik yang diketik tangan: baris RAB punya dua puluh lebih kunci,
 * dan yang terlewat membuat layarnya mati dengan "Undefined array key" —
 * merah yang menunjuk tampilan, bukan fitur yang sedang diuji.
 */
function dasarRabMalam(): array
{
    return json_decode(file_get_contents(base_path('tests/Fixtures/orcha-rab/show.json')), true)['data'];
}

function barisBiayaMalam(array $ubah = []): array
{
    return array_merge(dasarRabMalam()['ringkasan']['baris'][0], [
        'id' => 21,
        'kategori' => 'akomodasi',
        'kategori_label' => 'Akomodasi',
        'nama' => 'Homestay Rizkia Dieng',
        'satuan' => 'kamar_malam',
        'satuan_label' => 'per kamar per malam',
        'harga_satuan' => 800_000,
        'kapasitas' => 2,
        'unit_manual' => 2,
        'malam_manual' => null,
        'pakai_durasi' => true,
        'durasi_sebutan' => 'malam',
        'jumlah' => 1,
        'faktor' => 6,
        'penjelasan' => 'Rp 800.000 × 2 kamar × 3 malam',
        'subtotal' => 4_800_000,
        'subtotal_teks' => 'Rp 4.800.000',
        'harga_master_kini' => null,
        'selisih_master' => null,
    ], $ubah);
}

function palsukanRabMalam(array $baris, ?array $malamAkomodasi = null): void
{
    Http::fake(function (Request $r) use ($baris, $malamAkomodasi) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            return Http::response(
                json_decode(file_get_contents(base_path('tests/Fixtures/orcha-rab/katalog.json')), true)
            );
        }

        if (preg_match('#/rab/9$#', $jalur)) {
            $data = dasarRabMalam();
            $data['id'] = 9;
            $data['kode'] = 'RAB-2611-J2UX';
            $data['jumlah_hari'] = 4;
            $data['jumlah_malam'] = 3;
            $data['ringkasan']['baris'] = $baris;
            $data['ringkasan']['malam_akomodasi'] = $malamAkomodasi;

            return Http::response(['data' => $data]);
        }

        return Http::response(['data' => [], 'meta' => []]);
    });
}

test('kotak malam muncul untuk satuan yang dikali durasi', function () {
    palsukanRabMalam([barisBiayaMalam()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertSee('Malam')
        ->assertSee('malamBaris.21', false);
});

test('kotak malam tidak muncul untuk satuan yang tidak dikali durasi', function () {
    palsukanRabMalam([barisBiayaMalam([
        'satuan' => 'orang', 'satuan_label' => 'per orang',
        'pakai_durasi' => false, 'durasi_sebutan' => 'hari',
    ])]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertDontSee('malamBaris.21', false);
});

test('kotaknya berlabel Hari untuk armada', function () {
    palsukanRabMalam([barisBiayaMalam([
        'satuan' => 'unit_hari', 'satuan_label' => 'per unit per hari',
        'durasi_sebutan' => 'hari', 'nama' => 'Sewa Hiace',
    ])]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertSee('malamBaris.21', false);
});

test('kotak kosong untuk baris yang malamnya otomatis', function () {
    palsukanRabMalam([barisBiayaMalam(['malam_manual' => null])]);

    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 9]);

    /*
     | Diisi angka perjalanan, kotaknya akan terkirim sebagai 3 pada
     | suntingan berikutnya — lalu diam saat perjalanannya diperpanjang, dan
     | penawarannya kurang semalam.
     */
    expect($komponen->get('malamBaris')[21])->toBe('');
});

test('nol tetap terbaca nol, bukan kosong', function () {
    palsukanRabMalam([barisBiayaMalam(['malam_manual' => 0])]);

    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 9]);

    expect($komponen->get('malamBaris')[21])->toBe('0');
});

test('mengisi malam mengirimkannya ke Orcha', function () {
    palsukanRabMalam([barisBiayaMalam()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('malamBaris.21', '1');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        && $r['malam_manual'] === 1);
});

test('mengosongkan malam mengirim null, bukan nol', function () {
    palsukanRabMalam([barisBiayaMalam(['malam_manual' => 2])]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('malamBaris.21', '');

    /*
     | null = ikut panjang perjalanan; 0 = gratis. Mengirim 0 untuk kotak
     | yang dikosongkan akan menggratiskan hotelnya tanpa diminta.
     */
    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        && $r['malam_manual'] === null);
});

test('nol dikirim sebagai nol', function () {
    palsukanRabMalam([barisBiayaMalam()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('malamBaris.21', '0');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        && $r['malam_manual'] === 0);
});

test('spanduk menyebut malam yang tidak tertutup akomodasi', function () {
    palsukanRabMalam(
        [barisBiayaMalam(['malam_manual' => 1])],
        ['tertutup' => 2, 'perjalanan' => 3, 'cukup' => false],
    );

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertSee('baru menutup')
        ->assertSee('3 malam')
        ->assertSee('2 malam');
});

test('spanduk diam saat malamnya sudah tertutup', function () {
    palsukanRabMalam(
        [barisBiayaMalam(['malam_manual' => 3])],
        ['tertutup' => 3, 'perjalanan' => 3, 'cukup' => true],
    );

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertDontSee('baru menutup');
});

test('layar tidak mati saat Orcha belum mengirim malam_akomodasi', function () {
    /*
     | Orcha dan lemon tidak naik ke server pada detik yang sama. Kunci yang
     | belum ada harus membuat spanduknya absen, bukan halamannya 500.
     */
    palsukanRabMalam([barisBiayaMalam()], null);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertOk()
        ->assertDontSee('baru menutup');
});

test('layar tidak mati saat Orcha belum mengirim pakai_durasi', function () {
    $baris = barisBiayaMalam();
    unset($baris['pakai_durasi'], $baris['durasi_sebutan'], $baris['malam_manual']);

    palsukanRabMalam([$baris]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertOk()
        ->assertDontSee('malamBaris.21', false);
});
