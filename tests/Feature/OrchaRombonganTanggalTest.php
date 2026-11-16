<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaDaftarkanRombongan;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';

/**
 * Rombongan yang didaftarkan admin harus menyebut TANGGALNYA.
 *
 * Tanpa itu Orcha menaruhnya di keberangkatan terdekat — pilihan yang
 * kelihatan masuk akal dan karena itu tidak pernah dipertanyakan siapa pun.
 * Rombongan yang sudah sepakat 11–13 Desember lewat WhatsApp tercatat di
 * 27–29 November, dan tanggal yang salah itu mengalir ke kuota, batas
 * pelunasan, serta surat konfirmasinya.
 */
function palsukanRujukanPaket(array $keberangkatan): void
{
    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'paket_wisata' => [[
                'id' => 7, 'nama' => 'Open Trip Banyuwangi', 'kategori' => 'open_trip',
                'tanggal_berangkat' => '2026-11-27',
                'keberangkatan' => $keberangkatan,
            ]],
            'status_pendaftaran' => ['baru' => 'Baru', 'dp_masuk' => 'DP Masuk'],
            'kategori_paket' => ['open_trip' => 'Open Trip'],
        ]]),
        // 'data' berupa SENARAI, bukan satu objek: daftar pendaftaran
        // menelusurinya per baris, dan objek tunggal membuatnya membaca
        // huruf demi huruf — "Cannot access offset of type string on string".
        '*/pendaftaran*' => Http::response(['data' => [], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');
}

/** @return array<int, array<string, mixed>> */
function tigaRombongan(): array
{
    return [
        ['id' => 1, 'rentang_tanggal' => '27 – 29 November 2026', 'tanggal_berangkat' => '2026-11-27',
         'aktif' => true, 'penuh' => false, 'sisa_kursi' => 15],
        ['id' => 2, 'rentang_tanggal' => '4 – 6 Desember 2026', 'tanggal_berangkat' => '2026-12-04',
         'aktif' => true, 'penuh' => true, 'sisa_kursi' => 0],
        ['id' => 3, 'rentang_tanggal' => '11 – 13 Desember 2026', 'tanggal_berangkat' => '2026-12-11',
         'aktif' => true, 'penuh' => false, 'sisa_kursi' => 20],
    ];
}

/* =================== DAFTARKAN ROMBONGAN =================== */

test('tanggal keberangkatan ikut terkirim saat admin mendaftarkan rombongan', function () {
    palsukanRujukanPaket(tigaRombongan());

    Livewire::actingAs(adminPeserta())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('paketId', '7')
        ->set('keberangkatanId', '3')
        ->set('nama', 'Bu Rina')
        ->set('whatsapp', '081234567890')
        ->set('jumlahPeserta', 20)
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_contains($r->url(), '/pendaftaran')
        && ($r->data()['keberangkatan_id'] ?? null) === 3);
});

test('paket bertanggal banyak menolak disimpan tanpa memilih tanggal', function () {
    palsukanRujukanPaket(tigaRombongan());

    // Dibiarkan kosong, Orcha menaruhnya di rombongan terdekat tanpa bertanya.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('paketId', '7')
        ->set('keberangkatanId', '')
        ->set('nama', 'Bu Rina')
        ->set('whatsapp', '081234567890')
        ->set('jumlahPeserta', 20)
        ->call('simpan')
        ->assertHasErrors(['keberangkatanId']);
});

test('paket bertanggal satu terisi sendiri, tidak ditanyakan', function () {
    // Memilih dari daftar berisi satu pilihan adalah pekerjaan yang tidak
    // menambah apa pun.
    palsukanRujukanPaket([tigaRombongan()[0]]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('paketId', '7')
        ->assertSet('keberangkatanId', '1');
});

test('ganti paket membuang rombongan milik paket sebelumnya', function () {
    /*
     | Tanpa ini, id rombongan milik paket lama ikut terkirim dan Orcha
     | menolaknya dengan "bukan milik paket ini" — galat yang tidak ada
     | hubungannya dengan apa yang baru saja dilakukan admin.
     */
    palsukanRujukanPaket(tigaRombongan());

    $uji = Livewire::actingAs(adminPeserta())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('paketId', '7')
        ->set('keberangkatanId', '3');

    // Paket yang tidak ada di rujukan: daftarnya kosong, pilihannya dibuang.
    $uji->set('paketId', '99')->assertSet('keberangkatanId', '');
});

test('pilihan tanggal menyebut sisa kursinya', function () {
    palsukanRujukanPaket(tigaRombongan());

    // Admin memilih tanggal sambil tahu mana yang masih muat — tanpa itu ia
    // menyimpan dulu, lalu ditolak Orcha karena penuh.
    $isi = Livewire::actingAs(adminPeserta())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('paketId', '7')
        ->html();

    expect($isi)
        ->toContain('11 – 13 Desember 2026')
        ->toContain('sisa 20 kursi')
        ->toContain('penuh');
});

/* =================== SARINGAN DAFTAR =================== */

test('daftar pendaftaran bisa disaring per rombongan', function () {
    palsukanRujukanPaket(tigaRombongan());

    /*
     | Manifes tour leader dibentuk dari daftar yang sedang tampil. Saringan
     | per paket membuatnya memuat tiga rombongan sekaligus, dan kelebihannya
     | baru ketahuan di parkiran.
     */
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '7')
        ->set('filterKeberangkatan', '3');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/pendaftaran')
        && str_contains($r->url(), 'keberangkatan_id=3'));
});

test('ganti paket melepas saringan rombongan paket sebelumnya', function () {
    palsukanRujukanPaket(tigaRombongan());

    // "Paket A + rombongan milik paket B" memulangkan daftar kosong, yang
    // terbaca sebagai data yang hilang.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '7')
        ->set('filterKeberangkatan', '3')
        ->set('filterPaket', '9')
        ->assertSet('filterKeberangkatan', '');
});

test('saringan rombongan ikut ke tautan manifes', function () {
    palsukanRujukanPaket(tigaRombongan());

    // Lembar yang dicetak harus memuat rombongan yang sama dengan yang
    // sedang dilihat di layar.
    $uji = Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '7')
        ->set('filterKeberangkatan', '3');

    expect($uji->instance()->saringanTampil())
        ->toHaveKey('keberangkatan_id')
        ->and($uji->instance()->saringanTampil()['keberangkatan_id'])->toBe('3');
});

test('bersihkan saringan ikut melepas rombongan', function () {
    palsukanRujukanPaket(tigaRombongan());

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '7')
        ->set('filterKeberangkatan', '3')
        ->call('bersihkanSaringan')
        ->assertSet('filterKeberangkatan', '')
        ->assertSet('filterPaket', '');
});
