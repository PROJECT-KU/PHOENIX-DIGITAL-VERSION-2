<?php

use App\Livewire\Pages\Admin\RoleUser\RoleList;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

/**
 * Izin tambahan untuk satu orang, di atas izin perannya.
 *
 * Yang dijaga di sini terutama yang TIDAK boleh: rekan sekerja berperan sama
 * tidak boleh ikut mendapat akses. Tanpa itu, satu-satunya cara memberi akses
 * ke dua orang adalah memberikannya ke seluruh peran — persis keadaan yang
 * membuat 8 karyawan bisa membuka Orcha padahal yang dimaksud cuma dua.
 */
function izinUji(string $nama): Permission
{
    return Permission::firstOrCreate(
        ['name' => $nama],
        ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
    );
}

/**
 * Pengguna berprofil LENGKAP, supaya tidak dialihkan EnsureProfileComplete.
 *
 * Tanpa EmployeeDetail, setiap permintaan halaman admin berakhir 302 ke
 * halaman lengkapi profil — pengalihan yang tidak ada hubungannya dengan
 * izin, dan membuat uji akses merah karena alasan yang salah.
 */
function penggunaUji(Role $peran): User
{
    $user = User::factory()->create(['role_id' => $peran->id]);

    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id,
        'jabatan' => 'Karyawan Uji',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function peranUji(string $nama, array $izin = []): Role
{
    $role = Role::create(['name' => $nama.'-'.uniqid(), 'description' => 'uji']);

    foreach ($izin as $n) {
        $role->permissions()->attach(izinUji($n)->id);
    }

    return $role;
}

/* ===================== INTI: SIAPA YANG BOLEH ===================== */

it('memberi akses ke orangnya saja, bukan ke seluruh perannya', function () {
    $karyawan = peranUji('karyawan');          // TANPA akses_orcha
    $hanifah = User::factory()->create(['role_id' => $karyawan->id]);
    $rekan = User::factory()->create(['role_id' => $karyawan->id]);

    $hanifah->izinTambahan()->attach(izinUji('akses_orcha')->id);

    expect($hanifah->fresh()->hasPermission('akses_orcha'))->toBeTrue();
    /*
     | Inilah seluruh gunanya tabel ini. Sebelum ada, satu-satunya cara
     | memberi akses ke Hanifah adalah memberikannya ke peran karyawan —
     | dan tujuh rekannya ikut mendapatkannya.
     */
    expect($rekan->fresh()->hasPermission('akses_orcha'))->toBeFalse();
});

it('izin dari peran tetap berlaku seperti biasa', function () {
    $peran = peranUji('karyawan', ['view_dashboard']);
    $user = User::factory()->create(['role_id' => $peran->id]);

    // Perilaku lama tidak boleh berubah sedikit pun.
    expect($user->hasPermission('view_dashboard'))->toBeTrue();
    expect($user->hasPermission('akses_orcha'))->toBeFalse();
});

it('yang tidak punya izin tambahan sama sekali tetap ditolak', function () {
    $user = User::factory()->create(['role_id' => peranUji('karyawan')->id]);

    expect($user->hasPermission('akses_orcha'))->toBeFalse();
});

it('mencabut izin tambahan langsung menutup aksesnya', function () {
    $user = User::factory()->create(['role_id' => peranUji('karyawan')->id]);
    $user->izinTambahan()->attach(izinUji('akses_orcha')->id);

    expect($user->fresh()->hasPermission('akses_orcha'))->toBeTrue();

    $user->izinTambahan()->detach(izinUji('akses_orcha')->id);

    expect($user->fresh()->hasPermission('akses_orcha'))->toBeFalse();
});

it('hasAnyPermission dan hasAllPermissions ikut membaca izin pribadi', function () {
    $user = User::factory()->create(['role_id' => peranUji('karyawan', ['view_dashboard'])->id]);
    $user->izinTambahan()->attach(izinUji('akses_orcha')->id);
    $user = $user->fresh();

    expect($user->hasAnyPermission(['akses_orcha']))->toBeTrue();
    expect($user->hasAllPermissions(['view_dashboard', 'akses_orcha']))->toBeTrue();
});

it('peran tetap wajib — izin pribadi selalu duduk di atas sebuah peran', function () {
    $peran = peranUji('karyawan');
    $user = User::factory()->create(['role_id' => $peran->id]);
    $user->izinTambahan()->attach(izinUji('akses_orcha')->id);

    /*
     | Pengguna TANPA peran tidak mungkin ada di basis data ini: role_id
     | NOT NULL dan dijaga kunci asing, sehingga peran yang masih dipakai
     | pun tidak bisa dihapus.
     |
     | Dicatat di sini karena hasAnyPermission()/hasAllPermissions() dulu
     | menyerah lebih dulu dengan `if (! $this->role) return false;`.
     | Penjaga itu dibuang bersama fitur ini — bukan karena ada yang rusak,
     | melainkan supaya satu-satunya jawaban atas "dia boleh atau tidak"
     | datang dari hasPermission(), bukan dari jalan pintas yang kebetulan
     | tidak pernah terpicu.
     */
    expect(fn () => $peran->delete())->toThrow(Illuminate\Database\QueryException::class);
    expect($user->fresh()->hasPermission('akses_orcha'))->toBeTrue();
});

/* ===================== JALUR RUTE SUNGGUHAN ===================== */

it('rute orcha terbuka lewat izin pribadi', function () {
    $user = penggunaUji(peranUji('karyawan'));
    $user->izinTambahan()->attach(izinUji('akses_orcha')->id);

    // Bukan sekadar modelnya: penjaga sebenarnya ada di middleware rute.
    $this->actingAs($user->fresh())->get('/admin/orcha/dashboard')->assertSuccessful();
});

it('rute orcha tetap tertutup untuk rekan seperan', function () {
    $peran = peranUji('karyawan');
    $punya = penggunaUji($peran);
    $punya->izinTambahan()->attach(izinUji('akses_orcha')->id);

    $rekan = penggunaUji($peran);

    $this->actingAs($rekan)->get('/admin/orcha/dashboard')->assertForbidden();
});

/* ========================= LAYAR PENGELOLA ========================= */

it('menyimpan izin tambahan dari layar pengguna', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $peran = peranUji('karyawan');
    $target = User::factory()->create(['role_id' => $peran->id]);
    $izin = izinUji('akses_orcha');

    Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id)
        ->set('izinTambahan', [$izin->id])
        ->call('updateRoleUser');

    expect($target->fresh()->hasPermission('akses_orcha'))->toBeTrue();
});

it('tidak menyimpan izin yang sudah datang dari perannya', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $peran = peranUji('karyawan', ['akses_orcha']);
    $target = User::factory()->create(['role_id' => $peran->id]);
    $izin = izinUji('akses_orcha');

    Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id)
        ->set('izinTambahan', [$izin->id])
        ->call('updateRoleUser');

    /*
     | Baris pribadi yang menduplikasi izin peran tidak mengubah apa pun
     | HARI INI — tetapi saat izin itu dicabut dari perannya kelak, orang
     | ini diam-diam TETAP memilikinya, dan tidak ada yang mengira harus
     | memeriksa daftar pribadinya.
     */
    expect($target->fresh()->izinTambahan)->toHaveCount(0);
});

it('mencatat siapa yang memberi izinnya', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $target = User::factory()->create(['role_id' => peranUji('karyawan')->id]);

    Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id)
        ->set('izinTambahan', [izinUji('akses_orcha')->id])
        ->call('updateRoleUser');

    // "Sejak kapan dia bisa membuka ini, dan siapa yang memberi?" tidak bisa
    // dijawab tabel yang cuma berisi dua kunci asing.
    expect($target->fresh()->izinTambahan->first()->pivot->diberi_oleh)->toBe($admin->name);
});

it('membuka modal memuat izin pribadi yang sudah ada', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $target = User::factory()->create(['role_id' => peranUji('karyawan')->id]);
    $izin = izinUji('akses_orcha');
    $target->izinTambahan()->attach($izin->id);

    $komponen = Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id);

    expect($komponen->get('izinTambahan'))->toBe([$izin->id]);
});

it('menghapus centang mencabut izinnya', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $target = User::factory()->create(['role_id' => peranUji('karyawan')->id]);
    $target->izinTambahan()->attach(izinUji('akses_orcha')->id);

    Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id)
        ->set('izinTambahan', [])
        ->call('updateRoleUser');

    expect($target->fresh()->hasPermission('akses_orcha'))->toBeFalse();
});

it('saringan izin mempersempit daftarnya', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $target = User::factory()->create(['role_id' => peranUji('karyawan')->id]);
    izinUji('akses_orcha');
    Permission::firstOrCreate(['name' => 'view_gaji'],
        ['display_name' => 'Lihat Gaji', 'group' => 'gaji', 'description' => 'uji']);

    /*
     | 121 izin terlalu panjang untuk digulir setiap kali memberi satu izin.
     | Panel yang menuntut itu akan dilewati, lalu izinnya diberikan lewat
     | peran lagi seperti semula — persis keadaan yang hendak diperbaiki.
     */
    Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id)
        ->set('cariIzin', 'orcha')
        ->assertSee('akses_orcha')
        ->assertDontSee('Lihat Gaji');
});

it('saringan yang tidak menemukan apa pun mengatakannya', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $target = User::factory()->create(['role_id' => peranUji('karyawan')->id]);

    // Daftar kosong tanpa kalimat terbaca sebagai panel yang rusak.
    Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id)
        ->set('cariIzin', 'zzzzz-tidak-ada')
        ->assertSee('Tidak ada izin yang cocok');
});

it('tombol simpan menyebut izinnya juga', function () {
    $admin = adminRab(['akses_orcha', 'view_roles', 'edit_roles']);
    $target = User::factory()->create(['role_id' => peranUji('karyawan')->id]);

    // "Update Role User" di bawah panel izin membuat orang mengira
    // centangnya tidak ikut tersimpan.
    Livewire::actingAs($admin)
        ->test(RoleList::class)
        ->call('showModalEdit', $target->id)
        ->assertSee('Simpan Role & Izin');
});
