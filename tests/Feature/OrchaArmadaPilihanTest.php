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
        ->set('merekPilihan', 'Suzuki');

    expect($uji->instance()->modelPilihan())->toBe(['Ertiga', 'XL7']);

    $uji->set('merekPilihan', 'Toyota');

    expect($uji->instance()->modelPilihan())->toBe(['Avanza', 'HiAce Commuter', 'Innova Zenix']);
});

test('mengganti merek mengosongkan nama unit yang sudah dipilih', function () {
    fakeArmada();

    // Tanpa pengosongan ini, memilih Toyota lalu Avanza lalu berpindah ke
    // Suzuki akan menyimpan "Suzuki Avanza" — unit yang tidak pernah ada.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merekPilihan', 'Toyota')
        ->set('namaPilihan', 'Avanza')
        ->set('merekPilihan', 'Suzuki')
        ->assertSet('namaPilihan', '')
        ->assertSet('nama', '');
});

test('unit tersimpan memakai nilai dari dropdown', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merekPilihan', 'Suzuki')
        ->set('namaPilihan', 'Ertiga')
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
        ->set('merekPilihan', '__manual__')
        ->set('merekManual', 'Chery')
        ->set('namaManual', 'Tiggo 8 Pro')
        ->set('nopol', 'AB 7 ZZ')
        ->set('tarifHariTeks', '600.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && ($p->data()['merek'] ?? null) === 'Chery'
        && ($p->data()['nama'] ?? null) === 'Tiggo 8 Pro');
});

test('memilih isi manual pada merek langsung menyiapkan nama unit manual', function () {
    fakeArmada();

    // Merek di luar katalog tidak punya daftar model, jadi memaksa admin
    // memilih dari daftar kosong hanya membuang satu langkah.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merekPilihan', '__manual__')
        ->assertSet('namaPilihan', '__manual__');
});

test('manual yang dibiarkan kosong ditolak, bukan tersimpan tanpa nama', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class)
        ->set('merekPilihan', '__manual__')
        ->set('merekManual', '   ')
        ->set('tarifHariTeks', '300.000')
        ->call('simpan')
        ->assertHasErrors(['merek', 'nama']);
});

test('menyunting unit menyiapkan dropdown pada nilai tersimpannya', function () {
    fakeArmada();

    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('merekPilihan', 'Toyota')
        ->assertSet('namaPilihan', 'Avanza')
        ->assertSet('merekManual', '')
        ->assertSet('namaManual', '');
});

test('unit lama di luar katalog tidak kehilangan mereknya saat disunting', function () {
    fakeArmada(['merek' => 'Chery', 'nama' => 'Tiggo 8 Pro']);

    // Ini pengaman terpentingnya: dropdown yang tidak memuat merek unit itu
    // sendiri akan memaksa admin mengubahnya jadi merek lain. Nilainya harus
    // utuh di kotak manual, bukan hilang.
    Livewire::actingAs(adminArmada())->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->assertSet('merekPilihan', '__manual__')
        ->assertSet('merekManual', 'Chery')
        ->assertSet('namaPilihan', '__manual__')
        ->assertSet('namaManual', 'Tiggo 8 Pro')
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
        ->set('merekPilihan', '__manual__')
        ->set('merekManual', 'Toyota')
        ->set('namaManual', 'Avanza')
        ->set('tarifHariTeks', '350.000')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && ($p->data()['merek'] ?? null) === 'Toyota'
        && ($p->data()['nama'] ?? null) === 'Avanza');
});
