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
