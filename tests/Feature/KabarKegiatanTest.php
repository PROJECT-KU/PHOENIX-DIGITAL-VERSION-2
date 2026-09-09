<?php

use App\Livewire\Pages\Admin\Kegiatan\KegiatanKalender;
use App\Mail\UndanganKegiatanMail;
use App\Models\EmployeeDetail;
use App\Models\Kegiatan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * Kabar surel kepada peserta kegiatan.
 *
 * Yang dijaga di sini bukan "surelnya terkirim", melainkan KAPAN ia terkirim.
 * Mengirim tiap kali tombol Simpan ditekan akan melatih orang mengabaikan
 * kabar dari lemon — dan begitu itu terjadi, undangan yang benar-benar penting
 * pun ikut tidak dibaca.
 *
 * Penerimanya diperiksa di kolom To, bukan BCC: tiap peserta menerima suratnya
 * sendiri supaya tidak dianggap surat massal oleh penyaring spam.
 */
function orangKegiatan(array $izin = ['view_kegiatan', 'create_kegiatan', 'edit_kegiatan', 'delete_kegiatan']): User
{
    $peran = Role::create(['name' => 'uji-kabar-'.uniqid(), 'description' => 'Peran uji kabar kegiatan']);

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'kegiatan', 'description' => 'uji']
        );
        $peran->permissions()->attach($p->id);
    }

    $user = User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

beforeEach(function () {
    Mail::fake();
    // Alamat uji coba dikosongkan: yang diuji justru penerima sungguhannya.
    config()->set('kegiatan.email_uji', null);
});

it('peserta yang dipilih menerima undangan saat kegiatan dibuat', function () {
    $admin = orangKegiatan();
    $peserta = orangKegiatan(['view_kegiatan']);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Rapat produk')
        ->set('tanggalMulai', '2026-09-20')
        ->set('jamMulai', '09:00')
        ->set('peserta', [$peserta->id])
        ->call('simpan')
        ->assertHasNoErrors();

    Mail::assertSent(UndanganKegiatanMail::class, function ($surat) use ($peserta) {
        return $surat->rupa === UndanganKegiatanMail::UNDANGAN
            && $surat->hasTo($peserta->email);
    });
});

it('yang menekan tombol tidak mengirimi dirinya sendiri', function () {
    $admin = orangKegiatan();

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Rapat sendiri')
        ->set('tanggalMulai', '2026-09-20')
        ->set('jamMulai', '09:00')
        ->set('peserta', [$admin->id])
        ->call('simpan')
        ->assertHasNoErrors();

    Mail::assertNothingSent();
});

it('mengubah catatan saja tidak mengabari peserta lama', function () {
    $admin = orangKegiatan();
    $peserta = orangKegiatan(['view_kegiatan']);

    $k = Kegiatan::create([
        'judul' => 'Rapat rutin', 'jenis' => 'rapat',
        'mulai' => '2026-09-20 09:00:00', 'dibuat_oleh' => $admin->id,
    ]);
    $k->peserta()->attach($peserta->id);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('sunting', $k->id)
        ->set('deskripsi', 'Tambahan catatan kecil.')
        ->call('simpan')
        ->assertHasNoErrors();

    Mail::assertNothingSent();
});

it('menggeser waktu mengabari peserta lama', function () {
    $admin = orangKegiatan();
    $peserta = orangKegiatan(['view_kegiatan']);

    $k = Kegiatan::create([
        'judul' => 'Rapat rutin', 'jenis' => 'rapat',
        'mulai' => '2026-09-20 09:00:00', 'dibuat_oleh' => $admin->id,
    ]);
    $k->peserta()->attach($peserta->id);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('sunting', $k->id)
        ->set('jamMulai', '14:00')
        ->call('simpan')
        ->assertHasNoErrors();

    Mail::assertSent(UndanganKegiatanMail::class, function ($surat) use ($peserta) {
        return $surat->rupa === UndanganKegiatanMail::PERUBAHAN
            && $surat->hasTo($peserta->email);
    });
});

it('peserta yang baru ditambahkan dapat undangan, bukan pemberitahuan perubahan', function () {
    $admin = orangKegiatan();
    $lama = orangKegiatan(['view_kegiatan']);
    $baru = orangKegiatan(['view_kegiatan']);

    $k = Kegiatan::create([
        'judul' => 'Rapat rutin', 'jenis' => 'rapat',
        'mulai' => '2026-09-20 09:00:00', 'dibuat_oleh' => $admin->id,
    ]);
    $k->peserta()->attach($lama->id);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('sunting', $k->id)
        ->set('peserta', [$lama->id, $baru->id])
        ->call('simpan')
        ->assertHasNoErrors();

    // Yang baru diundang...
    Mail::assertSent(UndanganKegiatanMail::class, fn ($s) => $s->rupa === UndanganKegiatanMail::UNDANGAN
        && $s->hasTo($baru->email));

    // ...dan yang lama tidak diganggu, karena baginya tidak ada yang berubah.
    Mail::assertNotSent(UndanganKegiatanMail::class, fn ($s) => $s->rupa === UndanganKegiatanMail::PERUBAHAN);
});

it('peserta yang dikeluarkan diberi tahu', function () {
    $admin = orangKegiatan();
    $keluar = orangKegiatan(['view_kegiatan']);

    $k = Kegiatan::create([
        'judul' => 'Rapat rutin', 'jenis' => 'rapat',
        'mulai' => '2026-09-20 09:00:00', 'dibuat_oleh' => $admin->id,
    ]);
    $k->peserta()->attach($keluar->id);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('sunting', $k->id)
        ->set('peserta', [])
        ->call('simpan')
        ->assertHasNoErrors();

    Mail::assertSent(UndanganKegiatanMail::class, fn ($s) => $s->rupa === UndanganKegiatanMail::DIKELUARKAN
        && $s->hasTo($keluar->email));
});

it('menghapus kegiatan mengabari seluruh pesertanya', function () {
    $admin = orangKegiatan();
    $peserta = orangKegiatan(['view_kegiatan']);

    $k = Kegiatan::create([
        'judul' => 'Rapat batal', 'jenis' => 'rapat',
        'mulai' => '2026-09-20 09:00:00', 'dibuat_oleh' => $admin->id,
    ]);
    $k->peserta()->attach($peserta->id);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)->call('hapus', $k->id);

    Mail::assertSent(UndanganKegiatanMail::class, fn ($s) => $s->rupa === UndanganKegiatanMail::PEMBATALAN
        && $s->hasTo($peserta->email));
});

it('alamat uji coba menggantikan seluruh peserta sungguhan', function () {
    config()->set('kegiatan.email_uji', 'uji@example.test');

    $admin = orangKegiatan();
    $peserta = orangKegiatan(['view_kegiatan']);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Rapat percobaan')
        ->set('tanggalMulai', '2026-09-20')
        ->set('jamMulai', '09:00')
        ->set('peserta', [$peserta->id])
        ->call('simpan')
        ->assertHasNoErrors();

    Mail::assertSent(UndanganKegiatanMail::class, fn ($s) => $s->hasTo('uji@example.test')
        && ! $s->hasTo($peserta->email));
});

it('akun yang diblokir tidak dikirimi', function () {
    $admin = orangKegiatan();
    $diblokir = orangKegiatan(['view_kegiatan']);
    $diblokir->update(['status' => 'blokir']);

    Livewire::actingAs($admin)->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Rapat produk')
        ->set('tanggalMulai', '2026-09-20')
        ->set('jamMulai', '09:00')
        ->set('peserta', [$diblokir->id])
        ->call('simpan')
        ->assertHasNoErrors();

    Mail::assertNothingSent();
});
