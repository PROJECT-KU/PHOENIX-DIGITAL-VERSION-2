<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Saringan paket di daftar pendaftaran, dan absen naik kendaraan di manifes.
 *
 * Keduanya melayani satu pekerjaan yang sama: menyiapkan satu keberangkatan.
 * Saringan memastikan manifesnya berisi rombongan yang benar; kotak absen
 * memastikan tidak ada yang tertinggal saat kendaraan berangkat.
 */
function adminManifes(array $izin = ['akses_orcha', 'view_orcha_kesehatan']): User
{
    $role = Role::create(['name' => 'uji-manifes-'.uniqid(), 'description' => 'Peran untuk uji manifes']);

    foreach ($izin as $nama) {
        $permission = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );
        $role->permissions()->attach($permission->id);
    }

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function palsukanDaftarPendaftaran(): void
{
    Http::fake([
        '*/pendaftaran*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 0, 'halaman_terakhir' => 1,
        ]]),
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['baru' => 'Baru', 'lunas' => 'Lunas'],
            'paket_wisata' => [
                ['id' => 1, 'nama' => 'Open Trip Banyuwangi', 'kategori' => 'open_trip',
                    'tanggal_berangkat' => '2026-09-10'],
                ['id' => 2, 'nama' => 'Study Tour Bali', 'kategori' => 'study_tour',
                    'tanggal_berangkat' => null],
            ],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
});

/* --------------------------- SARINGAN PAKET --------------------------- */

test('pemilih paket tampil beserta tanggal keberangkatannya', function () {
    palsukanDaftarPendaftaran();

    $this->actingAs(adminManifes())
        ->get('/admin/orcha/pendaftaran')
        ->assertOk()
        ->assertSee('Semua paket')
        ->assertSee('Open Trip Banyuwangi')
        // Tanggalnya ikut: dua rombongan paket yang sama di bulan berbeda
        // hanya bisa dibedakan dari tanggalnya.
        ->assertSee('10 Sep 2026');
});

test('paket yang dipilih diteruskan ke orcha sebagai saringan', function () {
    palsukanDaftarPendaftaran();

    Livewire::actingAs(adminManifes())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '2');

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/pendaftaran')
        && str_contains($permintaan->url(), 'paket_id=2'));
});

test('mengganti paket mengembalikan daftar ke halaman satu', function () {
    palsukanDaftarPendaftaran();

    Livewire::actingAs(adminManifes())
        ->test(OrchaPendaftaranList::class)
        ->call('keHalaman', 4)
        ->assertSet('halaman', 4)
        ->set('filterPaket', '1')
        ->assertSet('halaman', 1);
});

test('tautan manifes membawa saringan yang sedang tampil', function () {
    palsukanDaftarPendaftaran();

    $halaman = Livewire::actingAs(adminManifes())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '1')
        ->set('filterStatus', 'lunas');

    // Manifes harus mengikuti apa yang dilihat admin di layar — kalau tidak,
    // yang tercetak bisa berisi rombongan yang sedang tidak ditangani.
    expect($halaman->instance()->saringanTampil())
        ->toBe(['status' => 'lunas', 'paket_id' => '1']);

    $halaman->assertSee('paket_id=1', false);
});

test('saringan paket ikut terkirim saat manifes diunduh', function () {
    Http::fake([
        '*/pendaftaran*' => Http::response(['data' => [[
            'id' => 9, 'kode' => 'OT-2308-A7K3', 'nama' => 'Budi Santoso', 'whatsapp' => '0812',
            'jumlah_peserta' => 1, 'peserta' => [['nama' => 'Budi Santoso', 'titik_jemput' => 'Jogja']],
            'jemput_per_titik' => ['Jogja' => ['Budi Santoso']],
            'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
            'tanggal_berangkat' => '2026-09-10', 'status' => 'lunas', 'status_label' => 'Lunas',
            'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => [],
            'jumlah_riwayat_kesehatan' => 0, 'dibuat_pada' => '2026-08-01T09:00:00+07:00',
        ]], 'meta' => ['halaman' => 1, 'per_halaman' => 100, 'total' => 1, 'halaman_terakhir' => 1]]),
        '*' => Http::response(['data' => []]),
    ]);

    $this->actingAs(adminManifes())
        ->get('/admin/orcha/pendaftaran-manifes?paket_id=1&status=lunas')
        ->assertOk();

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/pendaftaran')
        && str_contains($permintaan->url(), 'paket_id=1'));
});

/* ----------------------- ABSEN NAIK DI MANIFES ----------------------- */

test('manifes menyediakan kolom absen naik kendaraan', function () {
    $daftar = [[
        'id' => 9, 'kode' => 'OT-2308-A7K3', 'nama' => 'Budi Santoso', 'whatsapp' => '081234567890',
        'jumlah_peserta' => 2,
        'peserta' => [
            ['nama' => 'Budi Santoso', 'titik_jemput' => 'Terminal Giwangan'],
            ['nama' => 'Siti Rahmawati', 'titik_jemput' => 'Terminal Giwangan'],
        ],
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
        'tanggal_berangkat' => '2026-09-10',
    ]];

    $html = view('exports.orcha-manifest-daftar-pdf', [
        'daftar' => $daftar, 'kesehatan' => [], 'saringan' => [],
    ])->render();

    expect($html)
        // Kotak kosong untuk dicentang pena, satu per peserta.
        ->toContain('kotak-absen')
        ->and(substr_count($html, 'kotak-absen'))->toBeGreaterThanOrEqual(2)
        // Tajuk kolomnya disebut, supaya tim lapangan tahu kotak itu untuk apa.
        ->and($html)->toContain('Naik')
        ->and($html)->toContain('Centang kotak di kolom kiri begitu peserta naik kendaraan')
        // Hitungan yang diisi tangan sebelum roda berputar.
        ->and($html)->toContain('Sudah naik:')
        ->and($html)->toContain('dari 2 orang')
        ->and($html)->toContain('Diperiksa oleh:');
});

test('rombongan tanpa rincian peserta tidak muncul di manifes sama sekali', function () {
    // Bentuk data yang dulu membuat OT-1508-MUET hilang tanpa jejak: 46 orang,
    // tapi nama pesertanya belum pernah didata karena pendaftarannya mendahului
    // kolom daftar peserta.
    $daftar = [
        [
            'id' => 9, 'kode' => 'OT-1508-MUET', 'nama' => 'Siti Aminah',
            'whatsapp' => '081234567890', 'jumlah_peserta' => 46,
            'peserta' => [],
            'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
            'tanggal_berangkat' => '2026-10-19',
            'titik_jemput' => 'Jogja, Klaten, Surakarta',
        ],
        [
            'id' => 10, 'kode' => 'OT-2308-A7K3', 'nama' => 'Budi Santoso',
            'whatsapp' => '081298765432', 'jumlah_peserta' => 2,
            'peserta' => [
                ['nama' => 'Budi Santoso', 'titik_jemput' => 'Jogja'],
                ['nama' => 'Rina Wijaya', 'titik_jemput' => 'Jogja'],
            ],
            'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
            'tanggal_berangkat' => '2026-10-19', 'titik_jemput' => 'Jogja',
        ],
    ];

    $html = view('exports.orcha-manifest-daftar-pdf', [
        'daftar' => $daftar, 'kesehatan' => [], 'saringan' => [],
    ])->render();

    expect($html)
        // Tidak dipanggil, dan tidak pula disebut: nama yang tidak ada di daftar
        // mana pun hanya membuat tim lapangan mencarinya.
        ->not->toContain('OT-1508-MUET')
        ->not->toContain('Siti Aminah')
        ->not->toContain('Tidak dicetak')
        // Hitungan kepala hanya yang benar-benar dipanggil.
        ->toContain('2 peserta')
        // Yang memang berangkat tetap lengkap.
        ->toContain('Budi Santoso')
        ->toContain('Rina Wijaya');
});

test('manifes satu pendaftaran mengakui nama peserta yang belum didata', function () {
    $html = view('exports.orcha-manifest-pdf', [
        'pendaftaran' => [
            'kode' => 'OT-1508-MUET', 'nama' => 'Siti Aminah', 'whatsapp' => '081234567890',
            'email' => null, 'jumlah_peserta' => 46, 'peserta' => [],
            'jemput_per_titik' => [], 'titik_jemput' => 'Jogja, Klaten, Surakarta',
            'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
            'tanggal_berangkat' => '2026-10-19', 'status_label' => 'Baru',
            'catatan' => null, 'peserta_belum_isi' => [],
            'dibuat_pada' => '2026-08-15T09:00:00+07:00',
        ],
        'riwayat' => [],
    ])->render();

    expect($html)
        ->toContain('Nama peserta belum didata satu per satu')
        ->toContain('46')
        ->toContain('Jogja, Klaten, Surakarta')
        // Tetap ada tempat menghitung yang naik.
        ->toContain('Sudah naik:');
});
