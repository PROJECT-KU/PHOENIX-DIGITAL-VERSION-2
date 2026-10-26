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
        // ditarik sendiri saat destinasinya diklik.
        ->assertSee('Tarik ulang dari itinerary');
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
        return $baris->count() === 3
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
        ->call('simpan')
        ->assertHasErrors('isian.kapasitas')
        ->set('isian.kapasitas', '14')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/master-harga')
        && $r['harga'] === 1100000 && $r['kapasitas'] === 14 && $r['provinsi'] === null);
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
        '/admin/orcha/master-harga' => ['dsb-hero', 'dsb-kartu', 'dsb-isian'],
    ];

    foreach ($layar as $alamat => $kelas) {
        $isi = $this->actingAs($admin)->get($alamat)->assertOk()->getContent();

        foreach ($kelas as $k) {
            expect(str_contains($isi, $k))->toBeTrue("{$alamat} tidak memakai {$k}");
        }

        // Dialek lama tidak boleh kembali diam-diam.
        expect($isi)->not->toContain('card border-0 shadow-sm rounded-4');
    }
});
