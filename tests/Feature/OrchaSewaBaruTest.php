<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaDaftarkanRombongan;
use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaSewaBaru;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Menambah data Orcha dari lemon: mencatat sewa, dan memilihkan rombongan
 * dari tanggal yang diklik di kalender.
 *
 * Yang dijaga di sini dua hal yang paling mudah salah dan paling mahal
 * akibatnya: borang tidak boleh menawarkan pilihan yang pasti ditolak Orcha,
 * dan rombongan tidak boleh ditebak saat tanggalnya punya lebih dari satu
 * keberangkatan.
 */

/* Nama penolong diawali 'sewaBaru': penolong Pest hidup di ruang nama global. */
function sewaBaruOrang(): User
{
    $peran = Role::firstOrCreate(['name' => 'uji-sewa-baru'], ['description' => 'Peran uji']);

    foreach (['akses_orcha'] as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );

        if (! $peran->permissions()->where('permissions.id', $p->id)->exists()) {
            $peran->permissions()->attach($p->id);
        }
    }

    $user = User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function sewaBaruSetel(): void
{
    // Alamat LENGKAP sampai /api/v1 — sama dengan ORCHA_API_URL sungguhan.
    // Tanpa itu pola Http::fake di bawah tidak akan pernah cocok.
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    Cache::flush();
}

function sewaBaruUnit(array $ubah = []): array
{
    return array_merge([
        'id' => 7,
        'nama' => 'Avanza Uji',
        'sebutan' => 'Toyota Avanza 2022',
        'jenis_label' => 'Mobil',
        'kapasitas' => 7,
        'lepas_kunci' => true,
        'lepas_kunci_label' => 'Boleh lepas kunci',
        'sopir_label' => 'Sopir opsional',
        'transmisi_tersedia' => ['Manual', 'Matic'],
        'tarif' => ['jam' => 55000, '12jam' => 280000, 'hari' => 350000],
    ], $ubah);
}

/* ------------------------------------------------- Borang catat sewa */

test('borang hanya menawarkan satuan yang benar-benar bertarif untuk unitnya', function () {
    sewaBaruSetel();

    // Bus tanpa tarif per jam. Menawarkan "jam" berarti admin memilih sesuatu
    // yang pasti ditolak Orcha setelah Simpan ditekan.
    Http::fake(['*/api/v1/kendaraan*' => Http::response(['data' => [
        sewaBaruUnit(['id' => 9, 'nama' => 'Bus Uji', 'transmisi_tersedia' => ['Manual'],
            'tarif' => ['jam' => null, '12jam' => null, 'hari' => 2500000]]),
    ]], 200)]);

    Livewire::actingAs(sewaBaruOrang())->test(OrchaSewaBaru::class)
        ->set('unitId', '9')
        ->assertSet('satuan', 'hari')
        ->assertSet('transmisi', 'Manual');
});

test('pilihan yang tertinggal dari unit sebelumnya dibuang', function () {
    sewaBaruSetel();

    Http::fake(['*/api/v1/kendaraan*' => Http::response(['data' => [
        sewaBaruUnit(),
        sewaBaruUnit(['id' => 9, 'nama' => 'Bus Uji', 'transmisi_tersedia' => ['Manual'],
            'tarif' => ['jam' => null, '12jam' => null, 'hari' => 2500000]]),
    ]], 200)]);

    Livewire::actingAs(sewaBaruOrang())->test(OrchaSewaBaru::class)
        ->set('unitId', '7')
        ->set('transmisi', 'Matic')
        ->set('satuan', 'jam')
        // Pindah ke bus: Matic dan jam keduanya tidak berlaku lagi.
        ->set('unitId', '9')
        ->assertSet('transmisi', 'Manual')
        ->assertSet('satuan', 'hari');
});

test('unit yang wajib bersopir memaksa pilihan sopir di layar juga', function () {
    sewaBaruSetel();

    Http::fake(['*/api/v1/kendaraan*' => Http::response(['data' => [
        sewaBaruUnit(['id' => 9, 'lepas_kunci' => false, 'transmisi_tersedia' => ['Manual']]),
    ]], 200)]);

    Livewire::actingAs(sewaBaruOrang())->test(OrchaSewaBaru::class)
        ->set('denganSopir', 'tidak')
        ->set('unitId', '9')
        ->assertSet('denganSopir', 'ya');
});

test('penyewaan dikirim ke Orcha dan kodenya dikabarkan kembali', function () {
    sewaBaruSetel();

    Http::fake([
        '*/api/v1/kendaraan*' => Http::response(['data' => [sewaBaruUnit()]], 200),
        '*/api/v1/penyewaan' => Http::response(['data' => [
            'id' => 31, 'kode' => 'SK-2612-ABC123', 'nama' => 'Pak Budi', 'nama_kendaraan' => 'Avanza Uji',
        ]], 201),
    ]);

    Livewire::actingAs(sewaBaruOrang())->test(OrchaSewaBaru::class)
        ->set('unitId', '7')
        ->set('nama', 'Pak Budi')
        ->set('whatsapp', '0812-3456-7890')
        ->set('lokasiAntar', 'Bandara YIA')
        ->set('tanggalMulai', '2026-12-10')
        ->call('simpan')
        ->assertSet('galat', '')
        ->assertSee('SK-2612-ABC123');
});

test('penolakan dari Orcha ditampilkan apa adanya, bukan diganti kalimat sendiri', function () {
    sewaBaruSetel();

    Http::fake([
        '*/api/v1/kendaraan*' => Http::response(['data' => [sewaBaruUnit()]], 200),
        // Orcha sudah menjelaskan sendiri apa yang salah. Menggantinya dengan
        // "gagal menyimpan" membuang satu-satunya kalimat yang berguna.
        '*/api/v1/penyewaan' => Http::response(['message' => 'Unit ini tidak disewakan dengan satuan jam.'], 422),
    ]);

    Livewire::actingAs(sewaBaruOrang())->test(OrchaSewaBaru::class)
        ->set('unitId', '7')
        ->set('nama', 'Pak Budi')
        ->set('whatsapp', '0812-3456-7890')
        ->set('lokasiAntar', 'Bandara YIA')
        ->call('simpan')
        ->assertSee('tidak disewakan dengan satuan jam');
});

test('tanggal dari kalender jadi tanggal mulai sewa', function () {
    sewaBaruSetel();
    Http::fake(['*/api/v1/kendaraan*' => Http::response(['data' => [sewaBaruUnit()]], 200)]);

    // Sewa tidak terikat jadwal keberangkatan mana pun, jadi tanggalnya
    // dipakai apa adanya.
    /*
     | withUrlParams, BUKAN parameter kedua test().
     |
     | Parameter kedua test() diteruskan ke mount() menurut NAMA ARGUMENNYA —
     | dan mount() di sini tidak punya argumen apa pun, jadi nilainya terbuang
     | diam-diam. Yang mengisi properti #[Url] adalah withUrlParams.
     */
    Livewire::actingAs(sewaBaruOrang())->withUrlParams(['tanggal' => '2026-12-10'])
        ->test(OrchaSewaBaru::class)
        ->assertSet('tanggalMulai', '2026-12-10');
});

/* -------------------------------- Pra-pilih rombongan dari tanggal */

/**
 * Satu keberangkatan dengan SELURUH medan yang dikirim Orcha.
 *
 * Bukan sekadar yang dibaca pilihkanDariTanggal(). Borangnya juga digambar
 * saat diuji, dan pemilih tanggalnya membaca rentang_tanggal, penuh, serta
 * sisa_kursi — data uji yang cuma berisi tiga medan membuat ujinya merah
 * dengan "Undefined array key", galat yang sama sekali tidak menunjuk ke
 * hal yang sedang diuji.
 *
 * @return array<string, mixed>
 */
function sewaBaruKeberangkatan(int $id, string $tanggal, bool $aktif = true): array
{
    return [
        'id' => $id,
        'tanggal_berangkat' => $tanggal,
        'rentang_tanggal' => $tanggal,
        'aktif' => $aktif,
        'penuh' => false,
        'sisa_kursi' => 10,
    ];
}

function sewaBaruRujukan(array $paket): void
{
    Http::fake(['*/api/v1/rujukan*' => Http::response(['data' => ['paket_wisata' => $paket]], 200)]);
}

test('rombongan dipilihkan bila tanggal itu punya tepat satu keberangkatan aktif', function () {
    sewaBaruSetel();
    sewaBaruRujukan([
        ['id' => 3, 'nama' => 'Bromo', 'keberangkatan' => [
            sewaBaruKeberangkatan(11, '2026-12-10'),
            sewaBaruKeberangkatan(12, '2026-12-20'),
        ]],
    ]);

    Livewire::actingAs(sewaBaruOrang())
        ->withUrlParams(['tanggal' => '2026-12-10'])
        ->test(OrchaDaftarkanRombongan::class)
        ->assertSet('paketId', '3')
        ->assertSet('keberangkatanId', '11');
});

test('rombongan TIDAK ditebak bila tanggalnya punya dua keberangkatan', function () {
    sewaBaruSetel();
    sewaBaruRujukan([
        ['id' => 3, 'nama' => 'Bromo', 'keberangkatan' => [sewaBaruKeberangkatan(11, '2026-12-10')]],
        ['id' => 4, 'nama' => 'Dieng', 'keberangkatan' => [sewaBaruKeberangkatan(21, '2026-12-10')]],
    ]);

    // Satu bus ke Bromo dan satu ke Dieng berangkat Sabtu yang sama. Menebak
    // salah satunya berarti rombongan tercatat di perjalanan yang salah, dan
    // tanggal itu mengalir ke kuota, batas pelunasan, serta suratnya.
    Livewire::actingAs(sewaBaruOrang())
        ->withUrlParams(['tanggal' => '2026-12-10'])
        ->test(OrchaDaftarkanRombongan::class)
        ->assertSet('paketId', '')
        ->assertSet('keberangkatanId', '');
});

test('keberangkatan yang sudah ditutup tidak ikut dipilihkan', function () {
    sewaBaruSetel();
    sewaBaruRujukan([
        ['id' => 3, 'nama' => 'Bromo', 'keberangkatan' => [sewaBaruKeberangkatan(11, '2026-12-10', aktif: false)]],
    ]);

    // Membuka kembali tanggal yang sudah ditutup harus keputusan yang
    // disengaja, bukan efek samping dari mengklik kotak di kalender.
    Livewire::actingAs(sewaBaruOrang())
        ->withUrlParams(['tanggal' => '2026-12-10'])
        ->test(OrchaDaftarkanRombongan::class)
        ->assertSet('keberangkatanId', '');
});
