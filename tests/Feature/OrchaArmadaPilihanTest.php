<?php

use App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaForm;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Merek dan nama unit dipilih dari katalog, bukan diketik.
 *
 * Mengetik bebas menghasilkan ejaan berbeda untuk unit yang sama — "Avanza",
 * "avanza", "All New Avanza" — sehingga penyaringan di halaman publik tidak
 * dapat diandalkan. Nopol tetap diketik manual: tiap unit nomornya sendiri,
 * tidak ada daftar yang bisa dipilih.
 */
function adminArmada(): User
{
    $role = Role::create(['name' => 'uji-'.uniqid(), 'description' => 'uji']);
    $izin = Permission::firstOrCreate(['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']);
    $role->permissions()->attach($izin->id);

    $user = User::factory()->create(['role_id' => $role->id]);
    EmployeeDetail::create(['user_id' => $user->id, 'jabatan' => 'Admin', 'nomor_rekening' => '1',
        'tanggal_lahir' => '1995-01-01', 'phone' => '0812', 'alamat' => 'Yogyakarta']);

    return $user->fresh();
}

function katalogUji(): array
{
    return [
        'Toyota' => ['Avanza', 'HiAce Commuter', 'Innova Zenix'],
        'Suzuki' => ['Ertiga', 'XL7'],
    ];
}

function unitArmadaUji(array $ubah = []): array
{
    return ['data' => array_merge([
        'id' => 5, 'uuid' => 'abc', 'nama' => 'Avanza', 'merek' => 'Toyota',
        'jenis' => 'mobil', 'jenis_label' => 'Mobil', 'nopol' => 'AB 1234 CD',
        'kapasitas' => 7, 'transmisi_tersedia' => ['Matic'], 'transmisi_label' => 'Matic',
        'tarif' => ['jam' => 55000, '12jam' => 280000, 'hari' => 500000, 'sopir_per_hari' => 150000],
        'gambar' => null, 'tersedia' => true, 'jumlah_penyewaan' => 3,
        'kondisi' => null, 'kondisi_terkini' => null,
        'jadwal' => ['sedang_disewa' => false, 'kode_berjalan' => null, 'kembali_pada' => null,
            'kode_berikutnya' => null, 'mulai_berikutnya' => null],
    ], $ubah)];
}

function fakeArmada(array $ubahUnit = []): void
{
    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'jenis_kendaraan' => ['mobil' => 'Mobil'],
            'katalog_kendaraan' => katalogUji(),
            'kapasitas_kendaraan' => [
                'Toyota' => ['Avanza' => 7, 'HiAce Commuter' => 15],
                'Suzuki' => ['Ertiga' => 7],
            ],
            'jenis_per_model' => [
                'Toyota' => ['Avanza' => 'mobil', 'HiAce Commuter' => 'hiace'],
                'Suzuki' => ['Ertiga' => 'mobil'],
            ],
            'cc_per_model' => [
                'Toyota' => ['Avanza' => 1500, 'HiAce Commuter' => 2500],
                'Suzuki' => ['Ertiga' => 1500],
            ],
            'lepas_kunci_per_model' => [
                'Toyota' => ['Avanza' => true, 'HiAce Commuter' => false],
                'Suzuki' => ['Ertiga' => true],
            ],
            'varian_per_model' => [
                'Toyota' => [
                    'Avanza' => ['E', 'G', 'Veloz'],
                    'HiAce Commuter' => ['Kursi Kulit', 'Standar'],
                ],
                'Suzuki' => ['Ertiga' => ['GA', 'GL', 'GX']],
            ],
            'pemeriksaan_kendaraan' => ['kaca' => 'Kaca & spion'],
            'kondisi_pemeriksaan' => ['baik' => 'Baik', 'rusak' => 'Rusak'],
        ]]),
        '*/kendaraan/5' => Http::response(unitArmadaUji($ubahUnit)),
        '*' => Http::response(['data' => []]),
    ]);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
    cache()->forget('orcha.perlu-ditindak');
});

test('katalog merek tampil sebagai pilihan di formulir', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->assertSee('Toyota')
        ->assertSee('Suzuki')
        // Nomor polisi tetap diketik: tidak ada daftar nopol yang bisa dipilih.
        ->assertSee('Nomor polisi');
});

test('nama unit yang tampil mengikuti merek yang dipilih', function () {
    fakeArmada();

    $uji = Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Suzuki');

    expect($uji->instance()->modelPilihan())->toBe(['Ertiga', 'XL7']);

    $uji->set('merek', 'Toyota');

    expect($uji->instance()->modelPilihan())->toBe(['Avanza', 'HiAce Commuter', 'Innova Zenix']);
});

test('mengganti merek mengosongkan nama unit yang sudah dipilih', function () {
    fakeArmada();

    // Tanpa pengosongan ini, memilih Toyota lalu Avanza lalu berpindah ke
    // Suzuki akan menyimpan "Suzuki Avanza" — unit yang tidak pernah ada.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('merek', 'Suzuki')
        ->assertSet('nama', '')
        ->assertSet('nama', '');
});

test('unit tersimpan memakai nilai dari dropdown', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Suzuki')
        ->set('nama', 'Ertiga')
        ->set('nopol', 'AB 9 XY')
        ->set('kapasitas', 7)
        ->set('tarifHariTeks', '450.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/kendaraan')
        && ($p->data()['merek'] ?? null) === 'Suzuki'
        && ($p->data()['nama'] ?? null) === 'Ertiga'
        && ($p->data()['nopol'] ?? null) === 'AB 9 XY');
});

test('merek di luar katalog bisa ditulis manual', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Chery')
        ->set('nama', 'Tiggo 8 Pro')
        ->set('nopol', 'AB 7 ZZ')
        ->set('tarifHariTeks', '600.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && ($p->data()['merek'] ?? null) === 'Chery'
        && ($p->data()['nama'] ?? null) === 'Tiggo 8 Pro');
});

test('merek dan nama unit yang kosong ditolak, bukan tersimpan tanpa nama', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('tarifHariTeks', '300.000')
        ->call('simpan')
        ->assertHasErrors(['merek', 'nama']);
});

test('merek yang hanya berisi spasi dianggap kosong', function () {
    fakeArmada();

    // Popup "tulis sendiri" sudah menolak isian kosong di peramban, tapi dialog
    // bukan pengaman — komponennya memangkas dan memeriksa sendiri.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', '   ')
        ->set('tarifHariTeks', '300.000')
        ->call('simpan')
        ->assertHasErrors('merek');
});

test('menyunting unit memuat merek dan nama tersimpannya', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('merek', 'Toyota')
        ->assertSet('nama', 'Avanza')
        ->assertSee('Avanza');
});

test('unit lama di luar katalog tidak kehilangan mereknya saat disunting', function () {
    fakeArmada(['merek' => 'Chery', 'nama' => 'Tiggo 8 Pro']);

    // Ini pengaman terpentingnya: dropdown yang tidak memuat merek unit itu
    // sendiri akan memaksa admin mengubahnya jadi merek lain. Nilainya harus
    // utuh di kotak manual, bukan hilang.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('merek', 'Chery')
        ->assertSet('nama', 'Tiggo 8 Pro')
        ->assertSee('Chery')
        ->assertSee('Tiggo 8 Pro')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() !== 'GET'
        && ($p->data()['merek'] ?? null) === 'Chery'
        && ($p->data()['nama'] ?? null) === 'Tiggo 8 Pro');
});

test('katalog kosong tidak mengunci admin, unit tetap bisa ditulis manual', function () {
    // Kalau Orcha tidak terjangkau, rujukan() mengembalikan array kosong dan
    // kedua dropdown jadi tanpa pilihan. Jalur manual yang menyelamatkan
    // keadaan itu: admin tetap bisa menambah unit, bukan terhenti.
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('tarifHariTeks', '350.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && ($p->data()['merek'] ?? null) === 'Toyota'
        && ($p->data()['nama'] ?? null) === 'Avanza');
});

/* --------- TAMBAHAN MANUAL MASUK DAFTAR & BISA DIHAPUS --------- */

test('merek yang ditulis manual didaftarkan ke katalog Orcha', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('tambahKatalog', 'Esemka')
        ->assertSet('merek', 'Esemka');

    // Tidak cukup memakainya untuk unit ini saja — harus ikut terdaftar supaya
    // unit sejenis berikutnya tinggal memilih.
    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/katalog-kendaraan')
        && ($p->data()['merek'] ?? null) === 'Esemka');
});

test('nama unit manual didaftarkan di bawah merek yang sedang dipilih', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->call('tambahKatalog', 'Kijang Krista', true)
        ->assertSet('nama', 'Kijang Krista');

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/katalog-kendaraan')
        && ($p->data()['merek'] ?? null) === 'Toyota'
        && ($p->data()['model'] ?? null) === 'Kijang Krista');
});

test('nama unit tidak didaftarkan bila mereknya belum dipilih', function () {
    fakeArmada();

    // Entri model harus menempel pada mereknya; mengirim model tanpa merek hanya
    // menghasilkan baris yatim di katalog.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('tambahKatalog', 'Bima 1.3', true)
        ->assertSet('nama', 'Bima 1.3');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), '/katalog-kendaraan'));
});

test('menambah merek mengosongkan nama unit sebelumnya', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->call('tambahKatalog', 'Esemka')
        ->assertSet('merek', 'Esemka')
        ->assertSet('nama', '');
});

test('entri katalog bisa dihapus dari picker', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('hapusKatalog', 12);

    Http::assertSent(fn ($p) => $p->method() === 'DELETE'
        && str_contains($p->url(), '/katalog-kendaraan/12'));
});

test('daftar terbaru dikirim ke peramban sesudah menambah atau menghapus', function () {
    fakeArmada();

    // Rujukan disimpan 10 menit. Tanpa dibuang dan dikirim ulang, merek yang
    // baru didaftarkan tidak muncul sampai simpanannya kedaluwarsa — terlihat
    // seperti penambahannya gagal padahal tersimpan.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('tambahKatalog', 'Esemka')
        ->assertDispatched('orcha-katalog-segar');

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('hapusKatalog', 3)
        ->assertDispatched('orcha-katalog-segar');
});

test('Orcha tak terjangkau saat mendaftarkan tidak menghalangi pemakaian nilainya', function () {
    Http::fake(['*' => Http::response(['pesan' => 'gagal'], 500)]);

    // Menghalangi admin menyimpan unit karena katalognya gagal diperbarui adalah
    // menukar masalah kecil dengan masalah besar.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('tambahKatalog', 'Esemka')
        ->assertSet('merek', 'Esemka')
        ->assertDispatched('toast-error');
});

test('spasi berlebih pada tambahan manual dirapikan', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('tambahKatalog', '  Great   Wall  ')
        ->assertSet('merek', 'Great Wall');
});

test('tambahan manual yang kosong diabaikan, tidak dikirim ke Orcha', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->call('tambahKatalog', '   ')
        ->assertSet('merek', '');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), '/katalog-kendaraan'));
});

/* ------------------- KAPASITAS SEMI OTOMATIS ------------------- */

test('memilih nama unit mengisi kapasitas dari katalog', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'HiAce Commuter')
        // 15 kursi menurut spesifikasi, satu untuk sopir — jadi 14 penumpang.
        ->assertSet('kapasitas', 14)
        ->assertSet('kursiOtomatisDari', 'Toyota HiAce Commuter')
        // Keterangannya tampil supaya angkanya tidak dianggap keputusan mati.
        ->assertSee('ubah bila unit ini berbeda');
});

test('kapasitas tetap bisa diubah admin sesudah terisi otomatis', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->assertSet('kapasitas', 7)
        ->set('kapasitas', 6)
        ->assertSet('kapasitas', 6)
        ->assertSet('kapasitasDiubahManual', true);
});

test('koreksi kapasitas tidak ditimpa saat nama unit diganti dalam merek yang sama', function () {
    fakeArmada();

    // Kalau koreksi ikut tertimpa, admin kehilangan angkanya tanpa tahu kenapa.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('kapasitas', 6)
        ->set('nama', 'HiAce Commuter')
        ->assertSet('kapasitas', 6);
});

test('mengganti merek melepas koreksi, saran boleh mengisi lagi', function () {
    fakeArmada();

    // Unitnya berganti sama sekali, jadi koreksi untuk unit sebelumnya memang
    // tidak lagi berlaku.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('kapasitas', 6)
        ->set('merek', 'Suzuki')
        ->assertSet('kapasitasDiubahManual', false)
        ->set('nama', 'Ertiga')
        ->assertSet('kapasitas', 7);
});

test('model tanpa angka kursi tidak mengubah kapasitas', function () {
    fakeArmada();

    // Lebih baik isiannya dibiarkan daripada diisi angka yang belum tentu benar:
    // angka yang sudah tertulis cenderung tidak diperiksa lagi.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('kapasitas', 9)
        ->set('kapasitasDiubahManual', false)
        ->set('merek', 'Suzuki')
        ->set('nama', 'XL7')
        ->assertSet('kapasitas', 9)
        ->assertSet('kursiOtomatisDari', '');
});

test('menyunting unit memakai kapasitas tersimpannya, bukan saran katalog', function () {
    fakeArmada(['kapasitas' => 4]);

    // Nilai tersimpan menang saat halaman dibuka: unit ini benar-benar berkursi
    // 4 walau katalog menyebut 7, dan saran tidak boleh mengubahnya tanpa
    // diminta. Yang menyalakan saran lagi adalah penggantian modelnya — diuji
    // tersendiri di 'semi otomatis juga berlaku saat mengganti unit'.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('kapasitas', 4)
        ->assertSet('kursiOtomatisDari', '');
});

test('unit baru yang ditulis manual ikut mengisi kursi bila katalog mengetahuinya', function () {
    fakeArmada();

    // "Avanza" ditulis manual padahal sudah ada di katalog: Orcha menjawabnya
    // sudah ada, dan kursinya tetap terisi.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->call('tambahKatalog', 'Avanza', true)
        ->assertSet('nama', 'Avanza')
        ->assertSet('kapasitas', 7);
});

/* ---------- JENIS & CC SEMI OTOMATIS, TIPE, TAHUN ---------- */

test('memilih unit mengisi kapasitas, jenis, dan cc sekaligus', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'HiAce Commuter')
        ->assertSet('kapasitas', 14)
        ->assertSet('jenis', 'hiace')
        ->assertSet('cc', 2500);
});

test('semi otomatis berlaku di halaman tambah', function () {
    fakeArmada();

    // Halaman tambah adalah jalur utamanya: unit baru justru yang paling sering
    // diketik dari nol.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->assertSet('ubah', false)
        ->set('merek', 'Suzuki')
        ->set('nama', 'Ertiga')
        ->assertSet('kapasitas', 7)
        ->assertSet('jenis', 'mobil')
        ->assertSet('cc', 1500);
});

test('semi otomatis juga berlaku saat mengganti unit di halaman ubah', function () {
    fakeArmada(['kapasitas' => 4, 'nama' => 'Avanza', 'merek' => 'Toyota']);

    // Saat halaman dibuka, nilai tersimpan unitnya yang dipakai. Begitu modelnya
    // diganti, unit yang dimaksud sudah bukan yang sama — jadi saran berlaku.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('kapasitas', 4)
        ->set('nama', 'HiAce Commuter')
        ->assertSet('kapasitas', 14)
        ->assertSet('jenis', 'hiace')
        ->assertSet('cc', 2500);
});

test('membuka halaman ubah tidak menimpa nilai tersimpan dengan saran', function () {
    fakeArmada(['kapasitas' => 4, 'cc' => 1300, 'varian' => 'E', 'tahun' => 2019]);

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('kapasitas', 4)
        ->assertSet('cc', 1300)
        ->assertSet('varian', 'E')
        ->assertSet('tahun', 2019)
        ->assertSet('kursiOtomatisDari', '')
        ->assertSet('ccOtomatisDari', '');
});

test('koreksi tiap isian berdiri sendiri', function () {
    fakeArmada();

    // Mengoreksi kapasitas tidak boleh ikut membekukan cc, dan sebaliknya.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('kapasitas', 6)
        ->set('nama', 'HiAce Commuter')
        ->assertSet('kapasitas', 6)
        ->assertSet('cc', 2500)
        ->assertSet('jenis', 'hiace');
});

test('jenis yang diubah admin tidak ditimpa saran', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->assertSet('jenis', 'mobil')
        ->set('jenis', 'bus')
        ->set('nama', 'HiAce Commuter')
        ->assertSet('jenis', 'bus');
});

test('tipe tersedia sebagai pilihan dan dikosongkan saat unit berganti', function () {
    fakeArmada();

    $uji = Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza');

    expect($uji->instance()->varianPilihan())->toBe(['E', 'G', 'Veloz']);

    // Tipe melekat pada modelnya: "Veloz" tidak berlaku untuk Ertiga.
    $uji->set('varian', 'Veloz')
        ->set('nama', 'HiAce Commuter')
        ->assertSet('varian', '');
});

test('tipe, tahun, dan cc ikut terkirim saat disimpan', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('varian', 'Veloz')
        ->set('tahun', 2025)
        ->set('tarifHariTeks', '500.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && ($p->data()['varian'] ?? null) === 'Veloz'
        && ($p->data()['tahun'] ?? null) === 2025
        && ($p->data()['cc'] ?? null) === 1500);
});

test('tahun jauh di depan ditolak sebelum dikirim', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('tahun', 2035)
        ->set('tarifHariTeks', '500.000')
        ->call('simpan')
        ->assertHasErrors('tahun');
});

test('tipe dan tahun boleh dibiarkan kosong', function () {
    fakeArmada();

    // Unit lama tidak tahu tahunnya, dan memaksa angka ke sana hanya
    // menghasilkan data karangan yang terlihat seperti fakta.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Suzuki')
        ->set('nama', 'Ertiga')
        ->set('tarifHariTeks', '450.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && array_key_exists('varian', $p->data()) && $p->data()['varian'] === null
        && array_key_exists('tahun', $p->data()) && $p->data()['tahun'] === null);
});

/* ------------- LEPAS KUNCI & KURSI PENUMPANG ------------- */

test('memilih unit besar menandainya selalu dengan sopir', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'HiAce Commuter')
        ->assertSet('lepasKunci', false)
        // Akibatnya terlihat sebelum disimpan, di isian Kapasitas maupun kartunya.
        ->assertSet('kapasitas', 14)
        ->assertSee('14 penumpang')
        ->assertSee('Selalu dengan sopir');
});

test('mobil biasa tetap boleh lepas kunci dengan kursi penuh', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->assertSet('lepasKunci', true)
        ->assertSee('Boleh lepas kunci');

    expect(Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')->set('nama', 'Avanza')
        ->instance()->kursiTotal())->toBe(7);
});

test('menggeser sakelar lepas kunci menghitung ulang kapasitasnya', function () {
    fakeArmada();

    // Menandai unit "selalu dengan sopir" tanpa mengubah kapasitasnya akan
    // meninggalkan angka yang menjanjikan satu kursi lebih daripada yang ada.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->assertSet('kapasitas', 7)
        ->set('lepasKunci', false)
        ->assertSet('kapasitas', 6)
        ->set('lepasKunci', true)
        ->assertSet('kapasitas', 7);
});

test('tanpa angka katalog, sakelar tetap menyesuaikan kapasitas yang sudah tertulis', function () {
    fakeArmada();

    // Unit di luar katalog tidak punya angka rujukan, tapi arah perubahannya
    // tetap jelas: satu kursi dilepas untuk sopir, atau dikembalikan.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Chery')
        ->set('nama', 'Tiggo 8 Pro')
        ->set('kapasitas', 7)
        ->set('kapasitasDiubahManual', false)
        ->set('lepasKunci', false)
        ->assertSet('kapasitas', 6);
});

test('kapasitas yang sudah dikoreksi tidak dihitung ulang oleh sakelar', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('kapasitas', 5)
        ->set('lepasKunci', false)
        ->assertSet('kapasitas', 5);
});

test('pilihan lepas kunci admin tidak ditimpa saran', function () {
    fakeArmada();

    // Pemilik boleh memutuskan HiAce tertentu memang dilepas kunci; sistem tidak
    // berhak memaksakan saran atas keputusan itu.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'HiAce Commuter')
        ->assertSet('lepasKunci', false)
        ->set('lepasKunci', true)
        ->set('nama', 'Avanza')
        ->assertSet('lepasKunci', true);
});

test('mengganti merek melepas pilihan lepas kunci', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'Avanza')
        ->set('lepasKunci', false)
        ->set('merek', 'Toyota')
        ->assertSet('lepasKunciDiubahManual', false)
        ->set('nama', 'HiAce Commuter')
        ->assertSet('lepasKunci', false);
});

test('lepas kunci ikut terkirim saat disimpan', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'HiAce Commuter')
        ->set('tarifHariTeks', '1.200.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && ($p->data()['lepas_kunci'] ?? null) === false
        && ($p->data()['kapasitas'] ?? null) === 14);
});

test('membuka halaman ubah memakai lepas kunci tersimpannya', function () {
    fakeArmada(['nama' => 'HiAce Commuter', 'kapasitas' => 15, 'lepas_kunci' => true]);

    // Keputusan yang sudah tercatat tidak diubah tanpa diminta, walau saran
    // katalog menyebut sebaliknya.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('lepasKunci', true);
});

test('kapasitas tidak pernah turun di bawah satu', function () {
    fakeArmada();

    // Kapasitas 1 lalu ditandai dengan sopir menghasilkan 0 bila dikurangi
    // lugas — angka yang tidak berarti apa-apa.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('kapasitas', 1)
        ->set('kapasitasDiubahManual', false)
        ->set('lepasKunci', false)
        ->assertSet('kapasitas', 1);
});

test('tipe tersedia untuk unit besar, bukan daftar kosong', function () {
    fakeArmada();

    $uji = Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'HiAce Commuter');

    // Daftar tipe yang kosong justru untuk unit yang paling perlu dibedakan
    // adalah keluhan yang wajar.
    expect($uji->instance()->varianPilihan())->toBe(['Kursi Kulit', 'Standar']);
});

test('daftar armada menyebut penumpang dan kursi total untuk unit dengan sopir', function () {
    // Rujukan ke nama medan yang sudah berganti gagal DIAM-DIAM di blade: ?? null
    // membuatnya jatuh ke cabang lain dan menampilkan angka yang salah tanpa
    // galat. Uji ini yang menangkapnya, bukan mata.
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['jenis_kendaraan' => ['hiace' => 'HiAce']]]),
        '*' => Http::response(['data' => [[
            'id' => 9, 'uuid' => 'x', 'nama' => 'HiAce Commuter', 'merek' => 'Toyota',
            'varian' => 'Standar', 'tahun' => 2023, 'cc' => 2500,
            'jenis' => 'hiace', 'jenis_label' => 'HiAce', 'nopol' => 'AB 9 XX',
            'kapasitas' => 14, 'kursi_total' => 15, 'lepas_kunci' => false,
            'transmisi_tersedia' => ['Manual'], 'transmisi_label' => 'Manual',
            'tarif' => ['jam' => null, '12jam' => null, 'hari' => 1200000, 'sopir_per_hari' => 200000],
            'gambar' => null, 'tersedia' => true, 'jumlah_penyewaan' => 2,
            'kondisi' => null, 'kondisi_terkini' => null,
            'jadwal' => ['sedang_disewa' => false, 'kode_berjalan' => null, 'kembali_pada' => null,
                'kode_berikutnya' => null, 'mulai_berikutnya' => null],
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    Livewire::actingAs(adminArmada())->test(App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaList::class)
        ->assertSee('14 penumpang (15 kursi)')
        ->assertSee('Selalu dengan sopir')
        ->assertSee('Standar')
        ->assertSee('2023')
        ->assertSee('2.500 cc');
});

test('daftar armada tidak mengulang angka untuk unit lepas kunci', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['jenis_kendaraan' => ['mobil' => 'Mobil']]]),
        '*' => Http::response(['data' => [[
            'id' => 3, 'uuid' => 'y', 'nama' => 'Avanza', 'merek' => 'Toyota',
            'varian' => null, 'tahun' => null, 'cc' => 1500,
            'jenis' => 'mobil', 'jenis_label' => 'Mobil', 'nopol' => null,
            'kapasitas' => 7, 'kursi_total' => 7, 'lepas_kunci' => true,
            'transmisi_tersedia' => ['Matic'], 'transmisi_label' => 'Matic',
            'tarif' => ['jam' => null, '12jam' => null, 'hari' => 400000, 'sopir_per_hari' => null],
            'gambar' => null, 'tersedia' => true, 'jumlah_penyewaan' => 0,
            'kondisi' => null, 'kondisi_terkini' => null,
            'jadwal' => ['sedang_disewa' => false, 'kode_berjalan' => null, 'kembali_pada' => null,
                'kode_berikutnya' => null, 'mulai_berikutnya' => null],
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    Livewire::actingAs(adminArmada())->test(App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaList::class)
        ->assertSee('7 kursi')
        ->assertDontSee('7 penumpang (7 kursi)')
        ->assertDontSee('Selalu dengan sopir');
});
