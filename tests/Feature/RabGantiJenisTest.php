<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabForm;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Jenis perjalanan BOLEH diganti saat RAB diubah.
 *
 * Dulu tombolnya dimatikan di mode ubah dengan alasan "harga, modal, dan
 * titik impas sudah dihitung atas dasarnya". Alasannya benar separuh: harga
 * memang dihitung atas dasar jenisnya — tetapi dihitung HIDUP, tidak pernah
 * dibekukan, dan Orcha sendiri menerima pergantiannya pada jalur ubah.
 *
 * Akibatnya RAB yang salah pilih jenis sejak awal harus disusun ulang dari
 * nol, lengkap dengan destinasi dan seluruh biayanya.
 *
 * Yang dijaga di sini: boleh diganti, dan akibatnya dikatakan.
 */
function rabJenisOrang(): User
{
    $peran = Role::firstOrCreate(['name' => 'uji-rab-jenis'], ['description' => 'Peran uji']);

    $izin = Permission::firstOrCreate(
        ['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']
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

function rabJenisSetel(array $rab = []): void
{
    // Alamat LENGKAP sampai /api/v1 — tanpa itu pola fake tidak pernah cocok.
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    Cache::flush();

    $isi = array_merge([
        'id' => 25,
        'kode' => 'RAB-2611-MZBF',
        'judul' => 'Open Trip Dieng 2H1M',
        'jenis' => 'open',
        'status' => 'draf',
        'nama_pelanggan' => '',
        'provinsi' => 'Jawa Tengah',
        'daerah' => 'Dieng',
        'tanggal_mulai' => '2026-12-12',
        'jumlah_hari' => 2,
        'jumlah_malam' => 1,
        'jumlah_peserta' => 20,
        'peserta_minimum' => 10,
        'harga_dasar' => 'minimum',
        'itinerary' => [],
    ], $rab);

    Http::fake([
        '*/api/v1/rab/25' => Http::response(['data' => $isi], 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

test('tombol jenis tidak lagi dimatikan saat RAB diubah', function () {
    rabJenisSetel();

    Livewire::actingAs(rabJenisOrang())->test(OrchaRabForm::class, ['rab' => 25])
        ->assertSet('ubah', true)
        // Dulu markahnya memuat `disabled` pada kedua tombol jenis, sehingga
        // RAB yang salah pilih jenis harus disusun ulang dari nol.
        ->assertDontSee('Tidak bisa diganti di sini');
});

test('mengganti jenis memperingatkan bahwa harga dihitung ulang', function () {
    rabJenisSetel();

    Livewire::actingAs(rabJenisOrang())->test(OrchaRabForm::class, ['rab' => 25])
        // Belum diganti: tidak ada peringatan apa pun.
        ->assertDontSee('Harga per orang akan dihitung ulang')
        ->set('isian.jenis', 'private')
        ->assertSee('Harga per orang akan dihitung ulang')
        // Dan disebutkan APA yang berubah, bukan sekadar "harga berubah".
        ->assertSee('nama pelanggan jadi wajib diisi');
});

test('kembali ke jenis semula membuang peringatannya', function () {
    rabJenisSetel();

    Livewire::actingAs(rabJenisOrang())->test(OrchaRabForm::class, ['rab' => 25])
        ->set('isian.jenis', 'private')
        ->assertSee('Harga per orang akan dihitung ulang')
        ->set('isian.jenis', 'open')
        ->assertDontSee('Harga per orang akan dihitung ulang');
});

test('penawaran yang sudah dikirim diperingatkan lebih keras', function () {
    // Pelanggan sudah memegang angka yang lama. Mengubah dasar harganya tanpa
    // mengatakan itu berarti dua pihak memegang dua angka berbeda.
    rabJenisSetel(['status' => 'dikirim']);

    Livewire::actingAs(rabJenisOrang())->test(OrchaRabForm::class, ['rab' => 25])
        ->set('isian.jenis', 'private')
        ->assertSee('sudah dikirim ke pelanggan');
});

test('RAB baru tidak pernah memunculkan peringatan itu', function () {
    rabJenisSetel();

    // Tidak ada jenis tersimpan yang bisa diganti — barangnya belum ada.
    Livewire::actingAs(rabJenisOrang())->test(OrchaRabForm::class)
        ->set('isian.jenis', 'open')
        ->assertDontSee('Harga per orang akan dihitung ulang');
});
