<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaHariLiburList;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaMasterHargaList;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Layar Hari Libur dan kolom harga akhir pekan di master harga.
 *
 * Daftarnya milik Orcha; yang diuji di sini lemon mengirim ke jalur yang
 * benar, dan layarnya MENGATAKAN akibatnya — daftar yang kosong, kotak yang
 * mati, dan tanggal yang memang sudah Sabtu semuanya mudah disalahpahami
 * sebagai fitur yang tidak jalan.
 */
function palsukanLibur(array $daftar = [], array $meta = []): void
{
    Http::fake(function (Request $r) use ($daftar, $meta) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_ends_with($jalur, '/hari-libur/banyak') => Http::response([
                'masuk' => 2, 'lewat' => [], 'pesan' => '2 tanggal ditambahkan.',
            ], 201),
            str_contains($jalur, '/hari-libur') && $r->method() === 'POST' => Http::response([
                'data' => ['id' => 9], 'pesan' => '3 harga memakai tarif akhir pekan pada tanggal itu.',
            ], 201),
            str_contains($jalur, '/hari-libur') => Http::response([
                'data' => $daftar,
                'meta' => $meta + ['tahun' => [2027], 'harga_terpengaruh' => 3, 'lewat' => 0],
            ]),
            default => Http::response(['data' => [], 'meta' => []]),
        };
    });
}

/**
 * Pemalsu master harga MILIK BERKAS INI.
 *
 * Sengaja tidak meminjam pemalsu dari OrchaRabTest: fungsi Pest berlaku
 * global hanya saat kedua berkas sama-sama dijalankan, jadi berkas yang
 * meminjamnya hijau dalam rangkaian penuh dan merah begitu dijalankan
 * sendirian — merah yang menunjuk fungsi, bukan fitur.
 */
function palsukanMaster(array $data = []): void
{
    Http::fake(fn (Request $r) => str_contains($r->url(), '/master-harga')
        ? Http::response([
            'data' => $data,
            'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => count($data), 'kosakata' => [
                'kategori' => ['tiket' => 'Tiket', 'transportasi' => 'Transportasi'],
                'satuan' => ['orang' => 'per orang', 'unit' => 'per unit (sekali)', 'unit_hari' => 'per unit per hari'],
                'satuan_berkapasitas' => ['unit', 'unit_hari', 'kamar_malam'],
            ]],
        ], $r->method() === 'POST' ? 201 : 200)
        : Http::response(['data' => [], 'meta' => []]));
}

function barisLibur(array $ubah = []): array
{
    return array_merge([
        'id' => 1,
        'tanggal' => '2027-08-17',
        'tanggal_teks' => 'Selasa, 17 Agustus 2027',
        'nama' => 'HUT RI',
        'oleh' => 'Berto',
        'sudah_akhir_pekan' => false,
        'lewat' => false,
    ], $ubah);
}

test('layar hari libur memakai kerangka dasbor lemon', function () {
    palsukanLibur([barisLibur()]);

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/hari-libur')->assertOk()->getContent();

    foreach (['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-tabel', 'dsb-isian'] as $k) {
        expect($isi)->toContain($k);
    }

    // Dialek lama tidak boleh menyelinap lewat salinan dari layar lain.
    expect($isi)->not->toContain('card border-0 shadow-sm rounded-4');
});

test('daftar kosong mengatakan Sabtu & Minggu tetap terbaca', function () {
    palsukanLibur([]);

    /*
     | Kalimat ini yang menahan admin dari menyimpulkan "fiturnya tidak
     | jalan" lalu mendaftarkan 104 akhir pekan satu per satu.
     */
    $this->actingAs(adminRab())->get('/admin/orcha/hari-libur')->assertOk()
        ->assertSee('Sabtu', false)
        ->assertSee('Belum ada tanggal libur');
});

test('tanggal yang memang jatuh di Sabtu ditandai', function () {
    palsukanLibur([barisLibur([
        'tanggal' => '2027-01-09', 'tanggal_teks' => 'Sabtu, 9 Januari 2027',
        'nama' => 'Libur contoh', 'sudah_akhir_pekan' => true,
    ])]);

    // Mendaftarkannya tidak salah dan tidak merusak apa pun, tetapi juga
    // tidak mengubah harga mana pun — dan itu perlu dikatakan.
    $this->actingAs(adminRab())->get('/admin/orcha/hari-libur')->assertOk()
        ->assertSee('memang akhir pekan');
});

test('tanggal baru dikirim ke jalur hari libur', function () {
    palsukanLibur();

    Livewire::actingAs(adminRab())
        ->test(OrchaHariLiburList::class)
        ->call('bukaTambah')
        ->set('isian.tanggal', '2027-08-17')
        ->call('pakaiNama', 'HUT RI')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/hari-libur')
        && $r['tanggal'] === '2027-08-17' && $r['nama'] === 'HUT RI');
});

test('tanggal tanpa nama ditolak sebelum menyentuh Orcha', function () {
    palsukanLibur();

    Livewire::actingAs(adminRab())
        ->test(OrchaHariLiburList::class)
        ->call('bukaTambah')
        ->set('isian.tanggal', '2027-08-17')
        ->set('isian.nama', '')
        ->call('simpan')
        ->assertHasErrors('isian.nama');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST');
});

test('tempelan daftar libur dipecah jadi baris, pemisahnya bebas', function () {
    palsukanLibur();

    Livewire::actingAs(adminRab())
        ->test(OrchaHariLiburList::class)
        ->set('bukaTempel', true)
        ->set('tempel', "2027-01-01\tTahun Baru\n2027-08-17, HUT RI\n")
        ->call('kirimTempel')
        ->assertHasNoErrors();

    Http::assertSent(function (Request $r) {
        if (! str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/hari-libur/banyak')) {
            return false;
        }

        return $r['baris'] === [
            ['tanggal' => '2027-01-01', 'nama' => 'Tahun Baru'],
            ['tanggal' => '2027-08-17', 'nama' => 'HUT RI'],
        ];
    });
});

test('tempelan yang tidak terbaca ditolak dengan bentuk yang benar disebut', function () {
    palsukanLibur();

    Livewire::actingAs(adminRab())
        ->test(OrchaHariLiburList::class)
        ->set('bukaTempel', true)
        ->set('tempel', "bukan tanggal apa pun\nini juga bukan\n")
        ->call('kirimTempel')
        ->assertHasErrors('tempel');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/hari-libur/banyak'));
});

/* ------------------------- MASTER HARGA ------------------------- */

test('saklar dua harga mengirim harga akhir pekan; mati mengirim null', function () {
    palsukanMaster();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Tiket Baluran')
        ->set('isian.kategori', 'tiket')
        ->set('isian.satuan', 'orang')
        ->set('isian.harga', '21.000')
        ->set('duaHarga', true)
        ->set('isian.harga_akhir_pekan', '31.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/master-harga')
        && $r['harga'] === 21000 && $r['harga_akhir_pekan'] === 31000);
});

test('mematikan saklar mengosongkan angkanya, bukan menyimpannya diam-diam', function () {
    palsukanMaster();

    /*
     | Angka yang tidak terlihat lagi tetapi tetap tersimpan membuat harga
     | akhir pekan terus berlaku padahal admin yakin sudah mematikannya —
     | selisih yang tidak bisa ditemukan karena kotaknya tidak ada di layar.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Tiket Baluran')
        ->set('isian.harga', '21.000')
        ->set('duaHarga', true)
        ->set('isian.harga_akhir_pekan', '31.000')
        ->set('duaHarga', false)
        ->assertSet('isian.harga_akhir_pekan', '')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/master-harga')
        && $r['harga_akhir_pekan'] === null);
});

test('saklar hidup tanpa angka ditolak, tidak dikirim sebagai nol', function () {
    palsukanMaster();

    /*
     | Nol adalah harga yang SAH — ada tempat yang gratis di hari kerja — jadi
     | mengirim 0 untuk "lupa diisi" akan menggratiskan tiketnya tiap Sabtu.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Tiket Baluran')
        ->set('isian.harga', '21.000')
        ->set('duaHarga', true)
        ->call('simpan')
        ->assertHasErrors('isian.harga_akhir_pekan');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST');
});

test('harga akhir pekan ditampilkan di daftar master harga', function () {
    palsukanMaster([[
        'id' => 1, 'kategori' => 'tiket', 'kategori_label' => 'Tiket',
        'vendor_id' => null, 'vendor' => null, 'vendor_kontak' => null,
        'nama' => 'Tiket Baluran', 'provinsi' => 'Jawa Timur', 'daerah' => null,
        'destinasi' => 'Taman Nasional Baluran', 'satuan' => 'orang',
        'satuan_label' => 'per orang', 'harga' => 21000, 'harga_teks' => 'Rp 21.000',
        'harga_akhir_pekan' => 31000, 'harga_akhir_pekan_teks' => 'Rp 31.000',
        'dua_harga' => true, 'kapasitas' => null, 'otomatis' => false, 'aktif' => true,
        'catatan' => null, 'berlaku_sampai' => null, 'berlaku_sampai_teks' => null,
        'kedaluwarsa' => false, 'jumlah_rab' => 0,
        'diubah_pada' => null, 'diubah_teks' => 'baru saja',
    ]]);

    $this->actingAs(adminRab())->get('/admin/orcha/master-harga')->assertOk()
        ->assertSee('Rp 31.000')
        ->assertSee('akhir pekan');
});

/* ------------------- KOTAK KAPASITAS ------------------- */

test('kotak kapasitas tergambar dan tidak disembunyikan CSS', function () {
    palsukanMaster();

    /*
     | ".mh-muat" sempat berarti DUA hal di berkas yang sama — "muat"
     | (kapasitas, display:flex) dan "memuat" (pita loading, display:none).
     | Aturan yang belakangan menang, jadi kotaknya ada di DOM tetapi tidak
     | terlihat, sementara validasinya tetap menuntut diisi: harga bersatuan
     | per unit tidak bisa disimpan sama sekali, tanpa satu pun pesan yang
     | menyebut sebabnya.
     */
    $k = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'transportasi')
        ->set('isian.satuan', 'unit');

    expect($k->instance()->perluKapasitas())->toBeTrue();

    $html = $k->html();

    expect($html)->toContain('id="mh-kapasitas"')
        ->and($html)->toContain('class="mh-muat"')
        // Pitanya memakai nama sendiri, jadi display:none-nya tidak lagi
        // menyentuh kotak kapasitas.
        ->and($html)->toContain('mh-pita-muat');
});

test('tempelan massal menolak satuan berkapasitas sebelum menyentuh Orcha', function () {
    palsukanMaster();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'transportasi')
        ->set('isian.satuan', 'unit')
        ->set('isian.kapasitas', '')
        ->call('bukaTempel')
        ->set('tempelIsi', 'Sewa Hiace Jogja-Banyuwangi 4D3N	8500000')
        ->call('simpanTempel')
        // Galatnya menempel di kotak kapasitas, bukan di kotak tempelan —
        // di sanalah yang harus diisi.
        ->assertHasErrors('isian.kapasitas');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/master-harga/banyak'));
});

test('tempelan massal rute carter mengirim kapasitas bersama barisnya', function () {
    palsukanMaster();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.kategori', 'transportasi')
        ->set('isian.satuan', 'unit')
        ->set('isian.kapasitas', '14')
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->call('bukaTempel')
        ->set('tempelIsi', 'Sewa Hiace Jogja-Banyuwangi 4D3N	8500000
Sewa Hiace Jogja-Bromo 3D2N	6500000')
        ->call('simpanTempel')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/master-harga/banyak')
        && $r['kapasitas'] === 14
        && $r['satuan'] === 'unit'
        && count($r['baris']) === 2);
});

/* ------------------- TAWARAN TIKET KENDARAAN ------------------- */

/**
 * Penyusun RAB dengan tawaran tiket kendaraan dari Orcha.
 *
 * Kasus Alas Purwo: satu destinasi memungut tiket roda 4 DAN tiket bus,
 * keduanya atas destinasi yang sama. Ditarik otomatis, rombongan bermobil
 * ikut membayar tiket bus yang tidak pernah datang.
 */
function palsukanSusunDenganTawaran(array $tawaran): void
{
    $show = json_decode(file_get_contents(base_path('tests/Fixtures/orcha-rab/show.json')), true);
    $show['data']['tawaran_kendaraan'] = $tawaran;

    Http::fake(function (Request $r) use ($show) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_contains($jalur, '/rab/katalog') => Http::response(
                json_decode(file_get_contents(base_path('tests/Fixtures/orcha-rab/katalog.json')), true)
            ),
            str_contains($jalur, '/rab/3') => Http::response($show),
            default => Http::response(['data' => [], 'meta' => []]),
        };
    });
}

function tawaranTiket(array $ubah = []): array
{
    return array_merge([
        'master_harga_id' => 77,
        'nama' => 'Tiket kendaraan roda 4',
        'destinasi' => 'Taman Nasional Alas Purwo',
        'kapasitas' => 4,
        'harga' => 10_000,
        'harga_teks' => 'Rp 10.000',
        'harga_sebab' => null,
        'satuan_label' => 'per unit (sekali)',
    ], $ubah);
}

test('tiket kendaraan muncul sebagai tawaran, bukan baris biaya', function () {
    palsukanSusunDenganTawaran([
        tawaranTiket(),
        tawaranTiket(['master_harga_id' => 78, 'nama' => 'Tiket kendaraan bus', 'kapasitas' => 40, 'harga' => 50_000, 'harga_teks' => 'Rp 50.000']),
    ]);

    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Tiket masuk kendaraan')
        ->assertSee('Tiket kendaraan roda 4')
        ->assertSee('Tiket kendaraan bus')
        // Kalimat inilah yang menahan admin menyangka tarik biaya gagal.
        ->assertSee('Tidak ditarik sendiri', false)
        ->assertSee('Dibayar sekali di gerbang', false);
});

test('tarif akhir pekan ikut disebut di tawarannya', function () {
    palsukanSusunDenganTawaran([
        tawaranTiket(['harga' => 15_000, 'harga_teks' => 'Rp 15.000', 'harga_sebab' => 'harga akhir pekan (Sabtu, 9 Jan)']),
    ]);

    // Tanpa sebabnya, Rp 15.000 di sebelah master Rp 10.000 terbaca sebagai
    // harga master yang salah.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Rp 15.000')
        ->assertSee('harga akhir pekan (Sabtu, 9 Jan)');
});

test('tanpa tawaran, panelnya tidak muncul sama sekali', function () {
    palsukanSusunDenganTawaran([]);

    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertDontSee('Tiket masuk kendaraan');
});

test('memilih tiket kendaraan dikirim sebagai master_harga_id', function () {
    palsukanSusunDenganTawaran([tawaranTiket()]);

    Livewire::actingAs(adminRab())
        ->test(\App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun::class, ['rab' => 3])
        ->call('pakaiTiketKendaraan', 77);

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab/3/biaya')
        && $r['master_harga_id'] === 77);
});
