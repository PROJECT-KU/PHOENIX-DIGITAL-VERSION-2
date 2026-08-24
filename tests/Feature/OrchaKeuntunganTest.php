<?php

use App\Livewire\Pages\Admin\Orcha\Keuntungan\OrchaKeuntunganList;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketForm;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Halaman keuntungan paket Orcha di lemon.
 *
 * Hitungannya milik Orcha; yang diuji di sini adalah bahwa lemon meneruskan
 * saringan dengan benar, menggambar angkanya apa adanya, dan tidak pernah
 * mengubah "belum dihitung" jadi nol di tengah jalan.
 *
 * Pembuat admin ditulis ulang di berkas ini — bukan meminjam milik
 * OrchaDashboardTest — supaya berkas ini tetap bisa dijalankan sendirian
 * dengan --filter.
 */
function adminKeuntungan(array $izin = ['akses_orcha']): User
{
    $role = Role::create(['name' => 'uji-untung-'.uniqid(), 'description' => 'Peran untuk uji keuntungan']);

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

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
    cache()->forget('orcha.perlu-ditindak');
});

function balasanKeuntungan(array $ubah = []): array
{
    return ['data' => array_merge([
        'saringan' => ['dari' => null, 'sampai' => null, 'dasar' => 'daftar', 'dasar_label' => 'Tanggal pendaftaran masuk'],
        'ringkasan' => [
            'pendaftaran' => 3, 'peserta' => 12,
            'omzet' => 17160000, 'omzet_teks' => 'Rp 17.160.000',
            'modal' => 16800000, 'modal_teks' => 'Rp 16.800.000',
            'keuntungan' => 360000, 'keuntungan_teks' => 'Rp 360.000',
            'margin_rata_per_orang' => 30000, 'margin_rata_per_orang_teks' => 'Rp 30.000',
            'potensi_pendaftaran' => 2, 'potensi_peserta' => 4,
            'potensi_omzet' => 5720000, 'potensi_omzet_teks' => 'Rp 5.720.000',
            'potensi_keuntungan' => 120000, 'potensi_keuntungan_teks' => 'Rp 120.000',
            'belum_lengkap' => 0, 'paket_belum_lengkap' => [],
        ],
        'per_paket' => [[
            'kunci' => 1, 'nama' => 'Open Trip Banyuwangi', 'kategori' => 'open_trip',
            'kategori_label' => 'Open Trip', 'pendaftaran' => 3, 'peserta' => 12,
            'harga_jual' => 1430000, 'harga_jual_teks' => 'Rp 1.430.000',
            'harga_modal' => 1400000, 'harga_modal_teks' => 'Rp 1.400.000',
            'margin_per_orang' => 30000, 'margin_per_orang_teks' => 'Rp 30.000',
            'omzet' => 17160000, 'omzet_teks' => 'Rp 17.160.000',
            'modal' => 16800000, 'modal_teks' => 'Rp 16.800.000',
            'keuntungan' => 360000, 'keuntungan_teks' => 'Rp 360.000',
            'belum_lengkap' => 0,
        ]],
        'per_kategori' => [[
            'kunci' => 'open_trip', 'label' => 'Open Trip', 'pendaftaran' => 3, 'peserta' => 12,
            'omzet' => 17160000, 'omzet_teks' => 'Rp 17.160.000',
            'modal' => 16800000, 'modal_teks' => 'Rp 16.800.000',
            'keuntungan' => 360000, 'keuntungan_teks' => 'Rp 360.000', 'belum_lengkap' => 0,
        ]],
        'per_bulan' => [[
            'bulan' => '2026-08', 'bulan_label' => 'Agu 2026', 'pendaftaran' => 3, 'peserta' => 12,
            'omzet' => 17160000, 'omzet_teks' => 'Rp 17.160.000',
            'modal' => 16800000, 'modal_teks' => 'Rp 16.800.000',
            'keuntungan' => 360000, 'keuntungan_teks' => 'Rp 360.000',
        ]],
        'paket' => [[
            'id' => 1, 'nama' => 'Open Trip Banyuwangi', 'kategori' => 'open_trip',
            'harga_jual' => 1430000, 'harga_modal' => 1400000,
            'margin_per_orang' => 30000, 'margin_persen' => 2.1, 'modal_terisi' => true,
        ]],
        'dasar_tanggal' => ['daftar' => 'Tanggal pendaftaran masuk', 'berangkat' => 'Tanggal keberangkatan'],
    ], $ubah)];
}

function balasanRincian(): array
{
    return [
        'data' => [[
            'id' => 9, 'kode' => 'OT-2308-A7K3', 'nama' => 'Budi Santoso',
            'status' => 'lunas', 'status_label' => 'Lunas',
            'paket' => 'Open Trip Banyuwangi', 'peserta' => 2,
            'tanggal_daftar' => '2026-08-01', 'tanggal_berangkat' => '2026-09-10',
            'omzet' => 2860000, 'omzet_teks' => 'Rp 2.860.000',
            'modal' => 2800000, 'modal_teks' => 'Rp 2.800.000',
            'keuntungan' => 60000, 'keuntungan_teks' => 'Rp 60.000',
            'modal_terisi' => true,
        ]],
        'meta' => ['halaman' => 1, 'per_halaman' => 10, 'total' => 1, 'halaman_terakhir' => 1],
    ];
}

function palsukanKeuntungan(array $laporan = []): void
{
    Http::fake([
        '*/keuntungan/rincian*' => Http::response(balasanRincian()),
        '*/keuntungan*' => Http::response(balasanKeuntungan($laporan)),
        '*/rujukan*' => Http::response(['data' => ['kategori_paket' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);
}

/* ------------------------------ HAK AKSES ------------------------------ */

test('halaman keuntungan tertutup tanpa permission akses orcha', function () {
    Http::fake();

    $this->actingAs(adminKeuntungan([]))
        ->get('/admin/orcha/keuntungan')
        ->assertForbidden();
});

test('halaman keuntungan terbuka bagi yang berhak', function () {
    palsukanKeuntungan();

    $this->actingAs(adminKeuntungan())
        ->get('/admin/orcha/keuntungan')
        ->assertOk()
        ->assertSee('Keuntungan Paket Wisata');
});

/* ------------------------------ TAMPILAN ------------------------------ */

test('angka keuntungan, omzet, dan potensi tergambar apa adanya', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaKeuntunganList::class)
        ->assertSee('Rp 360.000')
        ->assertSee('Rp 17.160.000')
        ->assertSee('Rp 120.000')
        ->assertSee('Open Trip Banyuwangi')
        ->assertSee('OT-2308-A7K3');
});

test('laporan yang belum lengkap mengakui dirinya belum lengkap', function () {
    palsukanKeuntungan([
        'ringkasan' => array_merge(balasanKeuntungan()['data']['ringkasan'], [
            'belum_lengkap' => 2,
            'paket_belum_lengkap' => ['Private Trip Dieng'],
        ]),
    ]);

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaKeuntunganList::class)
        ->assertSee('2 pendaftaran belum bisa dihitung untungnya')
        ->assertSee('Private Trip Dieng');
});

test('porsi persen selalu berjumlah tepat seratus', function () {
    palsukanKeuntungan();

    $halaman = Livewire::actingAs(adminKeuntungan())->test(OrchaKeuntunganList::class);

    // Dibulatkan sendiri-sendiri, ketiganya jadi 62 + 24 + 15 = 101 —
    // tepat di bawah kalimat yang menjanjikan seratus.
    $persen = $halaman->instance()->porsiBulat([
        ['kunci' => 'a', 'keuntungan' => 3800000],
        ['kunci' => 'b', 'keuntungan' => 1460000],
        ['kunci' => 'c', 'keuntungan' => 900000],
    ]);

    expect(array_sum($persen))->toBe(100)
        ->and($persen['a'])->toBe(62);
});

test('baris yang rugi tidak kebagian porsi', function () {
    palsukanKeuntungan();

    $persen = Livewire::actingAs(adminKeuntungan())
        ->test(OrchaKeuntunganList::class)
        ->instance()
        ->porsiBulat([
            ['kunci' => 'untung', 'keuntungan' => 1000000],
            ['kunci' => 'rugi', 'keuntungan' => -100000],
        ]);

    expect($persen['untung'])->toBe(100)
        ->and($persen['rugi'])->toBe(0);
});

test('rentang tanpa keuntungan sama sekali tidak membagi persen', function () {
    palsukanKeuntungan();

    $persen = Livewire::actingAs(adminKeuntungan())
        ->test(OrchaKeuntunganList::class)
        ->instance()
        ->porsiBulat([['kunci' => 'a', 'keuntungan' => 0], ['kunci' => 'b', 'keuntungan' => -5000]]);

    expect(array_sum($persen))->toBe(0);
});

/* ------------------------------ SARINGAN ------------------------------ */

test('rentang bulan ini terkirim sebagai tanggal ke orcha', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaKeuntunganList::class)
        ->call('pilihRentang', 'bulan-ini')
        ->assertSet('dari', now()->startOfMonth()->toDateString())
        ->assertSet('sampai', now()->endOfMonth()->toDateString());

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), 'keuntungan')
        && str_contains($permintaan->url(), 'dari='.now()->startOfMonth()->toDateString()));
});

test('dasar tanggal keberangkatan ikut terkirim', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaKeuntunganList::class)
        ->set('dasar', 'berangkat');

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), 'dasar=berangkat'));
});

test('saringan mengembalikan halaman rincian ke nomor satu', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaKeuntunganList::class)
        ->call('keHalaman', 3)
        ->assertSet('halaman', 3)
        ->set('kategori', 'open_trip')
        ->assertSet('halaman', 1);
});

/* --------------------------- MODAL DI FORMULIR --------------------------- */

test('modal kosong tetap kosong, tidak berubah jadi nol', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaPaketForm::class)
        ->set('hargaModalTeks', '')
        ->assertSet('hargaModal', null)
        ->assertSee('modalnya belum diisi');
});

test('margin per orang terhitung begitu modal diisi', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaPaketForm::class)
        ->set('hargaTeks', '1.430.000')
        ->set('hargaModalTeks', '1400000')
        ->assertSet('hargaModal', 1400000)
        ->assertSet('hargaModalTeks', '1.400.000')
        ->assertSee('Rp 30.000');
});

test('paket yang dijual di bawah modal ditegur sebelum disimpan', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaPaketForm::class)
        ->set('hargaTeks', '1.350.000')
        ->set('hargaModalTeks', '1.400.000')
        ->assertSee('Rugi Rp 50.000');
});

test('modal ikut terkirim saat paket disimpan', function () {
    palsukanKeuntungan();

    Livewire::actingAs(adminKeuntungan())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Karimunjawa')
        ->set('hargaTeks', '1.430.000')
        ->set('hargaModalTeks', '1.400.000')
        ->call('simpan');

    Http::assertSent(function ($permintaan) {
        return str_contains($permintaan->url(), '/paket-wisata')
            && ($permintaan->data()['harga_modal'] ?? null) == 1400000;
    });
});
