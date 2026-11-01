<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaHariLiburList;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaMasterHargaList;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;
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

test('formulir harga terbagi bagian berjudul, bukan satu dinding kotak', function () {
    palsukanMaster();

    /*
     | Dua belas kotak tanpa judul terbaca sebagai satu dinding: matanya
     | tidak punya tempat berhenti, dan yang membacanya tidak tahu kotak
     | mana yang boleh dilewati. Bentuknya mengikuti borang Tambah Pesanan.
     */
    $html = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->html();

    foreach ([
        'Apa yang dibeli, dan berapa',
        'Berlaku di mana, dibeli dari siapa',
        'Namanya di daftar',
        'Kapan harga ini ikut ke RAB',
    ] as $judul) {
        expect($html)->toContain($judul);
    }

    /*
     | Dihitung dari MARKUP-nya (class="..."), bukan dari nama kelasnya:
     | blok <style> di berkas yang sama ikut terbawa ke html(), jadi
     | menghitung namanya memulangkan satu lebih banyak — dan angkanya
     | akan ditambah satu oleh siapa pun yang mengejar hijau.
     */
    expect(substr_count($html, 'class="mh-bagian-ket"'))->toBe(4)
        ->and(substr_count($html, 'class="mh-bagian-ikon"'))->toBe(4);
});

test('ketiga sakelar nyala/mati memakai satu bentuk yang sama', function () {
    /*
     | "Akhir pekan lebih mahal" sempat berupa ceklis polos sementara "Aktif"
     | dan "Dipilih manual" berupa kartu sakelar — satu formulir, dua idiom
     | untuk hal yang sama persis: menyalakan atau mematikan sesuatu. Yang
     | membacanya harus belajar dua kali, dan yang bentuknya lebih kecil
     | terbaca seperti setelan yang kurang penting — padahal ia yang
     | menentukan harga tiket di hari Sabtu.
     |
     | Angkanya ditulis apa adanya: sakelar keempat yang memang sengaja
     | ditambahkan ikut menyunting angka ini, dan suntingan itu yang membuat
     | perubahannya terlihat saat ditinjau.
     */
    $isi = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/rab/master-harga.blade.php')
    );

    expect(substr_count($isi, 'class="orcha-sakelar-kartu'))->toBe(3)
        // Ceklis polosnya benar-benar pergi, bukan sekadar ditimpa.
        ->and($isi)->not->toContain('mh-dua-saklar');
});

test('kartu sakelar menyebut KEADAANNYA, bukan nama setelannya', function () {
    palsukanMaster();

    /*
     | Judul yang tetap "Akhir pekan lebih mahal" saat sakelarnya mati
     | menyuruh pembacanya menebak: apakah ini keadaan sekarang, atau nama
     | tombolnya? Dua kartu lain sudah lama berubah kata; yang ini mengikut.
     */
    $mati = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)->call('bukaTambah')->html();

    expect($mati)->toContain('Harga sama setiap hari')
        ->and($mati)->not->toContain('Akhir pekan &amp; hari libur lebih mahal');

    $nyala = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)->call('bukaTambah')->set('duaHarga', true)->html();

    expect($nyala)->toContain('Akhir pekan &amp; hari libur lebih mahal')
        ->and($nyala)->not->toContain('Harga sama setiap hari');
});

test('tombol tempel Excel sebaris dengan tombol simpan, bukan barisnya sendiri', function () {
    palsukanMaster();

    $html = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)
        ->call('bukaTambah')
        ->html();

    // Berdiri sendiri, ia menyisakan hampir selebar kartu menganggur di
    // kanannya — dan tombol tunggal yang menggantung terbaca seperti sisa.
    $kaki = substr($html, strpos($html, 'mh-kaki-kiri'));

    expect($kaki)->toContain('Tempel banyak baris dari Excel')
        ->and(substr($kaki, 0, strpos($kaki, 'Batal')))->toContain('Tempel banyak baris');
});

/* ------------------- RIWAYAT PERUBAHAN ------------------- */

function palsukanRiwayat(array $riwayat): void
{
    Http::fake(function (Request $r) use ($riwayat) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_contains($jalur, '/riwayat') => Http::response([
                'data' => $riwayat,
                'meta' => ['nama' => 'Tiket Masuk Baluran', 'harga_kini' => 'Rp 21.000'],
            ]),
            default => Http::response(['data' => [], 'meta' => [
                'halaman' => 1, 'halaman_terakhir' => 1, 'total' => 0,
                'ringkas' => ['jumlah' => 1, 'dipakai' => 0, 'tanpa_vendor' => 1, 'mati' => 0, 'kedaluwarsa' => 0],
                'kosakata' => ['kategori' => ['tiket' => 'Tiket'], 'satuan' => ['orang' => 'per orang'],
                    'satuan_berkapasitas' => ['unit', 'unit_hari', 'kamar_malam']],
                'provinsi' => [], 'vendor' => [], 'destinasi' => [],
            ]]),
        };
    });
}

test('riwayat menampilkan perubahan selain harga, berikut siapa yang mengubahnya', function () {
    palsukanRiwayat([[
        'id' => 3, 'harga_lama' => 'Rp 21.000', 'harga_baru' => 'Rp 21.000',
        'selisih' => '+Rp 0', 'naik' => false, 'persen' => 0.0, 'harga_berubah' => false,
        'akhir_pekan' => null, 'oleh' => 'Berto', 'catatan' => null,
        'perubahan' => [
            ['medan' => 'Destinasi', 'dari' => 'Taman Nasional Baluran', 'ke' => 'TN Baluran'],
            ['medan' => 'Vendor', 'dari' => null, 'ke' => 'Armada Jaya'],
        ],
        'pada' => '2 Nov 2026, 09:14', 'pada_teks' => 'baru saja',
    ]]);

    /*
     | Yang paling sering salah justru destinasinya: salah satu huruf, dan
     | tiketnya berhenti ikut tertarik ke RAB. Dulu perubahan itu tidak
     | muncul di mana pun — panelnya hanya mengenal angka.
     */
    $html = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)->call('bukaRiwayat', 1)->html();

    expect($html)->toContain('Riwayat perubahan')
        ->and($html)->toContain('Destinasi')
        ->and($html)->toContain('Taman Nasional Baluran')
        ->and($html)->toContain('TN Baluran')
        ->and($html)->toContain('Armada Jaya')
        ->and($html)->toContain('Berto')
        // Medan yang tadinya kosong dikatakan kosong, bukan dibiarkan hampa.
        ->and($html)->toContain('kosong');
});

test('tabel riwayat tinggal tiga kolom, tanpa Dari/Jadi/Selisih yang kosong', function () {
    palsukanRiwayat([[
        'id' => 1, 'harga_lama' => 'Rp 18.000', 'harga_baru' => 'Rp 21.000',
        'selisih' => '+Rp 3.000', 'naik' => true, 'persen' => 16.7, 'harga_berubah' => true,
        'akhir_pekan' => null, 'oleh' => 'Berto', 'catatan' => null, 'perubahan' => [],
        'pada' => '12 Sep 2026, 08:30', 'pada_teks' => '2 bulan yang lalu',
    ]]);

    $html = Livewire::actingAs(adminRab())
        ->test(OrchaMasterHargaList::class)->call('bukaRiwayat', 1)->html();

    /*
     | Dari / Jadi / Selisih semuanya berporos harga, dan sejak perubahan
     | lain ikut dicatat ketiganya berdiri kosong justru pada baris yang
     | paling perlu dibaca.
     */
    $kepala = substr($html, strpos($html, 'mh-riwayat'), 600);

    expect($kepala)->toContain('Yang berubah')
        ->and($kepala)->not->toContain('>Selisih<')
        // Harga tetap membawa selisih & persennya, cuma pindah tempat.
        ->and($html)->toContain('+Rp 3.000')
        ->and($html)->toContain('16.7%');
});

/* ------------------- TEMPEL DARI EXCEL ------------------- */

test('tempelan terbaca walau dipisah koma atau spasi, bukan cuma TAB', function () {
    palsukanMaster();

    /*
     | TAB TIDAK BISA DIKETIK di dalam kotak teks — tombolnya memindahkan
     | fokus, bukan menyisipkan tab. Formatnya karena itu cuma jalan untuk
     | tempelan Excel, dan admin yang mengetik "Tiket Borobudur,100.000"
     | mendapat "belum ada baris yang terbaca" tanpa tahu apa yang kurang.
     */
    $k = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class);

    $baca = function (string $teks) use ($k) {
        $k->set('tempelIsi', $teks);

        return $k->instance()->barisTempel();
    };

    expect($baca('Tiket Borobudur,100.000'))
        ->toBe([['nama' => 'Tiket Borobudur', 'harga' => 100000, 'destinasi' => null]]);

    // Spasi saja pun cukup: angkanya diambil dari UJUNG baris.
    expect($baca('Tiket Borobudur 100.000'))
        ->toBe([['nama' => 'Tiket Borobudur', 'harga' => 100000, 'destinasi' => null]]);

    // Koma kedua jadi destinasinya.
    expect($baca('Tiket Borobudur, 100.000, Candi Borobudur'))
        ->toBe([['nama' => 'Tiket Borobudur', 'harga' => 100000, 'destinasi' => 'Candi Borobudur']]);
});

test('tempelan Excel tetap terbaca persis, termasuk nama bertanda koma', function () {
    palsukanMaster();

    $k = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class);

    /*
     | TAB & titik koma didahulukan. Kalau koma ikut dipakai memecah saat
     | TAB sudah ada, nama "Tiket Borobudur, Magelang" dari Excel akan
     | terbelah di komanya dan harganya hilang — tempelan yang dulu benar
     | jadi rusak demi memudahkan yang mengetik.
     */
    $k->set('tempelIsi', "Tiket Borobudur, Magelang\t100.000\tCandi Borobudur");

    expect($k->instance()->barisTempel())->toBe([[
        'nama' => 'Tiket Borobudur, Magelang',
        'harga' => 100000,
        'destinasi' => 'Candi Borobudur',
    ]]);
});

test('baris tanpa angka tetap dilewati, bukan masuk berharga nol', function () {
    palsukanMaster();

    $k = Livewire::actingAs(adminRab())->test(OrchaMasterHargaList::class);
    $k->set('tempelIsi', "Tiket Borobudur, 100.000\nbaris tanpa angka\n");

    // Harga nol adalah harga yang SAH; baris yang tidak terbaca tidak boleh
    // diam-diam jadi gratis.
    expect($k->instance()->barisTempel())->toHaveCount(1);
});

/* ------------------- CARTER PER RUTE ------------------- */

/**
 * Rekaman fixture, dibaca SENDIRI.
 *
 * Tidak meminjam pembaca dari OrchaRabTest: fungsi Pest berlaku global hanya
 * saat kedua berkas sama-sama dijalankan, jadi berkas yang meminjamnya hijau
 * dalam rangkaian penuh dan merah begitu dijalankan sendirian.
 */
function rekamanLibur(string $nama): array
{
    return json_decode(file_get_contents(base_path("tests/Fixtures/orcha-rab/{$nama}.json")), true);
}

/** Katalog RAB berisi master harga yang ditentukan. */
function palsukanKatalogMaster(array $master): void
{
    $show = rekamanLibur('show');
    $katalog = rekamanLibur('katalog');
    $katalog['data']['master'] = $master;

    Http::fake(function (Request $r) use ($show, $katalog) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_contains($jalur, '/rab/katalog') => Http::response($katalog),
            str_contains($jalur, '/rab/3') => Http::response($show),
            default => Http::response(['data' => [], 'meta' => []]),
        };
    });
}

function barisArmada(int $id, string $nama, string $satuan, int $harga, int $kapasitas, ?string $destinasi = null): array
{
    return [
        'id' => $id, 'kategori' => 'transportasi', 'kategori_label' => 'Transportasi',
        'nama' => $nama, 'destinasi' => $destinasi, 'daerah' => null,
        'satuan' => $satuan, 'satuan_label' => $satuan, 'harga' => $harga,
        'harga_teks' => 'Rp '.number_format($harga, 0, ',', '.'),
        'kapasitas' => $kapasitas, 'otomatis' => false, 'umum' => true,
        'vendor_id' => null, 'vendor' => null, 'vendor_kontak' => null,
    ];
}

test('carter per rute muncul di panel kendaraan, dan TIDAK dikali hari', function () {
    /*
     | Kasus yang ditanyakan: Hiace Jogja-Banyuwangi Rp 8.500.000 dan
     | Jogja-Bromo Rp 5.000.000. Keduanya carter satu rute — dibayar sekali
     | untuk seluruh perjalanan, bukan tarif harian.
     |
     | Tanpa ini keduanya tidak muncul di panel mana pun: "Tarik biaya"
     | melewati satuan unit, dan panel tiket kendaraan menuntut destinasi
     | yang tidak dipunyai sebuah rute. Satu-satunya jalan masuk tinggal
     | kotak pilih biasa, tanpa hitungan berapa unit untuk sekian peserta.
     */
    palsukanKatalogMaster([
        barisArmada(21, 'Sewa Hiace Jogja-Banyuwangi 4D3N', 'unit', 8_500_000, 14),
        barisArmada(22, 'Sewa Hiace Jogja-Bromo 3D2N', 'unit', 5_000_000, 14),
    ]);

    $saran = collect(Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->instance()->saranKendaraan());

    $banyuwangi = $saran->firstWhere('id', 21);
    $peserta = rekamanLibur('show')['data']['jumlah_peserta'];
    $unit = (int) ceil($peserta / 14);

    expect($saran)->toHaveCount(2)
        ->and($banyuwangi['sekali'])->toBeTrue()
        // Sekali bayar: harga x unit, TITIK. Dikali hari, RAB empat hari
        // memulangkan Rp 34 juta untuk satu Hiace.
        ->and($banyuwangi['total'])->toBe(8_500_000 * $unit)
        // Termurah lebih dulu — urutannya memang urutan rekomendasinya.
        ->and($saran->first()['id'])->toBe(22);
});

test('sewa harian tetap dikali hari', function () {
    palsukanKatalogMaster([
        barisArmada(23, 'Hiace harian', 'unit_hari', 1_200_000, 14),
    ]);

    $rab = rekamanLibur('show')['data'];
    $unit = (int) ceil($rab['jumlah_peserta'] / 14);

    $saran = collect(Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->instance()->saranKendaraan());

    expect($saran->first()['sekali'])->toBeFalse()
        ->and($saran->first()['total'])->toBe(1_200_000 * $unit * $rab['jumlah_hari']);
});

test('tiket gerbang tidak ikut ditawarkan sebagai kendaraan sewaan', function () {
    /*
     | Bersatuan unit juga, tetapi PUNYA destinasi — itu tiket gerbang di
     | sebuah tempat, bukan kendaraan yang disewa. Ia sudah punya panelnya
     | sendiri; masuk ke sini juga, satu baris yang sama ditawarkan dua kali
     | di layar yang sama.
     */
    palsukanKatalogMaster([
        barisArmada(24, 'Tiket kendaraan roda 4', 'unit', 10_000, 4, 'Taman Nasional Baluran'),
        barisArmada(25, 'Sewa Hiace Jogja-Bromo 3D2N', 'unit', 5_000_000, 14),
    ]);

    $saran = collect(Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->instance()->saranKendaraan());

    expect($saran->pluck('id')->all())->toBe([25]);
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
