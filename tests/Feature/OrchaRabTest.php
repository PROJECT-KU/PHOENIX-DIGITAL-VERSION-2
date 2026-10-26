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
