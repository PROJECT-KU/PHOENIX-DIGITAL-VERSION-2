<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Tab di daftar Pendaftaran Trip.
 *
 * Menggantikan kotak pilih status dan sakelar "perlu ditagih". Keduanya dulu
 * berdiri sebagai dua benda berbeda jenis — satu cara MELIHAT, satu daftar
 * PEKERJAAN — dan yang kedua tenggelam di antara saringan.
 */
/**
 * Admin uji MILIK BERKAS INI.
 *
 * Sengaja tidak meminjam adminTab() dari berkas sebelah: fungsi Pest
 * berlaku global hanya saat kedua berkas sama-sama dijalankan, jadi berkas
 * yang meminjamnya hijau dalam rangkaian penuh dan merah begitu dijalankan
 * sendirian — merah yang menunjuk fungsi, bukan fitur.
 *
 * Dua izin: tombol Unduh Daftar Peserta hanya ada bagi pemegang
 * view_orcha_kesehatan.
 */
function adminTab(): \App\Models\User
{
    $role = \App\Models\Role::create(['name' => 'uji-tab-'.uniqid(), 'description' => 'Peran uji tab']);

    foreach (['akses_orcha', 'view_orcha_kesehatan'] as $nama) {
        $izin = \App\Models\Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );
        $role->permissions()->attach($izin->id);
    }

    $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

/** Satu baris pendaftaran seperti yang dikirim Orcha. */
function barisTab(): array
{
    return [
        'id' => 5, 'kode' => 'OT-2611-A1', 'nama' => 'Siti Aminah',
        'whatsapp' => '081298765432', 'jumlah_peserta' => 2,
        'peserta' => [['nama' => 'Siti', 'titik_jemput' => 'Malioboro']],
        'jemput_per_titik' => [], 'kesehatan_terisi' => 2, 'kesehatan_lengkap' => true,
        'peserta_belum_isi' => [], 'jumlah_riwayat_kesehatan' => 2,
        'paket' => ['id' => 1, 'nama' => 'Open Trip Dieng'],
        'tanggal_berangkat' => now()->addDays(12)->toDateString(),
        'titik_jemput' => 'Malioboro', 'status' => 'dp_masuk', 'status_label' => 'DP Masuk',
        'angsuran' => null, 'pengingat_pelunasan_pada' => null,
        'dibuat_pada' => now()->toIso8601String(),
    ];
}

function palsukanTab(array $jumlahTab = [], bool $berisi = false): void
{
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake([
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['baru' => 'Baru', 'dp_masuk' => 'DP Masuk', 'lunas' => 'Lunas'],
            'paket_wisata' => [],
        ]]),
        '*' => Http::response([
            'data' => $berisi ? [barisTab()] : [],
            'meta' => array_merge(
                ['halaman' => 1, 'per_halaman' => 10, 'total' => $berisi ? 1 : 0, 'halaman_terakhir' => 1],
                $jumlahTab === [] ? [] : ['jumlah_tab' => $jumlahTab],
            ),
        ]),
    ]);
}

test('menekan tab status menyaring daftarnya', function () {
    palsukanTab();

    Livewire::actingAs(adminTab())
        ->test(OrchaPendaftaranList::class)
        ->call('setTab', 'lunas')
        ->assertSet('tabAktif', 'lunas')
        ->assertSet('filterStatus', 'lunas')
        // Tab status BUKAN daftar tagihan; keduanya tidak boleh menyala bersama.
        ->assertSet('perluDitagih', false);
});

test('tab perlu ditelepon menyalakan saringan tagihan, bukan status', function () {
    palsukanTab();

    Livewire::actingAs(adminTab())
        ->test(OrchaPendaftaranList::class)
        ->call('setTab', 'perlu_ditagih')
        ->assertSet('perluDitagih', true)
        /*
         | Statusnya dikosongkan. Dibiarkan terisi dari tab sebelumnya,
         | daftar tagihan ikut tersaring status lama — dan yang hilang dari
         | sana justru orang yang harus ditelepon hari ini.
         */
        ->assertSet('filterStatus', '');
});

test('tab semua mengosongkan keduanya', function () {
    palsukanTab();

    Livewire::actingAs(adminTab())
        ->test(OrchaPendaftaranList::class)
        ->call('setTab', 'perlu_ditagih')
        ->call('setTab', 'semua')
        ->assertSet('filterStatus', '')
        ->assertSet('perluDitagih', false);
});

test('pindah tab mengembalikan daftar ke halaman satu', function () {
    palsukanTab();

    // Pindah tab di halaman tiga menghasilkan layar kosong, dan yang
    // melihatnya menyimpulkan tabnya memang tidak berisi.
    Livewire::actingAs(adminTab())
        ->test(OrchaPendaftaranList::class)
        ->call('keHalaman', 3)
        ->call('setTab', 'lunas')
        ->assertSet('halaman', 1);
});

test('tab yang tidak dikenal jatuh ke semua', function () {
    palsukanTab();

    // Nilainya datang dari markah; yang dikarang tidak boleh menyaring
    // apa pun secara diam-diam.
    Livewire::actingAs(adminTab())
        ->test(OrchaPendaftaranList::class)
        ->call('setTab', 'karangan')
        ->assertSet('tabAktif', 'semua')
        ->assertSet('filterStatus', '');
});

test('angka tab tampil saat Orcha mengirimkannya', function () {
    palsukanTab(['semua' => 48, 'baru' => 6, 'dp_masuk' => 11, 'lunas' => 26,
        'dihubungi' => 3, 'batal' => 2, 'perlu_ditagih' => 4]);

    /*
     | Angka itulah yang membuat tabnya berguna. Tanpanya "Perlu ditelepon"
     | adalah tombol yang harus ditekan dulu untuk tahu apakah ada pekerjaan
     | di baliknya.
     */
    Livewire::actingAs(adminTab())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('48')
        ->assertSee('Perlu ditelepon');
});

test('tab tetap tergambar saat Orcha belum mengirim angkanya', function () {
    palsukanTab();

    /*
     | Orcha dan lemon tidak naik ke server pada detik yang sama. Tanpa
     | angka, tabnya masih berguna sebagai saringan — sedangkan halaman yang
     | mati karenanya tidak. Dan angkanya hilang, BUKAN jadi "0": nol yang
     | keliru terbaca sebagai "tidak ada pekerjaan di sana".
     */
    Livewire::actingAs(adminTab())
        ->test(OrchaPendaftaranList::class)
        ->assertOk()
        ->assertSee('Perlu ditelepon')
        ->assertSee('Sudah lunas');
});

test('kalimatnya memakai bahasa sehari-hari, bukan istilah sistem', function () {
    // Berisi: judul kolom hanya tergambar saat tabelnya ada.
    palsukanTab(berisi: true);

    /*
     | Layar ini dikerjakan admin yang tidak selalu terbiasa dengan istilah
     | aplikasi. "Status" dan "manifes" adalah kata kami, bukan kata mereka.
     */
    $isi = Livewire::actingAs(adminTab())->test(OrchaPendaftaranList::class)->html();

    expect($isi)->toContain('Yang mendaftar')
        ->toContain('Keberangkatan')
        ->toContain('Unduh Daftar Peserta')
        ->toContain('Keadaan pendaftaran')
        // Istilah lama tidak boleh kembali menyelinap.
        ->not->toContain('Semua status')
        ->not->toContain('Manifes</span>');
});
