<?php

use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanList;
use App\Models\Lowongan;
use Livewire\Livewire;

/*
 | Nama pembantunya berawalan "Daftar" dengan sengaja: `lowonganUji()` sudah
 | dipakai SitusAsthanaTest, dan dua berkas uji yang mendeklarasikan fungsi
 | bernama sama membuat seluruh rangkaian mati dengan "Cannot redeclare" —
 | hijau saat berkasnya dijalankan sendirian, fatal saat dijalankan bersama.
 */

/**
 * Layar Data Lowongan, sesudah disamakan dengan Pesanan Toko.
 *
 * Yang dijaga terutama keadaan KETIGA: lowongan yang masih bertanda aktif
 * tetapi batas lamarannya sudah lewat. Ia berhenti tampil di situs tanpa
 * satu pun tanda di layar ini sebelumnya — dan yang mengiranya masih
 * menarik pelamar menunggu lamaran yang tidak akan datang.
 */
function adminDaftarLowongan(): \App\Models\User
{
    return adminRab(['akses_asthana', 'view_lowongan', 'create_lowongan', 'edit_lowongan', 'delete_lowongan']);
}

function lowonganDaftarUji(array $ubah = []): Lowongan
{
    return Lowongan::create(array_merge([
        'title' => 'Programmer Laravel',
        'slug' => 'programmer-laravel-'.uniqid(),
        'is_active' => 'active',
        'unit' => 'reka',
        'jenis' => 'penuh_waktu',
        'pola_kerja' => 'kantor',
        'lokasi' => 'Yogyakarta',
        'requirements' => 'Uji',
        'descriptions' => 'Uji',
    ], $ubah));
}

it('memisahkan lewat batas dari yang sedang dibuka', function () {
    $dibuka = lowonganDaftarUji(['title' => 'Desainer Grafis', 'batas_lamaran' => now()->addDays(5)]);
    $lewat = lowonganDaftarUji(['title' => 'Admin Gudang', 'batas_lamaran' => now()->subDays(3)]);
    $ditutup = lowonganDaftarUji(['title' => 'Kurir', 'is_active' => 'non-active']);

    $komponen = Livewire::actingAs(adminDaftarLowongan())->test(LowonganPekerjaanList::class);

    $jumlah = $komponen->instance()->jumlahTab();

    expect($jumlah['semua'])->toBe(3)
        ->and($jumlah['terbuka'])->toBe(1)
        ->and($jumlah['lewat_batas'])->toBe(1)
        ->and($jumlah['nonaktif'])->toBe(1);

    // Tab "lewat batas" hanya berisi yang tanggalnya sudah lewat.
    $komponen->call('setTab', 'lewat_batas')
        ->assertSee('Admin Gudang')
        ->assertDontSee('Desainer Grafis')
        ->assertDontSee('Kurir');

    /*
     | Lowongan yang sudah ditutup TIDAK boleh ikut tab "lewat batas",
     | walau tanggalnya juga lewat: ia memang sengaja ditutup, dan
     | mencampurnya membuat daftar pekerjaan berisi hal yang sudah beres.
     */
    lowonganDaftarUji(['title' => 'Satpam', 'is_active' => 'non-active', 'batas_lamaran' => now()->subDays(9)]);
    expect($komponen->instance()->jumlahTab()['lewat_batas'])->toBe(1);
});

it('angka tab mengikuti pencarian yang sedang hidup', function () {
    lowonganDaftarUji(['title' => 'Desainer Grafis', 'batas_lamaran' => now()->addDays(5)]);
    lowonganDaftarUji(['title' => 'Admin Gudang', 'batas_lamaran' => now()->addDays(5)]);

    /*
     | Angka tab yang mengabaikan pencarian menjanjikan isi yang tidak ada
     | saat ditekan: "Masih dibuka 2" lalu daftarnya cuma satu baris.
     */
    $komponen = Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->set('search', 'Gudang');

    expect($komponen->instance()->jumlahTab()['terbuka'])->toBe(1);
});

it('menyebut berapa orang sudah melamar', function () {
    $lowongan = lowonganDaftarUji(['title' => 'Desainer Grafis']);

    \App\Models\JobApplication::create([
        'job_id' => $lowongan->id, 'name' => 'Budi', 'email' => 'budi@contoh.test',
        'phone' => '081234567890', 'cv_path' => 'uji.pdf',
    ]);

    // Pertanyaan pertama yang dibawa orang saat membuka layar ini, dan dulu
    // tidak terjawab di sini sama sekali.
    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertSee('1 orang');
});

it('saringan perusahaan mempersempit daftarnya', function () {
    lowonganDaftarUji(['title' => 'Desainer Grafis', 'unit' => 'reka']);
    lowonganDaftarUji(['title' => 'Tour Leader', 'unit' => 'orcha']);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->set('filterUnit', 'orcha')
        ->assertSee('Tour Leader')
        ->assertDontSee('Desainer Grafis');
});

it('membersihkan saringan mengembalikan tab ke semua', function () {
    lowonganDaftarUji(['title' => 'Desainer Grafis']);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->set('search', 'zzz')
        ->set('filterUnit', 'orcha')
        ->call('setTab', 'nonaktif')
        ->call('bersihkanSaringan')
        ->assertSet('search', '')
        ->assertSet('filterUnit', '')
        ->assertSet('tabAktif', 'semua')
        ->assertSee('Desainer Grafis');
});

it('kelas rupa yang dipakai layar ini punya definisinya', function () {
    /*
     | Penjaga "rupa hilang tanpa galat".
     |
     | .pt-daftar-kepala sempat HANYA ada di berkas gaya Pesanan Toko,
     | sementara Pendaftaran Trip dan layar ini memakainya lewat partial
     | kartu tab. Keduanya menggambarnya tanpa gaya, halamannya tetap 200,
     | dan tidak ada satu pun tanda bahwa rupanya hilang.
     */
    $isi = Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->html();

    foreach (['pt-daftar-kepala', 'pt-kartu-tab', 'dsb-tabel', 'dsb-kosong'] as $kelas) {
        expect($isi)->toContain('.'.$kelas);
    }
});
