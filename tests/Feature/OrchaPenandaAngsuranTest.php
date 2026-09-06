<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Penanda angsuran di daftar pendaftaran.
 *
 * Pesanan yang belum lunas menjelang berangkat berarti dua hal yang sangat
 * berbeda: ia menunggak, atau ia sedang menjalani jadwal yang kita sendiri
 * berikan. Sebelum penanda ini keduanya tergambar persis sama, dan yang
 * membedakannya cuma ingatan admin.
 */
function adminPendaftaran(): User
{
    $role = Role::create(['name' => 'uji-angsuran-'.uniqid(), 'description' => 'Peran uji angsuran']);

    $permission = Permission::firstOrCreate(
        ['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']
    );
    $role->permissions()->attach($permission->id);

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

/** Satu baris pendaftaran seperti yang dikirim Orcha, dengan penanda apa adanya. */
function barisPendaftaran(?array $angsuran): array
{
    return [
        'id' => 5,
        'kode' => 'OT-1508-0VCZ',
        'nama' => 'Siti Aminah',
        'whatsapp' => '081298765432',
        'jumlah_peserta' => 2,
        'peserta' => [['nama' => 'Siti', 'titik_jemput' => 'Malioboro']],
        'jemput_per_titik' => [],
        'kesehatan_terisi' => 2,
        'kesehatan_lengkap' => true,
        'paket' => ['id' => 1, 'nama' => 'Study Tour Bromo', 'titik_jemput' => []],
        'tanggal_berangkat' => now()->addDays(30)->toDateString(),
        'titik_jemput' => 'Malioboro',
        'status' => 'dp',
        'status_label' => 'DP Masuk',
        'angsuran' => $angsuran,
        'dibuat_pada' => now()->toIso8601String(),
    ];
}

function daftarDenganAngsuran(?array $angsuran): void
{
    Http::fake([
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['dp' => 'DP Masuk', 'lunas' => 'Lunas'],
            'paket_wisata' => [],
        ]]),
        '*' => Http::response([
            'data' => [barisPendaftaran($angsuran)],
            'meta' => ['halaman' => 1, 'per_halaman' => 10, 'total' => 1, 'halaman_terakhir' => 1],
        ]),
    ]);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
});

test('pesanan yang diangsur ditandai berikut kemajuannya', function () {
    daftarDenganAngsuran([
        'jumlah_termin' => 3,
        'lunas' => 1,
        'telat' => 0,
        'selesai' => false,
        'berikutnya' => now()->addDays(20)->toDateString(),
    ]);

    Livewire::actingAs(adminPendaftaran())
        ->test(OrchaPendaftaranList::class)
        // Angka kemajuannya ikut. "Diangsur" saja tidak menjawab pertanyaan
        // yang sedang dipikirkan admin, yaitu apakah orang ini bergerak.
        ->assertSee('Angsuran 1/3')
        ->assertDontSee('Angsuran telat');
});

test('termin yang lewat jatuh tempo diteriakkan, bukan disamarkan', function () {
    // Satu-satunya keadaan di daftar ini yang menuntut tindakan hari ini.
    daftarDenganAngsuran([
        'jumlah_termin' => 3,
        'lunas' => 1,
        'telat' => 1,
        'selesai' => false,
        'berikutnya' => now()->subDays(3)->toDateString(),
    ]);

    Livewire::actingAs(adminPendaftaran())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('Angsuran telat 1 termin')
        ->assertSee('orcha-cip-angsuran telat', escape: false);
});

test('pesanan tanpa rencana tidak diberi penanda', function () {
    /*
     | Penjaga arah sebaliknya. Penanda yang muncul di semua baris tidak
     | membedakan apa pun, dan admin berhenti mempercayainya dalam sehari.
     */
    daftarDenganAngsuran(null);

    Livewire::actingAs(adminPendaftaran())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('Siti Aminah')
        // Diperiksa lewat kelas cipnya, bukan lewat kata "Angsuran": kata itu
        // juga ada di komentar lembar gaya yang ikut tergambar, dan uji yang
        // gagal karena komentar mengajari orang berikutnya untuk memelonggarkannya.
        ->assertDontSee('<span class="orcha-cip-angsuran', escape: false);
});

test('baris tetap tergambar bila Orcha tidak mengirim kunci angsuran', function () {
    /*
     | Kuncinya hanya dikirim saat relasinya sengaja dimuat di Orcha. Halaman
     | yang mati karena satu kunci hilang adalah harga yang jauh lebih mahal
     | daripada penanda yang tidak muncul — dan versi Orcha di server tidak
     | selalu bergerak bersamaan dengan lemon.
     */
    Http::fake([
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['dp' => 'DP Masuk'],
            'paket_wisata' => [],
        ]]),
        '*' => Http::response([
            'data' => [collect(barisPendaftaran(null))->except('angsuran')->all()],
            'meta' => ['halaman' => 1, 'per_halaman' => 10, 'total' => 1, 'halaman_terakhir' => 1],
        ]),
    ]);

    Livewire::actingAs(adminPendaftaran())
        ->test(OrchaPendaftaranList::class)
        ->assertOk()
        ->assertSee('Siti Aminah');
});

test('daftar ini menyebut dirinya Pendaftaran Trip, bukan Open Trip saja', function () {
    /*
     | Satu daftar memuat open trip, private trip, dan study tour sekaligus —
     | dua yang terakhir dimasukkan admin lewat Daftarkan Rombongan. Nama
     | "Pendaftaran Open Trip" membuat admin mencari halaman lain untuk
     | rombongan sekolah, dan halaman itu tidak pernah ada.
     */
    daftarDenganAngsuran(null);

    Livewire::actingAs(adminPendaftaran())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('Pendaftaran Trip')
        ->assertDontSee('Pendaftaran Open Trip')
        // Keterangannya ikut jujur: tidak semuanya datang lewat website.
        ->assertSee('didaftarkan admin');
});

test('tombol halaman tidak menunjuk /livewire/update sesudah admin mencari', function () {
    /*
     | Cacat yang sempat berjalan di KELIMA BELAS daftar sekaligus, karena
     | semuanya memakai partial paginasi yang sama.
     |
     | Tautannya dirakit dengan request()->fullUrlWithQuery(). Benar pada
     | pemuatan pertama, dan hanya itu: begitu admin mengetik di kotak cari,
     | halaman digambar ulang DI DALAM permintaan Livewire, dan request() di
     | sana adalah POST ke /livewire/update. Seluruh tombol nomor lalu
     | menunjuk "/livewire/update?halaman=2".
     |
     | Rusaknya diam dan bersyarat — muat halaman, semuanya benar; ketik satu
     | huruf, semuanya rusak. Itu sebabnya ia bertahan lama.
     */
    Http::fake([
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['dp' => 'DP Masuk'], 'paket_wisata' => [],
        ]]),
        '*' => Http::response([
            'data' => [barisPendaftaran(null)],
            'meta' => ['halaman' => 1, 'per_halaman' => 25, 'total' => 120, 'halaman_terakhir' => 5],
        ]),
    ]);

    $layar = Livewire::actingAs(adminPendaftaran())
        ->test(OrchaPendaftaranList::class)
        ->set('cari', 'joko');

    // Href-nya tetap alamat halaman, bukan titik-akhir Livewire.
    expect($layar->html())->not->toContain('livewire/update?halaman')
        ->and($layar->html())->toContain('wire:click.prevent="keHalaman');

    // Perpindahannya lewat komponen, bukan lewat alamat — dan nomornya terikat
    // #[Url], jadi bilah alamat tetap ikut berubah.
    $layar->call('keHalaman', 3);
    expect($layar->get('halaman'))->toBe(3);
});
