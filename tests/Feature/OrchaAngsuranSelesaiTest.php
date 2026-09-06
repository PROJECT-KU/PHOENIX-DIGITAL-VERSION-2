<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Rencana angsuran yang seluruh terminnya sudah lunas.
 *
 * Kartunya TETAP tampil — jadwalnya adalah riwayat, dan riwayat yang hilang
 * begitu lunas justru yang dicari saat ada yang dipersoalkan. Yang tidak boleh
 * tersisa adalah tindakan yang tidak lagi masuk akal terhadapnya.
 */
function adminAngsuranSelesai(): User
{
    $role = Role::create(['name' => 'uji-angs-'.uniqid(), 'description' => 'Peran uji angsuran selesai']);

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

/** @param  array<int, string>  $status  status tiap termin, berurutan */
function orchaDenganTermin(array $status): void
{
    $termin = [];

    foreach ($status as $i => $keadaan) {
        $termin[] = [
            'urutan' => $i + 1,
            'nominal' => 1_000_000,
            'nominal_teks' => 'Rp 1.000.000',
            'jatuh_tempo' => now()->addDays(10 * ($i + 1))->toDateString(),
            'kurang' => $keadaan === 'lunas' ? 0 : 1_000_000,
            'status' => $keadaan,
        ];
    }

    Http::fake([
        '*/rujukan*' => Http::response(['data' => []]),
        '*/angsuran*' => Http::response(['data' => [
            'boleh_diangsur' => false,
            'lunas' => ! in_array('menunggu', $status, true) && ! in_array('telat', $status, true),
            'maks_termin' => 3,
            'ingatkan_hari_sebelum' => 3,
            'pilihan' => [],
            'rencana' => [
                'kode' => 'OT-1508-0VCZ',
                'jumlah_termin' => count($status),
                'catatan' => null,
                'dibuat_oleh' => 'Asthana',
                'dibuat_pada' => now()->toIso8601String(),
                'termin' => $termin,
            ],
        ]]),
        '*' => Http::response(['data' => [
            'id' => 5, 'kode' => 'OT-1508-0VCZ', 'nama' => 'Joko', 'whatsapp' => '0895',
            'email' => 'joko@contoh.test',
            'jumlah_peserta' => 4, 'peserta_dibayar' => 4, 'pendamping_gratis' => 0,
            'peserta' => [], 'jemput_per_titik' => [], 'bus_per_kelompok' => [],
            'kamar_per_kelompok' => [], 'peserta_belum_isi' => [],
            'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false,
            'jumlah_riwayat_kesehatan' => 0, 'riwayat_penggantian' => [],
            'surat_penggantian' => null, 'surat_penggantian_pada' => null,
            'tautan_kesehatan' => 'https://orcha.test/riwayat-kesehatan/OT-1508-0VCZ',
            'titik_jemput' => 'Malioboro', 'catatan' => null,
            'hari_ke_berangkat' => 40, 'pengingat_pelunasan_pada' => null,
            'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi', 'titik_jemput' => []],
            'status' => 'lunas', 'status_label' => 'Lunas',
            'tanggal_berangkat' => now()->addDays(40)->toDateString(),
            'dibuat_pada' => now()->toIso8601String(),
            'keuntungan' => [
                'jual_satuan' => 1_430_000, 'modal_satuan' => 1_400_000,
                'margin_satuan' => 30_000, 'omzet' => 5_720_000, 'potongan_promo' => 0,
                'biaya_tetap' => 0, 'modal_per_kepala' => 1_400_000,
                'modal' => 5_600_000, 'untung' => 120_000,
                'modal_terisi' => true, 'dihitung' => true,
            ],
            'tagihan' => [], 'pembayaran' => [], 'pembatalan' => [],
        ]]),
    ]);
}

/** Payload pendaftaran lengkap, dipakai beberapa uji di berkas ini. */
function barisPendaftaranLengkap(): array
{
    return [
        'id' => 5, 'kode' => 'OT-1508-0VCZ', 'nama' => 'Joko', 'whatsapp' => '0895',
        'email' => 'joko@contoh.test',
        'jumlah_peserta' => 4, 'peserta_dibayar' => 4, 'pendamping_gratis' => 0,
        'peserta' => [], 'jemput_per_titik' => [], 'bus_per_kelompok' => [],
        'kamar_per_kelompok' => [], 'peserta_belum_isi' => [],
        'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false,
        'jumlah_riwayat_kesehatan' => 0, 'riwayat_penggantian' => [],
        'surat_penggantian' => null, 'surat_penggantian_pada' => null,
        'tautan_kesehatan' => 'https://orcha.test/riwayat-kesehatan/OT-1508-0VCZ',
        'titik_jemput' => 'Malioboro', 'catatan' => null,
        'hari_ke_berangkat' => 40, 'pengingat_pelunasan_pada' => null,
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi', 'titik_jemput' => []],
        'status' => 'lunas', 'status_label' => 'Lunas',
        'tanggal_berangkat' => now()->addDays(40)->toDateString(),
        'dibuat_pada' => now()->toIso8601String(),
        'keuntungan' => [
            'jual_satuan' => 1_430_000, 'modal_satuan' => 1_400_000,
            'margin_satuan' => 30_000, 'omzet' => 5_720_000, 'potongan_promo' => 0,
            'biaya_tetap' => 0, 'modal_per_kepala' => 1_400_000,
            'modal' => 5_600_000, 'untung' => 120_000,
            'modal_terisi' => true, 'dihitung' => true,
        ],
        'tagihan' => [], 'pembayaran' => [], 'pembatalan' => [],
    ];
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
});

test('rencana yang sudah tuntas tidak lagi menawarkan pembatalan', function () {
    /*
     | Tidak ada yang bisa dibatalkan dari jadwal yang sudah selesai, dan
     | menawarkannya bukan sekadar tidak berguna — merusak. Yang tersisa dari
     | rencana tuntas cuma catatannya: bukti bahwa pelanggan diberi keringanan
     | dan ia menyelesaikannya.
     */
    orchaDenganTermin(['lunas', 'lunas', 'lunas']);

    Livewire::actingAs(adminAngsuranSelesai())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 5])
        ->assertDontSee('Batalkan rencana')
        // Tempatnya tidak dibiarkan kosong: baris tindakan yang tiba-tiba
        // kosong terbaca sebagai sesuatu yang gagal dimuat.
        ->assertSee('disimpan sebagai riwayat')
        // Jadwalnya sendiri TETAP tampil.
        ->assertSee('Angsuran ke-2');
});

test('lencana tidak lagi menulis berjalan untuk rencana yang selesai', function () {
    // Rencana tuntas yang dilabeli "berjalan" membuat admin mengira masih ada
    // yang perlu ditagih — dan ia menelepon orang yang sudah selesai membayar.
    orchaDenganTermin(['lunas', 'lunas', 'lunas']);

    Livewire::actingAs(adminAngsuranSelesai())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 5])
        ->assertSee('3× selesai')
        ->assertDontSee('3× berjalan');
});

test('rencana yang masih berjalan tetap bisa dibatalkan', function () {
    // Penjaga arah sebaliknya: tombolnya tidak boleh ikut hilang dari rencana
    // yang memang perlu dibatalkan karena keadaan pelanggan berubah.
    orchaDenganTermin(['lunas', 'menunggu', 'menunggu']);

    Livewire::actingAs(adminAngsuranSelesai())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 5])
        ->assertSee('Batalkan rencana')
        ->assertSee('3× berjalan')
        ->assertDontSee('disimpan sebagai riwayat');
});

test('termin yang telat mengubah lencananya, bukan disamarkan hijau', function () {
    /*
     | Satu-satunya keadaan di kartu ini yang menuntut tindakan hari ini.
     | Lencana hijau bertuliskan "berjalan" menyembunyikannya di antara
     | rencana yang memang sehat.
     */
    orchaDenganTermin(['lunas', 'telat', 'menunggu']);

    Livewire::actingAs(adminAngsuranSelesai())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 5])
        ->assertSee('3× ada yang telat')
        ->assertDontSee('3× berjalan');
});

test('catatan sistem tidak diakui sebagai catatan pemesan', function () {
    /*
     | Catatan pemesan dan catatan sistem berbagi SATU kolom di basis data.
     | Sebelum ini seluruh isinya diberi judul "Catatan dari pemesan" —
     | termasuk baris yang ditulis LepaskanKursiTertahan saat kursinya dilepas
     | otomatis.
     |
     | Admin yang membuka pemesanan batal lalu membaca alasan pembatalan
     | seolah pelanggan sendiri yang mengetiknya — dan alasan yang salah
     | atribusinya lebih menyesatkan daripada alasan yang tidak ditampilkan.
     */
    /*
     | TIDAK memanggil orchaDenganTermin() lebih dulu: Http::fake() yang
     | dipanggil dua kali tidak mengganti stub sebelumnya untuk pola yang sama,
     | jadi payload kedua tidak akan pernah terpakai — dan uji ini lolos atau
     | gagal karena alasan yang salah.
     */
    Http::fake([
        '*/rujukan*' => Http::response(['data' => []]),
        '*/angsuran*' => Http::response(['data' => ['boleh_diangsur' => false, 'lunas' => false,
            'maks_termin' => 1, 'ingatkan_hari_sebelum' => 3, 'pilihan' => [], 'rencana' => null]]),
        '*' => Http::response(['data' => array_merge(
            json_decode(json_encode(barisPendaftaranLengkap()), true),
            ['catatan' => "Tolong kursi dekat jendela.\n[Sistem] Kursi dilepas otomatis pada 6 September 2026, 14:20 — tidak ada pembayaran dalam 72 jam sejak pendaftaran."],
        )]),
    ]);

    $layar = Livewire::actingAs(adminAngsuranSelesai())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 5]);

    $isi = $layar->html();

    // Keduanya tampil, tetapi di kotaknya masing-masing.
    expect($isi)->toContain('Tolong kursi dekat jendela.')
        ->toContain('Dicatat sistem')
        ->toContain('tidak ada pembayaran dalam 72 jam');

    // Dan yang menentukan: baris sistem TIDAK berada di dalam kotak pemesan.
    $awalPemesan = strpos($isi, 'Catatan dari pemesan');
    $awalSistem = strpos($isi, 'Dicatat sistem');
    expect($awalPemesan)->toBeLessThan($awalSistem)
        ->and(substr($isi, $awalPemesan, $awalSistem - $awalPemesan))
        ->not->toContain('tidak ada pembayaran dalam 72 jam');
});
