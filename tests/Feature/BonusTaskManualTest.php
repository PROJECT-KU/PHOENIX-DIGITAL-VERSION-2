<?php

use App\Actions\Finance\SyncCashFlowAction;
use App\Actions\Gaji\BonusTaskPeriodeAction;
use App\Models\GajiKaryawans;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;

function karyawanUji(string $nama): User
{
    $peran = Role::firstOrCreate(['name' => 'karyawan']);

    return User::create([
        'name' => $nama,
        'email' => strtolower($nama).rand(1000, 9999).'@contoh.test',
        'password' => bcrypt('rahasia'),
        'role_id' => $peran->id,
    ]);
}

function gajiUji(User $user, int $bonus, bool $manual, string $status = 'pending'): GajiKaryawans
{
    return GajiKaryawans::create([
        // Dibuat di form, bukan di model, jadi uji mengisinya sendiri.
        'id_transaksi' => 'GJ-UJI-'.uniqid(),
        'deskripsi' => 'Gaji uji',
        'nama_karyawan' => $user->id,
        'tanggal_transaksi' => now(),
        'periode_bulan' => 8,
        'periode_tahun' => 2026,
        'gaji_pokok' => 1000000,
        'bonus_penyelesaian_task' => $bonus,
        'bonus_task_manual' => $manual,
        'status' => $status,
        'total' => 1000000 + $bonus,
    ]);
}

it('menyimpan penanda manual pada baris gaji', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    expect($gaji->fresh()->bonus_task_manual)->toBeTrue()
        ->and((int) $gaji->fresh()->bonus_penyelesaian_task)->toBe(250000);
});

it('pembagian pool TIDAK menimpa bonus yang diisi manual', function () {
    Setting::set(BonusTaskPeriodeAction::settingKey(8, 2026), 1000000);

    $manual = gajiUji(karyawanUji('Ani'), 250000, true);
    $otomatis = gajiUji(karyawanUji('Budi'), 0, false);

    app(BonusTaskPeriodeAction::class)->terapkan(8, 2026, app(SyncCashFlowAction::class));

    // Angka manual bertahan apa adanya.
    expect((int) $manual->fresh()->bonus_penyelesaian_task)->toBe(250000);
    // Baris otomatis tetap boleh diperbarui (tanpa task, hasilnya 0).
    expect($otomatis->fresh()->bonus_task_manual)->toBeFalse();
});

it('porsi manual dikeluarkan dari pool yang dibagikan', function () {
    Setting::set(BonusTaskPeriodeAction::settingKey(8, 2026), 1000000);

    gajiUji(karyawanUji('Ani'), 250000, true);

    $hasil = app(BonusTaskPeriodeAction::class)->distribusi(8, 2026);

    // Pool 1.000.000 dikurangi 250.000 yang sudah dipatok manual.
    expect($hasil['lockedBonus'])->toBe(250000)
        ->and($hasil['sisaPool'])->toBe(750000);
});

it('baris manual ditandai berbeda dengan baris yang terkunci karena completed', function () {
    Setting::set(BonusTaskPeriodeAction::settingKey(8, 2026), 1000000);

    $ani = karyawanUji('Ani');
    gajiUji($ani, 250000, true);

    $baris = collect(app(BonusTaskPeriodeAction::class)->distribusi(8, 2026)['rows'])
        ->firstWhere('user_id', $ani->id);

    expect($baris['manual'])->toBeTrue()
        ->and($baris['locked'])->toBeTrue()
        ->and($baris['status_gaji'])->toBe('pending');
});

it('penanda manual bisa diabaikan untuk melihat angka otomatisnya', function () {
    Setting::set(BonusTaskPeriodeAction::settingKey(8, 2026), 1000000);

    $ani = karyawanUji('Ani');
    gajiUji($ani, 250000, true);

    $hasil = app(BonusTaskPeriodeAction::class)->distribusi(8, 2026, (string) $ani->id);
    $baris = collect($hasil['rows'])->firstWhere('user_id', $ani->id);

    // Diabaikan -> tidak lagi dianggap beku, dan porsinya kembali ke pool.
    expect($baris['manual'])->toBeFalse()
        ->and($hasil['lockedBonus'])->toBe(0);
});

it('gaji completed tetap beku walau tidak ditandai manual', function () {
    Setting::set(BonusTaskPeriodeAction::settingKey(8, 2026), 1000000);

    $gaji = gajiUji(karyawanUji('Ani'), 400000, false, 'completed');

    app(BonusTaskPeriodeAction::class)->terapkan(8, 2026, app(SyncCashFlowAction::class));

    expect((int) $gaji->fresh()->bonus_penyelesaian_task)->toBe(400000);
});

it('form memuat penanda manual dan angkanya', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    $t = Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    );

    expect($t->get('bonus_task_manual'))->toBeTrue()
        ->and((string) $t->get('bonus_penyelesaian_task'))->toBe('250.000');
});

it('mematikan mode manual mengembalikan angka otomatis', function () {
    Setting::set(BonusTaskPeriodeAction::settingKey(8, 2026), 1000000);

    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    $t = Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )->set('bonus_task_manual', false);

    // Tanpa task, hitungan otomatisnya nol — dan itulah yang dikembalikan,
    // bukan angka manual yang tadi tersimpan.
    expect((int) preg_replace('/[^0-9]/', '', (string) $t->get('bonus_penyelesaian_task')))->toBe(0);
});

it('input bonus task bisa diubah saat mode manual menyala', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 0, false);

    $html = Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )->set('bonus_task_manual', true)->html();

    // Saat manual, input tidak lagi readonly.
    expect($html)->toContain('id="bonus_penyelesaian_task"')
        ->and($html)->toContain('Atur manual');
});

it('bonus manual ikut menambah total gaji', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 0, false);

    $t = Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )
        ->set('bonus_task_manual', true)
        ->set('bonus_penyelesaian_task', '300.000');

    expect((int) preg_replace('/[^0-9]/', '', (string) $t->get('total')))
        ->toBe(1000000 + 300000);
});

it('menyimpan form mengawetkan angka manual beserta penandanya', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 0, false);

    Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )
        ->set('bonus_task_manual', true)
        ->set('bonus_penyelesaian_task', '300.000')
        ->call('save');

    $tersimpan = $gaji->fresh();

    expect($tersimpan->bonus_task_manual)->toBeTrue()
        ->and((int) $tersimpan->bonus_penyelesaian_task)->toBe(300000);
});

it('angka manual bertahan setelah pool dibagikan ulang', function () {
    Setting::set(BonusTaskPeriodeAction::settingKey(8, 2026), 1000000);

    $gaji = gajiUji(karyawanUji('Ani'), 0, false);

    Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )
        ->set('bonus_task_manual', true)
        ->set('bonus_penyelesaian_task', '300.000')
        ->call('save');

    // Inilah jebakannya: tanpa penanda manual, langkah ini menimpa 300.000.
    app(BonusTaskPeriodeAction::class)->terapkan(8, 2026, app(SyncCashFlowAction::class));

    expect((int) $gaji->fresh()->bonus_penyelesaian_task)->toBe(300000);
});

it('tombol pulihkan berada di dalam kolom, bukan baris tersendiri', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    $html = Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )->html();

    // Disasar dari id kolomnya: kelas .rp-wrap dipakai belasan kolom lain di
    // form yang sama, jadi mencari pembungkus pertama akan salah sasaran.
    $mulai = strpos($html, 'id="bonus_penyelesaian_task"');
    $tutup = strpos($html, '</div>', $mulai);
    $didalam = substr($html, $mulai, $tutup - $mulai);

    // Tombol berada di dalam pembungkus kolom, sebelum pembungkus itu ditutup,
    // sehingga barisnya tetap sejajar dengan kolom lain.
    expect($didalam)->toContain('pakaiOtomatis()')
        ->and($didalam)->toContain('btn-otomatis');
});

it('tombol pulihkan tidak memakai warna sekunder', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    $html = Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )->html();

    expect($html)->not->toContain('btn-outline-secondary');
});

it('tombol pulihkan bekerja tanpa memanggil server', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    $html = Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )->html();

    // Dijalankan Alpine; tidak ada wire:click yang memicu perjalanan bolak-balik.
    expect($html)->toContain('x-on:click="pakaiOtomatis()"')
        ->and($html)->not->toContain('wire:click="pulihkanBonusTaskOtomatis"');
});

it('bonus manual bernilai nol tetap tersimpan', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )
        ->set('bonus_task_manual', true)
        ->set('bonus_penyelesaian_task', '0')
        ->call('save');

    // Nol adalah nilai sah: admin memang bisa meniadakan bonusnya.
    expect((int) $gaji->fresh()->bonus_penyelesaian_task)->toBe(0)
        ->and($gaji->fresh()->bonus_task_manual)->toBeTrue();
});

it('bonus manual bernilai satu tetap tersimpan', function () {
    $gaji = gajiUji(karyawanUji('Ani'), 250000, true);

    Livewire\Livewire::test(
        App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansForm::class,
        ['gajikaryawan' => $gaji]
    )
        ->set('bonus_task_manual', true)
        ->set('bonus_penyelesaian_task', '1')
        ->call('save');

    expect((int) $gaji->fresh()->bonus_penyelesaian_task)->toBe(1);
});

it('kolom bonus task memakai modifier yang sama dengan kolom rupiah lain', function () {
    $blade = file_get_contents(
        resource_path('views/livewire/pages/admin/gaji-karyawans/gaji-karyawans-form.blade.php')
    );

    // .blur membuat permintaan blur berlomba dengan permintaan simpan, sehingga
    // angka yang baru diketik bisa hilang.
    expect($blade)->toContain('wire:model="bonus_penyelesaian_task"')
        ->and($blade)->not->toContain('wire:model.blur="bonus_penyelesaian_task"');
});
