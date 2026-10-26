<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaMasterHargaList;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabList;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Layar RAB & master harga Orcha di lemon.
 *
 * Hitungannya milik Orcha. Yang diuji di sini: lemon mengirim perubahan ke
 * jalur yang benar, menggambar ulang dari JAWABAN Orcha (bukan hitungan
 * sendiri), dan tidak pernah menukar PDF internal dengan penawaran.
 *
 * Balasan palsunya direkam dari API Orcha yang sebenarnya
 * (tests/Fixtures/orcha-rab), supaya bentuknya tidak bisa melenceng diam-diam.
 */
function adminRab(array $izin = ['akses_orcha']): User
{
    $role = Role::create(['name' => 'uji-rab-'.uniqid(), 'description' => 'Peran untuk uji RAB']);

    foreach ($izin as $nama) {
        $permission = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );
        $role->permissions()->attach($permission->id);
    }

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id,
        'jabatan' => 'Admin Uji',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function rekamanRab(string $nama): array
{
    return json_decode(file_get_contents(base_path("tests/Fixtures/orcha-rab/{$nama}.json")), true);
}

function palsukanRab(): void
{
    Http::fake(function (Request $r) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_contains($jalur, '/rab/katalog') => Http::response(rekamanRab('katalog')),
            str_ends_with($jalur, '/rab/3/tarik-biaya') => Http::response(rekamanRab('tarik')),
            str_ends_with($jalur, '/rab/3/pdf') => Http::response('%PDF-1.7 palsu', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="rab.pdf"',
            ]),
            str_contains($jalur, '/rab/3') => Http::response(rekamanRab('show')),
            str_ends_with($jalur, '/rab') && $r->method() === 'POST' => Http::response(rekamanRab('show'), 201),
            str_ends_with($jalur, '/rab') => Http::response(rekamanRab('daftar')),
            str_contains($jalur, '/master-harga') => Http::response(rekamanRab('master')),
            default => Http::response(['data' => [], 'meta' => []]),
        };
    });
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
    cache()->forget('orcha.rab.katalog');
    cache()->forget('orcha.perlu-ditindak');
});

test('ketiga layar tertutup tanpa permission akses orcha', function () {
    Http::fake();
    $admin = adminRab([]);

    foreach (['/admin/orcha/rab', '/admin/orcha/rab/3', '/admin/orcha/master-harga', '/admin/orcha/rab/3/pdf'] as $alamat) {
        $this->actingAs($admin)->get($alamat)->assertForbidden();
    }
});

test('daftar RAB menampilkan harga dan untungnya', function () {
    palsukanRab();

    $this->actingAs(adminRab())->get('/admin/orcha/rab')
        ->assertOk()
        ->assertSee('RAB & Itinerary')
        ->assertSee('Jogja Heritage 3H2M')
        ->assertSee(rekamanRab('daftar')['data'][0]['harga_total_teks']);
});

test('RAB baru dikirim lalu admin dibawa ke penyusunnya', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaRabList::class)
        ->call('bukaTambah')
        ->set('isian.judul', 'Jogja Heritage 3H2M')
        ->set('isian.nama_pelanggan', 'Keluarga Hendra')
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->set('isian.jumlah_hari', 4)
        ->assertSet('isian.jumlah_malam', 3)
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertDispatched('orcha-sukses-pindah', url: route('admin.orcha.rab.susun', 3));

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with($r->url(), '/rab')
        && $r['provinsi'] === 'DI Yogyakarta'
        && $r['jumlah_hari'] === 4
        && $r['margin_jenis'] === 'persen');
});

test('malam lebih banyak dari hari ditahan sebelum sampai ke Orcha', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaRabList::class)
        ->call('bukaTambah')
        ->set('isian.judul', 'Trip')
        ->set('isian.nama_pelanggan', 'Bu Siti')
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->set('isian.jumlah_malam', 5)
        ->call('simpan')
        ->assertHasErrors('isian.jumlah_malam');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST');
});

test('penyusun menggambar itinerary, destinasi, dan ringkasan dari Orcha', function () {
    palsukanRab();
    $show = rekamanRab('show')['data'];

    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')
        ->assertOk()
        ->assertSee($show['kode'])
        ->assertSee('Candi Prambanan')
        ->assertSee($show['ringkasan']['harga_per_orang_teks'])
        ->assertSee('pakai harga baru')          // harga master sudah naik
        // Tombolnya kini jaring pengaman, bukan langkah wajib: biayanya
        // ditarik sendiri saat destinasinya diklik. Labelnya dipendekkan
        // supaya bilah atas muat satu baris; maksud lengkapnya ada di title.
        ->assertSee('Tarik ulang');
});

test('klik destinasi menyimpan itinerary utuh ke hari yang aktif', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaRabSusun::class, ['rab' => 3])
        ->call('pilihHari', 2)
        ->call('tambahDestinasi', 'Pantai Parangtritis');

    Http::assertSent(function (Request $r) {
        if ($r->method() !== 'PATCH' || ! str_ends_with($r->url(), '/rab/3/itinerary')) {
            return false;
        }

        $baris = collect($r['itinerary']);

        // Yang lama ikut terkirim (penggantian utuh), yang baru di hari 2
        // SESUDAH kegiatan hari 2 yang sudah ada.
        // Rekamannya kini berisi tiga kegiatan (termasuk satu ruas
        // perjalanan, supaya warna per jenis bisa diuji), jadi yang terkirim
        // empat: tiga lama ditambah yang baru.
        return $baris->count() === 4
            && $baris->last()['nama'] === 'Pantai Parangtritis'
            && $baris->last()['hari_ke'] === 2
            && $baris->last()['destinasi'] === 'Pantai Parangtritis'
            && $baris->first()['nama'] === 'Candi Prambanan';
    });
});

test('memperpendek perjalanan ditolak bila masih ada kegiatan di hari yang hilang', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaRabSusun::class, ['rab' => 3])
        ->set('kepala.jumlah_hari', 1)
        ->set('kepala.jumlah_malam', 0)
        ->call('simpanKepala')
        ->assertHasErrors('kepala.jumlah_hari');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'PATCH' && str_ends_with($r->url(), '/rab/3'));
});

test('harga baris disunting dikirim sebagai angka, bukan teks bertitik', function () {
    palsukanRab();
    $this->actingAs(adminRab());
    $id = rekamanRab('show')['data']['ringkasan']['baris'][0]['id'];

    Livewire::test(OrchaRabSusun::class, ['rab' => 3])
        ->set("hargaBaris.{$id}", '65.000');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_ends_with($r->url(), "/rab/3/biaya/{$id}")
        && $r['harga_satuan'] === 65000);
});

test('margin persen dan nominal dikirim dengan satuannya masing-masing', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    $layar = Livewire::test(OrchaRabSusun::class, ['rab' => 3])
        ->set('marginNilai', '25');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && str_ends_with($r->url(), '/rab/3')
        && $r['margin_jenis'] === 'persen' && $r['margin_nilai'] === 25);

    // Pindah jenis mengosongkan angka lama — "25" persen tidak boleh
    // berubah jadi Rp 25 per orang.
    $layar->set('marginJenis', 'per_orang')->assertSet('marginNilai', '')
        ->set('marginNilai', '150.000');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && str_ends_with($r->url(), '/rab/3')
        && $r['margin_jenis'] === 'per_orang' && $r['margin_nilai'] === 150000);
});

test('tarik biaya menyebut destinasi yang belum punya harga tiket', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaRabSusun::class, ['rab' => 3])
        ->call('tarikBiaya')
        ->assertSet('tanpaTiket', ['Malioboro'])
        ->assertSee('Belum ada harga tiket di master untuk');
});

test('PDF internal hanya bila diminta tegas; selain itu penawaran', function () {
    palsukanRab();
    $admin = adminRab();

    $this->actingAs($admin)->get('/admin/orcha/rab/3/pdf?jenis=internal')->assertOk();
    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/rab/3/pdf') && $r['jenis'] === 'internal');

    $this->actingAs($admin)->get('/admin/orcha/rab/3/pdf?jenis=ngawur')->assertOk();
    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/rab/3/pdf') && $r['jenis'] === 'penawaran');
});

test('kategori mengisi satuannya sendiri', function () {
    /*
     | Tiket dihitung per orang, bus per unit per hari, hotel per kamar per
     | malam — pasangan yang nyaris tidak pernah berbeda.
     |
     | Kapasitasnya TIDAK ikut ditebak. Transportasi bisa mobil pribadi, Elf,
     | atau bus besar; angka yang salah di sana tidak pernah terlihat salah,
     | karena yang meleset cuma jumlah unitnya.
     */
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->assertSet('isian.satuan', 'orang')            // bawaan: tiket
        ->set('isian.kategori', 'transportasi')
        ->assertSet('isian.satuan', 'unit_hari')
        ->assertSet('isian.kapasitas', '')
        ->set('isian.kategori', 'akomodasi')
        ->assertSet('isian.satuan', 'kamar_malam');
});

test('kapasitas tidak ditebak, tetapi dipilih dari daftar bernama', function () {
    /*
     | Satu klik, dan yang diklik mengatakan angka itu artinya apa. "14"
     | telanjang tidak menjawab pertanyaan yang sebenarnya ada di kepala —
     | ini muatan satu kendaraan, atau jumlah kendaraannya?
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'transportasi');

    // Transportasi bukan cuma Hiace.
    expect($layar->html())->toContain('Mobil pribadi')
        ->and($layar->html())->toContain('Hiace')
        ->and($layar->html())->toContain('Bus besar')
        // Kata yang memisahkan dua angka yang selama ini tertukar.
        ->and($layar->html())->toContain('bukan jumlah unit-nya');

    $layar->call('pakaiKapasitas', 59)->assertSet('isian.kapasitas', '59');

    // Kalimatnya ikut angkanya, bukan contoh mati yang selalu menyebut 14.
    expect($layar->html())->toContain('Satu unit muat <strong>59 orang</strong>')
        ->and($layar->html())->toContain('60 orang sudah butuh 2 unit');
});

test('kamar hotel memakai sebutan & pilihannya sendiri', function () {
    // "14" benar untuk Hiace dan omong kosong untuk kamar hotel.
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'akomodasi')
        ->call('pakaiKapasitas', 2)
        ->html();

    expect($isi)->toContain('Muat berapa orang per kamar?')
        ->and($isi)->toContain('Twin / Double')
        ->and($isi)->toContain('3 orang sudah butuh 2 kamar')
        // Pilihan kendaraan tidak ikut muncul. Diperiksa lewat nama yang
        // hanya ada di markah chip-nya — "Hiace" juga tertulis di komentar
        // CSS blok <style>, dan komentar CSS ikut terkirim ke peramban.
        ->and($isi)->not->toContain('Bus besar')
        ->and($isi)->not->toContain('Elf long');
});

test('ganti satuan mengosongkan kapasitas satuan sebelumnya', function () {
    /*
     | Kapasitas milik satuan lama tidak berlaku untuk yang baru. Dibiarkan,
     | "14" yang dimaksudkan sebagai kursi Hiace tersimpan sebagai 14 orang
     | per kamar — dan jumlah kamarnya jadi sepersekian dari yang sebenarnya.
     */
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'transportasi')
        ->call('pakaiKapasitas', 14)
        ->set('isian.satuan', 'kamar_malam')
        ->assertSet('isian.kapasitas', '');
});

test('satuan yang dipilih tangan tidak ditimpa kategori', function () {
    // Perbaikan yang menimpa pilihan orang berubah jadi gangguan.
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.satuan', 'rombongan')
        ->set('isian.kategori', 'transportasi')
        ->assertSet('isian.satuan', 'rombongan');
});

test('master harga per unit menuntut kapasitas sebelum dikirim', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'transportasi')
        ->set('isian.nama', 'Sewa Hiace')
        ->set('isian.satuan', 'unit_hari')
        ->set('isian.harga', '1100000')
        ->assertSet('isian.harga', '1.100.000')
        // Kapasitas bawaannya terisi, jadi yang diuji di sini keadaan saat
        // admin sengaja mengosongkannya.
        ->set('isian.kapasitas', '')
        ->call('simpan')
        ->assertHasErrors('isian.kapasitas')
        ->set('isian.kapasitas', '14')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/master-harga')
        && $r['harga'] === 1100000 && $r['kapasitas'] === 14 && $r['provinsi'] === null);
});

test('kapasitas tidak ikut terkirim untuk satuan yang tidak menghitung unit', function () {
    /*
     | Kotak kapasitas disembunyikan untuk satuan per orang. Nilai yang tidak
     | terlihat tidak boleh ikut tersimpan — admin yang mengetik 14 lalu
     | mengganti satuannya tidak punya cara melihat angka itu masih ada.
     */
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'transportasi')
        ->call('pakaiKapasitas', 14)
        ->set('isian.satuan', 'orang')
        // Dikembalikan paksa, meniru nilai basi yang kotaknya sudah
        // disembunyikan: pengamannya di simpan(), bukan cuma di layar.
        ->set('isian.kapasitas', '14')
        ->set('isian.nama', 'Tiket Masuk')
        ->set('isian.harga', '60000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/master-harga')
        && $r['kapasitas'] === null);
});

test('memilih destinasi mengisi provinsi dan daerahnya dari katalog', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.destinasi', 'Candi Prambanan')
        ->assertSet('isian.provinsi', 'DI Yogyakarta')
        ->assertSet('isian.daerah', 'Sleman');
});

test('halaman master harga tergambar dengan harganya', function () {
    palsukanRab();

    $this->actingAs(adminRab())->get('/admin/orcha/master-harga')
        ->assertOk()
        ->assertSee('Master Harga')
        ->assertSee('Tiket Candi Prambanan')
        ->assertSee('Rp 60.000');
});

/*
 | ---------------------------------------------------------------------------
 | Yang dulu diketik, kini terisi sendiri
 |
 | Setiap kotak yang jawabannya sudah diketahui dari kotak di sebelahnya bukan
 | pilihan, melainkan penyalinan — dan penyalinan yang dilakukan berpuluh kali
 | sehari adalah tempat salah ketik lahir.
 | ---------------------------------------------------------------------------
 */

test('judul RAB dirakit sendiri dari tujuan dan lamanya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->set('isian.nama_pelanggan', 'Keluarga Hendra')
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->set('isian.jumlah_hari', 3)
        ->set('isian.jumlah_peserta', 10)
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab')
        && ($r->data()['judul'] ?? null) === 'Private Trip DI Yogyakarta 3H2M');
});

test('kota yang diisi admin menang atas provinsi di judul', function () {
    palsukanRab();

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->set('isian.daerah', 'Sleman')
        ->set('isian.jumlah_hari', 2);

    expect($komponen->instance()->judulOtomatis())->toBe('Private Trip Sleman 2H1M');
});

test('judul yang ditulis sendiri tidak ditimpa rakitan', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->set('isian.judul', 'Jogja Heritage Spesial')
        ->set('isian.nama_pelanggan', 'Keluarga Hendra')
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && ($r->data()['judul'] ?? null) === 'Jogja Heritage Spesial');
});

test('klik destinasi langsung ikut menarik biayanya', function () {
    palsukanRab();

    /*
     | Menambahkan destinasi dan menghitung tiketnya adalah satu maksud, bukan
     | dua. Dulu dua tekanan tombol, dan yang lupa menekan tombol kedua
     | mengirim penawaran dengan modal yang kurang — kesalahan yang baru
     | ketahuan saat uangnya kurang.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('tambahDestinasi', 'Candi Prambanan');

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab/3/tarik-biaya'));
});

/**
 * Isi itinerary yang DIKIRIM ke Orcha pada permintaan terakhir.
 *
 * Diperiksa dari kirimannya, bukan dari properti komponen: tiap simpanan
 * menggambar ulang layar dari jawaban Orcha, dan jawaban palsu di uji ini
 * selalu rekaman yang sama — jadi properti komponen tidak pernah memuat
 * yang barusan ditambahkan. Yang menentukan tetap apa yang dikirim.
 *
 * @return array<int, array<string, mixed>>
 */
function itineraryTerkirim(): array
{
    $isi = [];

    Http::assertSent(function ($r) use (&$isi) {
        if (str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab/3/itinerary')) {
            $isi = $r->data()['itinerary'] ?? [];
        }

        return true;
    });

    return $isi;
}

test('jam kegiatan ditebak berurutan, bukan dibiarkan kosong', function () {
    palsukanRab();

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('tambahKegiatanBaku', 'Penjemputan peserta');

    // Hari 1 di rekaman sudah berisi kegiatan pukul 09.00, jadi tebakan
    // berikutnya 11.00 — +2 jam dari yang terakhir pada hari itu.
    $baru = collect(itineraryTerkirim())->firstWhere('nama', 'Penjemputan peserta');

    expect($baru['jam'])->toBe('11:00');

    $komponen->call('tambahKegiatanBaku', 'Sarapan');

    /*
     | 11.00 lagi, bukan 13.00 — dan itu benar DI UJI INI: jawaban palsunya
     | selalu rekaman yang sama, jadi kegiatan pertama tidak pernah
     | benar-benar masuk dan hari 1 tetap berisi satu kegiatan pukul 09.00.
     | Yang dibuktikan di sini jam selalu TERISI, bukan rantai tebakannya.
     */
    expect(collect(itineraryTerkirim())->firstWhere('nama', 'Sarapan')['jam'])->toBe('11:00');
});

test('hari yang masih kosong mulai dari pukul delapan', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('pilihHari', 3)
        ->call('tambahKegiatanBaku', 'Penjemputan peserta');

    expect(collect(itineraryTerkirim())->firstWhere('nama', 'Penjemputan peserta')['jam'])->toBe('08:00');
});

test('hanya kegiatan dari daftar baku yang diterima tombolnya', function () {
    palsukanRab();

    /*
     | Nama kegiatan datang dari markah, dan markah bisa dikarang pemanggilnya.
     | Yang dijaga bukan rupa layarnya, melainkan apa yang boleh masuk.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('tambahKegiatanBaku', 'Mampir ke tempat yang dikarang');

    Http::assertNotSent(fn ($r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab/3/itinerary'));
});

test('ketiga layar RAB memakai kerangka dasbor lemon', function () {
    palsukanRab();
    $admin = adminRab();

    $layar = [
        '/admin/orcha/rab' => ['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-tabel'],
        '/admin/orcha/rab/3' => ['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-isian'],
        '/admin/orcha/master-harga' => ['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-isian', 'dsb-tabel', 'dsb-lencana'],
    ];

    foreach ($layar as $alamat => $kelas) {
        $isi = $this->actingAs($admin)->get($alamat)->assertOk()->getContent();

        foreach ($kelas as $k) {
            expect(str_contains($isi, $k))->toBeTrue("{$alamat} tidak memakai {$k}");
        }

        // Dialek lama tidak boleh kembali diam-diam.
        expect($isi)->not->toContain('card border-0 shadow-sm rounded-4');
    }

    /*
     | Master harga paling lama memakai dialek campuran — tabel Bootstrap,
     | lencana bg-*-subtle, dan kerangka row/col di tengah halaman dsb-*.
     | Yang dijaga di sini bukan rupanya, melainkan tidak kembalinya dialek
     | itu diam-diam lewat salinan dari layar lain.
     */
    $sumber = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/master-harga.blade.php'));

    foreach (['table table-hover', 'bg-primary-subtle', 'empty-state-icon-wrapper', 'orcha-bagian-kepala'] as $lama) {
        expect($sumber)->not->toContain($lama);
    }
});

test('titik impas tampil di kartu ringkasan, bukan cuma ada di API', function () {
    /*
     | Pertanyaan yang ditanyakan admin bukan "berapa angka BEP-nya" melainkan
     | "rombongan sekecil ini aman atau belum" — jadi yang ditampilkan jumlah
     | pesertanya berikut dari mana angka itu datang.
     */
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();

    $ringkas = rekamanRab('show')['data']['ringkasan'];

    expect($isi)->toContain('Balik modal mulai')
        ->and($isi)->toContain($ringkas['bep_peserta'].' peserta')
        // Asalnya ikut disebut: angka tanpa asal-usul adalah angka yang
        // dihitung ulang di kertas, dan kertasnya yang dipakai.
        ->and($isi)->toContain($ringkas['modal_tetap_teks'])
        ->and($isi)->toContain($ringkas['sumbangan_per_orang_teks'])
        ->and($isi)->toContain('Untung per orang');
});

test('kegiatan punya jam mulai dan jam selesai, lamanya ikut terbaca', function () {
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();

    // Dua isian waktu per kegiatan, bukan satu.
    expect($isi)->toContain('itinerary.0.jam_selesai')
        ->and($isi)->toContain('Jam selesai')
        // Lamanya dari Orcha, tidak dihitung ulang di lemon.
        ->and($isi)->toContain(rekamanRab('show')['data']['itinerary'][0]['durasi_teks']);
});

test('jam selesai kegiatan baru ikut ditebak dua jam sesudah mulainya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('pilihHari', 3)
        ->call('tambahKegiatanBaku', 'Sarapan');

    $baru = collect(itineraryTerkirim())->firstWhere('nama', 'Sarapan');

    expect($baru['jam'])->toBe('08:00')
        ->and($baru['jam_selesai'])->toBe('10:00');
});

test('kegiatan berikutnya menyambung jam SELESAI, bukan jam mulai', function () {
    /*
     | Rekaman hari 1 berisi kegiatan 09.00–11.00. Menyambung dari jam
     | mulainya berarti kegiatan berikutnya dijadwalkan pukul 11.00 hanya
     | kebetulan; dari jam selesainya, ia memang bersambung. Bedanya baru
     | terasa pada kegiatan yang panjang — perjalanan empat jam diikuti
     | kegiatan dua jam sesudah ia MULAI berarti keduanya bertabrakan.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('pilihHari', 1)
        ->call('tambahKegiatanBaku', 'Makan siang');

    expect(collect(itineraryTerkirim())->firstWhere('nama', 'Makan siang')['jam'])->toBe('11:00');
});

/*
 | ---------------------------------------------------------------------------
 | Open trip di layar
 | ---------------------------------------------------------------------------
 */

test('jenis perjalanan dipilih lebih dulu, dan open trip menuntut peserta minimum', function () {
    palsukanRab();

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->call('bukaTambah');

    // Private trip sebagai bawaan: jumlah orangnya sudah pasti di kesepakatan.
    expect($komponen->instance()->openTrip())->toBeFalse();

    $komponen->set('isian.jenis', 'open')
        ->set('isian.nama_pelanggan', 'Rombongan Umum')
        ->set('isian.provinsi', 'Jawa Timur')
        ->set('isian.jumlah_peserta', 14)
        ->set('isian.peserta_minimum', 20)   // lebih banyak daripada targetnya
        ->call('simpan')
        ->assertHasErrors('isian.peserta_minimum');
});

test('open trip mengirim dasar harga ke Orcha, private trip tidak membawanya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->set('isian.jenis', 'open')
        ->set('isian.nama_pelanggan', 'Rombongan Umum')
        ->set('isian.provinsi', 'Jawa Timur')
        ->set('isian.jumlah_peserta', 14)
        ->set('isian.peserta_minimum', 5)
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab')
        && ($r->data()['jenis'] ?? null) === 'open'
        && ($r->data()['peserta_minimum'] ?? null) === 5
        && ($r->data()['harga_dasar'] ?? null) === 'minimum');
});

test('private trip tidak membawa sisa isian open trip', function () {
    /*
     | Kolom yang terisi tapi tak berarti akan dibaca sebagai keadaan nyata
     | kelak — "peserta minimum 5" pada private trip menyesatkan siapa pun
     | yang membukanya enam bulan lagi.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->set('isian.jenis', 'open')
        ->set('isian.peserta_minimum', 5)
        ->set('isian.jenis', 'private')          // berubah pikiran
        ->set('isian.nama_pelanggan', 'Keluarga Hendra')
        ->set('isian.provinsi', 'Jawa Timur')
        ->call('simpan')
        ->assertHasNoErrors();

    // array_key_exists, bukan ??: operator itu ikut menyala pada nilai null,
    // jadi ia tidak bisa membedakan "dikirim kosong" dari "tidak dikirim".
    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab')
        && array_key_exists('peserta_minimum', $r->data())
        && $r->data()['peserta_minimum'] === null
        && ($r->data()['harga_dasar'] ?? null) === 'peserta');
});

test('judul rakitan menyebut jenis perjalanannya', function () {
    palsukanRab();

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->set('isian.provinsi', 'Jawa Timur')
        ->set('isian.jumlah_hari', 3);

    expect($komponen->instance()->judulOtomatis())->toBe('Private Trip Jawa Timur 3H2M');

    $komponen->set('isian.jenis', 'open');

    expect($komponen->instance()->judulOtomatis())->toBe('Open Trip Jawa Timur 3H2M');
});

test('penyusun memperlihatkan dasar harga dan simulasi pesertanya', function () {
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();

    $r = rekamanRab('show')['data']['ringkasan'];

    // Pada open trip harga dan modal dihitung dari jumlah yang BERBEDA;
    // tanpa disebut, angkanya terbaca seolah dibagi jumlah yang sama.
    expect($isi)->toContain('harga dihitung atas dasar')
        ->and($isi)->toContain('Kalau yang datang sekian')
        // Angka impas tanpa tabelnya menuntut pembacanya percaya begitu saja.
        ->and($isi)->toContain($r['simulasi'][0]['laba_teks']);
});

test('persetujuan sadar ikut dikirim saat dijadikan pendaftaran', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('paketId', '1')
        ->set('abaikanImpas', true)
        ->call('jadikanPendaftaran');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'jadikan-pendaftaran')
        && ($r->data()['abaikan_impas'] ?? null) === true);
});

test('penolakan dari Orcha sampai apa adanya, bukan jadi kode 422', function () {
    /*
     | Orcha sudah menjelaskan sendiri apa yang salah. Menggantinya dengan
     | "Orcha membalas dengan kode 422" membuang satu-satunya kalimat yang
     | bisa ditindaklanjuti, dan menyisakan angka yang harus dicari artinya
     | ke orang lain.
     */
    Http::fake([
        '*/jadikan-pendaftaran' => Http::response([
            'pesan' => 'Peserta baru 2 orang, sementara trip ini baru balik modal mulai 5 orang.',
        ], 422),
        '*' => Http::response(rekamanRab('show')),
    ]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('paketId', '1')
        ->call('jadikanPendaftaran')
        ->assertDispatched('toast-error', message: 'Peserta baru 2 orang, sementara trip ini baru balik modal mulai 5 orang.');
});

test('kartu itinerary tidak menyisakan baris cip yang ragged', function () {
    /*
     | Deret cip yang melipat dengan flex meninggalkan sisa di ujung kanan
     | tiap baris sebesar cip yang tidak muat lagi — dan pada baris terakhir
     | sisanya bisa selebar setengah kartu. Grid auto-fill membagi lebarnya
     | rata, jadi tiap baris selalu habis.
     |
     | Dijaga di sumber gayanya: yang rusak kalau seseorang mengembalikannya
     | ke flex adalah rupa, dan rupa tidak menerbitkan galat apa pun.
     */
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/susun.blade.php'));

    // Jendelanya dibuat lapang: komentar di dalam aturannya panjang, dan
    // jendela yang terlalu sempit memotong justru baris yang diperiksa —
    // uji merah yang menunjuk hal yang tidak bersalah.
    $aturan = substr($gaya, strpos($gaya, '.rab-destinasi,'), 1200);

    expect($aturan)->toContain('display: grid')
        ->and($aturan)->toContain('auto-fill')
        // Nama tombol tidak boleh dipotong: yang namanya tidak terbaca utuh
        // tidak bisa dipilih dengan yakin.
        ->and($aturan)->not->toContain('text-overflow: ellipsis');
});

test('baris kegiatan melipat menurut lebar wadahnya, bukan lebar layar', function () {
    /*
     | Media query mengukur lebar LAYAR, sementara yang menjepit baris ini
     | lebar WADAHnya. Di layar lebar dengan kartu sempit, aturan ponselnya
     | tidak pernah menyala dan barisnya meluber tanpa satu pun tanda.
     */
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/susun.blade.php'));

    $aturan = substr($gaya, strpos($gaya, '.rab-kegiatan {'), 320);

    expect($aturan)->toContain('flex-wrap: wrap')
        ->and($aturan)->not->toContain('grid-template-areas');
});

test('tiap kegiatan diberi warna menurut jenisnya', function () {
    palsukanRab();

    /*
     | Hari 2 dipilih karena di sanalah rekamannya memuat DUA jenis sekaligus
     | — destinasi dan ruas perjalanan. Hari 1 sengaja tidak diganggu: uji
     | tebakan jam membaca isinya, dan menambah kegiatan di sana membuat uji
     | itu merah karena alasan yang tidak ada hubungannya dengan warna.
     */
    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('pilihHari', 2)
        ->html();

    /*
     | Sepuluh kartu yang seragam menuntut pembacanya mengeja tiap baris
     | untuk tahu mana perjalanan dan mana destinasi. Warnanya di TEPI, bukan
     | latar penuh: latar berwarna membuat kotak isian di dalamnya susah
     | dibaca, dan yang perlu dibedakan cuma jenisnya.
     */
    expect($isi)->toContain('class="rab-kegiatan" style="--c: #0369a1"')   // perjalanan
        ->and($isi)->toContain('class="rab-kegiatan" style="--c: #1f7a44"') // destinasi
        ->and($isi)->toContain('rab-pil-jenis');
});

test('warna di layar sama dengan warna di kedua PDF', function () {
    /*
     | Satu perjalanan tidak boleh memakai warna berbeda untuk hal yang sama
     | di layar dan di berkas yang dicetak DARI layar itu. Peta warnanya
     | disalin dari templat PDF; yang dijaga di sini kesamaannya.
     */
    $layar = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/susun.blade.php'));
    $peta = substr($layar, strpos($layar, '$rupaKegiatan = ['), 900);

    foreach ([
        'destinasi' => '#1f7a44',
        // Bukan biru merek lama: menuliskannya langsung dilarang di layar
        // Orcha, jadi birunya diganti di layar DAN di kedua PDF sekaligus.
        'perjalanan' => '#0369a1',
        'makan' => '#e0912f',
        'snack' => '#0e7490',
        'istirahat' => '#6d28d9',
    ] as $jenis => $warna) {
        expect(str_contains($peta, "'{$jenis}' => ["))->toBeTrue("jenis {$jenis} tidak ada di peta");
        expect(str_contains($peta, $warna))->toBeTrue("warna {$warna} untuk {$jenis} tidak cocok dengan PDF");
    }
});

test('lamanya kegiatan berdiri di baris keterangan, bukan menggantung sendiri', function () {
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();

    // Di bawah kotak jam ia menambah satu baris sendiri dan menyisakan ruang
    // kosong di sebelahnya; di baris keterangan ia mengisi baris yang memang
    // sudah ada.
    // class="…", bukan sekadar nama kelasnya: kemunculan pertama nama itu
    // ada di blok <style> halaman, dan jendela yang mendarat di sana
    // memeriksa aturan gaya alih-alih markahnya.
    $baris = substr($isi, strpos($isi, 'class="rab-ket-baris"'), 700);

    expect($baris)->toContain('rab-pil-lama')
        ->and($baris)->toContain('rab-ket')
        // Penanda lama di bawah kotak jam tidak boleh kembali.
        ->and($isi)->not->toContain('rab-waktu-lama');
});

test('pil jenis dan lama setinggi kotak isian di sebelahnya', function () {
    /*
     | Dijaga di sumber gayanya, bukan di markah: yang rusak kalau seseorang
     | mematok tingginya dengan angka adalah rupa, dan rupa tidak menerbitkan
     | galat apa pun.
     |
     | Kotak isian di kartu ini dipatok 38px — bukan 40px bawaan sistem
     | desain — jadi angka yang disalin tangan akan meleset lagi begitu salah
     | satunya berubah. Yang benar: diregangkan oleh barisnya.
     */
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/susun.blade.php'));

    $baris = substr($gaya, strpos($gaya, '.rab-ket-baris {'), 480);
    expect($baris)->toContain('align-items: stretch');

    // Jendela dilebarkan: komentar di dalam aturannya panjang, dan jendela
    // yang terlalu sempit memotong justru baris yang diperiksa.
    $pil = substr($gaya, strpos($gaya, '.rab-pil-jenis, .rab-pil-lama {'), 1500);
    expect($pil)->not->toContain('height: 38px')
        ->and($pil)->not->toContain('height: 40px')
        // Lantai boleh, patokan tidak: tanpa lantai keduanya menciut saat
        // kotak isiannya turun ke baris sendiri di layar sempit.
        ->and($pil)->toContain('min-height: 34px');
});

test('rincian biaya: kolom perhitungan dibubarkan, pita kategori membawa angkanya', function () {
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();
    $r = rekamanRab('show')['data']['ringkasan'];

    /*
     | Kolom "Perhitungan" berisi kalimat pendek di kolom selebar sepertiga
     | tabel, jadi tiap baris menyisakan lubang di tengahnya. Kini kalimat itu
     | menempel di bawah nama biayanya — tempat ia memang menjelaskan.
     */
    expect($isi)->not->toContain('<th>PERHITUNGAN</th>')
        ->and($isi)->toContain('rab-biaya-hitung')
        ->and($isi)->toContain($r['baris'][0]['penjelasan']);

    // Pita kategori membawa totalnya sendiri; sebagai nama yang melintang
    // sendirian ia cuma garis pemisah yang kebetulan bertulisan.
    foreach ($r['per_kategori'] as $kat) {
        expect($isi)->toContain($kat['total_teks']);
    }

    // Dan kartunya ditutup angka, bukan tautan yang menggantung.
    expect($isi)->toContain('rab-kaki')
        ->and($isi)->toContain('Modal total');
});

test('tabel biaya menumpuk jadi kartu di ponsel', function () {
    /*
     | Tanpa ini ia menggulir mendatar di dalam pembungkusnya — dan subtotal
     | berikut tombol hapusnya tidak pernah terlihat tanpa digeser lebih
     | dulu, padahal subtotal itulah yang dicari mata.
     */
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/susun.blade.php'));

    expect($gaya)->toContain('.rab-tabel-biaya thead { display: none; }');

    palsukanRab();
    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();

    // Label kolomnya menempel di tiap sel, supaya kartu ponselnya punya
    // keterangan sendiri.
    foreach (['Harga satuan', 'Jumlah', 'Subtotal'] as $judul) {
        expect($isi)->toContain('data-judul="'.$judul.'"');
    }
});

test('pencarian tanpa hasil tidak dilaporkan sebagai katalog kosong', function () {
    /*
     | Dua keadaan yang berbeda, dan sebelumnya keduanya diberi kalimat yang
     | sama. Mengaku "tidak ada destinasi katalog untuk provinsi ini" padahal
     | ada sepuluh membuat admin pergi mengisi data yang sudah terisi.
     |
     | Kata yang dicari sering memang bukan destinasi: nama di rincian biaya
     | datang dari master harga, daftar yang berbeda dari katalog destinasi.
     */
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('cariDestinasi', 'depok')
        ->html();

    expect($isi)->toContain('Tidak ada destinasi bernama')
        ->and($isi)->not->toContain('Tidak ada destinasi katalog untuk provinsi ini')
        // Dan ditawarkan jalan keluarnya, bukan sekadar kabar nihil.
        ->and($isi)->toContain('Tambah sebagai kegiatan')
        ->and($isi)->toContain('Master Harga');
});

test('kata yang dicari bisa langsung jadi kegiatan', function () {
    // Tanpa ini admin harus menghapus ketikannya, pindah ke kotak lain, dan
    // mengetik ulang kata yang sama.
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('cariDestinasi', 'Pantai Depok')
        ->call('tambahDariCari')
        ->assertSet('cariDestinasi', '');

    $baru = collect(itineraryTerkirim())->firstWhere('nama', 'Pantai Depok');

    expect($baru)->not->toBeNull()
        // Kegiatan biasa, bukan destinasi katalog: ia memang tidak ada di
        // sana, dan menandainya sebagai destinasi akan membuat penarikan
        // biaya mencari tiket yang tidak pernah ada.
        ->and($baru['destinasi'])->toBeNull();
});

test('katalog yang benar-benar kosong tetap dikabarkan apa adanya', function () {
    Http::fake([
        '*/rab/katalog*' => Http::response(['data' => ['destinasi' => [], 'master' => [], 'paket' => [], 'kosakata' => []]]),
        '*' => Http::response(rekamanRab('show')),
    ]);

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->html();

    expect($isi)->toContain('Tidak ada destinasi katalog untuk provinsi ini');
});

test('destinasi di master harga DIPILIH dari katalog, bukan diketik', function () {
    /*
     | Harga tiket dijodohkan ke itinerary lewat nama ini persis. Sebagai
     | ketikan bebas (input + datalist, yang cuma menyarankan), satu huruf
     | berbeda membuat tiketnya tidak pernah ikut tertarik dan RAB-nya
     | kekurangan modal tanpa satu pun tanda.
     */
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->html();

    expect($isi)->toContain('<select id="mh-destinasi"')
        ->and($isi)->toContain('isian.destinasi')
        ->and($isi)->toContain('Tidak terikat destinasi')
        // Datalist cuma menyarankan; yang dipakai sekarang kotak pilih.
        ->and($isi)->not->toContain('rab-daftar-destinasi');
});

test('nilai lama yang tidak ada di katalog tetap ditawarkan dan ditandai', function () {
    /*
     | Menyunting baris lain tidak boleh diam-diam menghapus nilai yang sudah
     | tersimpan — tetapi admin harus tahu bahwa nama itu tidak akan pernah
     | menarik tiket apa pun.
     */
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.destinasi', 'prambanan')   // ada di data, tidak ada di katalog
        ->html();

    expect($isi)->toContain('tidak ada di katalog')
        ->and($isi)->toContain('tidak akan ikut tertarik');
});

test('destinasi di luar daftar bisa ditambahkan tanpa meninggalkan formulir', function () {
    /*
     | Kotak pilih hanya memuat yang sudah ada di katalog, sementara destinasi
     | baru memang bermunculan. Tanpa jalan ini admin harus pindah menu untuk
     | mendaftarkannya — dan seluruh isian yang sudah diketiknya hilang.
     |
     | Nama yang terpilih sesudahnya harus sama PERSIS dengan yang dikirim,
     | karena jodoh harga tiket ke itinerary memang lewat nama.
     */
    $katalogDipanggil = 0;

    Http::fake(function (Request $r) use (&$katalogDipanggil) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            $katalogDipanggil++;
            $data = rekamanRab('katalog');

            // Sesudah ditambahkan, Orcha memang memulangkan katalog yang
            // sudah berisi destinasi barunya.
            if ($katalogDipanggil > 1) {
                $data['data']['destinasi'][] = [
                    'nama' => 'Tebing Breksi', 'provinsi' => 'DI Yogyakarta',
                    'daerah' => 'Sleman', 'punya_tiket' => false,
                ];
            }

            return Http::response($data);
        }

        if (str_ends_with($jalur, '/katalog-destinasi')) {
            return Http::response(['pesan' => 'Tebing Breksi ditambahkan ke daftar.', 'data' => []], 201);
        }

        return Http::response(rekamanRab('master'));
    });

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.daerah', 'Sleman')
        ->set('destinasiBaru', true)
        //  Spasi berlebih sengaja: Orcha merapikannya, dan yang terpilih di
        //  sini harus mengikuti bentuk rapi itu, bukan ketikan mentahnya.
        ->set('namaDestinasiBaru', '  Tebing   Breksi ')
        ->call('tambahDestinasiKatalog');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/katalog-destinasi')
        && $r['nama'] === 'Tebing Breksi'
        && $r['daerah'] === 'Sleman');

    $layar->assertSet('isian.destinasi', 'Tebing Breksi')
        ->assertSet('destinasiBaru', false)
        ->assertSet('namaDestinasiBaru', '');

    // Katalognya disimpan sepuluh menit; tanpa dilupakan, destinasi yang baru
    // saja dibuat tidak muncul di kotak pilihnya sendiri dan tampak seperti
    // gagal tersimpan.
    expect($katalogDipanggil)->toBeGreaterThan(1)
        ->and($layar->html())->toContain('Tebing Breksi')
        ->and($layar->html())->not->toContain('Tebing Breksi — tidak ada di katalog');
});

test('nama destinasi baru yang terlalu pendek tidak dikirim', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('destinasiBaru', true)
        ->set('namaDestinasiBaru', 'ab')
        ->call('tambahDestinasiKatalog')
        ->assertHasErrors('namaDestinasiBaru')
        // Kotaknya tetap terbuka berisi ketikannya, bukan tertutup diam-diam.
        ->assertSet('destinasiBaru', true)
        ->assertSet('namaDestinasiBaru', 'ab');

    Http::assertNotSent(fn (Request $r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/katalog-destinasi'));
});

test('jalan menambah destinasi terlihat di formulir master harga', function () {
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->html();

    expect($isi)->toContain('Destinasinya belum ada di daftar');
});

/*
 | ---------------------------------------------------------------------------
 | Master harga: yang dulu diketik, kini terisi sendiri
 | ---------------------------------------------------------------------------
 */

test('nama tiket dirakit dari destinasi yang dipilih', function () {
    /*
     | Bukan sekadar menghemat ketikan: namanya dirakit DARI destinasi yang
     | dipilih, jadi tidak bisa lagi berbeda dari destinasi yang dimaksudnya.
     */
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.destinasi', 'Candi Prambanan')
        ->set('isian.harga', '60000')
        // Terlihat di kotaknya lebih dulu, bukan cuma terkirim: nama yang baru
        // muncul sesudah menyimpan tidak bisa dikoreksi oleh yang tak
        // melihatnya. (Sesudah simpan formulirnya ditutup dan dikosongkan,
        // jadi yang bisa diperiksa di sana isi permintaannya.)
        ->assertSee('Tiket Masuk Candi Prambanan')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/master-harga')
        && $r['nama'] === 'Tiket Masuk Candi Prambanan'
        && $r['destinasi'] === 'Candi Prambanan');
});

test('nama yang diketik tangan menang atas nama rakitan', function () {
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.destinasi', 'Candi Prambanan')
        ->set('isian.nama', 'Tiket Prambanan (turis asing)')
        ->set('isian.harga', '375000')
        ->call('simpan');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/master-harga')
        && $r['nama'] === 'Tiket Prambanan (turis asing)');
});

test('nama rakitan diperlihatkan sebelum disimpan', function () {
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'akomodasi')
        ->set('isian.daerah', 'Sleman')
        ->html();

    expect($isi)->toContain('Namanya terisi sendiri')
        ->and($isi)->toContain('Hotel di Sleman');
});

test('nama tetap wajib bila tak ada yang bisa dirakit', function () {
    // Kategori "lain-lain" tanpa tempat: tidak ada bahan untuk merakit nama,
    // jadi kotaknya tetap harus diisi — bukan tersimpan tanpa nama.
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'lainnya')
        ->set('isian.harga', '50000')
        ->call('simpan')
        ->assertHasErrors('isian.nama');
});

test('kotak destinasi & kapasitas hanya muncul saat memang dipakai', function () {
    /*
     | Kotak yang hadir untuk tidak diisi tetap menuntut dibaca lebih dulu.
     | Destinasi hanya berlaku untuk tiket; kapasitas hanya untuk satuan yang
     | menghitung jumlah unit.
     */
    palsukanRab();
    $layar = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class)->call('bukaTambah');

    // Bawaannya tiket per orang: destinasi ada, kapasitas tidak.
    expect($layar->html())->toContain('id="mh-destinasi"')
        ->and($layar->html())->not->toContain('id="mh-kapasitas"');

    $layar->set('isian.kategori', 'transportasi');

    expect($layar->html())->toContain('id="mh-kapasitas"')
        ->and($layar->html())->not->toContain('id="mh-destinasi"');
});

test('destinasi yang sudah terisi tidak disembunyikan oleh ganti kategori', function () {
    /*
     | Menyembunyikan kotak yang nilainya masih tersimpan sama dengan
     | menghapusnya tanpa memberi tahu — dan nilai itu yang menentukan
     | apakah tiketnya ikut tertarik ke RAB.
     */
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.destinasi', 'Candi Prambanan')
        ->set('isian.kategori', 'konsumsi')
        ->html();

    expect($isi)->toContain('id="mh-destinasi"')
        ->and($isi)->toContain('Candi Prambanan');
});

test('tiket tanpa destinasi diberi tahu akibatnya', function () {
    /*
     | Penariknya mencari harga tiket lewat nama destinasi di itinerary.
     | Tiket tanpa destinasi memang tersimpan — tetapi tidak pernah terpakai,
     | dan RAB-nya kekurangan modal tanpa satu pun tanda.
     */
    palsukanRab();
    $layar = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class)->call('bukaTambah');

    expect($layar->html())->toContain('tidak pernah ikut tertarik ke RAB');

    // Yang ditandai "wajib" memang masuk sendiri, jadi peringatannya gugur.
    $layar->set('otomatis', true);

    expect($layar->html())->not->toContain('tidak pernah ikut tertarik ke RAB');

    // Begitu destinasinya dipilih, peringatannya juga gugur.
    $layar->set('otomatis', false)->set('isian.destinasi', 'Candi Prambanan');

    expect($layar->html())->not->toContain('tidak pernah ikut tertarik ke RAB');
});

test('kategori di daftar harga berwarna menurut jenisnya', function () {
    // Sepuluh kategori berlencana abu sama saja dengan tidak berlencana:
    // matanya harus membaca tulisannya satu per satu.
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/master-harga')->assertOk()->getContent();

    expect($isi)->toContain('dsb-lencana')
        ->and($isi)->toContain('bi-ticket-perforated');
});

/*
 | ---------------------------------------------------------------------------
 | Kendaraan yang muat untuk rombongan ini
 |
 | Master harga menyimpan "kendaraan ini muat berapa"; penyusun RAB yang
 | mempertemukan angka itu dengan jumlah peserta. Tanpanya admin membagi di
 | kepala setiap kali — dan yang paling sering terjadi bukan salah hitung,
 | melainkan tidak dihitung sama sekali.
 | ---------------------------------------------------------------------------
 */

test('penyusun menyarankan kendaraan menurut jumlah peserta', function () {
    palsukanRab();

    $saran = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->instance()->saranKendaraan();

    /*
     | 17 peserta, 3 hari. Hiace muat 14 — yang "biasa dipakai" — ternyata
     | butuh 2 unit dan jadi lebih mahal daripada satu Elf. Justru perkara
     | semacam ini yang tidak terlihat saat dibagi di kepala.
     */
    expect(collect($saran)->pluck('nama')->all())->toBe([
        'Sewa Elf Long + driver',      // 1 unit × 1.450.000 × 3 = 4.350.000
        'Sewa Hiace + driver',         // 2 unit × 1.100.000 × 3 = 6.600.000
        'Sewa Bus Besar + driver',     // 1 unit × 3.200.000 × 3 = 9.600.000
    ]);

    $elf = $saran[0];
    expect($elf['unit'])->toBe(1)
        ->and($elf['muat'])->toBe(19)
        ->and($elf['sisa'])->toBe(2)
        ->and($elf['total'])->toBe(4_350_000);

    // Pembulatan ke atas, bukan ke bawah: 17 ÷ 14 adalah dua unit, bukan 1,2.
    // Membulatkan ke bawah berarti tiga orang tidak punya kursi.
    $hiace = collect($saran)->firstWhere('nama', 'Sewa Hiace + driver');
    expect($hiace['unit'])->toBe(2)
        ->and($hiace['sisa'])->toBe(11)
        ->and($hiace['total'])->toBe(6_600_000);
});

test('angka saran kendaraan sama dengan rumus rincian biaya', function () {
    /*
     | Kalau rumusnya berbeda, angka di panel saran akan berselisih dengan
     | angka di rincian biaya tepat di bawahnya — dan yang dipercaya admin
     | jadi yang mana saja. Rumus Orcha: harga × ceil(peserta/muat) × hari.
     */
    palsukanRab();

    $rab = rekamanRab('show')['data'];
    $peserta = (int) $rab['jumlah_peserta'];
    $hari = (int) $rab['jumlah_hari'];

    $master = collect(rekamanRab('katalog')['data']['master'])
        ->where('satuan', 'unit_hari')->keyBy('nama');

    $saran = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->instance()->saranKendaraan();

    foreach ($saran as $k) {
        $m = $master[$k['nama']];
        $unit = (int) ceil($peserta / (int) $m['kapasitas']);

        expect($k['unit'])->toBe($unit)
            ->and($k['total'])->toBe((int) $m['harga'] * $unit * $hari);
    }
});

test('kendaraan yang sudah ada di RAB tidak ditawarkan lagi', function () {
    // Menambahkannya dua kali berarti menyewa dua armada untuk satu
    // rombongan — dan modalnya naik tanpa seorang pun berniat menaikkannya.
    palsukanRab();

    $saran = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->instance()->saranKendaraan();

    $dipakai = collect(rekamanRab('show')['data']['ringkasan']['baris'])
        ->pluck('master_harga_id')->filter()->all();

    foreach ($saran as $k) {
        expect($k['sudah'])->toBe(in_array($k['id'], $dipakai, true));
    }
});

test('tombol pakai kendaraan mengirim id masternya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('pakaiKendaraan', 12);

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab/3/biaya')
        && $r['master_harga_id'] === 12);
});

test('panel kendaraan tergambar dengan unit dan sisa kursinya', function () {
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();

    expect($isi)->toContain('Kendaraan untuk 17 peserta')
        ->and($isi)->toContain('paling murah')
        // Dua angka yang membuat pilihannya bisa dibandingkan tanpa dihitung
        // ulang: berapa unitnya, dan berapa kursi yang menganggur.
        ->and($isi)->toContain('2 unit')
        ->and($isi)->toContain('11 kursi kosong');
});
