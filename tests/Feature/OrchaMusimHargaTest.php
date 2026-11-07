<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaMasterHargaForm;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaMusimHargaIsi;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaMusimHargaList;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Layar Musim Harga — Nataru, Lebaran, long weekend.
 *
 * Daftarnya milik Orcha; yang diuji di sini lemon mengirim ke jalur yang
 * benar, dan layarnya MENGATAKAN bedanya dengan Hari Libur. Keduanya paling
 * mudah tertukar, dan yang tertukar membuat Nataru dibayar tarif hari Sabtu
 * tanpa satu pun tanda.
 */
function palsukanMusim(array $daftar = [], array $meta = [], array $calon = []): void
{
    Http::fake(function (Request $r) use ($daftar, $meta, $calon) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_contains($jalur, '/calon') => Http::response(['data' => $calon]),
            str_contains($jalur, '/isi') => Http::response(['data' => [
                'musim' => $daftar[0] ?? barisMusim(),
                'harga' => [],
            ]]),
            str_contains($jalur, '/pasang') => Http::response(['pesan' => '2 tarif Nataru disimpan.']),
            str_contains($jalur, '/musim-harga') && $r->method() === 'POST' => Http::response([
                'data' => barisMusim(), 'pesan' => 'Musim "Nataru 2026/2027" ditambahkan.',
            ], 201),
            str_contains($jalur, '/musim-harga') && $r->method() === 'PATCH' => Http::response([
                'data' => barisMusim(), 'pesan' => 'Musim diperbarui.',
            ]),
            str_contains($jalur, '/musim-harga') => Http::response([
                'data' => $daftar,
                'meta' => $meta + ['tahun' => [2026, 2027], 'harga_terpasang' => 4, 'lewat' => 0],
            ]),
            default => Http::response(['data' => [], 'meta' => []]),
        };
    });
}

function barisMusim(array $ubah = []): array
{
    return array_merge([
        'id' => 1,
        'nama' => 'Nataru 2026/2027',
        'tanggal_mulai' => '2026-12-20',
        'tanggal_sampai' => '2027-01-05',
        'rentang_teks' => '20 Des 2026 – 5 Jan 2027',
        'jumlah_hari' => 17,
        'prioritas' => 0,
        'aktif' => true,
        'oleh' => 'Berto',
        'lewat' => false,
        'berjalan' => false,
        'jumlah_harga' => 4,
        'tumpang' => [],
    ], $ubah);
}

function calonMusim(array $ubah = []): array
{
    return array_merge([
        'id' => 11,
        'nama' => 'Hotel Dieng',
        'kategori' => 'akomodasi',
        'kategori_label' => 'Akomodasi',
        'provinsi' => 'Jawa Tengah',
        'vendor' => 'CV Dieng Indah',
        'satuan_label' => 'per kamar per malam',
        'harga' => 450_000,
        'harga_teks' => 'Rp 450.000',
        'harga_akhir_pekan' => 550_000,
        'harga_musim' => null,
    ], $ubah);
}

/* ========================= KERANGKA & KALIMAT ========================= */

test('layar musim harga memakai kerangka dasbor lemon', function () {
    palsukanMusim([barisMusim()]);

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/musim-harga')->assertOk()->getContent();

    foreach (['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-tabel', 'dsb-isian'] as $k) {
        expect($isi)->toContain($k);
    }

    // Dialek lama tidak boleh menyelinap lewat salinan dari layar lain.
    expect($isi)->not->toContain('card border-0 shadow-sm rounded-4');
});

test('layar menyebut bedanya dengan hari libur', function () {
    palsukanMusim([barisMusim()]);

    /*
     | Kalimat ini yang menahan admin dari mengisi Nataru sebagai tanggal
     | merah — yang terasa benar, jalan tanpa galat, dan menghasilkan tarif
     | hari Sabtu untuk musim termahal dalam setahun.
     */
    $this->actingAs(adminRab())->get('/admin/orcha/musim-harga')->assertOk()
        ->assertSee('Bukan sama dengan Hari Libur', false)
        ->assertSee('akhir pekan', false);
});

test('daftar kosong mengatakan hari kerja & akhir pekan tetap jalan', function () {
    palsukanMusim([]);

    $this->actingAs(adminRab())->get('/admin/orcha/musim-harga')->assertOk()
        ->assertSee('Belum ada musim')
        ->assertSee('sudah berjalan sejak awal', false);
});

test('musim tanpa satu pun tarif disebut di muka', function () {
    palsukanMusim([barisMusim(['jumlah_harga' => 0])]);

    /*
     | Musim yang terdaftar tanpa tarif adalah pekerjaan setengah jalan, dan
     | setengahnya yang hilang tidak terlihat: rombongannya tetap dibayari
     | tarif hari biasa.
     */
    $this->actingAs(adminRab())->get('/admin/orcha/musim-harga')->assertOk()
        ->assertSee('belum punya satu pun tarif', false);
});

test('musim bertumpuk disebut berikut pemenangnya', function () {
    palsukanMusim([barisMusim(['tumpang' => [
        ['id' => 2, 'nama' => 'Puncak Tahun Baru', 'rentang_teks' => '31 Des 2026 – 1 Jan 2027', 'menang' => true],
    ]])]);

    $this->actingAs(adminRab())->get('/admin/orcha/musim-harga')->assertOk()
        ->assertSee('bertumpuk dengan Puncak Tahun Baru', false)
        ->assertSee('yang itu menang', false);
});

/* ============================== PENGIRIMAN ============================== */

test('musim baru dikirim ke jalur musim-harga', function () {
    palsukanMusim();

    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaList::class)
        ->call('bukaTambah')
        ->call('pakaiNama', 'Nataru')
        ->set('isian.tanggal_mulai', '2026-12-20')
        ->set('isian.tanggal_sampai', '2027-01-05')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/musim-harga')
        && $r['nama'] === 'Nataru'
        && $r['tanggal_mulai'] === '2026-12-20'
        && $r['tanggal_sampai'] === '2027-01-05');
});

test('musim sehari diterima', function () {
    palsukanMusim();

    /*
     | after_or_equal, bukan after. Mewajibkan "sesudah" membuat musim sehari
     | mustahil ditulis, dan admin akan menuliskannya sebagai 1–2 Januari:
     | satu hari tarif tinggi yang tidak diminta siapa pun.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Tahun Baru')
        ->set('isian.tanggal_mulai', '2027-01-01')
        ->set('isian.tanggal_sampai', '2027-01-01')
        ->call('simpan')
        ->assertHasNoErrors();
});

test('tanggal selesai sebelum mulai ditolak sebelum menyentuh Orcha', function () {
    palsukanMusim();

    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Nataru')
        ->set('isian.tanggal_mulai', '2026-12-20')
        ->set('isian.tanggal_sampai', '2026-12-10')
        ->call('simpan')
        ->assertHasErrors('isian.tanggal_sampai');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST');
});

test('musim tanpa nama ditolak sebelum menyentuh Orcha', function () {
    palsukanMusim();

    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', '')
        ->set('isian.tanggal_mulai', '2026-12-20')
        ->set('isian.tanggal_sampai', '2027-01-05')
        ->call('simpan')
        ->assertHasErrors('isian.nama');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST');
});

test('mematikan musim mengirim aktif false', function () {
    palsukanMusim([barisMusim()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaList::class)
        ->call('ubahAktif', 1, false);

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && $r['aktif'] === false);
});

test('menyimpan musim membuang katalog yang tersimpan', function () {
    palsukanMusim();
    cache()->put('orcha.rab.katalog', ['musim' => []], now()->addMinutes(10));

    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Nataru')
        ->set('isian.tanggal_mulai', '2026-12-20')
        ->set('isian.tanggal_sampai', '2027-01-05')
        ->call('simpan');

    /*
     | Tanpa ini, admin yang baru menambahkan Nataru lalu membuka borang harga
     | TIDAK menemukan kotaknya selama sepuluh menit — lalu menyimpulkan
     | fiturnya tidak jalan, dan tarifnya tidak pernah diisi.
     */
    expect(cache()->get('orcha.rab.katalog'))->toBeNull();
});

/* ============================ LAYAR ISI TARIF ============================ */

test('layar isi tarif menampilkan kotak per harga', function () {
    palsukanMusim([barisMusim()], [], [calonMusim()]);

    $this->actingAs(adminRab())->get('/admin/orcha/musim-harga/1/isi')->assertOk()
        ->assertSee('Hotel Dieng')
        ->assertSee('Rp 450.000')
        // Petunjuk di kotak yang kosong: yang dibiarkan kosong tetap memakai
        // tarif hari biasa, dan itu memang jawaban yang benar untuk tiket.
        ->assertSee('tarif biasa', false)
        // Angkanya bertitik seperti kolom di sebelahnya. "900000" telanjang
        // di samping "Rp 450.000" adalah satu nol yang terlewat baca.
        ->assertSee('orcha-uang', false);
});

test('kotak kosong dikirim sebagai null, bukan nol', function () {
    palsukanMusim([barisMusim()], [], [calonMusim()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaIsi::class, ['musim' => 1])
        ->set('nilai.11', '')
        ->call('simpan');

    /*
     | Nol adalah harga yang SAH — ada tempat yang digratiskan di musim
     | tertentu. Mengirim 0 untuk "tidak naik" akan menggratiskan hotelnya
     | sepanjang Nataru.
     */
    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/pasang')
        && $r['baris'][0]['harga'] === null);
});

test('nol dikirim sebagai nol', function () {
    palsukanMusim([barisMusim()], [], [calonMusim()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaIsi::class, ['musim' => 1])
        ->set('nilai.11', '0')
        ->call('simpan');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/pasang')
        && $r['baris'][0]['harga'] === 0);
});

test('isi cepat persen dihitung dari harga hari biasa dan dibulatkan', function () {
    palsukanMusim([barisMusim()], [], [calonMusim()]);

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaIsi::class, ['musim' => 1])
        ->call('isiPersen', 75, [calonMusim()]);

    // 450.000 + 75% = 787.500 → dibulatkan ke ribuan ke atas = 788.000.
    // Angka seperti 787.500 terbaca sebagai hasil kalkulator, bukan harga.
    expect($komponen->get('nilai')[11])->toBe('788.000');
});

test('isi cepat tidak memakai harga akhir pekan sebagai dasar', function () {
    palsukanMusim([barisMusim()], [], [calonMusim()]);

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaMusimHargaIsi::class, ['musim' => 1])
        ->call('isiPersen', 100, [calonMusim()]);

    /*
     | Dihitung dari tarif akhir pekan, "+100%" akan jadi dua kali angka yang
     | berbeda-beda antar baris — dan tidak ada apa pun di layar yang
     | menyebutkan dasar mana yang dipakai.
     */
    expect($komponen->get('nilai')[11])->toBe('900.000');
    expect($komponen->get('nilai')[11])->not->toBe('1.100.000');
});

test('selisih yang lebih murah daripada harga biasa diperingatkan', function () {
    palsukanMusim([barisMusim()], [], [calonMusim(['harga_musim' => 300_000])]);

    /*
     | Tarif musim yang lebih MURAH hampir selalu salah ketik: nol yang
     | kurang, atau angka yang tertukar dengan harga biasa.
     */
    $this->actingAs(adminRab())->get('/admin/orcha/musim-harga/1/isi')->assertOk()
        ->assertSee('lebih murah daripada harga biasa', false);
});

/* ======================== BORANG MASTER HARGA ======================== */

test('borang master harga punya kotak tarif musim saat ada musim terdaftar', function () {
    cache()->forget('orcha.rab.katalog');

    Http::fake(fn (Request $r) => str_contains($r->url(), '/rab/katalog')
        ? Http::response(['data' => [
            'provinsi' => ['Jawa Tengah'],
            'destinasi' => [],
            'vendor' => [],
            'kosakata' => ['kategori' => ['tiket' => 'Tiket'], 'satuan' => ['orang' => 'per orang'],
                'satuan_berkapasitas' => ['unit']],
            'musim' => [[
                'id' => 1, 'nama' => 'Nataru 2026/2027',
                'rentang_teks' => '20 Des 2026 – 5 Jan 2027',
                'tanggal_mulai' => '2026-12-20', 'jumlah_hari' => 17, 'lewat' => false,
            ]],
        ]])
        : Http::response(['data' => [], 'meta' => []]));

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaForm::class)
        ->set('pakaiMusim', true);

    $komponen->assertSee('Nataru 2026/2027')->assertSee('20 Des 2026 – 5 Jan 2027');
});

test('tarif musim ikut terkirim saat borang disimpan', function () {
    cache()->forget('orcha.rab.katalog');

    Http::fake(function (Request $r) {
        if (str_contains($r->url(), '/rab/katalog')) {
            return Http::response(['data' => [
                'provinsi' => ['Jawa Tengah'], 'destinasi' => [], 'vendor' => [],
                'kosakata' => ['kategori' => ['tiket' => 'Tiket'], 'satuan' => ['orang' => 'per orang'],
                    'satuan_berkapasitas' => ['unit']],
                'musim' => [[
                    'id' => 1, 'nama' => 'Nataru 2026/2027',
                    'rentang_teks' => '20 Des 2026 – 5 Jan 2027',
                    'tanggal_mulai' => '2026-12-20', 'jumlah_hari' => 17, 'lewat' => false,
                ]],
            ]]);
        }

        return Http::response(['data' => ['id' => 5], 'pesan' => 'Harga ditambahkan.'],
            $r->method() === 'POST' ? 201 : 200);
    });

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaForm::class)
        ->set('isian.nama', 'Hotel Dieng')
        ->set('isian.kategori', 'tiket')
        ->set('isian.satuan', 'orang')
        ->set('isian.harga', '450.000')
        ->set('pakaiMusim', true)
        ->set('hargaMusim.1', '900.000')
        ->call('simpan');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_contains($r->url(), '/master-harga')
        && ($r['harga_musim'][0]['musim_id'] ?? null) === 1
        && ($r['harga_musim'][0]['harga'] ?? null) === 900000);
});

test('mematikan saklar musim mengosongkan angkanya', function () {
    cache()->forget('orcha.rab.katalog');
    Http::fake(fn () => Http::response(['data' => [], 'meta' => []]));

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaForm::class)
        ->set('hargaMusim.1', '900.000')
        ->set('pakaiMusim', true)
        ->set('pakaiMusim', false);

    /*
     | Kalau tidak, angka yang tidak terlihat lagi tetap terkirim dan tarif
     | musim terus berlaku padahal admin yakin sudah mematikannya — selisih
     | yang tidak bisa ditemukan karena kotaknya tidak ada di layar.
     */
    expect($komponen->get('hargaMusim')[1])->toBe('');
});

/* ============================== PENJAGA ============================== */

test('kedua layar musim memasang penangan toast-error', function () {
    /*
     | Keduanya mengirim `toast-error` saat penyimpanan ditolak, dan yang
     | mendengarkannya hidup di partial skrip. Tanpa partial itu, gagal
     | simpan tidak memunculkan apa pun — tarif yang tidak tersimpan terlihat
     | persis seperti tarif yang tersimpan. Sudah pernah kena di layar
     | borang vendor dan hari libur.
     */
    foreach (['musim-harga', 'musim-harga-isi'] as $berkas) {
        $isi = file_get_contents(resource_path("views/livewire/pages/admin/orcha/rab/{$berkas}.blade.php"));

        expect($isi)->toContain("@include('livewire.pages.admin.orcha.partials.skrip')");
    }
});

test('gaya kedua layar musim ditulis telanjang, bukan didorong ke stack', function () {
    /*
     | Layout templateindex tidak punya satu pun @stack, jadi gaya yang
     | didorong ke sana tidak pernah ikut tergambar — halamannya tetap 200,
     | cuma tampil tanpa gayanya sendiri, dan tidak ada galat yang
     | menyebutkannya.
     */
    foreach (['musim-harga', 'musim-harga-isi'] as $berkas) {
        $isi = file_get_contents(resource_path("views/livewire/pages/admin/orcha/rab/{$berkas}.blade.php"));

        expect($isi)->toContain('<style>');
        expect($isi)->not->toContain("@push('gaya')");
    }
});

/* ===================== SISI "SIMPAN KE MASTER" ===================== */

test('sisi musim ikut ditawarkan saat Orcha mengirimkannya', function () {
    $susun = new App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;

    $sisi = $susun->sisiMaster([
        'master_dua_harga' => true,
        'sisi_master' => [
            ['nilai' => 'hari_kerja', 'label' => 'Harga hari kerja', 'harga' => 450_000],
            ['nilai' => 'akhir_pekan', 'label' => 'Harga akhir pekan', 'harga' => 550_000],
            ['nilai' => 'musim:1', 'label' => 'Harga Nataru 2026/2027', 'harga' => 900_000],
        ],
    ]);

    expect($sisi)->toHaveCount(3);
    expect(array_column($sisi, 'nilai'))->toContain('musim:1');
});

test('dua sisi klasik tetap ada walau Orcha belum mengirim sisi_master', function () {
    $susun = new App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;

    /*
     | Orcha dan lemon tidak naik ke server pada detik yang sama. Tanpa jalan
     | mundur ini, lemon baru yang bicara dengan Orcha lama kehilangan kedua
     | tombol sisi — dan "Simpan ke master" kembali menebak sisinya, yaitu
     | kemunduran yang persis dicegah tombol itu dibuat.
     */
    $sisi = $susun->sisiMaster(['master_dua_harga' => true]);

    expect(array_column($sisi, 'nilai'))->toBe(['hari_kerja', 'akhir_pekan']);
});

test('baris bermaster satu harga tidak menawarkan sisi apa pun', function () {
    $susun = new App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;

    expect($susun->sisiMaster(['master_dua_harga' => false]))->toBe([]);
});

test('spanduk RAB menyebut musim perjalanan dan baris yang belum punya tarifnya', function () {
    /*
     | Spanduk inilah satu-satunya tempat selisihnya bisa terbaca sebelum
     | penawaran dikirim. "Memang tidak naik" dan "lupa diisi" terlihat persis
     | sama dari angkanya, dan yang kedua baru terbaca di meja resepsionis
     | hotel saat rombongannya sudah berdiri di sana.
     */
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/susun.blade.php'));

    expect($isi)->toContain("\$musimPerjalanan = \$r['musim_perjalanan'] ?? null;");
    expect($isi)->toContain("\$musimBelum = \$r['musim_belum_diisi'] ?? [];");
    expect($isi)->toContain('belum punya tarif');
});
