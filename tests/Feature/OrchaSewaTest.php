<?php

use Illuminate\Support\Facades\Http;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';

function sewaUji(array $ubah = []): array
{
    return array_merge([
        'id' => 5, 'kode' => 'SW-0001', 'nama' => 'Budi', 'whatsapp' => '0812', 'email' => null,
        'kendaraan' => ['id' => 1, 'nama' => 'Avanza', 'transmisi' => 'Matic', 'sebutan' => 'Mobil',
            'kapasitas' => 6, 'kursi_total' => 7],
        'sopir_label' => 'Lepas kunci', 'operasional_label' => '-', 'satuan' => 'hari',
        'satuan_label' => 'Hari', 'durasi' => 2, 'durasi_label' => '2 hari',
        'tanggal_mulai' => '2026-09-01', 'jam_mulai' => '08:00', 'dengan_sopir' => false,
        'lokasi_antar' => 'Jogja', 'lokasi_kembali' => 'Jogja', 'tujuan' => null, 'luar_kota' => false,
        'tanggal_selesai' => '2026-09-03', 'jam_selesai' => '08:00', 'jadwal_selesai' => null,
        'terlambat' => false, 'terlambat_menit' => 0,
        'denda_keterlambatan_usulan' => 0, 'denda_kerusakan_usulan' => 0,
        'rincian_denda_kerusakan' => [], 'rincian_denda' => [],
        'diserahkan_pada' => null, 'dikembalikan_pada' => null,
        'kilometer_awal' => null, 'kilometer_akhir' => null,
        'bahan_bakar_awal' => null, 'bahan_bakar_akhir' => null,
        'jaminan' => null, 'berkas_jaminan' => null,
        'kondisi_awal' => [], 'kondisi_akhir' => [], 'kerusakan_baru' => [],
        'kondisi_unit_terkini' => [], 'estimasi_biaya' => 500000,
        'denda_keterlambatan' => 0, 'denda_kerusakan' => 0, 'denda_lain' => 0,
        'catatan_denda' => null, 'total_denda' => 0, 'total_tagihan' => 500000,
        'catatan' => null, 'status' => 'baru', 'status_label' => 'Baru',
        'dibuat_pada' => '2026-08-25T09:00:00+07:00',
    ], $ubah);
}

test('tombol serah terima adalah tautan ke halamannya sendiri', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Lembarnya kini halaman tersendiri. Sebagai jendela, ia dibuka lewat
     | tombol Livewire — dan tombol itu tidak merespons di tempat admin.
     | Tautan biasa tidak menunggu apa pun hidup lebih dulu: alamatnya sudah
     | ada di HTML, dan peramban tahu cara membukanya tanpa JavaScript.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    expect($isi)
        ->toContain('/admin/orcha/penyewaan/5/serah-terima')
        ->not->toContain('wire:click="buka(')
        ->not->toContain('buka({"');
});

test('halaman serah terima mengisi lembarnya dari nomor di alamat', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => sewaUji(['kilometer_awal' => 12345])]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/5/serah-terima')
        ->assertOk()
        ->assertSee('Serah Terima Kendaraan')
        ->assertSee('SW-0001')
        ->assertSee('12345');
});

test('nomor yang sudah tidak ada memberi jalan kembali, bukan lembar kosong', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => null]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Sewa bisa hilang di antara halaman digambar dan tautan ditekan — dihapus
    // admin lain, atau alamatnya disalin dari catatan lama.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/999/serah-terima')
        ->assertOk()
        ->assertSee('tidak bisa dibuka')
        ->assertSee('Kembali ke daftar');
});

test('daftar sewa tidak lagi memikul isi lembar serah terima', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Dulu seluruh lembar — pemeriksaan bodi per bagian, rincian denda, kamera
    // — ikut tergambar di setiap pemuatan daftar meski tak seorang pun
    // membukanya, dan setiap penekanan tombol menyeret keadaan sebesar itu
    // bolak-balik ke server.
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    expect($isi)
        ->not->toContain('Simpan Serah Terima')
        ->not->toContain('orcha-kamera-video');
});

test('judul kolom daftar sewa ditengahkan, termasuk yang isinya rata kanan', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Layar ini dipindahkan ke bahasa rupa dasbor (.dsb-tabel), yang sudah
     | mengurus perataan judul kolomnya sendiri. Yang dijaga sekarang: tabelnya
     | memang .dsb-tabel, dan kolom yang isinya rata kanan menyatakan
     | perataannya di judulnya juga — kalau hanya selnya yang dirata-kanan,
     | judul dan angkanya berdiri di dua garis yang berbeda.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    $kepala = substr($isi, strpos($isi, '<thead'), strpos($isi, '</thead>') - strpos($isi, '<thead'));

    expect($isi)->toContain('class="dsb-tabel sw-tabel"')
        // Tagihan & Aksi rata kanan, judulnya ikut.
        ->and(substr_count($kepala, 'text-align: right;'))->toBe(2);
});

test('tombol aksi setinggi tombol ikon di bahasa rupa dasbor', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Terukur sebelumnya: ikon detail 58x38 dan tombol serah terima 127x29 —
    // dua benda bersebelahan dengan tinggi berbeda sembilan piksel, dan garis
    // bawahnya tidak pernah sejajar.
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    /*
     | Diiris dari TABELNYA, bukan dari '<tbody' pertama di halaman.
     |
     | Halaman admin memuat tabel lain lebih dulu (bilah samping), jadi irisan
     | yang dimulai dari '<tbody' pertama justru membentang melewati blok
     | <style> — dan nama kelas yang muncul di aturan CSS ikut terhitung.
     | Terukur: 6 kemunculan untuk satu baris data.
     */
    $mulaiTabel = strpos($isi, 'class="dsb-tabel sw-tabel"');
    $tabel = substr($isi, $mulaiTabel, strpos($isi, '</table>', $mulaiTabel) - $mulaiTabel);

    /*
     | Tinggal SATU kendali di sel aksi.
     |
     | Tombol ikon "lihat detail" dibuang: nama penyewanya di kolom pertama
     | sudah menjadi tautan ke halaman yang sama, jadi ikon itu mengulang
     | jalan yang sudah ada — sambil memakan lebar yang membuat tombol
     | sebelahnya terpotong di tepi kartu (terukur pada 1440px).
     |
     | Tingginya tetap dijaga di bawah: begitu ada tombol kedua kembali ke
     | sel ini, keduanya harus sejajar.
     */
    expect(substr_count($tabel, 'sw-lanjut'))->toBe(1);

    /*
     | Tingginya DIPATOK sama, bukan diperiksa lewat nama kelasnya: nama yang
     | sama tidak menjamin angka yang sama, dan selisih sembilan piksel itulah
     | yang dulu membuat garis bawah keduanya tidak pernah sejajar.
     |
     | .dsb-tabel-btn setinggi 34px (partials/dasbor-gaya); yang berlabel
     | mengikuti angka itu, bukan 44px bawaan .dsb-tombol yang terlalu jangkung
     | untuk duduk di dalam baris tabel.
     */
    $gayaDasbor = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));
    preg_match('/\.dsb-tabel-btn\s*\{[^}]*?height:\s*(\d+)px/s', $gayaDasbor, $ikon);

    $mulai = strpos($isi, '.sw-lanjut {');
    preg_match('/height:\s*(\d+)px/', substr($isi, $mulai, strpos($isi, '}', $mulai) - $mulai), $label);

    expect($ikon[1] ?? null)->not->toBeNull('Tinggi .dsb-tabel-btn tidak ditemukan.')
        ->and($label[1] ?? null)->toBe($ikon[1], 'Tombol berlabel tidak setinggi tombol ikon di sebelahnya.');
});

test('status di daftar sewa ringkas dan berwarna menurut keadaannya', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji(['status' => 'berjalan'])], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Layout memaksa 48px pada SETIAP .form-select — terlalu besar untuk satu
    // sel tabel, dan daftar sepuluh sewa jadi memakan layar untuk dua puluh.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')
        ->assertOk()
        ->assertSee('orcha-status-ringkas', false)
        ->assertSee('data-status="berjalan"', false);
});

test('kepala lembar serah terima satu kartu, sebentuk dengan Pesanan Toko', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => sewaUji()]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Judul dan angka ringkasnya dulu tinggal di DUA kartu berturutan, dan
     | layar ini jadi satu-satunya di antara Pesanan Toko, Pendaftaran Trip,
     | dan Detail sewa yang bentuknya begitu. Bantalan di antara kedua kartu
     | memakan tinggi tanpa memuat apa pun.
     |
     | Yang diperiksa bukan "ada kartu kepala" — itu selalu benar — melainkan
     | bahwa judul DAN ketiga angkanya berada di dalam kartu yang SAMA. Karena
     | itu isinya dipotong dari pembuka kartu pertama sampai pembuka kartu
     | berikutnya, lalu yang dicari di dalam potongan itu saja.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/5/serah-terima')->assertOk()->getContent();

    $pembuka = '<div class="card border-0 shadow-sm rounded-4 mb-4">';
    $awal = strpos($isi, $pembuka);
    $kedua = strpos($isi, $pembuka, $awal + 1);

    expect($awal)->not->toBeFalse()
        ->and($kedua)->not->toBeFalse();

    $kepala = substr($isi, $awal, $kedua - $awal);

    expect($kepala)
        ->toContain('Serah Terima Kendaraan')
        ->toContain('orcha-tautan-balik')
        ->toContain('SW-0001')
        ->toContain('Ditunggu kembali')
        ->toContain('Keterlambatan');

    // Satu jalan kembali, bukan dua: tautan di kepala sudah menuju daftar,
    // jadi tombol senama di sebelahnya dibuang.
    expect($kepala)->not->toContain('Kembali ke daftar');
});

test('tabel pemeriksaan tiga kolom, tanpa lajur yang tak pernah terisi', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => sewaUji()]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Lajur "Perubahan" hanya terisi pada baris yang memburuk. Selama unitnya
     | belum diperiksa — keadaan lembar ini saat dibuka — tidak satu pun dari
     | tiga belas barisnya terisi, dan yang tergambar adalah lajur kosong
     | selebar 16% di sebelah dua pemilih yang justru terjepit.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/5/serah-terima')->assertOk()->getContent();

    expect($isi)
        ->not->toContain('<th style="width:16%">Perubahan</th>')
        ->toContain('<th style="width:31%">Saat diserahkan</th>')
        ->toContain('<th style="width:31%">Saat kembali</th>');
});

it('detail sewa memakai kerangka dasbor lemon, bukan dialek sendiri', function () {
    $sumber = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/penyewaan/detail.blade.php')
    );

    // Sumber bahasa rupanya ikut dimuat, kalau tidak seluruh kelas dsb-* mati
    // tanpa satu pun galat — halaman tetap 200, hanya tampil polos.
    expect($sumber)->toContain("@include('livewire.pages.admin.partials.dasbor-gaya')");

    foreach (['dsb-hero', 'dsb-hero-aksi', 'dsb-rak', 'dsb-stat', 'dsb-kartu', 'dsb-data', 'dsb-kosong'] as $kelas) {
        expect(str_contains($sumber, $kelas))->toBeTrue("kerangka {$kelas} tidak dipakai");
    }

    // Barisnya sendiri hidup di partial bersama, jadi diperiksa di sana —
    // kalau tidak, halaman yang memanggil partial yang salah tetap lolos.
    $baris = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/partials/baris-data.blade.php')
    );
    expect($sumber)->toContain('orcha.partials.baris-data')
        ->and($baris)->toContain('dsb-data-baris')
        ->and($baris)->toContain('dsb-data-nilai');

    // Dialek lama tidak boleh kembali diam-diam.
    expect($sumber)->not->toContain('orcha-kartu-tindakan')
        ->not->toContain('orcha-bagian-nomor')
        ->not->toContain('orcha-bagian-judul');
});

/* ---------------------- VENDOR & MODAL SEWA ---------------------- */

test('daftar sewa punya tab "Belum ada vendor" di kelompok pekerjaan', function () {
    Http::fake([
        '*/penyewaan*' => Http::response([
            'data' => [sewaUji()],
            'meta' => ['total' => 1, 'jumlah_tab' => ['semua' => 1, 'tanpa_vendor' => 1]],
        ]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Mencari unit ke vendor adalah satu-satunya langkah di alur sewa yang
     | jawabannya ada di tangan orang lain, dan pesanan yang unitnya tidak
     | pernah dicarikan berakhir dibatalkan di hari H. Karena itu ia tab
     | berangka, bukan saringan yang harus ditemukan dulu.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    expect($isi)->toContain('Belum ada vendor');
});

test('pindah ke tab belum ada vendor mengirim saringannya ke Orcha', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [], 'meta' => ['jumlah_tab' => []]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    Livewire::actingAs(adminPeserta())
        ->test(App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanList::class)
        ->call('setTab', 'tanpa_vendor')
        ->assertSet('tanpaVendor', true)
        // Statusnya TIDAK ikut tersaring: "belum ada vendor" berlaku lintas
        // status, dan pesanan bisa sudah DP masuk sementara unitnya belum
        // dicarikan.
        ->assertSet('filterStatus', '');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'tanpa_vendor=1'));
});

test('detail sewa menyebut modal belum dicatat, bukan Rp 0', function () {
    Http::fake([
        '*/vendor*' => Http::response(['data' => [], 'meta' => []]),
        '*/penyewaan*' => Http::response(['data' => sewaUji([
            'modal' => null, 'untung' => null, 'margin_persen' => null,
            'vendor' => null, 'vendor_ditentukan' => false, 'milik_sendiri' => false,
        ])]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Modal nol membuat untungnya terbaca sebesar SELURUH harga sewa, dan
     | angka itu akan dipercaya karena tampil seperti hasil hitungan — bukan
     | seperti data yang belum diisi.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/5')->assertOk()->getContent();

    expect($isi)->toContain('Belum dicatat')
        ->toContain('Belum diketahui')
        ->toContain('Unitnya belum dicarikan ke vendor mana pun');
});

test('detail sewa menampilkan vendor dan untungnya begitu modal dicatat', function () {
    Http::fake([
        '*/vendor*' => Http::response(['data' => [], 'meta' => []]),
        '*/penyewaan*' => Http::response(['data' => sewaUji([
            'estimasi_biaya' => 700000,
            'modal' => 500000, 'untung' => 200000, 'margin_persen' => 28.6,
            'vendor' => ['id' => 9, 'nama' => 'CV Rekanan Jaya', 'kontak' => '0812-1111', 'jenis' => 'transportasi'],
            'vendor_ditentukan' => true, 'milik_sendiri' => false,
        ])]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/5')->assertOk()->getContent();

    expect($isi)->toContain('CV Rekanan Jaya')
        ->toContain('0812-1111')
        ->toContain('Rp 200.000')
        ->not->toContain('Unitnya belum dicarikan ke vendor mana pun');
});

test('modal yang dikosongkan dikirim sebagai null, bukan nol', function () {
    Http::fake([
        '*/vendor*' => Http::response(['data' => [], 'meta' => []]),
        '*/penyewaan*' => Http::response(['data' => sewaUji()]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Nol berarti unitnya gratis. Hanya null yang berarti "belum dicatat",
    // dan cuma itu yang membuat layar jujur bilang untungnya belum diketahui.
    Livewire::actingAs(adminPeserta())
        ->test(App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanDetail::class, ['penyewaan' => 5])
        ->set('vendorId', '')
        ->set('modal', '')
        ->call('simpanVendor');

    Http::assertSent(function ($r) {
        if (! str_contains($r->url(), '/vendor') || $r->method() !== 'PATCH') {
            return false;
        }

        return array_key_exists('modal', $r->data()) && $r->data()['modal'] === null;
    });
});

test('borang armada mengirim vendor dan modal, dan unit milik sendiri tanpa vendor', function () {
    Http::fake([
        '*/vendor*' => Http::response(['data' => [
            ['id' => 9, 'nama' => 'CV Rekanan Jaya', 'jenis' => 'transportasi'],
        ], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $komponen = Livewire::actingAs(adminPeserta())
        ->test(App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaForm::class)
        // Merek lebih dulu: updatedMerek() mengosongkan nama unit, jadi nama
        // yang diisi sebelum merek akan terhapus lagi sebelum sempat dipakai.
        ->set('merek', 'Toyota')
        ->set('nopol', 'AB 1234 CD')
        ->set('tarifHariTeks', '350.000')
        ->set('vendorId', '9')
        ->set('modalHariTeks', '250.000')
        ->set('nama', 'Avanza Uji');

    $komponen->call('simpan');

    Http::assertSent(function ($r) {
        if ($r->method() !== 'POST' || ! str_contains($r->url(), '/kendaraan')) {
            return false;
        }

        return ($r->data()['vendor_id'] ?? null) === 9
            && (int) ($r->data()['modal_hari'] ?? 0) === 250000;
    });

    // Dinyalakan "milik sendiri": vendornya dibersihkan, bukan cuma diabaikan
    // saat menyimpan — dua jawaban untuk satu pertanyaan di satu borang.
    $komponen->set('milikSendiri', true)->assertSet('vendorId', '');
});

/* ------------- VENDOR: PESAN, DAFTAR, DAN LAPORAN ------------- */

test('detail sewa punya pesan siap kirim untuk vendor, terpisah dari penyewa', function () {
    Http::fake([
        '*/vendor*' => Http::response(['data' => [], 'meta' => []]),
        '*/penyewaan*' => Http::response(['data' => sewaUji([
            'vendor' => ['id' => 9, 'nama' => 'CV Rekanan Jaya', 'kontak' => '08121111', 'jenis' => 'Armada'],
            'vendor_ditentukan' => true, 'modal' => 500000,
        ])]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $pesan = Livewire::actingAs(adminPeserta())
        ->test(App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanDetail::class, ['penyewaan' => 5])
        ->instance()
        ->pilihanPesanVendor();

    expect($pesan)->not->toBeEmpty();

    $gabung = collect($pesan)->pluck('pesan')->implode(' ');

    /*
     | Nama dan nomor PENYEWA tidak boleh ikut ke vendor. Vendor perlu tahu
     | unit, waktu, dan titik jemput — bukan siapa penyewanya. Sekali
     | terkirim, data pelanggan itu tidak bisa ditarik kembali.
     */
    expect($gabung)->toContain('CV Rekanan Jaya')
        ->toContain('Rp 500.000')
        ->not->toContain('Budi')
        ->not->toContain('0812345678');
});

test('harga yang belum dicatat tidak dikirim ke vendor sebagai Rp 0', function () {
    Http::fake([
        '*/vendor*' => Http::response(['data' => [], 'meta' => []]),
        '*/penyewaan*' => Http::response(['data' => sewaUji([
            'vendor' => ['id' => 9, 'nama' => 'CV Rekanan Jaya', 'kontak' => '08121111', 'jenis' => 'Armada'],
            'vendor_ditentukan' => true, 'modal' => null,
        ])]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $gabung = collect(Livewire::actingAs(adminPeserta())
        ->test(App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanDetail::class, ['penyewaan' => 5])
        ->instance()
        ->pilihanPesanVendor())->pluck('pesan')->implode(' ');

    // "Rp 0" ke vendor terbaca sebagai penawaran gratis.
    expect($gabung)->toContain('mohon dikonfirmasi')
        ->not->toContain('Rp 0');
});

test('tanpa vendor tercatat, tidak ada pesan vendor sama sekali', function () {
    Http::fake([
        '*/vendor*' => Http::response(['data' => [], 'meta' => []]),
        '*/penyewaan*' => Http::response(['data' => sewaUji(['vendor' => null])]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Tombol yang membuka daftar kosong mengajari admin bahwa menekannya
    // tidak ada gunanya.
    $pesan = Livewire::actingAs(adminPeserta())
        ->test(App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanDetail::class, ['penyewaan' => 5])
        ->instance()
        ->pilihanPesanVendor();

    expect($pesan)->toBe([]);
});

test('daftar sewa menyebut vendor per baris, dan menandai yang belum dicarikan', function () {
    Http::fake([
        '*/penyewaan*' => Http::response([
            'data' => [
                sewaUji(['id' => 5, 'vendor' => ['id' => 9, 'nama' => 'CV Rekanan Jaya', 'kontak' => '08121111', 'jenis' => 'Armada']]),
                sewaUji(['id' => 6, 'kode' => 'SW-0002', 'vendor' => null, 'status' => 'baru']),
            ],
            'meta' => ['total' => 2, 'jumlah_tab' => []],
        ]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Daftar ini sudah punya tab "Belum ada vendor", tetapi barisnya sendiri
     | tidak pernah menyebut vendor — jadi satu-satunya cara tahu adalah
     | membuka pesanannya satu per satu, persis pekerjaan yang hendak
     | dihemat tabnya.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    expect($isi)->toContain('CV Rekanan Jaya')
        ->toContain('Vendor belum dicarikan');
});

test('layar keuntungan sewa menyebut yang belum dicatat modalnya', function () {
    Http::fake([
        '*/keuntungan-sewa/rincian*' => Http::response(['data' => [], 'meta' => []]),
        '*/keuntungan-sewa*' => Http::response(['data' => [
            'ringkas' => [
                'pesanan' => 5, 'pesanan_dihitung' => 3, 'tanpa_modal' => 2,
                'pendapatan' => 5000000, 'pendapatan_dihitung' => 3000000,
                'modal' => 2000000, 'untung' => 1000000, 'margin_persen' => 33.3,
            ],
            'per_unit' => [], 'per_vendor' => [], 'vendor' => [],
            'dasar_tanggal' => ['mulai' => 'Tanggal mulai sewa'],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/keuntungan-sewa')->assertOk()->getContent();

    /*
     | Angka yang paling penting di layar ini bukan untungnya, melainkan
     | berapa yang belum dicatat: selama ia belum nol, untung di atasnya
     | masih kurang dari yang sebenarnya.
     */
    expect($isi)->toContain('2 pesanan belum dicatat modalnya')
        ->toContain('Rp 5.000.000')       // pendapatan dari SELURUH pesanan
        ->toContain('Rp 1.000.000');      // untung dari yang dicatat saja
});
