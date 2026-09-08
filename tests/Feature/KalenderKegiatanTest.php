<?php

use App\Livewire\Pages\Admin\Kegiatan\KegiatanKalender;
use App\Models\EmployeeDetail;
use App\Models\Kegiatan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

/**
 * Kalender kegiatan bersama.
 *
 * Yang dijaga di sini bukan tampilannya, melainkan hal-hal yang diam-diam salah:
 * kisi yang tidak menutup satu bulan penuh, kegiatan yang tersimpan di bulan lain
 * dari yang sedang dilihat, dan izin yang bisa ditembus lewat pemanggilan langsung.
 */
function penggunaKalender(array $izin): User
{
    $peran = Role::create(['name' => 'uji-kegiatan-'.uniqid(), 'description' => 'Peran uji kalender']);

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'kegiatan', 'description' => 'uji']
        );
        $peran->permissions()->attach($p->id);
    }

    $user = User::factory()->create(['role_id' => $peran->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

it('kisi kalender selalu mulai Senin dan menutup seluruh bulan', function () {
    // September 2026 mulai hari Selasa — kasus yang paling mudah salah.
    Carbon::setTestNow('2026-09-15 08:00:00');

    $minggu = Livewire::actingAs(penggunaKalender(['view_kegiatan']))
        ->test(KegiatanKalender::class)
        ->viewData('minggu');

    $rata = collect($minggu)->flatMap(fn ($m) => $m['hari']);

    expect($rata->first()['tanggal'])->toBe('2026-08-31')  // Senin sebelum tanggal 1
        ->and($rata->last()['tanggal'])->toBe('2026-10-04') // Minggu setelah tanggal 30
        ->and($rata->count() % 7)->toBe(0)
        ->and($rata->where('bulanIni', true)->count())->toBe(30);

    Carbon::setTestNow();
});

it('menyimpan kegiatan memindahkan kalender ke bulan kegiatannya', function () {
    Carbon::setTestNow('2026-09-15 08:00:00');

    Livewire::actingAs(penggunaKalender(['view_kegiatan', 'create_kegiatan']))
        ->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Rapat kuartal')
        ->set('tanggalMulai', '2026-11-03')
        ->set('jamMulai', '10:00')
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertSet('bulan', 11)
        ->assertSet('tahun', 2026)
        ->assertSet('tanggalTerpilih', '2026-11-03');

    expect(Kegiatan::where('judul', 'Rapat kuartal')->first()->mulai->format('Y-m-d H:i'))
        ->toBe('2026-11-03 10:00');

    Carbon::setTestNow();
});

it('tanggal selesai boleh kosong untuk kegiatan yang berakhir di hari yang sama', function () {
    Livewire::actingAs(penggunaKalender(['view_kegiatan', 'create_kegiatan']))
        ->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Briefing pagi')
        ->set('tanggalMulai', '2026-09-10')
        ->set('jamMulai', '08:00')
        ->set('jamSelesai', '09:30')
        ->call('simpan')
        ->assertHasNoErrors();

    $k = Kegiatan::where('judul', 'Briefing pagi')->first();

    expect($k->selesai->format('Y-m-d H:i'))->toBe('2026-09-10 09:30');
});

it('menolak waktu selesai yang mendahului waktu mulai', function () {
    Livewire::actingAs(penggunaKalender(['view_kegiatan', 'create_kegiatan']))
        ->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Terbalik')
        ->set('tanggalMulai', '2026-09-10')
        ->set('jamMulai', '14:00')
        ->set('jamSelesai', '09:00')
        ->call('simpan')
        ->assertHasErrors('jamSelesai');

    expect(Kegiatan::where('judul', 'Terbalik')->exists())->toBeFalse();
});

it('kegiatan seharian tidak wajib mengisi jam', function () {
    Livewire::actingAs(penggunaKalender(['view_kegiatan', 'create_kegiatan']))
        ->test(KegiatanKalender::class)
        ->call('buatBaru')
        ->set('judul', 'Libur bersama')
        ->set('jenis', 'libur')
        ->set('seharian', true)
        ->set('tanggalMulai', '2026-09-12')
        ->set('jamMulai', '')
        ->call('simpan')
        ->assertHasNoErrors();

    $k = Kegiatan::where('judul', 'Libur bersama')->first();

    expect($k->seharian)->toBeTrue()
        ->and($k->rentangWaktu())->toBe('Seharian');
});

it('tanpa izin tambah, memanggil simpan langsung pun tidak membuat apa pun', function () {
    Livewire::actingAs(penggunaKalender(['view_kegiatan']))
        ->test(KegiatanKalender::class)
        ->set('judul', 'Selundupan')
        ->set('tanggalMulai', '2026-09-10')
        ->set('jamMulai', '10:00')
        ->call('simpan');

    expect(Kegiatan::count())->toBe(0);
});

it('tanpa izin hapus, memanggil hapus langsung pun tidak menghapus', function () {
    $pemilik = penggunaKalender(['view_kegiatan']);

    $k = Kegiatan::create([
        'judul' => 'Rapat penting', 'jenis' => 'rapat',
        'mulai' => '2026-09-10 09:00:00', 'dibuat_oleh' => $pemilik->id,
    ]);

    Livewire::actingAs($pemilik)
        ->test(KegiatanKalender::class)
        ->call('hapus', $k->id);

    expect(Kegiatan::whereKey($k->id)->exists())->toBeTrue();
});

it('saringan "saya saja" hanya menampilkan kegiatan yang saya buat atau saya ikuti', function () {
    Carbon::setTestNow('2026-09-15 08:00:00');

    $saya = penggunaKalender(['view_kegiatan']);
    $lain = penggunaKalender(['view_kegiatan']);

    Kegiatan::create(['judul' => 'Punya saya', 'jenis' => 'rapat', 'mulai' => '2026-09-10 09:00:00', 'dibuat_oleh' => $saya->id]);
    Kegiatan::create(['judul' => 'Punya orang lain', 'jenis' => 'rapat', 'mulai' => '2026-09-11 09:00:00', 'dibuat_oleh' => $lain->id]);

    $diundang = Kegiatan::create(['judul' => 'Saya diundang', 'jenis' => 'rapat', 'mulai' => '2026-09-12 09:00:00', 'dibuat_oleh' => $lain->id]);
    $diundang->peserta()->attach($saya->id);

    Livewire::actingAs($saya)
        ->test(KegiatanKalender::class)
        ->set('hanyaSaya', true)
        ->assertSee('Punya saya')
        ->assertSee('Saya diundang')
        ->assertDontSee('Punya orang lain');

    Carbon::setTestNow();
});

it('menghapus kegiatan ikut melepas pesertanya', function () {
    $user = penggunaKalender(['view_kegiatan', 'delete_kegiatan']);

    $k = Kegiatan::create(['judul' => 'Dibatalkan', 'jenis' => 'rapat', 'mulai' => '2026-09-10 09:00:00', 'dibuat_oleh' => $user->id]);
    $k->peserta()->attach($user->id);

    Livewire::actingAs($user)->test(KegiatanKalender::class)->call('hapus', $k->id);

    expect(Kegiatan::count())->toBe(0)
        ->and(DB::table('kegiatan_peserta')->where('kegiatan_id', $k->id)->count())->toBe(0);
});

it('halaman kalender tertutup bagi yang tidak punya izin melihat', function () {
    $this->actingAs(penggunaKalender([]))
        ->get(route('admin.kegiatan.index'))
        ->assertForbidden();
});
