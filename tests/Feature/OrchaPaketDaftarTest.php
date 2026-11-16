<?php

use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketList;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';
require_once __DIR__.'/OrchaPaketTampilanTest.php';

/**
 * Saringan, pengurutan, dan duplikat di daftar Paket Wisata.
 *
 * Ketiganya punya satu kesamaan: salah pasang tidak menimbulkan galat, hanya
 * daftar yang isinya lain daripada yang diminta — dan daftar yang terpotong
 * selalu terbaca sebagai data yang hilang.
 */
function palsukanDaftarPaket(array $baris = []): void
{
    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'kategori_paket' => ['open_trip' => 'Open Trip'],
            'status_tayang' => [
                'tayang' => 'Tayang', 'terjadwal' => 'Terjadwal',
                'berakhir' => 'Berakhir', 'draf' => 'Draf', 'arsip' => 'Arsip',
            ],
        ]]),
        '*/paket-wisata*' => Http::response(['data' => $baris, 'meta' => ['total' => count($baris)]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');
}

test('saringan keadaan diteruskan ke orcha, bukan disaring di sini', function () {
    palsukanDaftarPaket([barisPaketUji()]);

    /*
     | Menyaring sesudah data sampai berarti halaman kedua bisa kosong
     | padahal datanya ada: Orcha mengirim sepuluh baris per halaman, dan
     | membuang separuhnya di sini membuat penomorannya berbohong.
     */
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketList::class)
        ->set('filterTampil', 'draf');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/paket-wisata')
        && str_contains($r->url(), 'tampil=draf'));
});

test('pengurutan diteruskan ke orcha', function () {
    palsukanDaftarPaket([barisPaketUji()]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketList::class)
        ->set('urut', 'harga-termahal');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'urut=harga-termahal'));
});

test('mengganti saringan mengembalikan ke halaman pertama', function () {
    palsukanDaftarPaket([barisPaketUji()]);

    // Tanpa ini, menyaring dari halaman 3 memulangkan halaman 3 dari hasil
    // yang baru — yang biasanya kosong, dan terbaca sebagai "tidak ada".
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketList::class)
        ->set('halaman', 3)
        ->set('filterTampil', 'draf')
        ->assertSet('halaman', 1)
        ->set('halaman', 3)
        ->set('urut', 'nama')
        ->assertSet('halaman', 1);
});

test('cip tampil di website menyaring, dan menekannya lagi melepas saringan', function () {
    palsukanDaftarPaket([barisPaketUji()]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketList::class)
        ->call('saringTampil', 'tayang')
        ->assertSet('filterTampil', 'tayang')
        // Jalan keluarnya lewat tombol yang sama — tanpa itu satu-satunya
        // cara melepasnya adalah menebak di mana saringannya berada.
        ->call('saringTampil', 'tayang')
        ->assertSet('filterTampil', '');
});

test('bersihkan saringan mengosongkan keempatnya sekaligus', function () {
    palsukanDaftarPaket([barisPaketUji()]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketList::class)
        ->set('cari', 'ijen')
        ->set('filterStatus', 'open_trip')
        ->set('filterTampil', 'draf')
        ->set('urut', 'nama')
        ->call('bersihkanSaringan')
        ->assertSet('cari', '')
        ->assertSet('filterStatus', '')
        ->assertSet('filterTampil', '')
        ->assertSet('urut', '')
        ->assertSet('halaman', 1);
});

test('keadaan penayangan dan kategori adalah dua saringan terpisah', function () {
    palsukanDaftarPaket([barisPaketUji()]);

    // Dua saringan yang menumpang satu properti akan saling menghapus, dan
    // yang kalah hilang tanpa galat.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketList::class)
        ->set('filterStatus', 'open_trip')
        ->set('filterTampil', 'draf')
        ->assertSet('filterStatus', 'open_trip')
        ->assertSet('filterTampil', 'draf');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'kategori=open_trip')
        && str_contains($r->url(), 'tampil=draf'));
});

test('duplikat paket memanggil orcha dan dikonfirmasi lebih dulu', function () {
    palsukanDaftarPaket([barisPaketUji()]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketList::class)
        ->call('salin', 7)
        ->assertDispatched('order-updated');

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_contains($r->url(), '/paket-wisata/7/salin'));

    // Satu tekanan tak sengaja menambah paket baru ke daftar, jadi tombolnya
    // lewat pembungkus konfirmasi bersama — bukan wire:click telanjang.
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata')->assertOk()->getContent();

    expect($isi)
        ->toContain('data-action="salin"')
        ->toContain('Duplikat paket ini?')
        ->not->toContain('wire:click="salin(');
});

test('tombol lihat memakai tautan pratinjau, bukan alamat publik', function () {
    palsukanDaftarPaket([barisPaketUji([
        'status_tayang' => 'draf', 'status_tayang_label' => 'Draf',
        'tautan_pratinjau' => 'https://orchajourney.com/paket/uuid-7?signature=xyz',
    ])]);

    /*
     | Alamat publik menjawab 404 untuk paket draf, jadi tombol ini dulu mati
     | justru untuk paket yang paling perlu dilihat — yang belum selesai.
     */
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata')->assertOk()->getContent();

    $tabel = tabelPaket($isi);

    expect($tabel)
        ->toContain('signature=xyz')
        ->toContain('belum tampil di website');
});

test('paket dengan banyak tanggal ditandai di kolom jadwal', function () {
    palsukanDaftarPaket([barisPaketUji(['jumlah_keberangkatan' => 3])]);

    // Tanggal di kolom itu cuma yang pertama; tanpa penanda, admin mengira
    // dua rombongan lain belum dibuat.
    $tabel = tabelPaket(test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata')->assertOk()->getContent());

    expect($tabel)->toContain('3 tanggal');
});

test('paket bertanggal tunggal tidak ikut ditandai', function () {
    palsukanDaftarPaket([barisPaketUji(['jumlah_keberangkatan' => 1])]);

    $tabel = tabelPaket(test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata')->assertOk()->getContent());

    expect($tabel)->not->toContain('tanggal</span>');
});
