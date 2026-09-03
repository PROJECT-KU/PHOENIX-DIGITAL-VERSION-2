<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Mencatat pembayaran yang diterima admin sendiri.
 *
 * Private trip dan study tour tidak lewat formulir konfirmasi publik. Panitia
 * mentransfer lalu mengabari lewat WhatsApp, kadang cuma dengan kalimat "sudah
 * ditransfer ya" tanpa tangkapan layar. Yang memastikan uangnya benar-benar
 * masuk adalah admin yang membuka mutasi rekening — dan sebelum ini
 * pemeriksaan itu tidak punya tempat pulang.
 */
function adminBayar(): User
{
    $role = Role::create(['name' => 'uji-bayar-'.uniqid(), 'description' => 'Peran untuk uji bayar']);

    foreach (['akses_orcha', 'view_orcha_kesehatan'] as $nama) {
        $izin = Permission::firstOrCreate(['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']);
        $role->permissions()->attach($izin->id);
    }

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function pendaftaranPalsu(array $ubah = []): array
{
    return array_merge([
        'id' => 7, 'kode' => 'OT-0309-K7QMXV', 'nama' => 'Panitia Sekolah',
        'whatsapp' => '081234567890', 'email' => null,
        'jumlah_peserta' => 42, 'pendamping_gratis' => 2, 'peserta_dibayar' => 40,
        'peserta' => [], 'jemput_per_titik' => [],
        'bus_per_kelompok' => [], 'kamar_per_kelompok' => [],
        'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => [],
        'paket' => ['id' => 5, 'nama' => 'Study Tour SMA', 'titik_jemput' => []],
        'tanggal_berangkat' => now()->addDays(20)->toDateString(),
        'hari_ke_berangkat' => 20, 'pengingat_pelunasan_pada' => null,
        'titik_jemput' => 'Halaman sekolah', 'catatan' => null,
        'status' => 'baru', 'status_label' => 'Baru',
        'riwayat_penggantian' => [], 'surat_penggantian' => null,
        'surat_penggantian_pada' => null,
        'tautan_kesehatan' => 'https://orchajourney.com/riwayat-kesehatan?kode=OT-0309-K7QMXV',
        'dibuat_pada' => now()->toIso8601String(),
        'tagihan' => [
            'total' => 20000000, 'total_teks' => 'Rp 20.000.000',
            'sudah' => 0, 'sudah_teks' => 'Rp 0',
            'sisa' => 20000000, 'sisa_teks' => 'Rp 20.000.000',
            'dp' => 5000000, 'dp_teks' => 'Rp 5.000.000', 'dp_persen' => 25,
            'lunas' => config('uji.lunas', false), 'jenis_disarankan' => 'dp',
        ],
        'pembayaran' => [], 'pembatalan' => null,
    ], $ubah);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake(function ($permintaan) {
        if (str_contains($permintaan->url(), '/pembayaran') && $permintaan->method() === 'POST') {
            return Http::response(['pesan' => 'Pembayaran dicatat. Status pesanan ikut menjadi DP Masuk.'], 201);
        }

        if (str_contains($permintaan->url(), '/rujukan')) {
            return Http::response(['data' => [
                'status_pendaftaran' => ['baru' => 'Baru', 'dp_masuk' => 'DP Masuk'],
                'jenis_pembayaran' => ['dp' => 'Uang Muka (DP)', 'pelunasan' => 'Pelunasan'],
                'pembayaran' => ['pelunasan_hari_sebelum' => 5],
                'paket_wisata' => [],
            ]]);
        }

        return Http::response(['data' => pendaftaranPalsu()]);
    });
});

test('tombolnya ada selama belum lunas', function () {
    Livewire::actingAs(adminBayar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->assertSee('Catat pembayaran diterima');
});

test('tombolnya hilang begitu lunas', function () {
    /*
     | Tombol yang selalu ada tetapi tidak selalu berguna hanya menambah benda
     | yang harus diabaikan mata — dan mencatat pembayaran pada pesanan yang
     | sudah lunas hampir selalu salah orang.
     */
    config()->set('uji.lunas', true);

    Livewire::actingAs(adminBayar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->assertDontSee('Catat pembayaran diterima');
});

test('atas nama pengirim terisi si pemesan lebih dulu', function () {
    // Yang paling sering benar tidak perlu diketik tiap kali.
    Livewire::actingAs(adminBayar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->call('bukaFormulirBayar')
        ->assertSet('bayar.atas_nama_pengirim', 'Panitia Sekolah')
        ->assertSet('bayar.tanggal_transfer', now()->toDateString());
});

test('nominal, bank, dan atas nama wajib', function () {
    /*
     | Bank dan atas nama yang dipakai mencocokkan dengan mutasi rekening saat
     | ada yang mempersoalkan. Catatan uang tanpa asal-usulnya cuma angka yang
     | harus dipercaya.
     */
    Livewire::actingAs(adminBayar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->call('bukaFormulirBayar')
        ->set('bayar.atas_nama_pengirim', '')
        ->call('catatBayar')
        ->assertHasErrors(['bayar.nominal', 'bayar.bank_pengirim', 'bayar.atas_nama_pengirim']);

    Http::assertNotSent(fn ($p) => $p->method() === 'POST');
});

test('tanggal transfer di masa depan ditolak sebelum dikirim', function () {
    // Transfer yang belum terjadi bukan transfer. Angka yang masuk lebih awal
    // membuat laporan bulan ini menyebut uang yang baru akan datang.
    Livewire::actingAs(adminBayar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->call('bukaFormulirBayar')
        ->set('bayar.nominal', '5000000')
        ->set('bayar.bank_pengirim', 'BCA')
        ->set('bayar.tanggal_transfer', now()->addDays(3)->toDateString())
        ->call('catatBayar')
        ->assertHasErrors('bayar.tanggal_transfer');
});

test('yang sah terkirim ke Orcha lalu formulirnya tertutup', function () {
    Livewire::actingAs(adminBayar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->call('bukaFormulirBayar')
        ->set('bayar.nominal', '5000000')
        ->set('bayar.bank_pengirim', 'BCA')
        ->set('bayar.jenis', 'dp')
        ->call('catatBayar')
        ->assertHasNoErrors()
        ->assertSet('bukaBayar', false);

    Http::assertSent(fn ($p) => str_contains($p->url(), '/pendaftaran/7/pembayaran')
        && $p->method() === 'POST'
        && $p['nominal'] === 5000000
        && $p['bank_pengirim'] === 'BCA');
});

test('peserta yang ditagih disebut terpisah saat ada pendamping gratis', function () {
    /*
     | "42 orang" pada rombongan yang dua di antaranya guru pendamping gratis
     | membuat siapa pun mengalikannya dengan harga satuan dan mendapat angka
     | yang tidak cocok dengan total tagihan di sebelahnya — lalu menyimpulkan
     | salah satunya salah.
     */
    Livewire::actingAs(adminBayar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->assertSee('42 orang · 40 ditagih');
});
