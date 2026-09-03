<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Saringan "perlu ditagih" di layar Pendaftaran.
 *
 * Pengingat pelunasan sudah dikirim Orcha tiap pagi. Yang tersisa di daftar
 * ini justru yang sudah dikirimi dan tetap diam — dan itu cuma bisa
 * diselesaikan lewat telepon.
 *
 * Sebelum ada saringan ini, satu-satunya cara menemukannya adalah membuka
 * pendaftaran satu per satu dan menghitung tanggalnya di kepala. Pekerjaannya
 * cukup melelahkan sehingga tidak pernah benar-benar dikerjakan.
 */
function adminTagihan(): User
{
    $role = Role::create(['name' => 'uji-tagih-'.uniqid(), 'description' => 'Peran untuk uji tagihan']);

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

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake([
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['baru' => 'Baru', 'dp_masuk' => 'DP Masuk', 'lunas' => 'Lunas'],
            'paket_wisata' => [],
        ]]),
        '*' => Http::response(['data' => [[
            'id' => 7, 'kode' => 'OT-0309-A1B2', 'nama' => 'Budi', 'whatsapp' => '0812',
            'jumlah_peserta' => 2, 'peserta' => [], 'jemput_per_titik' => [],
            'paket' => ['id' => 1, 'nama' => 'Trip Uji', 'titik_jemput' => []],
            'titik_jemput' => 'Terminal Bungurasih',
            'catatan' => null,
            'tanggal_berangkat' => now()->addDays(3)->toDateString(),
            'hari_ke_berangkat' => 3,
            'pengingat_pelunasan_pada' => null,
            'status' => 'dp_masuk', 'status_label' => 'DP Masuk',
            'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => [],
            'dibuat_pada' => now()->toIso8601String(),
        ]], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 1, 'halaman_terakhir' => 1]]),
    ]);
});

test('saringan tagihan mati secara bawaan', function () {
    // Kalau menyala sendiri, layar Pendaftaran mendadak menyembunyikan
    // sebagian besar isinya dan tidak ada yang tahu kenapa.
    Livewire::actingAs(adminTagihan())
        ->test(OrchaPendaftaranList::class)
        ->assertSet('perluDitagih', false);
});

test('menyalakannya mengirim perlu_ditagih ke Orcha', function () {
    Livewire::actingAs(adminTagihan())
        ->test(OrchaPendaftaranList::class)
        ->set('perluDitagih', true);

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/pendaftaran')
        && str_contains($permintaan->url(), 'perlu_ditagih=1'));
});

test('saat mati, parameternya TIDAK ikut terkirim', function () {
    /*
     | Mengirim perlu_ditagih=0 membuat Orcha membaca parameternya ada. Di sana
     | saringannya dibaca dengan boolean(), jadi "0" memang mati — tetapi
     | mengirim yang tidak perlu berarti perilakunya bergantung pada bagaimana
     | pihak seberang menafsirkan nol, dan itu bukan hal yang layak
     | dipertaruhkan diam-diam.
     */
    Livewire::actingAs(adminTagihan())->test(OrchaPendaftaranList::class);

    Http::assertSent(fn ($permintaan) => ! str_contains($permintaan->url(), 'perlu_ditagih'));
});

test('bersihkan saringan ikut mematikannya', function () {
    Livewire::actingAs(adminTagihan())
        ->test(OrchaPendaftaranList::class)
        ->set('perluDitagih', true)
        ->assertSee('Bersihkan')
        ->call('bersihkanSaringan')
        ->assertSet('perluDitagih', false);
});

test('menyalakannya mengembalikan daftar ke halaman satu', function () {
    // Menyala di halaman tiga menghasilkan layar kosong, dan yang melihatnya
    // menyimpulkan tidak ada yang perlu ditagih.
    Livewire::actingAs(adminTagihan())
        ->test(OrchaPendaftaranList::class)
        ->call('keHalaman', 3)
        ->set('perluDitagih', true)
        ->assertSet('halaman', 1);
});

test('keadaan pengingat terlihat hanya saat daftar tagihan menyala', function () {
    /*
     | Yang mengangkat telepon perlu tahu mana yang sudah menerima surat dan
     | tetap diam. Menanyakan "sudah terima email kami?" kepada orang yang
     | belum dikirimi membuat kita terdengar seperti sedang mengarang.
     */
    Livewire::actingAs(adminTagihan())
        ->test(OrchaPendaftaranList::class)
        ->assertDontSee('belum diingatkan')
        ->set('perluDitagih', true)
        ->assertSee('belum diingatkan');
});
