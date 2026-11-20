<?php

use App\Livewire\Pages\Admin\Permission\PermissionForm;
use App\Livewire\Pages\Admin\RoleUser\AksesAkun;
use App\Livewire\Pages\Admin\RoleUser\RolePermissionEdit;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Surat\Surat;
use App\Support\FiturAdmin;
use App\Support\ModulIzin;
use App\Support\Surat\ModeSurat;
use Livewire\Livewire;

/**
 * Arsip Surat harus bisa diatur per orang, sama seperti Orcha & Asthana.
 *
 * Modul baru gampang setengah terpasang: izinnya ada di basis data, tetapi
 * layar yang membagikannya tidak mengenalnya — sehingga admin melihat blok
 * tanpa nama, atau nama grup mentahnya dengan garis bawah, dan menyimpulkan
 * izinnya belum dibuat. Berkas ini menjaga seluruh jalur pembagiannya.
 */
function peranIzin(array $izin, string $awalan = 'uji'): Role
{
    $role = Role::create(['name' => $awalan.'-'.uniqid(), 'description' => 'Peran uji izin']);

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'arsip_surat', 'description' => 'uji']
        );
        $role->permissions()->attach($p->id);
    }

    return $role;
}

function orangIzin(?Role $role = null): \App\Models\User
{
    $role ??= peranIzin([]);
    $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id,
        'jabatan' => 'Staf Administrasi Uji',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Banyuwangi',
    ]);

    return $user;
}

function suratRahasia(array $ubah = []): Surat
{
    return Surat::create(array_merge([
        'arah' => 'masuk',
        'perihal' => 'Hasil audit internal semester II',
        'tanggal_surat' => '2026-11-01',
        'dari' => 'Kantor Akuntan Publik',
        'sifat' => 'rahasia',
        'status' => 'baru',
    ], $ubah));
}

function suratBiasa(array $ubah = []): Surat
{
    return Surat::create(array_merge([
        'arah' => 'masuk',
        'perihal' => 'Undangan rapat koordinasi',
        'tanggal_surat' => '2026-11-02',
        'dari' => 'Kadin',
        'sifat' => 'biasa',
        'status' => 'baru',
    ], $ubah));
}

/* ==================== TERDAFTAR DI SEMUA LAYAR PEMBAGI ==================== */

it('mendaftarkan arsip surat di ketiga layar yang membagikan izin', function () {
    expect(ModulIzin::META)->toHaveKey('arsip_surat')
        ->and(ModulIzin::label('arsip_surat'))->toBe('Arsip Surat');

    // Borang pembuatan izin mengisi pilihan modulnya dari daftar yang sama.
    Livewire::test(PermissionForm::class)
        ->assertSet('sidebarModules.arsip_surat', 'Arsip Surat');

    expect(FiturAdmin::DAFTAR)->toHaveKey('arsip_surat')
        ->and(FiturAdmin::DAFTAR['arsip_surat']['rute'])->toContain('admin.surat.');
});

it('memberi nama pada SETIAP grup izin yang dipakai seeder', function () {
    /*
     | Dibaca dari kode seedernya, bukan dari basis data.
     |
     | Grup yang belum punya nama tampil sebagai kunci mentahnya di layar
     | pembagian izin — "Arsip_surat" — dan itu tidak pernah memunculkan
     | galat. Penjaga ini yang membuat modul BERIKUTNYA tidak terlupa.
     */
    $grup = [];

    foreach (glob(database_path('seeders/*.php')) as $berkas) {
        preg_match_all("/'group'\s*=>\s*'([a-z_]+)'/", file_get_contents($berkas), $cocok);
        $grup = array_merge($grup, $cocok[1]);
    }

    $tanpaNama = array_values(array_diff(array_unique($grup), array_keys(ModulIzin::META)));

    expect($tanpaNama)->toBe([]);
});

it('menyebut arsip surat di layar Role lengkap dengan izinnya', function () {
    $this->actingAs(orangIzin(peranIzin(['view_roles', 'edit_roles'])));
    $this->seed(\Database\Seeders\AksesArsipSuratSeeder::class);

    $peran = peranIzin([]);

    Livewire::test(RolePermissionEdit::class, ['role' => $peran])
        ->assertSee('Arsip Surat')
        ->assertSee('Lihat Surat Rahasia')
        ->assertSee('Kelola Klasifikasi Surat');
});

it('menyebut arsip surat di layar Akses Akun per orang, bukan kunci mentahnya', function () {
    $this->actingAs(orangIzin(peranIzin(['view_roles'])));
    $this->seed(\Database\Seeders\AksesArsipSuratSeeder::class);

    $karyawan = orangIzin();

    Livewire::test(AksesAkun::class, ['user' => $karyawan->id])
        ->assertSee('Arsip Surat')
        ->assertDontSee('Arsip_surat');
});

/* ==================== IZIN PER ORANG, BUKAN HANYA PER PERAN ==================== */

it('membuka arsip surat untuk karyawan yang izinnya diberikan per orang', function () {
    $this->seed(\Database\Seeders\AksesArsipSuratSeeder::class);

    // Perannya kosong; izinnya menempel pada ORANGNYA.
    $karyawan = orangIzin(peranIzin([]));
    $karyawan->izinTambahan()->sync(
        Permission::whereIn('name', ['akses_arsip_surat', 'view_surat'])->pluck('id')->all()
    );

    $this->actingAs($karyawan->fresh())
        ->get('/admin/arsip-surat')
        ->assertOk();
});

it('menutup arsip surat untuk karyawan yang tidak diberi izin apa pun', function () {
    $this->seed(\Database\Seeders\AksesArsipSuratSeeder::class);

    $this->actingAs(orangIzin(peranIzin([])))
        ->get('/admin/arsip-surat')
        ->assertForbidden();
});

/* ==================== JEDA LAYANAN ==================== */

it('ikut bisa ditutup sementara lewat Jeda Layanan', function () {
    $this->seed(\Database\Seeders\AksesArsipSuratSeeder::class);

    $karyawan = orangIzin(peranIzin(['akses_arsip_surat', 'view_surat']));

    $this->actingAs($karyawan)->get('/admin/arsip-surat/daftar')->assertOk();

    FiturAdmin::setel('arsip_surat', true, 'Sedang dirapikan.');

    $this->actingAs($karyawan)->get('/admin/arsip-surat/daftar')
        ->assertStatus(503)
        ->assertSee('Arsip Surat');

    // Yang mengerjakan perbaikannya tetap bisa masuk.
    $tukang = orangIzin(peranIzin(['akses_arsip_surat', 'view_surat', 'manage_jeda_layanan']));

    $this->actingAs($tukang)->get('/admin/arsip-surat/daftar')->assertOk();
});

/* ==================== SURAT RAHASIA ==================== */

it('menyembunyikan surat rahasia dari yang tidak berizin', function () {
    suratRahasia();
    suratBiasa();

    $this->actingAs(orangIzin(peranIzin(['akses_arsip_surat', 'view_surat'])))
        ->get('/admin/arsip-surat/daftar')
        ->assertOk()
        ->assertSee('Undangan rapat koordinasi')
        ->assertDontSee('Hasil audit internal semester II');
});

it('menampilkan surat rahasia kepada pemegang izinnya', function () {
    suratRahasia();

    $this->actingAs(orangIzin(peranIzin(['akses_arsip_surat', 'view_surat', 'view_surat_rahasia'])))
        ->get('/admin/arsip-surat/daftar')
        ->assertOk()
        ->assertSee('Hasil audit internal semester II');
});

it('menyaring angka dasbor, bukan hanya daftarnya', function () {
    suratRahasia();
    suratBiasa();

    /*
     | Kalau hanya daftar yang disaring, dasbor menulis "Seluruh surat 2"
     | sementara daftarnya memuat satu baris — dan selisih itu memberitahu
     | persis apa yang sedang disembunyikan.
     */
    $biasa = orangIzin(peranIzin(['akses_arsip_surat', 'view_surat']));

    $angka = Livewire::actingAs($biasa)
        ->test(\App\Livewire\Pages\Admin\Surat\SuratDashboard::class)
        ->viewData('angka');

    expect($angka['seluruh'])->toBe(1);

    expect(ModeSurat::perlu($biasa)['surat_baru'])->toBe(1);
    expect(ModeSurat::perlu(orangIzin(peranIzin(['view_surat', 'view_surat_rahasia'])))['surat_baru'])->toBe(2);
});

it('menolak membuka detail surat rahasia', function () {
    $surat = suratRahasia();

    $this->actingAs(orangIzin(peranIzin(['akses_arsip_surat', 'view_surat'])))
        ->get('/admin/arsip-surat/'.$surat->id)
        ->assertForbidden();
});

it('menolak membuka surat rahasia lewat layar ubah', function () {
    $surat = suratRahasia();

    // Jalan masuk kedua: borang ubah memuat seluruh isi suratnya.
    $this->actingAs(orangIzin(peranIzin(['akses_arsip_surat', 'view_surat', 'edit_surat'])))
        ->get('/admin/arsip-surat/'.$surat->id.'/ubah')
        ->assertForbidden();
});

it('menolak mengunduh pindaian surat rahasia', function () {
    $surat = suratRahasia();

    $lampiran = \App\Models\Surat\SuratLampiran::create([
        'surat_id' => $surat->id,
        'nama' => 'audit.pdf',
        'jalur' => 'surat/audit.pdf',
        'ukuran' => 1024,
        'jenis' => 'application/pdf',
    ]);

    /*
     | Alamat berkas tidak melewati komponen Livewire mana pun, jadi
     | penjagaan di layar tidak menyentuhnya — padahal pindaian inilah isi
     | suratnya, bukan barisnya di daftar.
     */
    $this->actingAs(orangIzin(peranIzin(['akses_arsip_surat', 'view_surat'])))
        ->get(route('admin.surat.lampiran', [$surat, $lampiran]))
        ->assertForbidden();
});

it('menyembunyikan disposisi surat rahasia dari angka yang perlu ditindak', function () {
    $surat = suratRahasia();

    \App\Models\Surat\SuratDisposisi::create([
        'surat_id' => $surat->id,
        'kepada_nama' => 'Pak Budi',
        'instruksi' => 'Periksa temuannya.',
        'batas' => now()->subWeek()->toDateString(),
        'status' => 'menunggu',
    ]);

    $biasa = orangIzin(peranIzin(['akses_arsip_surat', 'view_surat']));

    expect(ModeSurat::perlu($biasa)['disposisi_telat'])->toBe(0);
    expect(ModeSurat::perlu(orangIzin(peranIzin(['view_surat', 'view_surat_rahasia'])))['disposisi_telat'])->toBe(1);
});
