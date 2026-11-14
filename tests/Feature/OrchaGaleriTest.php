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
        'fokus_x' => 50, 'fokus_y' => 50,
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
        // Hitungannya kini dua cip terpisah di kepala halaman: berapa yang
        // dipajang, dan berapa yang ditahan. Saat tidak ada yang ditahan, cip
        // keduanya memang tidak digambar.
        //
        // Yang diperiksa bunyi cipnya ("0 disembunyikan"), BUKAN kata
        // "disembunyikan" saja: gaya layar ini ditulis inline di blade, dan
        // komentar di dalamnya ikut terkirim sebagai isi respons — asersi atas
        // kata umum akan merah tanpa ada yang salah di layarnya.
        ->assertSee('2 tampil di beranda')
        ->assertDontSee('0 disembunyikan')
        ->assertSee('2 foto');
});

test('hapus foto memakai konfirmasi SweetAlert, bukan dialog bawaan peramban', function () {
    palsukanGaleri([fotoGaleri(1, ['keterangan' => 'Kawah Ijen'])]);

    /*
     | Dialog bawaan peramban menampilkan "127.0.0.1:8001 says" di atas
     | kalimatnya — terbaca seperti peringatan sistem yang bocor, bukan bagian
     | dari aplikasi. Pola .pcek-konfirmasi sudah dipakai halaman Orcha lain.
     |
     | Konfirmasinya sendiri BUKAN pengaman: penghapusan tetap diperiksa server,
     | dan skrip yang tidak sempat jalan hanya membuat tombolnya langsung
     | bekerja — bukan membuka pintu bagi yang tidak berhak.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/galeri')->assertOk()->getContent();

    expect($isi)
        ->toContain('pcek-konfirmasi')
        ->toContain('data-action="hapus"')
        ->toContain('berkasnya ikut terhapus dari server')
        // Dialog bawaan tidak boleh tersisa di halaman ini.
        ->not->toContain('wire:confirm');
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
        ->assertSee('1 tampil di beranda')
        ->assertSee('1 disembunyikan');
});

test('foto ditampilkan lewat alamat Orcha, bukan alamat lemon', function () {
    palsukanGaleri([fotoGaleri(1)]);

    /*
     | Foto disimpan di Orcha, dan API mengirim jalur relatif
     | "/storage/galeri/x.webp". Memasangnya apa adanya membuat peramban
     | mencarinya di alamat lemon — hasilnya kotak gambar rusak, dan admin tidak
     | punya petunjuk apa pun tentang sebabnya.
     */
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/galeri')
        ->assertOk()
        ->assertSee('src="https://orcha.test/storage/galeri/1.webp"', false);
});

test('tiap foto boleh punya keterangan sendiri dalam satu unggahan', function () {
    palsukanGaleri();

    /*
     | Satu keterangan untuk seluruh unggahan benar selama fotonya serombongan
     | dari acara yang sama — dan salah begitu admin memilih foto dari beberapa
     | trip sekaligus. Yang terjadi kalau dipaksa satu bukan admin mengeluh,
     | melainkan keterangannya diisi asal-asalan lalu tidak pernah dibetulkan.
     */
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->set('fotoBaru', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
        ])
        ->set('keteranganPer.0', 'Kawah Ijen')
        ->set('keteranganPer.1', 'Pantai Pulau Merah')
        ->set('tampilBaru', false)
        ->call('unggah')
        ->assertDispatched('order-updated')
        // Isiannya dikosongkan setelah terkirim, supaya unggahan berikutnya
        // tidak diam-diam mewarisi keterangan rombongan sebelumnya.
        ->assertSet('keteranganPer', [])
        ->assertSet('tampilBaru', true);

    $terkirim = [];
    Http::assertSent(function ($p) use (&$terkirim) {
        if ($p->method() === 'POST' && str_contains($p->url(), '/galeri')) {
            $isi = collect($p->data());
            $terkirim[] = [
                $isi->firstWhere('name', 'keterangan')['contents'] ?? null,
                $isi->firstWhere('name', 'tampil')['contents'] ?? null,
            ];
        }

        return true;
    });

    expect($terkirim)->toBe([['Kawah Ijen', '0'], ['Pantai Pulau Merah', '0']]);
});

test('satu isian bisa menyamakan keterangan seluruh foto', function () {
    palsukanGaleri();

    // Foto serombongan dari acara yang sama tetap terlayani: mengetik dua belas
    // kali kalimat yang sama adalah pekerjaan yang tidak menghasilkan apa pun.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->set('fotoBaru', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
            UploadedFile::fake()->image('c.jpg'),
        ])
        ->set('keteranganBaru', 'Rombongan SMA 1 di Kawah Ijen')
        ->call('samakanKeterangan')
        ->assertSet('keteranganPer', [
            'Rombongan SMA 1 di Kawah Ijen',
            'Rombongan SMA 1 di Kawah Ijen',
            'Rombongan SMA 1 di Kawah Ijen',
        ]);
});

test('memilih foto menyiapkan tempat keterangan sebanyak fotonya', function () {
    palsukanGaleri();

    // Larik yang tumbuh sambil diketik membuat Livewire kehilangan jejak baris
    // mana milik foto mana.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->set('fotoBaru', [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.jpg'),
        ])
        ->assertCount('keteranganPer', 2);
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

test('foto besar TIDAK ditolak: ukurannya diurus Orcha, bukan dijegal di sini', function () {
    palsukanGaleri();

    /*
     | Apa pun yang lolos dari sini diubah Orcha jadi WebP dengan sisi
     | terpanjang 1920px, jadi foto kamera ponsel belasan MB tersimpan sebagai
     | beberapa ratus KB. Batas 4 MB yang dulu dipasang hanya memindahkan
     | pekerjaan mengecilkan foto ke admin, untuk hasil yang sama persis — dan
     | yang benar-benar terjadi bukan admin mengecilkan foto, melainkan
     | fotonya tidak jadi diunggah.
     */
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->set('fotoBaru', [UploadedFile::fake()->image('ijen.jpg')->size(12288)])
        ->assertHasNoErrors()
        ->call('unggah')
        ->assertHasNoErrors()
        ->assertDispatched('order-updated');

    Http::assertSent(fn ($p) => $p->method() === 'POST' && str_ends_with($p->url(), '/galeri'));
});

test('unggahan diberi waktu lebih lama daripada permintaan biasa', function () {
    /*
     | Sepuluh detik benar untuk permintaan berisi teks — halaman yang
     | menggantung lebih buruk daripada halaman yang berterus terang. Untuk
     | unggahan ia salah: foto belasan MB tidak selesai terkirim dalam sepuluh
     | detik di jaringan biasa, dan yang dilihat admin bukan "terlalu besar"
     | melainkan "server tidak bisa dihubungi" — pesan yang membuatnya
     | menyalahkan jaringan lalu mencoba lagi dan lagi.
     */
    expect((int) config('orcha.timeout_unggah'))
        ->toBeGreaterThan((int) config('orcha.timeout'));
});

test('potongan di admin memakai titik fokus yang tersimpan', function () {
    palsukanGaleri([fotoGaleri(1, ['fokus_x' => 30, 'fokus_y' => 80])]);

    /*
     | Admin harus melihat potongan yang PERSIS akan dilihat pengunjung.
     | Kalau ubinnya selalu dipotong dari tengah sementara beranda memakai
     | titik fokus, admin menilai fotonya dari bagian yang ternyata tidak
     | terpajang — dan baru tahu setelah membuka berandanya sendiri.
     */
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/galeri')
        ->assertOk()
        ->assertSee('object-position: 30% 80%', false);
});

test('penggeser titik fokus benar-benar tergambar saat menyunting', function () {
    palsukanGaleri([fotoGaleri(1, ['fokus_x' => 30, 'fokus_y' => 80])]);

    /*
     | Penggesernya memakai @entangle, yang HANYA bisa dikompilasi di dalam
     | komponen Livewire. Merendernya lewat view() biasa melempar "Undefined
     | variable $__livewire" — jadi cabang formulir sunting ini tidak akan
     | pernah teruji oleh pemeriksaan Blade biasa, dan galatnya baru muncul
     | saat admin menekan tombol pensil di produksi.
     */
    $isi = Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->call('ubah', fotoGaleri(1, ['fokus_x' => 30, 'fokus_y' => 80]))
        ->html();

    expect($isi)->toContain('gal-fokus-kotak')
        ->toContain('pointerdown')
        // Nilai tersimpan jadi titik awal penggeser, bukan 50/50.
        ->toContain('30')
        ->toContain('80');
});

test('titik fokus ikut terkirim saat foto disimpan', function () {
    palsukanGaleri([fotoGaleri(1)]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->call('ubah', fotoGaleri(1))
        ->set('fokusX', 25)
        ->set('fokusY', 75)
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/galeri/1')
        && (int) $p['fokus_x'] === 25
        && (int) $p['fokus_y'] === 75);
});

test('menyembunyikan foto tidak menggeser titik fokusnya', function () {
    palsukanGaleri([fotoGaleri(1, ['fokus_x' => 10, 'fokus_y' => 90])]);

    // Jalur balikTampil mengirim ulang SELURUH baris ke Orcha. Kalau titik
    // fokusnya tidak ikut, menyembunyikan satu foto diam-diam mengembalikan
    // potongannya ke tengah — tanpa galat, dan tanpa admin menyadarinya.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->call('balikTampil', fotoGaleri(1, ['fokus_x' => 10, 'fokus_y' => 90]));

    Http::assertSent(fn ($p) => str_contains($p->url(), '/galeri/1')
        && (int) $p['fokus_x'] === 10
        && (int) $p['fokus_y'] === 90);
});

test('titik fokus di luar 0-100 ditolak sebelum dikirim', function () {
    palsukanGaleri([fotoGaleri(1)]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaGaleriList::class)
        ->call('ubah', fotoGaleri(1))
        ->set('fokusY', 140)
        ->call('simpan')
        ->assertHasErrors('fokusY');
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
