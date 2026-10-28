<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaMasterHargaList;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabList;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;
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
            str_ends_with($jalur, '/rab/3/itinerary') => Http::response(
                rekamanRab('show') + ['tiket_dibuang' => ['Tiket Borobudur']]
            ),
            str_contains($jalur, '/rab/3/biaya/') && $r->method() === 'DELETE' => Http::response(
                rekamanRab('show') + ['destinasi_tertinggal' => 'Candi Prambanan']
            ),
            str_ends_with($jalur, '/rab/3/pdf') => Http::response('%PDF-1.7 palsu', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="rab.pdf"',
            ]),
            str_ends_with($jalur, '/rab/3/salin') => Http::response([
                'pesan' => 'Disalin jadi RAB-2610-QW9Z. Statusnya draf, dengan masa berlaku baru terhitung hari ini.',
                'data' => ['id' => 9, 'kode' => 'RAB-2610-QW9Z'],
            ], 201),
            str_ends_with($jalur, '/rab/3/versi') => Http::response([
                'data' => [[
                    'id' => 4, 'urutan' => 2, 'harga_total_teks' => 'Rp 9.000.000',
                    'harga_per_orang_teks' => 'Rp 900.000', 'untung_teks' => 'Rp 1.200.000',
                    'persen_untung' => 15.4, 'jumlah_peserta' => 10, 'jumlah_baris' => 6,
                    'oleh' => 'Berto', 'pada' => '20 Okt 2026, 10:00', 'pada_teks' => 'seminggu yang lalu',
                    'beda_kini' => 'lebih murah Rp 500.000 dari sekarang',
                ]],
                'meta' => ['kode' => 'RAB-2609-A7K3', 'harga_kini_teks' => 'Rp 9.500.000'],
            ]),
            str_ends_with($jalur, '/ke-master') => Http::response(
                rekamanRab('show') + ['pesan' => 'Master harga "Tiket Candi Prambanan" turun jadi Rp 40.000. RAB berikutnya memakai angka ini.']
            ),
            str_contains($jalur, '/rab/3') => Http::response(rekamanRab('show')),
            str_ends_with($jalur, '/rab') && $r->method() === 'POST' => Http::response(rekamanRab('show'), 201),
            str_ends_with($jalur, '/rab') => Http::response(rekamanRab('daftar')),
            str_ends_with($jalur, '/master-harga/massal') => Http::response(['pesan' => '2 harga dinaikkan.', 'jumlah' => 2, 'batch' => 7]),
            str_ends_with($jalur, '/massal/7/batal') => Http::response(['pesan' => '2 harga dikembalikan seperti semula.', 'jumlah' => 2]),
            str_ends_with($jalur, '/master-harga/id-cocok') => Http::response([
                'data' => [1, 2, 3, 4, 5, 6, 7], 'meta' => ['jumlah' => 7],
            ]),
            str_ends_with($jalur, '/master-harga/salin-provinsi') => Http::response([
                'pesan' => '4 harga disalin ke Bali. 1 dilewati karena namanya sudah ada di sana.',
                'masuk' => 4, 'lewat' => 1,
            ], 201),
            str_ends_with($jalur, '/master-harga/banyak') => Http::response([
                'pesan' => '2 harga ditambahkan. 1 baris dilewati.',
                'masuk' => 2,
                'gagal' => [['baris' => 1, 'nama' => 'Tiket Borobudur', 'sebab' => 'sudah ada di wilayah yang sama']],
            ], 201),
            str_contains($jalur, '/master-harga/riwayat') => Http::response(['meta' => ['jumlah' => 340, 'batas' => 100], 'data' => [[
                'id' => 9, 'harga_id' => 1, 'harga_nama' => 'Tiket Candi Prambanan',
                'harga_lama' => 'Rp 50.000', 'harga_baru' => 'Rp 60.000',
                'selisih' => '+Rp 10.000', 'naik' => true, 'persen' => 20.0,
                'oleh' => 'Berto', 'catatan' => null,
                'pada' => '18 Sep 2026, 19:26', 'pada_teks' => '2 bulan yang lalu',
            ]]]),
            str_ends_with($jalur, '/dipakai') => Http::response([
                'data' => [[
                    'rab_id' => 3, 'kode' => 'RAB-0003', 'judul' => 'Open Trip Dieng',
                    'status' => 'draft', 'tanggal' => '12 Des 2026',
                    'harga_beku' => 'Rp 50.000', 'beda' => 'lebih murah dari sekarang',
                ]],
                'meta' => ['nama' => 'Tiket Candi Prambanan', 'harga_kini' => 'Rp 60.000'],
            ]),
            str_ends_with($jalur, '/riwayat') => Http::response([
                'data' => [[
                    'id' => 9, 'harga_lama' => 'Rp 50.000', 'harga_baru' => 'Rp 60.000',
                    'selisih' => '+Rp 10.000', 'naik' => true, 'persen' => 20.0,
                    'oleh' => 'Berto', 'catatan' => null,
                    'pada' => '18 Sep 2026, 19:26', 'pada_teks' => '2 bulan yang lalu',
                ]],
                'meta' => ['nama' => 'Tiket Candi Prambanan', 'harga_kini' => 'Rp 60.000'],
            ]),
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
        // Beda harga ditawarkan DUA arah: angkanya sendiri tidak bisa
        // membedakan "master naik" dari "tim menemukan lebih murah".
        ->assertSee('pakai harga master')
        ->assertSee('simpan ke master')
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
        // Untung per kepala tetap disebut — kini di kartu untungnya sendiri,
        // sebagai "<angka> per orang", bukan sebagai baris berlabel.
        ->and($isi)->toContain($ringkas['untung_per_orang_teks'].' per orang');
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

    expect($isi)->toContain('tidak ada di daftar destinasi')
        ->and($isi)->toContain('tidak akan ikut terbawa saat');
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

    expect($isi)->toContain('Nama ini akan terisi sendiri')
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

    expect($layar->html())->toContain('tidak akan pernah ikut terbawa ke RAB');

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

/*
 | ---------------------------------------------------------------------------
 | Vendor: dari siapa sebuah harga dibeli
 | ---------------------------------------------------------------------------
 */

test('vendor dipilih dari daftar, kontaknya terlihat di formulir', function () {
    /*
     | Nomornya TIDAK diketik di master harga — ia hidup di daftar vendor,
     | supaya nomor yang berganti cukup diubah sekali dan seluruh harga yang
     | memakainya ikut benar.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah');

    expect($layar->html())->toContain('id="mh-vendor"')
        ->and($layar->html())->toContain('PO Sumber Jaya');

    $layar->set('isian.vendor_id', '1');

    // Nomornya muncul begitu vendornya dipilih: yang harus dicari di menu
    // lain adalah yang tidak sempat dicari.
    expect($layar->html())->toContain('081234000111 (Pak Budi)');
});

test('vendor tanpa nomor ditandai di formulir harga', function () {
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.vendor_id', '3')     // Vendor Tanpa Nomor
        ->html();

    expect($isi)->toContain('belum punya nomor');
});

test('vendor terkirim sebagai id, kosong jadi null', function () {
    // Kolomnya kunci asing — "" bukan id yang ada di tabel vendor, dan
    // mengirimnya membuat Orcha menolak dengan galat yang tidak menyebut
    // sebabnya.
    palsukanRab();
    $this->actingAs(adminRab());

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.vendor_id', '1')
        ->set('isian.destinasi', 'Candi Prambanan')
        ->set('isian.harga', '60000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with($r->url(), '/master-harga') && $r['vendor_id'] === 1);

    Livewire::test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.destinasi', 'Candi Prambanan')
        ->set('isian.harga', '60000')
        ->call('simpan');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with($r->url(), '/master-harga')
        && array_key_exists('vendor_id', $r->data()) && $r['vendor_id'] === null);
});

test('vendor ikut terbaca di daftar harga', function () {
    palsukanRab();

    $this->actingAs(adminRab())->get('/admin/orcha/master-harga')
        ->assertOk()
        ->assertSee('PO Sumber Jaya')
        ->assertSee('081234000111 (Pak Budi)');
});

test('kontak vendor terbaca di rincian biaya RAB', function () {
    /*
     | Inti seluruh pekerjaan ini: menelepon vendor tidak lagi menuntut
     | pindah menu. Nomornya dibaca HIDUP dari daftar vendor, jadi yang
     | tertulis selalu nomor yang berlaku hari ini.
     */
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()->getContent();

    expect($isi)->toContain('PO Sumber Jaya')
        ->and($isi)->toContain('081234000111 (Pak Budi)');
});

test('vendor baru bisa ditambahkan tanpa meninggalkan formulir harga', function () {
    /*
     | Satu-satunya jalan sebelumnya adalah tautan ke daftar vendor — yang
     | MEMINDAHKAN halaman, dan seluruh isian harga yang sudah diketik hilang
     | bersamanya. Persoalan yang sama dengan destinasi baru.
     */
    $katalogDipanggil = 0;

    Http::fake(function (Request $r) use (&$katalogDipanggil) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            $katalogDipanggil++;
            $data = rekamanRab('katalog');

            if ($katalogDipanggil > 1) {
                $data['data']['vendor'][] = ['id' => 77, 'nama' => 'PO Baru Jaya', 'kontak' => '0899', 'jenis' => null];
            }

            return Http::response($data);
        }

        if (str_ends_with($jalur, '/vendor')) {
            return Http::response(['data' => ['id' => 77, 'nama' => 'PO Baru Jaya'], 'pesan' => 'Vendor ditambahkan.'], 201);
        }

        return Http::response(rekamanRab('master'));
    });

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Sewa Bus Baru')      // isian yang TIDAK boleh hilang
        ->set('vendorBaru', true)
        ->set('namaVendorBaru', '  PO Baru   Jaya ')
        ->set('kontakVendorBaru', '0899')
        ->call('tambahVendorKatalog');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor')
        && $r['nama'] === 'PO Baru Jaya'
        && $r['kontak'] === '0899');

    $layar->assertSet('isian.vendor_id', '77')      // langsung terpilih
        ->assertSet('vendorBaru', false)
        ->assertSet('isian.nama', 'Sewa Bus Baru'); // isiannya utuh

    // Katalognya disimpan sepuluh menit; tanpa dilupakan, vendor yang baru
    // dibuat tidak muncul di kotak pilihnya sendiri.
    expect($katalogDipanggil)->toBeGreaterThan(1)
        ->and($layar->html())->toContain('PO Baru Jaya');
});

test('nama vendor baru yang terlalu pendek tidak dikirim', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('vendorBaru', true)
        ->set('namaVendorBaru', 'P')
        ->call('tambahVendorKatalog')
        ->assertHasErrors('namaVendorBaru')
        ->assertSet('vendorBaru', true);

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor'));
});

test('harga bisa disaring menurut vendornya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('filterVendor', '1');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga')
        && $r->method() === 'GET' && str_contains($r->url(), 'vendor=1'));

    // "-" berarti yang BELUM punya vendor — daftar pekerjaan yang tersisa.
    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('filterVendor', '-');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga')
        && $r->method() === 'GET' && str_contains($r->url(), 'vendor=-'));
});

test('biaya manual bisa menyebut vendornya', function () {
    // Justru di biaya manual vendor dadakan muncul: sewa perahu, jip,
    // pemandu lokal.
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('bukaManual', true)
        ->set('manual.nama', 'Sewa perahu Rawa Jombor')
        ->set('manual.harga_satuan', '350000')
        ->set('manual.vendor_id', '1')
        ->call('tambahManual');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab/3/biaya')
        && $r['vendor_id'] === 1);
});

test('biaya manual tanpa vendor mengirim null, bukan string kosong', function () {
    // Kolomnya kunci asing — "" bukan id yang ada di tabel vendor.
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('bukaManual', true)
        ->set('manual.nama', 'Parkir tambahan')
        ->set('manual.harga_satuan', '20000')
        ->call('tambahManual');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab/3/biaya')
        && array_key_exists('vendor_id', $r->data()) && $r['vendor_id'] === null);
});

test('vendor di master harga dan rincian biaya RAB membawa ke rinciannya', function () {
    // Tiga jalan buntu: nomornya terbaca, tetapi rinciannya hanya bisa
    // dibuka lewat menu lain.
    palsukanRab();
    $this->actingAs(adminRab());

    $isi = Livewire::test(OrchaMasterHargaList::class)->call('bukaTambah')
        ->set('isian.vendor_id', '1')->html();

    expect($isi)->toContain(route('admin.orcha.vendor.detail', 1));

    $this->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee(route('admin.orcha.vendor.detail', 1), false);

    $this->get('/admin/orcha/master-harga')->assertOk()
        ->assertSee(route('admin.orcha.vendor.detail', 1), false);
});

test('master harga menyebut dipakai berapa kali dan kapan terakhir diubah', function () {
    /*
     | Dua angka yang menjawab pertanyaan yang sama: harga ini masih relevan
     | atau tidak. Keduanya sudah ada di balasan Orcha sejak lama dan tidak
     | pernah ditampilkan.
     */
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/master-harga')->assertOk()->getContent();

    expect($isi)->toContain('3&times;')
        ->and($isi)->toContain('2 bulan yang lalu')
        // Yang belum pernah dipakai ditulis "belum", bukan angka nol —
        // nol di kolom berisi angka terbaca sebagai jumlah.
        ->and($isi)->toContain('belum');
});

test('vendor punya kolom sendiri, yang belum ada ditandai', function () {
    // Sebagai baris kecil di bawah nama, "mana yang belum ada vendornya"
    // harus dipindai satu per satu.
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/master-harga')->assertOk()->getContent();

    expect($isi)->toContain('Dibeli dari')
        ->and($isi)->toContain('mh-tanpa-vendor');
});

test('harga yang belum pernah dipakai bisa disaring dari layar', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('filterDipakai', 'belum');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga')
        && $r->method() === 'GET' && str_contains($r->url(), 'dipakai=belum'));
});

test('tombol PDF master harga membawa saringannya', function () {
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('filterKategori', 'tiket')
        ->set('filterDipakai', 'belum')
        ->html();

    // Dicocokkan per bagian: Blade menulis "&" sebagai "&amp;".
    expect($isi)->toContain(route('admin.orcha.master-harga.pdf'))
        ->and($isi)->toContain('kategori=tiket')
        ->and($isi)->toContain('dipakai=belum');
});

test('beberapa harga bisa dipasangkan ke satu vendor sekaligus', function () {
    /*
     | Satu vendor biasanya memasok beberapa harga. Lewat formulir,
     | memasangnya berarti membuka, menyimpan, dan menutup sebanyak
     | barisnya, untuk satu keputusan yang sama.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('pilih', ['1', '2'])
        ->set('pasangKeVendor', '1')
        ->call('pasangVendor')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor/1/pasang')
        && $r['harga'] === [1, 2]);

    // Pilihannya dilepas supaya bilahnya tidak menggantung sesudah selesai.
    $layar->assertSet('pilih', [])->assertSet('pasangKeVendor', '');
});

test('memasangkan tanpa memilih vendor ditahan, tidak dikirim', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('pilih', ['1'])
        ->call('pasangVendor')
        ->assertHasErrors('pasangKeVendor');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/pasang'));
});

test('penolakan harga kembar dari Orcha sampai apa adanya ke admin', function () {
    /*
     | Orcha menjelaskan sendiri kembarannya yang mana. Menggantinya dengan
     | "Orcha membalas dengan kode 422" membuang satu-satunya kalimat yang
     | bisa ditindaklanjuti.
     */
    Http::fake(function (Request $r) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_ends_with($jalur, '/master-harga') && $r->method() === 'POST') {
            return Http::response([
                'pesan' => 'Sudah ada harga untuk destinasi "Candi Prambanan" di wilayah yang sama.',
            ], 422);
        }

        return str_contains($jalur, '/rab/katalog')
            ? Http::response(rekamanRab('katalog'))
            : Http::response(rekamanRab('master'));
    });

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.destinasi', 'Candi Prambanan')
        ->set('isian.harga', '60000')
        ->call('simpan')
        ->assertDispatched('toast-error', message: 'Sudah ada harga untuk destinasi "Candi Prambanan" di wilayah yang sama.');
});

test('tiap sel master harga berdiri di bawah judul kolomnya', function () {
    /*
     | Satu <th> yang lupa ditulis tidak membuat tabelnya rusak — peramban
     | tetap menggambarnya rapi, hanya saja seluruh sel mulai dari kolom
     | yang hilang bergeser satu ke kanan. Yang terbaca admin: wilayah di
     | bawah judul "Dibeli dari", vendor di bawah "Harga". Semuanya masuk
     | akal dibaca satu-satu, dan semuanya salah.
     |
     | Di layar sempit cacat ini tidak terlihat sama sekali: thead
     | disembunyikan dan tiap sel memakai label data-judul-nya sendiri.
     */
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->html();

    /*
     | Dipatok ke tabelnya dulu. Blok <style> halaman ini berisi aturan
     | untuk ".dsb-tabel tbody tr" beserta komentarnya, dan komentar CSS
     | ikut terkirim ke peramban — mencari "<tbody" di seluruh halaman
     | menangkap kalimat di komentar, bukan markahnya.
     */
    preg_match('#<table class="dsb-tabel mh-tabel">(.*?)</table>#s', $isi, $tabel);

    expect($tabel)->not->toBeEmpty();

    preg_match('#<thead>(.*?)</thead>#s', $tabel[1], $kepala);
    // Baris DATA yang pertama, bukan pita kategori yang mendahuluinya —
    // pita itu satu sel ber-colspan, dan menghitung selnya selalu memulangkan 1.
    preg_match('#<tr wire:key="mh-(.*?)</tr>#s', $tabel[1], $barisPertama);

    expect($kepala)->not->toBeEmpty()
        ->and($barisPertama)->not->toBeEmpty();

    $jumlahTh = substr_count($kepala[1], '</th>');
    $jumlahTd = substr_count($barisPertama[1], '</td>');

    expect($jumlahTh)->toBe($jumlahTd);

    /*
     | Dan tiap judul kolom memakai perataan yang sama dengan selnya —
     | angka rata kanan, "Dipakai" di tengah. Judul rata kiri di atas
     | angka rata kanan membuat keduanya tampak milik kolom berbeda.
     */
    preg_match_all('#<t([hd])([^>]*)>#', $kepala[1].$barisPertama[1], $sel, PREG_SET_ORDER);

    $rataKepala = [];
    $rataSel = [];
    foreach ($sel as $s) {
        $rata = str_contains($s[2], 'text-end') ? 'kanan'
            : (str_contains($s[2], 'text-center') ? 'tengah' : 'kiri');
        $s[1] === 'h' ? $rataKepala[] = $rata : $rataSel[] = $rata;
    }

    expect($rataKepala)->toBe($rataSel);
});

/*
|--------------------------------------------------------------------------
| Master harga: menemukan, mengurutkan, menyalin, dan mengubah massal
|--------------------------------------------------------------------------
*/

test('saringan status dan pengurut diteruskan ke Orcha apa adanya', function () {
    /*
     | Hitungannya milik Orcha. Yang diperiksa di sini: pilihan di layar
     | sampai ke jalur yang benar — saringan yang terlihat terpasang tetapi
     | tidak pernah terkirim adalah cacat yang tampak seperti data salah.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('filterStatus', 'kedaluwarsa')
        ->set('urut', 'termahal');

    Http::assertSent(function (Request $r) {
        $q = parse_url($r->url(), PHP_URL_QUERY) ?? '';
        parse_str($q, $p);

        return str_contains($r->url(), '/master-harga')
            && ($p['status'] ?? null) === 'kedaluwarsa'
            && ($p['urut'] ?? null) === 'termahal';
    });
});

test('ringkasan dibaca dari Orcha dan setiap angkanya bisa diklik', function () {
    /*
     | Angka yang menunjuk pekerjaan tetapi tidak membawa ke sana cuma
     | menambah beban. Yang diperiksa: angkanya memang digambar, dan
     | tombolnya memasang saringan yang dijanjikan.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class);

    expect($layar->html())->toContain('Belum punya vendor')
        ->and($layar->html())->toContain('Masa berlakunya lewat');

    $layar->set('filterStatus', 'kedaluwarsa')->assertSet('filterStatus', 'kedaluwarsa');
});

test('kenaikan massal menolak persen kosong sebelum menyentuh Orcha', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('pilih', ['1', '2'])
        ->set('aksiMassal', 'naik')
        ->set('persenMassal', '')
        ->call('jalankanMassal')
        ->assertHasErrors('persenMassal');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/massal'));
});

test('persen di luar batas tidak pernah dikirim', function () {
    /*
     | Satu angka nol kelebihan tidak boleh menghapus harga seisi kategori.
     | Orcha menolaknya juga — penjagaan di layar hanya supaya galatnya
     | terbaca sebagai kalimat, bukan sebagai kegagalan jaringan.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('pilih', ['1'])
        ->set('aksiMassal', 'turun')
        ->set('persenMassal', '150')
        ->call('jalankanMassal')
        ->assertHasErrors('persenMassal');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/massal'));
});

test('kenaikan massal mengirim koma sebagai titik', function () {
    /*
     | Admin mengetik "7,5" karena begitulah angka ditulis di sini; JSON
     | hanya mengenal titik. Tanpa penerjemahan ini yang terkirim adalah 7.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('pilih', ['1', '2'])
        ->set('aksiMassal', 'naik')
        ->set('persenMassal', '7,5')
        ->call('jalankanMassal')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga/massal')
        && $r['persen'] === 7.5
        && $r['aksi'] === 'naik'
        && $r['id'] === [1, 2]);
});

test('perintah massal mengosongkan centangnya setelah berhasil', function () {
    /*
     | Centang yang tertinggal setelah perintah dijalankan adalah perintah
     | kedua yang menunggu ditekan tanpa disadari.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('pilih', ['1', '2'])
        ->set('aksiMassal', 'matikan')
        ->call('jalankanMassal')
        ->assertSet('pilih', [])
        ->assertSet('aksiMassal', '');
});

test('menyalin membuka baris BARU, bukan menyunting yang disalin', function () {
    /*
     | Kalau mode suntingnya ikut terbawa, "salin" diam-diam menimpa baris
     | aslinya — kerusakan yang baru ketahuan saat harga lamanya hilang.
     */
    palsukanRab();

    $baris = rekamanRab('master')['data'][0];

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('salin', $baris);

    $layar->assertSet('sunting', null)
        ->assertSet('tambah', true)
        ->assertSet('isian.nama', $baris['nama'].' (salinan)')
        // Sisanya memang ikut tersalin — itulah gunanya.
        ->assertSet('isian.kategori', $baris['kategori'])
        ->assertSet('isian.destinasi', $baris['destinasi']);
});

test('riwayat harga dibaca dari Orcha dan ditutup tanpa meninggalkan sisa', function () {
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaRiwayat', 1);

    expect($layar->html())->toContain('Riwayat harga')
        ->and($layar->html())->toContain('+Rp 10.000');

    $layar->call('tutupRiwayat')
        ->assertSet('riwayatUntuk', null)
        ->assertSet('riwayat', []);
});

test('centang semua hanya menyentuh baris yang sedang terlihat', function () {
    /*
     | Mencentang baris yang tidak kelihatan adalah cara tercepat mematikan
     | harga yang tidak pernah dilihat orangnya.
     */
    palsukanRab();

    $terlihat = collect(rekamanRab('master')['data'])->pluck('id')->all();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('pilihSemua', $terlihat);

    expect($layar->get('pilih'))->toBe(array_map('strval', $terlihat));

    // Menekannya lagi melepas semuanya.
    $layar->call('pilihSemua', $terlihat)->assertSet('pilih', []);
});

test('masa berlaku ikut terkirim saat menyimpan', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Tiket uji')
        ->set('isian.harga', '50.000')
        ->set('isian.berlaku_sampai', '2027-03-01')
        ->call('simpan');

    Http::assertSent(fn (Request $r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/master-harga')
        && $r->method() === 'POST'
        && $r['berlaku_sampai'] === '2027-03-01');
});

/*
|--------------------------------------------------------------------------
| Master harga: jalan keluar, tambah banyak, batalkan, telusur
|--------------------------------------------------------------------------
*/

test('penolakan kembaran tinggal di formulir berikut jalan keluarnya', function () {
    /*
     | Penolakan ini menuntut KEPUTUSAN — ganti namanya, atau memang dua
     | harga yang berbeda. Keputusan tidak bisa diambil dari toast yang
     | sudah lenyap, jadi ia harus tinggal di layar.
     */
    Http::fake(function (Request $r) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            return Http::response(rekamanRab('katalog'));
        }
        if (str_ends_with($jalur, '/master-harga') && $r->method() === 'POST') {
            return Http::response([
                'pesan' => 'Sudah ada harga bernama "Tiket Candi Prambanan" di wilayah yang sama.',
                'kembaran' => ['id' => 1, 'nama' => 'Tiket Candi Prambanan', 'harga_teks' => 'Rp 60.000'],
            ], 422);
        }

        return Http::response(rekamanRab('master'));
    });

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Tiket Candi Prambanan')
        ->set('isian.harga', '60.000')
        ->call('simpan');

    // Formulirnya TIDAK ditutup, dan pesannya tergambar berikut tombolnya.
    $layar->assertSet('tambah', true);

    expect($layar->get('kembaranPesan'))->toContain('Sudah ada harga bernama')
        ->and($layar->get('kembaranBaris')['harga_teks'])->toBe('Rp 60.000')
        ->and($layar->html())->toContain('Tetap simpan');
});

test('tetap simpan menerobos penjaga lewat paksa=1', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Tiket Candi Prambanan')
        ->set('isian.harga', '60.000')
        ->call('simpanPaksa');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga?paksa=1')
        && $r->method() === 'POST');
});

test('tambah lagi mempertahankan yang khas sesinya, mengosongkan yang khas barisnya', function () {
    /*
     | Dua puluh tiket Jogja berarti kategori, provinsi, satuan, dan
     | vendornya sama dua puluh kali. Mengosongkan semuanya membuat "tambah
     | lagi" sama lelahnya dengan membuka formulir baru.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('lanjutTambah', true)
        ->set('isian.kategori', 'tiket')
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->set('isian.nama', 'Tiket Satu')
        ->set('isian.harga', '50.000')
        ->call('simpan');

    $layar->assertSet('tambah', true)
        ->assertSet('isian.nama', '')
        ->assertSet('isian.harga', '')
        ->assertSet('isian.provinsi', 'DI Yogyakarta')
        ->assertSet('isian.kategori', 'tiket');
});

test('tempelan spreadsheet dipecah apa adanya, tab maupun titik koma', function () {
    /*
     | Menyalin dari Excel memberi tab, dari sebagian tabel web memberi
     | titik koma, dan admin tidak bisa diminta tahu bedanya. "Rp 50.000"
     | dan "50000" sama-sama angka yang sama.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('tempelIsi', "Tiket Borobudur\tRp 50.000\nTiket Dieng;30000;Dataran Tinggi Dieng\n\n   \nTanpa harga");

    $baris = $layar->instance()->barisTempel();

    expect($baris)->toHaveCount(2)
        ->and($baris[0])->toBe(['nama' => 'Tiket Borobudur', 'harga' => 50000, 'destinasi' => null])
        ->and($baris[1])->toBe(['nama' => 'Tiket Dieng', 'harga' => 30000, 'destinasi' => 'Dataran Tinggi Dieng']);
});

test('baris tempelan yang dilewati tetap terlihat setelah sisanya masuk', function () {
    /*
     | Baris yang gugur adalah pekerjaan yang masih harus diselesaikan —
     | kalau ia hilang bersama panelnya, tidak ada yang tahu ia pernah ada.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->call('bukaTempel')
        ->set('tempelIsi', "Tiket Borobudur\t50000\nTiket Dieng\t30000\nTiket Ketep\t15000")
        ->call('simpanTempel');

    $layar->assertSet('tempelBuka', true);

    expect($layar->get('tempelGagal'))->toHaveCount(1)
        ->and($layar->html())->toContain('sudah ada di wilayah yang sama');
});

test('perintah massal menawarkan pembatalan tepat sesudahnya', function () {
    /*
     | Tawaran yang datang belakangan tidak pernah dipakai: saat orang sadar
     | salah, yang dicarinya tombol di tempat ia menekan tadi.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('pilih', ['1', '2'])
        ->set('aksiMassal', 'naik')
        ->set('persenMassal', '10')
        ->call('jalankanMassal');

    $layar->assertSet('batchTerakhir', 7);
    expect($layar->html())->toContain('Batalkan, kembalikan seperti semula');

    $layar->call('batalkanBatch')->assertSet('batchTerakhir', null);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga/massal/7/batal'));
});

test('kolom Dipakai membuka daftar RAB berikut harga yang dibekukan di sana', function () {
    /*
     | Yang ditampilkan harga yang DIBEKUKAN di RAB itu, bukan harga hari
     | ini — justru selisihnya yang jadi alasan orang membukanya.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaDipakai', 1);

    expect($layar->html())->toContain('RAB-0003')
        ->and($layar->html())->toContain('Rp 50.000')
        ->and($layar->html())->toContain('lebih murah dari sekarang');

    $layar->call('tutupDipakai')->assertSet('dipakaiUntuk', null);
});

test('perubahan terakhir seluruh daftar terbaca dalam satu panel', function () {
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaRiwayatSemua');

    expect($layar->html())->toContain('Tiket Candi Prambanan')
        ->and($layar->html())->toContain('+Rp 10.000');

    $layar->call('tutupRiwayatSemua')->assertSet('riwayatSemua', []);
});

test('kotak pilih menggambarkan nilai yang tersimpan, bukan pilihan pertama', function () {
    /*
     | <select> TIDAK otomatis menandai pilihan yang dipegang komponen —
     | wire:model mengurus perubahan, bukan gambar awalnya. Tanpa
     | @selected(), membuka "Ubah" pada baris transportasi menggambarkan
     | kategorinya sebagai "Tiket & Retribusi" (pilihan pertama daftar),
     | dan menekan Simpan mengubah kategorinya diam-diam.
     |
     | Cacat yang tidak berbunyi: halamannya tetap tergambar rapi, dan yang
     | salah baru ketahuan saat harga transportasi berpindah ke tiket.
     */
    palsukanRab();

    $baris = collect(rekamanRab('master')['data'])->firstWhere('kategori', 'transportasi');

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaSunting', $baris['id'], $baris)
        ->html();

    preg_match('#<select id="mh-kategori".*?</select>#s', $isi, $kategori);
    preg_match('#<select id="mh-satuan".*?</select>#s', $isi, $satuan);

    expect($kategori[0])->toMatch('/<option value="transportasi"[^>]*\bselected\b/')
        ->and($kategori[0])->not->toMatch('/<option value="tiket"[^>]*\bselected\b/')
        ->and($satuan[0])->toMatch('/<option value="unit_hari"[^>]*\bselected\b/');
});

test('saringan yang terbawa dari alamat tergambar sebagai terpilih', function () {
    /*
     | Saringan ini hidup di URL (#[Url]), jadi tautan yang dibagikan atau
     | halaman yang dimuat ulang datang dengan nilainya sudah terpasang.
     | Kalau kotaknya tetap membaca "Aktif & dimatikan" sementara daftarnya
     | hanya memuat yang mati, yang terbaca admin adalah data yang salah.
     */
    palsukanRab();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('filterStatus', 'mati')
        ->set('urut', 'termahal')
        ->set('filterDipakai', 'belum')
        ->html();

    expect($isi)->toMatch('/<option value="mati"[^>]*\bselected\b/')
        ->and($isi)->toMatch('/<option value="termahal"[^>]*\bselected\b/')
        ->and($isi)->toMatch('/<option value="belum"[^>]*\bselected\b/');
});

/*
|--------------------------------------------------------------------------
| Master harga: halaman, pilih semua yang cocok, salin provinsi, urut kolom
|--------------------------------------------------------------------------
*/

test('jumlah baris per halaman dipilih admin, tetapi hanya dari daftar yang dikenal', function () {
    /*
     | Angkanya datang dari URL. "?baris=100000" akan meminta Orcha
     | memulangkan seluruh tabel dalam satu permintaan — jadi yang di luar
     | daftar dikembalikan ke sepuluh, bukan dipercaya apa adanya.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class);

    $layar->set('barisHalaman', 50);
    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'per_halaman=50'));

    $layar->set('barisHalaman', 100000);
    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'per_halaman=10'));
});

test('centang semua yang cocok meminta id ke Orcha, bukan merakit dari halaman', function () {
    /*
     | Yang terlihat cuma sepuluh dari seratus. Mencentang yang terlihat
     | lalu menyebutnya "semua" adalah janji yang tidak ditepati — dan
     | perintah massal berikutnya menyentuh lebih sedikit baris daripada
     | yang dikira admin.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->set('filterKategori', 'tiket')
        ->call('pilihSemuaCocok');

    expect($layar->get('pilih'))->toBe(['1', '2', '3', '4', '5', '6', '7']);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga/id-cocok')
        && str_contains($r->url(), 'kategori=tiket'));
});

test('salin antar provinsi menolak tujuan yang sama dengan asalnya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaSalin')
        ->set('salinDari', 'Bali')
        ->set('salinKe', 'Bali')
        ->call('salinProvinsi')
        ->assertHasErrors('salinKe');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'salin-provinsi'));
});

test('salin antar provinsi mengirim asal dan tujuan, lalu menutup panelnya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaSalin')
        ->set('salinDari', 'DI Yogyakarta')
        ->set('salinKe', 'Bali')
        ->call('salinProvinsi')
        ->assertSet('salinBuka', false);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga/salin-provinsi')
        && $r['dari'] === 'DI Yogyakarta' && $r['ke'] === 'Bali');
});

test('menekan judul kolom mengurutkan, dan menekannya lagi membalik arah', function () {
    /*
     | Itu yang dicoba orang lebih dulu, sebelum kotak "Urut" ditemukan.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class);

    $layar->call('urutkanLewatKolom', 'harga')->assertSet('urut', 'termahal');
    expect($layar->instance()->arahUrut('harga'))->toBe('turun');

    $layar->call('urutkanLewatKolom', 'harga')->assertSet('urut', 'termurah');
    expect($layar->instance()->arahUrut('harga'))->toBe('naik');

    // Pindah ke kolom lain, bukan membalik yang ini.
    $layar->call('urutkanLewatKolom', 'dipakai')->assertSet('urut', 'tersering');
    expect($layar->instance()->arahUrut('harga'))->toBeNull();
});

test('tanggal berlaku yang sudah lewat diperingatkan, bukan ditolak', function () {
    /*
     | Harga lama yang memang sudah kedaluwarsa sah dicatat apa adanya.
     | Yang tidak boleh adalah diterima DIAM-DIAM: salah ketik "2020" saat
     | menambah harga baru lalu tidak pernah ketahuan.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->set('isian.berlaku_sampai', now()->subYear()->toDateString());

    expect($layar->instance()->berlakuSudahLewat())->toBeTrue()
        ->and($layar->html())->toContain('Tanggal ini sudah lewat');

    // Dan tetap bisa disimpan.
    $layar->set('isian.nama', 'Tiket lama')->set('isian.harga', '10.000')
        ->call('simpan')->assertHasNoErrors();

    $layar->set('isian.berlaku_sampai', now()->addYear()->toDateString());
    expect($layar->instance()->berlakuSudahLewat())->toBeFalse();
});

test('panel perubahan terakhir mengatakan berapa yang tidak ditampilkan', function () {
    /*
     | Daftar yang dipotong diam-diam membuat orang mengira itulah
     | seluruhnya.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaRiwayatSemua');

    expect($layar->html())->toContain('dari')
        ->and($layar->html())->toContain('340');

    // Periode dipersempit → dimintakan ulang ke Orcha.
    $layar->set('riwayatHari', 7);
    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/master-harga/riwayat')
        && str_contains($r->url(), 'hari=7'));
});

/*
|--------------------------------------------------------------------------
| Daftar RAB: ringkasan, saringan, salin, versi, dan tautan WhatsApp
|--------------------------------------------------------------------------
*/

test('saringan dan pengurut daftar RAB diteruskan ke Orcha', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->set('filterProvinsi', 'Bali')
        ->set('filterJenis', 'open')
        ->set('urut', 'berangkat')
        ->set('filterKedaluwarsa', true);

    Http::assertSent(function (Request $r) {
        parse_str(parse_url($r->url(), PHP_URL_QUERY) ?? '', $p);

        return str_contains($r->url(), '/rab')
            && ($p['provinsi'] ?? null) === 'Bali'
            && ($p['jenis'] ?? null) === 'open'
            && ($p['urut'] ?? null) === 'berangkat'
            && ($p['kedaluwarsa'] ?? null) === '1';
    });
});

test('warna untung mengikuti tipisnya margin, bukan selalu hijau', function () {
    /*
     | Ini satu-satunya angka di layar yang bisa mencegah penawaran merugi
     | terkirim. Selalu hijau membuat yang merugi pun tergambar meyakinkan.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabList::class)->instance();

    expect($layar->rupaUntung(-3.5))->toBe('rugi')
        ->and($layar->rupaUntung(0.0))->toBe('tipis')
        ->and($layar->rupaUntung(9.9))->toBe('tipis')
        ->and($layar->rupaUntung(18.0))->toBe('sehat')
        ->and($layar->rupaUntung(null))->toBe('netral');
});

test('tautan WhatsApp dinormalkan ke format internasional', function () {
    /*
     | wa.me menolak "0812…". Nomor yang tidak masuk akal tidak diberi
     | tombolnya sama sekali — tombol yang membuka WhatsApp kosong lebih
     | buruk daripada tidak ada tombol.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabList::class)->instance();

    $baris = [
        'whatsapp' => '0812-3400-0111', 'nama_pelanggan' => 'Bu Sari', 'judul' => 'Private Trip Jogja',
        'kode' => 'RAB-2609-A7K3', 'harga_per_orang_teks' => 'Rp 900.000', 'jumlah_peserta' => 10,
    ];

    $tautan = $layar->tautanWa($baris);

    expect($tautan)->toStartWith('https://wa.me/6281234000111?text=')
        ->and(urldecode($tautan))->toContain('Bu Sari')
        ->and(urldecode($tautan))->toContain('RAB-2609-A7K3')
        ->and($layar->tautanWa(['whatsapp' => '08'] + $baris))->toBeNull();
});

test('menyalin penawaran mengirim hanya kotak yang diisi', function () {
    /*
     | Menyalin biasanya berarti "sama, kecuali satu-dua hal" — yang
     | dikosongkan harus ikut nilai aslinya, bukan terkirim sebagai kosong
     | dan menghapus data.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->call('bukaSalin', 3, ['id' => 3])
        ->set('salinIsian.nama_pelanggan', 'Pelanggan Lain')
        ->call('salin')
        ->assertSet('salinDari', null);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/rab/3/salin')
        && $r['nama_pelanggan'] === 'Pelanggan Lain'
        && ! array_key_exists('whatsapp', $r->data())
        && ! array_key_exists('tanggal_mulai', $r->data()));
});

test('versi penawaran dibaca dari Orcha berikut selisihnya', function () {
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->call('bukaVersi', 3);

    expect($layar->html())->toContain('Versi 2')
        ->and($layar->html())->toContain('lebih murah Rp 500.000 dari sekarang');

    $layar->call('tutupVersi')->assertSet('versi', []);
});

test('jumlah baris daftar RAB dibatasi ke pilihan yang dikenal', function () {
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabList::class);

    $layar->set('barisHalaman', 25);
    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'per_halaman=25'));

    $layar->set('barisHalaman', 9999);
    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'per_halaman=10'));
});

test('perubahan data perjalanan yang belum disimpan ditandai, dan batal mengembalikannya', function () {
    /*
     | Panel itu hanya tersimpan saat Simpan ditekan. Tanpa tanda,
     | menutupnya membuang perubahannya tanpa sepatah kata.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3]);

    expect($layar->instance()->kepalaBerubah())->toBeFalse();

    $layar->set('kepala.judul', 'Judul yang diketik tapi belum disimpan');

    expect($layar->instance()->kepalaBerubah())->toBeTrue()
        ->and($layar->html())->toContain('belum disimpan');

    $layar->call('batalKepala');

    expect($layar->instance()->kepalaBerubah())->toBeFalse();
});

test('tiket yang ikut terbuang bersama itinerary dikatakan, tidak hilang diam-diam', function () {
    /*
     | Baris biaya yang hilang sendiri tanpa sepatah kata membuat total
     | berubah tanpa sebab yang terlihat — dan yang pertama dicurigai
     | justru hitungannya.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('hapusKegiatan', 0)
        ->assertDispatched('order-updated', fn ($nama, $p) => str_contains($p['message'], 'Tiket Borobudur')
            && str_contains($p['message'], 'tidak lagi dikunjungi'));
});

test('menghapus tiket yang tempatnya masih dikunjungi menawarkan membuang kunjungannya', function () {
    /*
     | Itinerary tidak diubah diam-diam: ia rencana yang dibaca pelanggan.
     | Yang ditawarkan pilihannya, bukan akibatnya.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('hapusBiaya', 11);

    $layar->assertSet('tiketTertinggal', 'Candi Prambanan');

    expect($layar->html())->toContain('masih ada di itinerary')
        ->and($layar->html())->toContain('Buang juga dari itinerary');

    // Dan menolak tawaran itu tidak menyentuh itinerary.
    $layar->call('lupakanTertinggal')->assertSet('tiketTertinggal', '');
});

test('membuang kunjungan hanya menghapus yang destinasinya cocok', function () {
    palsukanRab();

    /*
     | Itinerary-nya diambil dari rekaman Orcha apa adanya: menyetelnya
     | lewat ->set() memicu simpan, dan jawabannya menimpa kembali isinya
     | dengan rekaman itu — uji yang mengandaikan isian sendiri akan
     | memeriksa sesuatu yang sudah tidak ada.
     */
    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('tiketTertinggal', 'Candi Prambanan')
        ->call('buangKunjungan');

    Http::assertSent(function (Request $r) {
        if (! str_contains($r->url(), '/rab/3/itinerary') || ! is_array($r['itinerary'])) {
            return false;
        }

        $destinasi = collect($r['itinerary'])->pluck('destinasi')->all();

        // Kunjungan Prambanan hilang; Malioboro dan kegiatan tanpa
        // destinasi tetap di tempatnya.
        return ! in_array('Candi Prambanan', $destinasi, true)
            && in_array('Malioboro', $destinasi, true)
            && count($destinasi) === 2;
    });

    $layar->assertSet('tiketTertinggal', '');
});

/*
|--------------------------------------------------------------------------
| Penyusun: urungkan, salin hari, semua hari, dan kirim
|--------------------------------------------------------------------------
*/

test('kegiatan yang dihapus bisa dikembalikan', function () {
    /*
     | Dialog konfirmasi menahan kesalahan yang DISADARI. Yang tidak
     | disadari — klik meleset di baris sebelahnya — hanya bisa ditolong
     | oleh urungkan.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3]);

    $nama = $layar->get('itinerary')[0]['nama'];

    $layar->call('hapusKegiatan', 0);

    expect($layar->get('kegiatanTerhapus')['nama'])->toBe($nama)
        ->and($layar->html())->toContain('Kembalikan');

    // Rekaman Orcha menimpa daftarnya tiap kali disimpan, jadi yang
    // diperiksa isi yang DIKIRIM, bukan yang tergambar sesudahnya.
    $layar->call('urungkanHapus')->assertSet('kegiatanTerhapus', []);

    Http::assertSent(function (Request $r) use ($nama) {
        return str_contains($r->url(), '/rab/3/itinerary')
            && is_array($r['itinerary'])
            && collect($r['itinerary'])->pluck('nama')->contains($nama);
    });
});

test('menyalin hari membawa seluruh kegiatannya ke hari lain', function () {
    /*
     | Trip tiga hari yang polanya mirip — sarapan, destinasi, makan siang,
     | hotel — diketik tiga kali tanpa ini.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3]);

    $hari1 = collect($layar->get('itinerary'))->where('hari_ke', 1)->count();

    $layar->call('salinHari', 2);

    Http::assertSent(function (Request $r) use ($hari1) {
        if (! str_contains($r->url(), '/rab/3/itinerary') || ! is_array($r['itinerary'])) {
            return false;
        }

        return collect($r['itinerary'])->where('hari_ke', 2)->count() >= $hari1;
    });

    // Yang menyalin dibawa ke hari tujuannya — kalau tidak, hasil
    // salinannya tidak pernah terlihat.
    $layar->assertSet('hariAktif', 2);
});

test('menyalin ke hari yang sama tidak melakukan apa pun', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('salinHari', 1);

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/rab/3/itinerary'));
});

test('mengosongkan hari membuang kegiatannya dan mengatakan tak bisa diurungkan', function () {
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3]);

    $layar->call('kosongkanHari')
        // Penyangga urungkan dikosongkan: satu hari penuh tidak muat di sana,
        // dan tombol yang menjanjikan lebih dari yang bisa ditepati lebih
        // buruk daripada tidak ada tombol.
        ->assertSet('kegiatanTerhapus', []);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/rab/3/itinerary')
        && collect($r['itinerary'])->where('hari_ke', 1)->isEmpty());
});

test('susun ulang tidak menghilangkan kegiatan hari lain', function () {
    /*
     | Yang dikirim dari layar hanya indeks hari yang sedang dibuka. Tanpa
     | penjagaan ini, menggeser satu baris akan membuang seluruh hari lain
     | — dan hilangnya baru ketahuan saat PDF dicetak.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3]);

    $semula = count($layar->get('itinerary'));
    $hari1 = collect($layar->get('itinerary'))
        ->filter(fn ($k) => (int) $k['hari_ke'] === 1)->keys()->all();

    // Dibalik urutannya.
    $layar->call('susunUlang', array_reverse($hari1));

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/rab/3/itinerary')
        && count($r['itinerary']) === $semula);
});

test('tautan WhatsApp tersedia di penyusun, bukan cuma di daftar', function () {
    /*
     | Penawaran SELESAI disiapkan di layar ini, dan di sinilah orang
     | menekan kirim.
     */
    palsukanRab();

    $layar = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3]);

    $tautan = $layar->instance()->tautanWa();

    expect($tautan)->toStartWith('https://wa.me/62')
        ->and(urldecode($tautan))->toContain(rekamanRab('show')['data']['kode']);
});

test('tombol lihat membuka PDF tanpa mengunduhnya', function () {
    /*
     | Memeriksa hasilnya berarti unduh → buka → tutup → ulang, berkali-kali
     | dalam satu penawaran.
     */
    palsukanRab();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->getContent();

    expect($isi)->toContain('pratinjau=1');
});

/*
|--------------------------------------------------------------------------
| Tanggal pulang: hari & malam terbaca sendiri dari rentangnya
|--------------------------------------------------------------------------
*/

test('tanggal pulang mengisi jumlah hari dan malam, hari berangkat ikut terhitung', function () {
    /*
     | Yang disepakati dengan pelanggan TANGGALNYA ("berangkat 10, pulang
     | 12"), bukan jumlah harinya. Menyuruh admin menerjemahkannya sendiri
     | adalah hitungan kepala yang mudah meleset satu — dan yang meleset
     | satu hari menggeser seluruh modal akomodasi.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->call('bukaTambah')
        ->set('isian.tanggal_mulai', '2026-11-10')
        ->set('isian.tanggal_pulang', '2026-11-12')
        // 10 → 12 adalah 3 hari 2 malam, bukan 2 hari.
        ->assertSet('isian.jumlah_hari', 3)
        ->assertSet('isian.jumlah_malam', 2);
});

test('mengubah jumlah hari menggeser tanggal pulangnya', function () {
    /*
     | Dua arah, supaya ketiga angka itu tidak pernah saling bertentangan
     | di layar.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->call('bukaTambah')
        ->set('isian.tanggal_mulai', '2026-11-10')
        ->set('isian.jumlah_hari', 5)
        ->assertSet('isian.tanggal_pulang', '2026-11-14')
        ->assertSet('isian.jumlah_malam', 4);
});

test('pulang sebelum berangkat diabaikan, bukan dihitung negatif', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->call('bukaTambah')
        ->set('isian.tanggal_mulai', '2026-11-10')
        ->set('isian.tanggal_pulang', '2026-11-05')
        // Tetap di nilai bawaannya; tidak ada "0 hari" atau angka minus.
        ->assertSet('isian.jumlah_hari', 3);
});

test('tanggal pulang tidak ikut dikirim ke Orcha', function () {
    /*
     | Di sana ia DITURUNKAN dari tanggal mulai + jumlah hari, bukan
     | disimpan. Mengirimnya berarti menitipkan angka kedua untuk hal yang
     | sama — dan yang kedua itulah yang akan berselisih begitu jumlah
     | harinya diubah lewat jalur lain.
     */
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabList::class)
        ->call('bukaTambah')
        ->set('isian.nama_pelanggan', 'Keluarga Uji')
        ->set('isian.provinsi', 'DI Yogyakarta')
        ->set('isian.tanggal_mulai', '2026-11-10')
        ->set('isian.tanggal_pulang', '2026-11-12')
        ->call('simpan');

    Http::assertSent(fn (Request $r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/rab')
        && $r->method() === 'POST'
        && $r['jumlah_hari'] === 3
        && ! array_key_exists('tanggal_pulang', $r->data()));
});

test('panel data perjalanan ikut menghitung dari rentang tanggalnya', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('kepala.tanggal_mulai', '2026-12-01')
        ->set('kepala.tanggal_pulang', '2026-12-04')
        ->assertSet('kepala.jumlah_hari', 4)
        ->assertSet('kepala.jumlah_malam', 3);
});

test('judul, kota, dan jumlah malam terlihat tanpa membuka apa pun', function () {
    palsukanRab();

    // Ketiganya terisi sendiri, tetapi kotaknya TIDAK disembunyikan: yang
    // tersembunyi di balik tombol menyembunyikan juga bahwa ia boleh diubah.
    Livewire::actingAs(adminRab())->test(OrchaRabList::class)
        ->call('bukaTambah')
        ->assertSee('Judul perjalanan')
        ->assertSee('Kota / daerah')
        ->assertSee('Jumlah malam')
        // Baris rakitannya tetap ada — ia menjawab "kalau dibiarkan kosong,
        // jadinya apa?" sebelum penawarannya tercetak.
        ->assertSee('Judul & jumlah malam terisi sendiri')
        ->assertDontSee('Sesuaikan sendiri');
});

/*
|--------------------------------------------------------------------------
| Negosiasi & temuan harga
|--------------------------------------------------------------------------
*/

test('harga yang ditawar pelanggan dikirim sebagai angka, bukan teks bertitik', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('hargaNego', '1.150.000');

    Http::assertSent(function (Request $r) {
        return str_ends_with((string) parse_url($r->url(), PHP_URL_PATH), '/rab/3')
            && $r->method() === 'PATCH'
            && $r['harga_nego'] === 1150000;
    });
});

test('alasan ikut terkirim selama kesepakatannya ada', function () {
    // Balasannya MEMANTULKAN yang disimpan, seperti Orcha sungguhan. Fixture
    // yang statis mengembalikan harga nego ke kosong tiap kali disimpan,
    // dan isian berikutnya jadi tidak punya harga untuk ditempeli.
    $tersimpan = [];
    Http::fake(function (Request $r) use (&$tersimpan) {
        $tersimpan = array_merge($tersimpan, array_filter($r->data(), fn ($v) => $v !== null));
        $show = rekamanRab('show');
        $show['data']['harga_nego'] = $tersimpan['harga_nego'] ?? null;
        $show['data']['alasan_nego'] = $tersimpan['alasan_nego'] ?? null;

        return Http::response($show);
    });

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('hargaNego', '1.150.000')
        ->set('alasanNego', 'Pelanggan lama');

    Http::assertSent(fn (Request $r) => ($r['alasan_nego'] ?? null) === 'Pelanggan lama'
        && $r['harga_nego'] === 1150000);
});

test('mengosongkan harga nego melepas kesepakatan, bukan mengirim nol', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('hargaNego', '')
        ->call('simpanNego');

    // null berarti "tidak ada kesepakatan"; nol akan tersimpan sebagai
    // harga Rp 0 di Orcha yang belum tentu menolaknya.
    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && $r['harga_nego'] === null);
});

test('batas aman diisikan dari angka Orcha, bukan diketik ulang', function () {
    palsukanRab();

    $batas = rekamanRab('show')['data']['ringkasan']['nego']['batas']['harga'];

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('pakaiBatasAman');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && $r['harga_nego'] === $batas);
});

test('alasan tanpa harga tidak menyimpan apa pun', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('alasanNego', 'Belum deal');

    // Yang disimpan adalah kesepakatannya; alasan cuma keterangannya.
    Http::assertNotSent(fn (Request $r) => $r->method() === 'PATCH' && isset($r['alasan_nego']));
});

test('harga temuan bisa disimpan ke master berikut catatannya', function () {
    palsukanRab();
    $id = rekamanRab('show')['data']['ringkasan']['baris'][0]['id'];

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set("catatanBaris.{$id}", 'Hasil nego langsung, low season')
        ->call('simpanKeMaster', $id);

    // Catatannya disimpan lewat barisnya sendiri...
    Http::assertSent(function (Request $r) use ($id) {
        return str_ends_with((string) parse_url($r->url(), PHP_URL_PATH), "/rab/3/biaya/{$id}")
            && $r->method() === 'PATCH'
            && $r['catatan'] === 'Hasil nego langsung, low season';
    });

    // ...dan "simpan ke master" tidak mengirimnya lagi: dua sumber untuk
    // satu kalimat akan berselisih.
    Http::assertSent(function (Request $r) use ($id) {
        return str_ends_with((string) parse_url($r->url(), PHP_URL_PATH), "/rab/3/biaya/{$id}/ke-master")
            && $r->method() === 'POST';
    });
});

test('arah selisih master disebut dengan kata, bukan tanda minus', function () {
    palsukanRab();

    $murah = ['selisih_master' => -10000];
    $mahal = ['selisih_master' => 15000];
    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3]);

    expect($komponen->instance()->temuanLebihMurah($murah))->toBeTrue()
        ->and($komponen->instance()->temuanLebihMurah($mahal))->toBeFalse()
        // Yang dibaca admin adalah besarnya, arahnya diceritakan kalimatnya.
        ->and($komponen->instance()->selisihMasterTeks($murah))->toBe('10.000')
        ->and($komponen->instance()->selisihMasterTeks($mahal))->toBe('15.000');
});

test('panel nego terbuka sendiri bila kesepakatannya sudah ada', function () {
    Http::fake(function (Request $r) {
        $show = rekamanRab('show');
        $show['data']['harga_nego'] = 1_150_000;
        $show['data']['alasan_nego'] = 'Pelanggan lama';
        $show['data']['ringkasan']['nego_aktif'] = true;

        return Http::response($show);
    });

    // Angka yang mengubah harga jual tidak boleh bersembunyi di balik tombol.
    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->assertSet('bukaNego', true)
        ->assertSet('hargaNego', '1.150.000')
        ->assertSee('disepakati');
});

test('kesepakatan ditampilkan berdampingan dengan harga hitungan', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $r = &$show['data']['ringkasan'];
        $show['data']['harga_nego'] = 500_000;
        $r['nego_aktif'] = true;
        $r['nego']['aktif'] = true;
        $r['nego']['sesudah'] = array_merge($r['nego']['sesudah'], [
            'harga_per_orang' => 500_000, 'harga_per_orang_teks' => 'Rp 500.000',
            'untung' => 1_000_000, 'untung_teks' => 'Rp 1.000.000',
            'persen_untung' => 12.5, 'bep_peserta' => 9,
        ]);
        $r['nego']['selisih'] = array_merge($r['nego']['selisih'], [
            'bagian_untung_dilepas' => 45.5, 'poin_persen' => -7.5,
        ]);

        return Http::response($show);
    });

    $halaman = $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk();

    // Kedua sisi hadir: potongannya tidak punya arti tanpa pembandingnya.
    $halaman->assertSee('Hitungan')
        ->assertSee('Disepakati')
        ->assertSee('Rp 500.000')
        ->assertSee($rekaman = rekamanRab('show')['data']['ringkasan']['nego']['sebelum']['harga_per_orang_teks'])
        // Angka yang paling sering mengejutkan saat tawaran disetujui.
        ->assertSee('45,5% dari untung', escape: false);
});

test('margin dan titik impas yang tak terhitung disebut apa adanya', function () {
    palsukanRab();

    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])->instance();

    // 0% dan "0 peserta" dua-duanya terbaca sebagai kabar baik, padahal
    // artinya angkanya memang tidak bisa dihitung.
    expect($komponen->persenTeks(null))->toBe('—')
        ->and($komponen->persenTeks(12.5))->toBe('12,5%')
        ->and($komponen->impasTeks(null))->toBe('tak tercapai')
        ->and($komponen->impasTeks(9))->toBe('9 org');
});

test('kartu untung ikut menyebut untung sebelum ditawar dan yang dilepas', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $r = &$show['data']['ringkasan'];
        $show['data']['harga_nego'] = 500_000;
        $r['nego_aktif'] = true;
        $r['untung_teks'] = 'Rp 1.000.000';
        $r['nego']['aktif'] = true;
        $r['nego']['sebelum'] = array_merge($r['nego']['sebelum'], [
            'untung_teks' => 'Rp 1.492.000', 'persen_untung' => 20.0,
        ]);
        $r['nego']['selisih'] = array_merge($r['nego']['selisih'], [
            'untung' => -492_000, 'untung_per_orang' => -28_941, 'bagian_untung_dilepas' => 33.0,
        ]);

        return Http::response($show);
    });

    // Untung sesudah kesepakatan tidak berarti apa-apa sendirian: angka ini
    // jawaban seluruh layar, dan pertanyaannya jadi "dibanding berapa?".
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Sebelum ditawar')
        ->assertSee('Rp 1.492.000')
        ->assertSee('Dilepas')
        ->assertSee('Rp 492.000')
        ->assertSee('33%')
        ->assertSee('Rp 28.941');
});

test('rupiah untuk kalimat selalu punya angkanya, juga saat nol', function () {
    palsukanRab();

    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])->instance();

    // keRupiah() memulangkan kosong untuk nol — benar di kotak isian, salah
    // di kalimat: "Dilepas" tanpa nilai terbaca seperti data yang gagal muat.
    expect($komponen->rupiahTeks(0))->toBe('Rp 0')
        ->and($komponen->rupiahTeks(492_000))->toBe('Rp 492.000');
});

test('margin yang tidak lagi menentukan harga mengatakannya sendiri', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $r = &$show['data']['ringkasan'];
        $show['data']['harga_nego'] = 11_000;
        $show['data']['margin_jenis'] = 'persen';
        $show['data']['margin_nilai'] = 20;
        $r['nego_aktif'] = true;
        $r['nego']['aktif'] = true;
        $r['nego']['sesudah'] = array_merge($r['nego']['sesudah'], [
            'harga_per_orang_teks' => 'Rp 11.000', 'persen_untung' => 10.0,
        ]);

        return Http::response($show);
    });

    // Tim mengisi 20%, kartu untung menulis 10%, dan tanpa kalimat ini dua
    // angka itu bertentangan di layar yang sama tanpa ada yang menjelaskan.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('tidak sedang dipakai')
        ->assertSee('Rp 11.000')
        ->assertSee('10%')
        ->assertSee('Lepas kesepakatan');
});

test('tanpa kesepakatan, bagian margin tidak memberi peringatan apa pun', function () {
    palsukanRab();

    // Peringatan yang muncul saat tidak ada yang salah akan diabaikan juga
    // saat memang ada yang salah.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertDontSee('tidak sedang dipakai');
});

test('selisih antara margin diminta dan untung disebut namanya', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $r = &$show['data']['ringkasan'];
        $show['data']['pembulatan'] = 5000;
        $r['margin_diminta_teks'] = 'Rp 20.000';
        $r['untung_teks'] = 'Rp 50.000';
        $r['tambahan_pembulatan'] = 30_000;
        $r['tambahan_pembulatan_teks'] = 'Rp 30.000';

        return Http::response($show);
    });

    // "Margin diminta Rp 20.000" tepat di atas "untung Rp 50.000" tanpa
    // penjelasan terbaca sebagai salah hitung, bukan sebagai pembulatan.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Tambahan dari pembulatan')
        ->assertSee('+Rp 30.000')
        ->assertSee('naik ke kelipatan Rp 5.000');
});

test('pembulatan yang tidak menambah apa-apa tidak menambah baris', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $show['data']['ringkasan']['tambahan_pembulatan'] = 0;

        return Http::response($show);
    });

    // Baris bernilai nol hanya menambah yang harus dibaca.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertDontSee('Tambahan dari pembulatan');
});

test('pembulatan yang menambah lebih banyak daripada margin ditegur', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $r = &$show['data']['ringkasan'];
        $show['data']['pembulatan'] = 5000;
        $r['margin_diminta'] = 20_000;
        $r['margin_diminta_teks'] = 'Rp 20.000';
        $r['tambahan_pembulatan'] = 30_000;
        $r['tambahan_pembulatan_teks'] = 'Rp 30.000';

        return Http::response($show);
    });

    // Kelipatan 5.000 pada harga mentah 12.000 melompat 25%. Bukan galat,
    // tetapi hampir tidak pernah yang dimaksud.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('terlalu kasar untuk harga sebesar ini');
});

test('pembulatan yang wajar tidak ditegur', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $r = &$show['data']['ringkasan'];
        $r['margin_diminta'] = 1_500_000;
        $r['tambahan_pembulatan'] = 2_000;
        $r['tambahan_pembulatan_teks'] = 'Rp 2.000';

        return Http::response($show);
    });

    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Tambahan dari pembulatan')
        ->assertDontSee('terlalu kasar untuk harga sebesar ini');
});

/*
|--------------------------------------------------------------------------
| Hitungan per peserta
|--------------------------------------------------------------------------
*/

test('hitungan per peserta tampil lengkap dari modal sampai untung rombongan', function () {
    palsukanRab();
    $r = rekamanRab('show')['data']['ringkasan'];

    // Bentuk yang sudah dipakai tim di lembar kerjanya. Lima baris, satu
    // alur, semuanya per kepala — bukan total rombongan.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Hitungan per peserta')
        ->assertSee('Modal per peserta')
        ->assertSee($r['modal_per_kepala_teks'])
        ->assertSee('Target margin')
        ->assertSee('Harga jual per peserta')
        ->assertSee($r['harga_per_orang_teks'])
        ->assertSee('Untung per peserta')
        ->assertSee($r['untung_per_orang_teks'])
        ->assertSee('Untung rombongan')
        ->assertSee($r['untung_teks']);
});

test('persen dari harga jual bisa dipilih dan dikirim sebagai bilangan bulat', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('marginJenis', 'persen_harga')
        ->set('marginNilai', '5');

    Http::assertSent(function (Request $r) {
        return $r->method() === 'PATCH'
            && ($r['margin_jenis'] ?? null) === 'persen_harga'
            // Bilangan bulat, bukan rupiah bertitik seperti jenis nominal.
            && $r['margin_nilai'] === 5;
    });
});

test('beda kedua jenis persen dijelaskan dengan contoh angka', function () {
    palsukanRab();

    // "Margin 20%" punya dua arti yang dipakai orang dengan kata yang sama,
    // dan keduanya memberi harga berbeda untuk modal yang sama.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('menambah di atas modal')
        ->assertSee('menyisakan persennya di dalam harga')
        ->assertSee('Rp 120.000')
        ->assertSee('Rp 125.000');
});

test('persen di kartu untung menyebut tolok ukurnya', function () {
    palsukanRab();

    // Tanpa ini ia bertabrakan dengan "target margin dari harga jual" di
    // bawahnya: dua angka berbeda untuk perjalanan yang sama.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('persennya diukur dari modal');
});

test('pilihan pembulatan dirakit dari kosakata Orcha, bukan daftar sendiri', function () {
    palsukanRab();

    // Daftar yang ditulis dua kali akan melenceng: Rp 100 dan Rp 500 pernah
    // diterima API tetapi tidak pernah ditawarkan layar.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Rp 100')
        ->assertSee('Rp 500')
        ->assertSee('Rp 25.000')
        ->assertSee('Rp 100.000')
        ->assertSee('tanpa pembulatan');
});

test('layar tetap bisa digambar saat Orcha belum mengirim daftar pembulatan', function () {
    Http::fake(function (Illuminate\Http\Client\Request $r) {
        $jalur = (string) parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            $katalog = rekamanRab('katalog');
            // Orcha yang belum dideploy tidak mengirim kunci ini sama sekali.
            unset($katalog['meta']['kosakata']['pembulatan'], $katalog['kosakata']['pembulatan']);

            return Http::response($katalog);
        }

        return Http::response(rekamanRab('show'));
    });

    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Bulatkan ke atas per');
});

test('harga jual menyebut asalnya saat pembulatan menggesernya', function () {
    palsukanRab();

    // Kelipatan kasar membuat kotak margin terlihat mati: modal Rp 5.500
    // dengan kelipatan Rp 5.000 memulangkan Rp 10.000 untuk margin 0%
    // sampai 80%. Tanpa baris ini, yang mengubahnya mengira isiannya rusak.
    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('dibulatkan dari Rp 525.000');
});

test('harga yang tidak tergeser pembulatan tidak menambah keterangan', function () {
    Http::fake(function () {
        $show = rekamanRab('show');
        $r = &$show['data']['ringkasan'];
        // Tidak ada yang digeser: angkanya sudah bulat sejak awal.
        $r['harga_sebelum_bulat'] = $r['harga_per_orang'];
        $r['harga_sebelum_bulat_teks'] = $r['harga_per_orang_teks'];

        return Http::response($show);
    });

    $this->actingAs(adminRab())->get('/admin/orcha/rab/3')->assertOk()
        ->assertSee('Harga jual per peserta')
        ->assertDontSee('dibulatkan dari');
});

/*
|--------------------------------------------------------------------------
| Kelipatan pembulatan sendiri
|--------------------------------------------------------------------------
*/

test('memilih Lainnya membuka kotak ketik tanpa menyimpan apa pun', function () {
    palsukanRab();

    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('pilihanBulat', 'lainnya');

    // Menyimpan nilai lama sebagai "pilihan baru" membuat harga sesaat
    // bergeser padahal admin belum memutuskan apa-apa.
    $komponen->assertSet('pilihanBulat', 'lainnya');
    Http::assertNotSent(fn (Request $r) => $r->method() === 'PATCH' && isset($r['pembulatan']));
});

test('kelipatan yang diketik sendiri dikirim sebagai angka', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('pilihanBulat', 'lainnya')
        ->set('pembulatanLain', '2.500');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && $r['pembulatan'] === 2500);
});

test('kelipatan yang diketik dijepit ke batas yang sama dengan Orcha', function () {
    palsukanRab();

    // Dijepit SEBELUM dikirim, bukan dilempar balik sebagai toast merah
    // dari Orcha. Yang diperiksa muatannya: nilai di layar sesudahnya
    // datang dari balasan Orcha, bukan dari jepitan ini.
    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('pembulatanLain', '0');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && $r['pembulatan'] === 1);

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('pembulatanLain', '99.999.999');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH' && $r['pembulatan'] === 1_000_000);
});

test('kelipatan di luar daftar saran membuka kotak ketiknya sendiri', function () {
    Http::fake(function (Request $r) {
        $jalur = (string) parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            return Http::response(rekamanRab('katalog'));
        }

        $show = rekamanRab('show');
        $show['data']['pembulatan'] = 2_500;

        return Http::response($show);
    });

    // RAB yang kelipatannya tidak ada di daftar harus tetap memperlihatkan
    // angkanya — bukan diam-diam jatuh ke pilihan terdekat.
    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->assertSet('pilihanBulat', 'lainnya')
        ->assertSet('pembulatanLain', '2.500');
});

test('kelipatan yang ada di daftar tidak membuka kotak ketik', function () {
    palsukanRab();

    Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 3])
        ->assertSet('pilihanBulat', (string) rekamanRab('show')['data']['pembulatan'])
        ->assertSet('pembulatanLain', '');
});
