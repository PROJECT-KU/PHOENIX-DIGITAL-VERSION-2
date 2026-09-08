<?php

use App\Models\EmployeeDetail;
use App\Models\Kegiatan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Agenda kegiatan di dasbor.
 *
 * Intinya satu: seseorang hanya boleh melihat kegiatan yang menyangkut dirinya.
 * Kalender memang terbuka untuk semua peran, tapi dasbor adalah ringkasan
 * pribadi — memampatkan rapat orang lain ke sana membuatnya tidak berguna.
 */
function orangDasbor(): User
{
    $peran = Role::firstOrCreate(
        ['name' => 'uji-dasbor'],
        ['description' => 'Peran uji agenda dasbor']
    );

    // view_dashboard wajib: tanpa itu rutenya 403 dan yang teruji bukan
    // agendanya, melainkan penjaga izinnya.
    foreach (['view_kegiatan', 'view_dashboard'] as $nama) {
        $izin = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']
        );

        if (! $peran->permissions()->where('permissions.id', $izin->id)->exists()) {
            $peran->permissions()->attach($izin->id);
        }
    }

    $user = User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

it('agenda hanya memuat kegiatan yang menyangkut orangnya', function () {
    Carbon::setTestNow('2026-09-15 08:00:00');

    $saya = orangDasbor();
    $lain = orangDasbor();

    $diundang = Kegiatan::create(['judul' => 'Saya diundang', 'jenis' => 'rapat', 'mulai' => '2026-09-20 09:00:00', 'dibuat_oleh' => $lain->id]);
    $diundang->peserta()->attach($saya->id);

    Kegiatan::create(['judul' => 'Saya yang membuat', 'jenis' => 'rapat', 'mulai' => '2026-09-21 09:00:00', 'dibuat_oleh' => $saya->id]);
    Kegiatan::create(['judul' => 'Bukan urusan saya', 'jenis' => 'rapat', 'mulai' => '2026-09-22 09:00:00', 'dibuat_oleh' => $lain->id]);

    $judul = Kegiatan::agenda($saya->id)->pluck('judul')->all();

    expect($judul)->toBe(['Saya diundang', 'Saya yang membuat']);

    Carbon::setTestNow();
});

it('kegiatan yang sudah lewat tidak lagi muncul, tapi yang hari ini tetap', function () {
    Carbon::setTestNow('2026-09-15 13:00:00');

    $saya = orangDasbor();

    // Kemarin: sudah lewat.
    $kemarin = Kegiatan::create(['judul' => 'Kemarin', 'jenis' => 'rapat', 'mulai' => '2026-09-14 09:00:00', 'dibuat_oleh' => $saya->id]);
    // Pagi tadi: jamnya lewat, TAPI harinya masih hari ini — orang masih perlu
    // melihatnya untuk tahu apa yang sudah dijalani hari ini.
    $pagiTadi = Kegiatan::create(['judul' => 'Pagi tadi', 'jenis' => 'rapat', 'mulai' => '2026-09-15 08:00:00', 'dibuat_oleh' => $saya->id]);
    $besok = Kegiatan::create(['judul' => 'Besok', 'jenis' => 'rapat', 'mulai' => '2026-09-16 09:00:00', 'dibuat_oleh' => $saya->id]);

    $judul = Kegiatan::agenda($saya->id)->pluck('judul')->all();

    expect($judul)->toBe(['Pagi tadi', 'Besok'])
        ->and($judul)->not->toContain('Kemarin');

    Carbon::setTestNow();
});

it('agenda terurut dari yang paling dekat', function () {
    Carbon::setTestNow('2026-09-15 08:00:00');

    $saya = orangDasbor();

    foreach (['2026-09-25 09:00:00', '2026-09-18 09:00:00', '2026-09-30 09:00:00'] as $i => $waktu) {
        Kegiatan::create(['judul' => 'Kegiatan '.$i, 'jenis' => 'rapat', 'mulai' => $waktu, 'dibuat_oleh' => $saya->id]);
    }

    expect(Kegiatan::agenda($saya->id)->pluck('judul')->all())
        ->toBe(['Kegiatan 1', 'Kegiatan 0', 'Kegiatan 2']);

    Carbon::setTestNow();
});

it('dasbor karyawan menampilkan agendanya', function () {
    Carbon::setTestNow('2026-09-15 08:00:00');

    $saya = orangDasbor();
    $lain = orangDasbor();

    $milikSaya = Kegiatan::create(['judul' => 'Rapat tim saya', 'jenis' => 'rapat', 'mulai' => '2026-09-20 09:00:00', 'dibuat_oleh' => $lain->id]);
    $milikSaya->peserta()->attach($saya->id);

    Kegiatan::create(['judul' => 'Rapat orang lain', 'jenis' => 'rapat', 'mulai' => '2026-09-21 09:00:00', 'dibuat_oleh' => $lain->id]);

    $this->actingAs($saya)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Agenda Saya')
        ->assertSee('Rapat tim saya')
        ->assertDontSee('Rapat orang lain');

    Carbon::setTestNow();
});
