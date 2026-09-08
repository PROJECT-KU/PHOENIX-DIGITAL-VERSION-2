<?php

use App\Mail\ModulAdminDijedaMail;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\KabarJedaModul;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * Saat modul admin ditutup, karyawan dikabari lewat surel.
 *
 * Penerimanya punya dua sumber yang saling meniadakan: alamat uji coba selama
 * JEDA_EMAIL_UJI terisi, dan data karyawan bila dikosongkan.
 */
beforeEach(function () {
    Mail::fake();
    config(['jeda.email_uji' => null]);
});

afterEach(function () {
    Setting::query()->delete();
});

function karyawanAktif(string $email, array $izin = []): User
{
    $peran = Role::create(['name' => 'uji-kabar-'.uniqid(), 'description' => 'Peran uji kabar']);

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']
        );
        $peran->permissions()->attach($p->id);
    }

    $user = User::factory()->create([
        'role_id' => $peran->id,
        'email' => $email,
        'status' => 'active',
    ]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

it('selama alamat uji terisi, kabar hanya ke alamat itu', function () {
    config(['jeda.email_uji' => 'bertojunikrisnanto@gmail.com']);
    karyawanAktif('karyawan.a@contoh.test');
    karyawanAktif('karyawan.b@contoh.test');

    // Percobaan tidak boleh mengganggu tim.
    expect(KabarJedaModul::penerima())->toBe(['bertojunikrisnanto@gmail.com'])
        ->and(KabarJedaModul::modeUji())->toBeTrue();
});

it('bila alamat uji dikosongkan, penerimanya diambil dari data karyawan', function () {
    karyawanAktif('karyawan.a@contoh.test');
    karyawanAktif('karyawan.b@contoh.test');

    // Keadaan yang dituju saat deploy.
    expect(KabarJedaModul::penerima())
        ->toContain('karyawan.a@contoh.test')
        ->toContain('karyawan.b@contoh.test')
        ->and(KabarJedaModul::modeUji())->toBeFalse();
});

it('yang menekan tombol tidak ikut dikirimi', function () {
    $pelaku = karyawanAktif('penekan@contoh.test');
    karyawanAktif('lainnya@contoh.test');

    // Ia baru saja melakukannya dan sudah melihat konfirmasinya di layar.
    expect(KabarJedaModul::penerima($pelaku))
        ->not->toContain('penekan@contoh.test')
        ->toContain('lainnya@contoh.test');
});

it('akun tanpa data kepegawaian bukan karyawan, jadi tidak dikirimi', function () {
    User::factory()->create(['email' => 'bukan.karyawan@contoh.test', 'status' => 'active']);

    expect(KabarJedaModul::penerima())->not->toContain('bukan.karyawan@contoh.test');
});

it('akun yang tidak aktif tidak dikirimi', function () {
    $nonaktif = karyawanAktif('nonaktif@contoh.test');
    $nonaktif->update(['status' => 'blokir']);

    expect(KabarJedaModul::penerima())->not->toContain('nonaktif@contoh.test');
});

it('surel terkirim saat modul ditutup dari panel', function () {
    config(['jeda.email_uji' => 'bertojunikrisnanto@gmail.com']);
    $admin = karyawanAktif('admin@contoh.test', ['view_jeda_layanan', 'manage_jeda_layanan']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanModul', 'keuangan');

    Mail::assertSent(ModulAdminDijedaMail::class, function ($mail) {
        return $mail->ditutup === true
            && $mail->namaModul === 'Keuangan'
            && $mail->hasTo('bertojunikrisnanto@gmail.com');
    });
});

it('surel juga terkirim saat modul dibuka kembali', function () {
    config(['jeda.email_uji' => 'bertojunikrisnanto@gmail.com']);
    \App\Support\FiturAdmin::setel('keuangan', true);
    $admin = karyawanAktif('admin2@contoh.test', ['view_jeda_layanan', 'manage_jeda_layanan']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanModul', 'keuangan');

    // Yang dikabari saat ditutup berhak tahu kapan bisa bekerja lagi.
    Mail::assertSent(ModulAdminDijedaMail::class, fn ($mail) => $mail->ditutup === false);
});

it('gagal kirim surel tidak membatalkan penutupan modulnya', function () {
    config(['jeda.email_uji' => 'bertojunikrisnanto@gmail.com']);
    Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP mati'));

    $admin = karyawanAktif('admin3@contoh.test', ['view_jeda_layanan', 'manage_jeda_layanan']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanModul', 'blog');

    // Modulnya ditutup karena ada yang perlu diperbaiki; surel yang gagal
    // adalah urusan yang jauh lebih ringan.
    expect(\App\Support\FiturAdmin::ditutup('blog'))->toBeTrue();
});

it('tanpa penerima sama sekali, tidak ada surel yang dicoba dikirim', function () {
    expect(KabarJedaModul::kirim('Keuangan', true, 'pesan', null))->toBe(0);

    Mail::assertNothingSent();
});

it('surel selalu membawa versi teks di samping HTML-nya', function () {
    config(['jeda.email_uji' => 'uji@contoh.test']);
    $admin = karyawanAktif('admin4@contoh.test', ['view_jeda_layanan', 'manage_jeda_layanan']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanModul', 'keuangan');

    // HTML tanpa pasangan teks adalah penanda spam yang paling sering dipakai
    // penyaring, dan kabar ini justru yang tidak boleh nyasar ke folder spam.
    Mail::assertSent(ModulAdminDijedaMail::class, function ($mail) {
        $dirakit = $mail->build();

        return $dirakit->textView === 'emails.modul-admin-dijeda-teks'
            && $dirakit->view === 'emails.modul-admin-dijeda';
    });
});

it('balasan diarahkan ke kotak yang sungguh dibaca', function () {
    config(['jeda.email_uji' => 'uji@contoh.test']);
    $admin = karyawanAktif('admin5@contoh.test', ['view_jeda_layanan', 'manage_jeda_layanan']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanModul', 'blog');

    Mail::assertSent(ModulAdminDijedaMail::class, function ($mail) {
        return ! empty($mail->build()->replyTo);
    });
});
