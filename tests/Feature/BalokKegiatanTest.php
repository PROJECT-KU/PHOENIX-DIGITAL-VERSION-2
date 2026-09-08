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
 * Kegiatan yang membentang beberapa hari tampil sebagai SATU balok.
 *
 * Yang dijaga di sini justru hal-hal yang tidak terlihat salah di layar sampai
 * datanya kebetulan pas: balok yang terpotong batas minggu, dua balok yang
 * saling menimpa karena berbagi lajur, dan kegiatan bulan sebelah yang
 * kakinya masuk ke bulan ini.
 */
function orangBalok(): User
{
    $peran = Role::firstOrCreate(['name' => 'uji-balok'], ['description' => 'Peran uji balok']);

    $izin = Permission::firstOrCreate(
        ['name' => 'view_kegiatan'],
        ['display_name' => 'view_kegiatan', 'group' => 'kegiatan', 'description' => 'uji']
    );

    if (! $peran->permissions()->where('permissions.id', $izin->id)->exists()) {
        $peran->permissions()->attach($izin->id);
    }

    $user = User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

/** Seluruh balok September 2026 dari sudut pandang $user. */
function balokSeptember(User $user): array
{
    Carbon::setTestNow('2026-09-08 08:00:00');

    $minggu = Livewire::actingAs($user)->test(KegiatanKalender::class)->viewData('minggu');

    return collect($minggu)->flatMap(fn ($m) => $m['balok'])->all();
}

it('kegiatan 8-9 September jadi satu balok selebar dua kolom', function () {
    $u = orangBalok();

    Kegiatan::create([
        'judul' => 'Rapat dua hari', 'jenis' => 'rapat',
        'mulai' => '2026-09-08 09:00:00', 'selesai' => '2026-09-09 16:00:00',
        'dibuat_oleh' => $u->id,
    ]);

    $balok = balokSeptember($u);

    expect($balok)->toHaveCount(1);

    // 8 September 2026 adalah Selasa: kolom ke-1 bila minggu mulai Senin.
    expect($balok[0]['kolom'])->toBe(1)
        ->and($balok[0]['rentang'])->toBe(2)
        ->and($balok[0]['sambungKiri'])->toBeFalse()
        ->and($balok[0]['sambungKanan'])->toBeFalse();

    Carbon::setTestNow();
});

it('kegiatan sehari tetap satu kolom', function () {
    $u = orangBalok();

    Kegiatan::create([
        'judul' => 'Rapat sebentar', 'jenis' => 'rapat',
        'mulai' => '2026-09-11 09:00:00', 'selesai' => '2026-09-11 10:00:00',
        'dibuat_oleh' => $u->id,
    ]);

    $balok = balokSeptember($u);

    expect($balok[0]['rentang'])->toBe(1);

    Carbon::setTestNow();
});

it('tanpa jam selesai, kegiatan tetap satu kolom penuh dan tidak lenyap', function () {
    $u = orangBalok();

    // Jam selesainya kosong: tanpa penanganan khusus, kegiatan ini berdurasi
    // nol dan hilang dari kisi begitu jamnya lewat.
    $k = Kegiatan::create([
        'judul' => 'Belum pasti selesainya', 'jenis' => 'rapat',
        'mulai' => '2026-09-11 09:00:00', 'dibuat_oleh' => $u->id,
    ]);

    expect($k->akhirEfektif()->format('Y-m-d H:i'))->toBe('2026-09-11 23:59')
        ->and($k->beberapaHari())->toBeFalse();

    $balok = balokSeptember($u);

    expect($balok)->toHaveCount(1)
        ->and($balok[0]['rentang'])->toBe(1);

    Carbon::setTestNow();
});

it('kegiatan yang melewati batas minggu dipecah jadi dua balok bersambung', function () {
    $u = orangBalok();

    // Sabtu 12 s/d Selasa 15 September: melompati pergantian minggu.
    Kegiatan::create([
        'judul' => 'Kunjungan empat hari', 'jenis' => 'acara',
        'mulai' => '2026-09-12 08:00:00', 'selesai' => '2026-09-15 17:00:00',
        'dibuat_oleh' => $u->id,
    ]);

    $balok = balokSeptember($u);

    expect($balok)->toHaveCount(2);

    // Potongan pertama: Sabtu–Minggu, berakhir di tepi kanan minggu.
    expect($balok[0]['kolom'])->toBe(5)
        ->and($balok[0]['rentang'])->toBe(2)
        ->and($balok[0]['sambungKiri'])->toBeFalse()
        ->and($balok[0]['sambungKanan'])->toBeTrue();

    // Potongan kedua: Senin–Selasa, menyambung dari minggu sebelumnya.
    expect($balok[1]['kolom'])->toBe(0)
        ->and($balok[1]['rentang'])->toBe(2)
        ->and($balok[1]['sambungKiri'])->toBeTrue()
        ->and($balok[1]['sambungKanan'])->toBeFalse();

    Carbon::setTestNow();
});

it('dua kegiatan yang tanggalnya beririsan tidak berbagi lajur', function () {
    $u = orangBalok();

    Kegiatan::create(['judul' => 'A', 'jenis' => 'rapat', 'mulai' => '2026-09-08 09:00:00', 'selesai' => '2026-09-10 17:00:00', 'dibuat_oleh' => $u->id]);
    Kegiatan::create(['judul' => 'B', 'jenis' => 'acara', 'mulai' => '2026-09-09 09:00:00', 'selesai' => '2026-09-11 17:00:00', 'dibuat_oleh' => $u->id]);

    $balok = collect(balokSeptember($u))->keyBy(fn ($b) => $b['kegiatan']->judul);

    expect($balok['A']['lajur'])->not->toBe($balok['B']['lajur']);

    Carbon::setTestNow();
});

it('kegiatan yang tidak beririsan boleh berbagi lajur teratas', function () {
    $u = orangBalok();

    Kegiatan::create(['judul' => 'A', 'jenis' => 'rapat', 'mulai' => '2026-09-08 09:00:00', 'selesai' => '2026-09-09 17:00:00', 'dibuat_oleh' => $u->id]);
    Kegiatan::create(['judul' => 'B', 'jenis' => 'acara', 'mulai' => '2026-09-11 09:00:00', 'selesai' => '2026-09-12 17:00:00', 'dibuat_oleh' => $u->id]);

    $balok = collect(balokSeptember($u))->keyBy(fn ($b) => $b['kegiatan']->judul);

    expect($balok['A']['lajur'])->toBe(0)
        ->and($balok['B']['lajur'])->toBe(0);

    Carbon::setTestNow();
});

it('kegiatan bulan sebelah yang kakinya masuk bulan ini tetap terlihat', function () {
    $u = orangBalok();

    // Mulai 30 Agustus, selesai 2 September: mulainya di luar bulan yang dibuka.
    Kegiatan::create([
        'judul' => 'Menyeberang bulan', 'jenis' => 'acara',
        'mulai' => '2026-08-30 08:00:00', 'selesai' => '2026-09-02 17:00:00',
        'dibuat_oleh' => $u->id,
    ]);

    expect(balokSeptember($u))->not->toBeEmpty();

    Carbon::setTestNow();
});

it('kegiatan keempat pada hari yang sama diringkas jadi "+1 lagi"', function () {
    $u = orangBalok();

    foreach (range(1, 4) as $i) {
        Kegiatan::create([
            'judul' => 'Rapat '.$i, 'jenis' => 'rapat',
            'mulai' => sprintf('2026-09-08 %02d:00:00', 8 + $i),
            'dibuat_oleh' => $u->id,
        ]);
    }

    Carbon::setTestNow('2026-09-08 08:00:00');

    $minggu = Livewire::actingAs($u)->test(KegiatanKalender::class)->viewData('minggu');

    $mingguKedua = collect($minggu)->first(fn ($m) => collect($m['balok'])->isNotEmpty());

    expect(collect($mingguKedua['balok']))->toHaveCount(KegiatanKalender::LAJUR_MAKS)
        ->and($mingguKedua['lebih'][1])->toBe(1); // Selasa, kolom ke-1

    Carbon::setTestNow();
});

it('memilih tanggal 9 menampilkan kegiatan yang mulai tanggal 8', function () {
    $u = orangBalok();

    Kegiatan::create([
        'judul' => 'Rapat dua hari', 'jenis' => 'rapat',
        'mulai' => '2026-09-08 09:00:00', 'selesai' => '2026-09-09 16:00:00',
        'dibuat_oleh' => $u->id,
    ]);

    Carbon::setTestNow('2026-09-08 08:00:00');

    Livewire::actingAs($u)->test(KegiatanKalender::class)
        ->call('pilihTanggal', '2026-09-09')
        ->assertSee('Rapat dua hari');

    Carbon::setTestNow();
});
