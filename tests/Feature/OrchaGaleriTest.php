<?php

use App\Livewire\Pages\Admin\Orcha\Galeri\OrchaGaleriList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';

function palsukanGaleri(array $daftar = []): void
{
    Http::fake([
        '*/galeri*' => Http::response(['data' => $daftar]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);
}

function fotoGaleri(int $id, array $ubah = []): array
{
    return array_merge([
        'id' => $id, 'foto' => "https://orcha.test/storage/galeri/{$id}.webp",
        'keterangan' => null, 'urutan' => $id, 'tampil' => true,
    ], $ubah);
}

test('galeri kosong menjelaskan apa yang terjadi di beranda', function () {
    palsukanGaleri();

    // Beranda tidak pernah tampak kosong karena jatuh ke foto destinasi.
    // Admin yang tidak diberi tahu akan mengira galerinya sudah terisi.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/galeri')
        ->assertOk()
        ->assertSee('Belum ada foto di galeri')
        ->assertSee('beranda memakai foto destinasi sebagai penggantinya');
});

test('foto tampil sebagai petak, lengkap dengan nomor urutnya', function () {
    palsukanGaleri([
        fotoGaleri(1, ['keterangan' => 'Rombongan di Kawah Ijen', 'urutan' => 1]),
        fotoGaleri(2, ['urutan' => 2]),
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/galeri')
        ->assertOk()
        ->assertSee('Rombongan di Kawah Ijen')
        // Yang tanpa keterangan tidak dibiarkan kosong melompong.
        ->assertSee('Tanpa keterangan')
        ->assertSee('2 dari 2 foto tampil');
});

test('foto yang disembunyikan ditandai, bukan dihilangkan dari layar admin', function () {
    palsukanGaleri([
        fotoGaleri(1),
        fotoGaleri(2, ['tampil' => false]),
    ]);

    // Admin tetap perlu melihatnya untuk memutuskan ditampilkan lagi atau
    // dihapus.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/galeri')
        ->assertOk()
        ->assertSee('Disembunyikan')
        ->assertSee('1 dari 2 foto tampil');
});

test('banyak foto bisa diunggah sekaligus', function () {
    palsukanGaleri();

    /*
     | Inilah bedanya dengan formulir etalase yang lain: sepulang trip admin
     | memegang selusin foto. Mengunggahnya satu per satu lewat jendela berarti
     | dua belas kali buka-isi-simpan, dan yang paling sering terjadi bukan
     | admin mengeluh melainkan admin berhenti di foto kelima.
     */
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->set('fotoBaru', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
            UploadedFile::fake()->image('c.jpg'),
        ])
        ->call('unggah')
        ->assertDispatched('order-updated',
            fn ($nama, $data) => str_contains($data['message'], '3 foto ditambahkan'));

    $terkirim = 0;
    Http::assertSent(function ($p) use (&$terkirim) {
        if ($p->method() === 'POST' && str_contains($p->url(), '/galeri')) {
            $terkirim++;
        }

        return true;
    });

    expect($terkirim)->toBe(3);
});

test('sebagian gagal disebut jumlahnya, bukan dianggap berhasil semua', function () {
    Http::fake([
        // Satu-satunya jalur POST gagal; GET daftarnya tetap jalan.
        '*/galeri' => Http::sequence()
            ->push(['data' => []])
            ->push('', 500)
            ->push(['pesan' => 'ok'], 201),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Admin berhak tahu persis berapa yang perlu diulang, bukan menghitung
    // ulang sendiri dari daftar setelah halaman digambar ulang.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->set('fotoBaru', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
        ])
        ->call('unggah')
        ->assertDispatched('order-updated',
            fn ($nama, $data) => str_contains($data['message'], 'gagal'));
});

test('berkas selain gambar ditolak sebelum dikirim', function () {
    palsukanGaleri();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->set('fotoBaru', [UploadedFile::fake()->create('catatan.txt', 10)])
        ->assertHasErrors('fotoBaru.*');
});

test('menyembunyikan foto tidak menghapus berkasnya', function () {
    palsukanGaleri([fotoGaleri(1)]);

    // Yang diminta cuma "jangan tampil dulu"; menghapusnya berarti kehilangan
    // berkasnya.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->call('balikTampil', fotoGaleri(1))
        ->assertDispatched('order-updated');

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/galeri/1')
        && $p['tampil'] === false);
});

test('keterangan dan urutan bisa diubah tanpa mengganti fotonya', function () {
    palsukanGaleri([fotoGaleri(1)]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->call('ubah', fotoGaleri(1))
        ->set('keterangan', 'Rombongan SMA 1')
        ->set('urutan', 3)
        ->call('simpan')
        ->assertSet('sedangDiubah', null);

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/galeri/1')
        && $p['keterangan'] === 'Rombongan SMA 1'
        && (int) $p['urutan'] === 3);
});
