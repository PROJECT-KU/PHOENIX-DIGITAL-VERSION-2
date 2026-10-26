<?php

use App\Livewire\Pages\Admin\Orcha\Vendor\OrchaVendorList;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Daftar vendor di lemon.
 *
 * Yang dijaga di sini bukan rupa layarnya, melainkan satu janji: nomor vendor
 * hidup di SATU tempat. Dulu ia cuma muat di kolom catatan master harga —
 * teks bebas, tidak bisa dicari, dan tidak ikut tersalin ke RAB.
 */
function rekamanVendor(): array
{
    return [
        'data' => [
            ['id' => 1, 'nama' => 'PO Sumber Jaya', 'kontak' => '081234000111 (Pak Budi)',
                'jenis' => 'Armada', 'catatan' => 'Pool di Maguwo', 'aktif' => true,
                'jumlah_harga' => 3, 'diubah_pada' => '2026-10-27T08:00:00+07:00'],
            ['id' => 3, 'nama' => 'Vendor Tanpa Nomor', 'kontak' => null,
                'jenis' => null, 'catatan' => null, 'aktif' => true,
                'jumlah_harga' => 0, 'diubah_pada' => '2026-10-27T08:00:00+07:00'],
        ],
        'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 2, 'per_halaman' => 15],
    ];
}

function palsukanVendor(): void
{
    Http::fake(function (Request $r) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_contains($jalur, '/vendor') && $r->method() === 'POST' => Http::response(
                ['data' => rekamanVendor()['data'][0], 'pesan' => 'Vendor ditambahkan.'], 201),
            str_contains($jalur, '/vendor') && in_array($r->method(), ['PATCH', 'PUT'], true) => Http::response(
                ['data' => rekamanVendor()['data'][0], 'pesan' => 'Vendor diperbarui.']),
            str_contains($jalur, '/vendor') && $r->method() === 'DELETE' => Http::response(
                ['pesan' => 'Vendor dihapus. Harga yang memakainya tetap ada.']),
            str_contains($jalur, '/vendor') => Http::response(rekamanVendor()),
            default => Http::response(['data' => [], 'meta' => []]),
        };
    });
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rab.katalog');
});

test('halaman vendor tergambar dengan nomornya', function () {
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee('PO Sumber Jaya')
        ->assertSee('081234000111 (Pak Budi)')
        // Berapa harga yang akan kehilangan kontaknya bila dihapus — angka
        // yang perlu dilihat SEBELUM menekan hapus.
        ->assertSee('3 harga');
});

test('vendor tanpa nomor ditandai, bukan dibiarkan kosong', function () {
    /*
     | Vendor tanpa nomor adalah vendor yang tetap harus dicari saat
     | dibutuhkan. Sel kosong terbaca seperti "tidak apa-apa"; yang benar
     | adalah "ini belum selesai".
     */
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee('belum ada nomor');
});

test('vendor baru terkirim beserta kontaknya', function () {
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'PO Sumber Jaya')
        ->set('isian.kontak', '081234000111 (Pak Budi)')
        ->call('pakaiJenis', 'Armada')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor')
        && $r['nama'] === 'PO Sumber Jaya'
        && $r['kontak'] === '081234000111 (Pak Budi)'
        && $r['jenis'] === 'Armada');
});

test('nomor tidak dipaksa berformat angka', function () {
    /*
     | "0812xxx (Pak Budi)" justru bentuk yang paling berguna saat menelepon.
     | Memaksa format angka membuat yang paling berguna ditolak aplikasi.
     */
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Hotel Mawar')
        ->set('isian.kontak', '0274-555000 ext. 12, WA 0812-3456 (Mbak Rina)')
        ->call('simpan')
        ->assertHasNoErrors();
});

test('nama vendor tetap wajib', function () {
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.kontak', '08123456789')
        ->call('simpan')
        ->assertHasErrors('isian.nama');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor'));
});

test('jenis yang ditekan dua kali terlepas lagi', function () {
    // Tanpa ini, jenis yang salah tekan tidak punya cara dibatalkan.
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->call('pakaiJenis', 'Armada')
        ->assertSet('isian.jenis', 'Armada')
        ->call('pakaiJenis', 'Armada')
        ->assertSet('isian.jenis', '');
});

test('menyimpan vendor melupakan katalog RAB', function () {
    /*
     | Daftar vendor ikut disimpan di katalog RAB. Tanpa dilupakan, vendor
     | yang baru dibuat tidak muncul di kotak pilih master harga dan tampak
     | seperti gagal tersimpan.
     */
    palsukanVendor();
    cache()->put('orcha.rab.katalog', ['vendor' => [], 'destinasi' => []], now()->addMinutes(10));

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'PO Sumber Jaya')
        ->call('simpan');

    expect(cache()->get('orcha.rab.katalog'))->toBeNull();
});

test('peringatan hapus menyebut berapa harga yang kehilangan kontaknya', function () {
    // Angka itu yang membedakan "hapus saja" dari "tunggu dulu".
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee('3 harga akan kehilangan kontaknya', false);
});

test('layar vendor memakai kerangka dasbor lemon', function () {
    palsukanVendor();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/vendor')->assertOk()->getContent();

    foreach (['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-tabel', 'dsb-lencana'] as $k) {
        expect(str_contains($isi, $k))->toBeTrue("layar vendor tidak memakai {$k}");
    }
});
