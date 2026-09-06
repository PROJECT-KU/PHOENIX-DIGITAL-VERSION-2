<?php

use App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranCek;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Melampirkan bukti susulan pada catatan pembayaran yang sudah ada.
 *
 * Sebelum ini satu-satunya jalur yang menerima berkas adalah pencatatan
 * pembayaran BARU. Admin yang lupa melampirkan buktinya tinggal punya dua
 * pilihan, dan dua-duanya buruk: mencatat ulang — yang menghitung uangnya dua
 * kali sehingga tagihannya salah — atau membiarkannya tanpa gambar, sehingga
 * tidak ada yang bisa ditelusuri kalau suatu saat dipersoalkan.
 *
 * Fitur yang sama sudah lama ada untuk bukti pesanan Phoenix; yang kurang
 * justru sisi Orcha.
 */
function adminBuktiSusulan(): User
{
    $role = Role::create(['name' => 'uji-bukti-'.uniqid(), 'description' => 'Peran untuk uji bukti']);

    $izin = Permission::firstOrCreate(['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']);
    $role->permissions()->attach($izin->id);

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function buktiPalsu(array $ubah = []): array
{
    return array_merge([
        'id' => 5, 'kode' => 'OT-0309-K7QMXV', 'jenis' => 'dp',
        'jenis_label' => 'Uang Muka (DP)', 'nominal' => 1250000,
        'nominal_formatted' => 'Rp 1.250.000',
        'tanggal_transfer' => now()->subDay()->toDateString(),
        'bank_pengirim' => 'BCA', 'atas_nama_pengirim' => 'SMA Negeri 3',
        'bukti' => null, 'bukti_riwayat' => [],
        'catatan' => null, 'status' => 'diterima', 'status_label' => 'Diterima',
        'catatan_admin' => null, 'pesanan' => null,
        'dibuat_pada' => now()->toIso8601String(),
    ], $ubah);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake(function ($permintaan) {
        if (str_contains($permintaan->url(), '/bukti') && $permintaan->method() === 'POST') {
            return Http::response([
                'pesan' => 'Bukti transfer dilampirkan.',
                'data' => buktiPalsu(['bukti' => '/storage/bukti-bayar/baru.webp']),
            ]);
        }

        if (str_contains($permintaan->url(), '/rujukan')) {
            return Http::response(['data' => ['status_pembayaran' => ['diterima' => 'Diterima']]]);
        }

        return Http::response(['data' => config('uji.bukti', buktiPalsu())]);
    });
});

test('isian unggah muncul walau catatannya belum punya bukti', function () {
    /*
     | Justru itu keadaan yang fiturnya dibuat untuknya. Sebelumnya layar cuma
     | menyebut "pelanggan tidak melampirkan berkas" dan berhenti di situ —
     | tidak ada jalan keluar apa pun dari layar itu.
     */
    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->assertSee('Lampirkan bukti susulan');
});

test('kalimatnya berubah jadi "ganti" saat buktinya sudah ada', function () {
    // Dua tindakan yang berbeda akibatnya: satu melengkapi catatan yang
    // kurang, satu mengubah bukti yang sudah pernah dipakai memutuskan.
    config()->set('uji.bukti', buktiPalsu(['bukti' => '/storage/bukti-bayar/lama.webp']));

    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->assertSee('Ganti bukti')
        ->assertSee('Yang lama tetap tersimpan');
});

test('berkas yang bukan gambar ditahan di layar, bukan setelah terkirim', function () {
    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->set('buktiBaru', UploadedFile::fake()->create('daftar.pdf', 100, 'application/pdf'))
        ->call('unggahBukti')
        ->assertHasErrors('buktiBaru');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), '/bukti') && $p->method() === 'POST');
});

test('tanpa berkas, tidak ada yang dikirim', function () {
    // Jalur ini tidak punya guna lain selain melampirkan berkasnya.
    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->call('unggahBukti')
        ->assertHasErrors('buktiBaru');
});

test('gambar yang sah terkirim sebagai multipart bernama bukti', function () {
    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->set('buktiBaru', UploadedFile::fake()->image('mutasi.jpg'))
        ->call('unggahBukti')
        ->assertHasNoErrors()
        // Dilepas setelah terkirim: berkas yang tertinggal ikut terunggah lagi
        // saat Livewire menggambar ulang.
        ->assertSet('buktiBaru', null);

    Http::assertSent(fn ($p) => str_contains($p->url(), '/pembayaran/5/bukti')
        && $p->method() === 'POST'
        && $p->hasFile('bukti'));
});

test('buktinya disegarkan dari jawaban Orcha, bukan ditebak', function () {
    /*
     | Alamat gambarnya dirakit di Orcha. Menebaknya di lemon berarti tautan
     | yang salah begitu jalur penyimpanannya berubah — dan yang melihat
     | gambar rusak menyimpulkan buktinya hilang.
     */
    $halaman = Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->set('buktiBaru', UploadedFile::fake()->image('mutasi.jpg'))
        ->call('unggahBukti');

    expect($halaman->get('bukti')['bukti'])->toBe('/storage/bukti-bayar/baru.webp');
});

test('mengunggah bukti TIDAK ikut mengubah status', function () {
    /*
     | Menyimpan status mengirim email ke pelanggan yang tidak bisa ditarik
     | kembali; melampirkan bukti tidak mengabari siapa pun. Menggabungkannya
     | berarti admin yang cuma ingin menyusulkan gambar ikut mengirim surat.
     */
    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->set('buktiBaru', UploadedFile::fake()->image('mutasi.jpg'))
        ->call('unggahBukti');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), '/status'));
});

test('riwayat penggantian tergambar beserta pelakunya', function () {
    /*
     | Catatan uang yang buktinya berganti diam-diam adalah hal yang paling
     | sulit dijelaskan saat dipersoalkan — dan yang mempersoalkannya biasanya
     | bukan kita.
     */
    config()->set('uji.bukti', buktiPalsu([
        'bukti' => '/storage/bukti-bayar/baru.webp',
        'bukti_riwayat' => [[
            'bukti' => '/storage/bukti-bayar/lama.webp',
            'diganti_pada' => now()->subHour()->toIso8601String(),
            'oleh' => 'Asthana',
        ]],
    ]));

    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->assertSee('Bukti sebelumnya (1)')
        ->assertSee('oleh Asthana');
});

test('tanpa riwayat, tidak ada bagian yang menganga', function () {
    // Bagian kosong berjudul "Bukti sebelumnya (0)" membuat orang mencari
    // sesuatu yang memang tidak ada.
    Livewire::actingAs(adminBuktiSusulan())
        ->test(OrchaPembayaranCek::class, ['pembayaran' => 5])
        ->assertDontSee('Bukti sebelumnya');
});
