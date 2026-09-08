<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\FiturAdmin;
use Livewire\Livewire;

/**
 * Menutup sementara satu MODUL ADMIN tanpa mencabut izin siapa pun.
 *
 * Yang paling penting diuji bukan penutupannya, melainkan PENGAMANNYA: tidak
 * boleh ada keadaan di mana modul terkunci dan tak seorang pun bisa membukanya.
 */
afterEach(function () {
    Setting::query()->delete();
});

function penggunaAdmin(array $izin): User
{
    $peran = Role::create(['name' => 'uji-modul-'.uniqid(), 'description' => 'Peran uji modul admin']);

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']
        );
        $peran->permissions()->attach($p->id);
    }

    $user = User::factory()->create(['role_id' => $peran->id]);

    // Tanpa data karyawan, EnsureProfileComplete mengalihkan sebelum rute
    // adminnya sempat dijalankan — 302, bukan halaman yang hendak diuji.
    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

it('semua modul terbuka saat belum pernah disetel', function () {
    foreach (array_keys(FiturAdmin::DAFTAR) as $modul) {
        expect(FiturAdmin::ditutup($modul))->toBeFalse();
    }
});

it('menutup satu modul tidak menyentuh modul lain', function () {
    FiturAdmin::setel('keuangan', true);

    expect(FiturAdmin::ditutup('keuangan'))->toBeTrue()
        ->and(FiturAdmin::ditutup('pesanan'))->toBeFalse()
        ->and(FiturAdmin::ditutup('kepegawaian'))->toBeFalse();
});

it('nama rute dipetakan ke modulnya lewat awalannya', function () {
    expect(FiturAdmin::dariRute('admin.gajikaryawan.index'))->toBe('kepegawaian')
        ->and(FiturAdmin::dariRute('admin.presensi.rekap'))->toBe('kepegawaian')
        ->and(FiturAdmin::dariRute('admin.cashflow.index'))->toBe('keuangan')
        ->and(FiturAdmin::dariRute('admin.orcha.dashboard'))->toBe('orcha');
});

it('halaman Jeda Layanan sendiri tidak pernah bisa ditutup', function () {
    // Kalau bisa, admin mengunci dirinya keluar dari satu-satunya tempat
    // untuk membukanya kembali.
    expect(FiturAdmin::dariRute('admin.jeda-layanan.index'))->toBeNull();
});

it('dasbor dan akun profil juga tidak bisa ditutup', function () {
    // Karyawan yang baru masuk harus punya tempat mendarat, dan harus tetap
    // bisa mengganti sandinya.
    expect(FiturAdmin::dariRute('admin.dashboard'))->toBeNull()
        ->and(FiturAdmin::dariRute('admin.account.profile'))->toBeNull();
});

it('karyawan biasa ditahan saat modulnya ditutup', function () {
    FiturAdmin::setel('keuangan', true, 'Arus kas sedang dihitung ulang.');

    $karyawan = penggunaAdmin(['view_cashflow']);

    $this->actingAs($karyawan)->get('/admin/cashflow')
        ->assertStatus(503)
        ->assertSee('Keuangan sedang diperbaiki')
        ->assertSee('Arus kas sedang dihitung ulang.');
});

it('pemegang izin kelola tetap bisa masuk modul yang ditutup', function () {
    FiturAdmin::setel('keuangan', true);

    // Dialah yang mengerjakan perbaikannya — kalau ikut terkunci, tak ada yang
    // bisa memastikan modulnya sudah benar sebelum dibuka untuk yang lain.
    $pengelola = penggunaAdmin(['view_cashflow', 'manage_jeda_layanan']);

    $this->actingAs($pengelola)->get('/admin/cashflow')->assertOk();
});

it('modul lain tetap terbuka saat satu modul ditutup', function () {
    FiturAdmin::setel('keuangan', true);

    $karyawan = penggunaAdmin(['view_dashboard']);

    $this->actingAs($karyawan)->get('/admin/dashboard')->assertOk();
});

it('menutup modul tidak mencabut izin siapa pun', function () {
    $karyawan = penggunaAdmin(['view_cashflow']);

    FiturAdmin::setel('keuangan', true);
    FiturAdmin::setel('keuangan', false);

    // Setelah dibuka, semua kembali seperti semula tanpa perlu memasang ulang izin.
    expect($karyawan->fresh()->hasPermission('view_cashflow'))->toBeTrue();
    $this->actingAs($karyawan)->get('/admin/cashflow')->assertOk();
});

it('modul yang tidak dikenal diabaikan, bukan menimbulkan galat', function () {
    FiturAdmin::setel('modul-karangan', true);

    expect(Setting::where('key', 'like', 'fitur_admin_%')->count())->toBe(0);
});

/* ===================== Panel admin ===================== */

it('panel mendaftar seluruh modul admin', function () {
    Livewire::actingAs(penggunaAdmin(['view_jeda_layanan', 'manage_jeda_layanan']))
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->assertSee('Modul Admin')
        ->assertSee('0 ditutup dari '.count(FiturAdmin::DAFTAR))
        ->assertSee('Semua modul admin bisa dipakai');
});

it('modul yang ditutup naik ke atas', function () {
    FiturAdmin::setel('keuangan', true);

    $html = Livewire::actingAs(penggunaAdmin(['view_jeda_layanan', 'manage_jeda_layanan']))
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->html();

    expect(strpos($html, 'Keuangan'))->toBeLessThan(strpos($html, 'Bisa dipakai &middot;'));
});

it('admin bisa menutup dan membuka modul dari panel', function () {
    $t = Livewire::actingAs(penggunaAdmin(['view_jeda_layanan', 'manage_jeda_layanan']))
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanModul', 'blog')
        ->assertDispatched('swal-success');

    expect(FiturAdmin::ditutup('blog'))->toBeTrue();

    $t->call('alihkanModul', 'blog');

    expect(FiturAdmin::ditutup('blog'))->toBeFalse();
});

it('tanpa izin kelola, menutup modul ditolak server', function () {
    Livewire::actingAs(penggunaAdmin(['view_jeda_layanan']))
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanModul', 'blog')
        ->assertDispatched('swal-error');

    expect(FiturAdmin::ditutup('blog'))->toBeFalse();
});

/* ===================== Waktu perbaikan ===================== */

it('waktu mulai dicatat saat modul ditutup', function () {
    expect(FiturAdmin::mulai('keuangan'))->toBeNull();

    FiturAdmin::setel('keuangan', true);

    expect(FiturAdmin::mulai('keuangan'))->not->toBeNull()
        ->and(FiturAdmin::mulai('keuangan')->isToday())->toBeTrue();
});

it('menyimpan ulang keterangan tidak memundurkan waktu mulai', function () {
    FiturAdmin::setel('keuangan', true);
    $mulai = FiturAdmin::mulai('keuangan');

    $this->travel(2)->hours();
    FiturAdmin::setel('keuangan', true, 'Keterangan diperbarui di tengah jalan.');

    // "Sudah ditutup sejak kapan" harus tetap menunjuk penutupan pertamanya.
    expect(FiturAdmin::mulai('keuangan')->eq($mulai))->toBeTrue();
});

it('perkiraan selesai boleh dikosongkan', function () {
    FiturAdmin::setel('keuangan', true);

    // Menebak waktu selesai yang tidak diketahui lalu meleset lebih merusak
    // kepercayaan daripada mengaku belum tahu.
    expect(FiturAdmin::sampai('keuangan'))->toBeNull();
});

it('perkiraan selesai tersimpan bila diisi', function () {
    FiturAdmin::setel('keuangan', true, null, '2026-09-09 08:00:00');

    expect(FiturAdmin::sampai('keuangan')->format('Y-m-d H:i'))->toBe('2026-09-09 08:00');
});

it('membuka kembali membersihkan jejak waktunya', function () {
    FiturAdmin::setel('keuangan', true, null, '2026-09-09 08:00:00');
    FiturAdmin::setel('keuangan', false);

    // Penutupan berikutnya tidak boleh mewarisi waktu lama yang tak berlaku.
    expect(FiturAdmin::mulai('keuangan'))->toBeNull()
        ->and(FiturAdmin::sampai('keuangan'))->toBeNull();

    FiturAdmin::setel('keuangan', true);
    expect(FiturAdmin::sampai('keuangan'))->toBeNull();
});

it('modul yang terbuka tidak punya waktu mulai maupun selesai', function () {
    expect(FiturAdmin::mulai('blog'))->toBeNull()
        ->and(FiturAdmin::sampai('blog'))->toBeNull();
});

it('halaman pemberitahuan menyebut sejak kapan dan sampai kapan', function () {
    FiturAdmin::setel('keuangan', true, 'Arus kas dihitung ulang.', '2026-09-09 08:00:00');

    $this->actingAs(penggunaAdmin(['view_cashflow']))->get('/admin/cashflow')
        ->assertStatus(503)
        ->assertSee('Ditutup sejak')
        ->assertSee('Perkiraan selesai');
});
