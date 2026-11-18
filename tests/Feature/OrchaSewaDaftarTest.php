<?php

/**
 * Layar daftar Sewa Kendaraan, sesudah dibentuk ulang mengikuti Pesanan Toko.
 *
 * Yang dikejar bukan kemiripan rupa, melainkan satu hal yang sama: admin yang
 * tidak setiap hari memakai aplikasi harus bisa melihat ada pekerjaan di mana
 * TANPA menekan apa pun dulu. Pemilih status yang lama menyembunyikan enam
 * pilihan di balik satu kotak dan tidak pernah menyebut berapa isinya.
 */

use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function swAdmin(): User
{
    $role = Role::create(['name' => 'uji-sewa-'.uniqid(), 'description' => 'uji sewa']);

    $izin = Permission::firstOrCreate(['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']);
    $role->permissions()->attach($izin->id);

    $user = User::factory()->create(['role_id' => $role->id]);
    EmployeeDetail::create(['user_id' => $user->id, 'jabatan' => 'Admin', 'nomor_rekening' => '1',
        'tanggal_lahir' => '1995-01-01', 'phone' => '0812', 'alamat' => 'Yogyakarta']);

    return $user->fresh();
}

function swBaris(int $id, array $ubah = []): array
{
    return array_merge([
        'id' => $id,
        'kode' => 'SW-1711-'.$id,
        'nama' => 'Penyewa '.$id,
        'whatsapp' => '08120000000'.$id,
        'kendaraan' => ['nama' => 'Avanza 2022 · AB 1234 CD', 'transmisi' => 'Matic'],
        'tanggal_mulai' => now()->addDays($id)->toDateString(),
        'tanggal_selesai' => now()->addDays($id + 2)->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '17:00',
        'durasi_label' => '3 hari',
        'dengan_sopir' => true,
        'status' => 'baru',
        'status_label' => 'Baru',
        'estimasi_biaya' => 850_000,
        'terlambat' => false,
        'terlambat_menit' => 0,
        'dikembalikan_pada' => null,
        'total_denda' => 0,
    ], $ubah);
}

/** Parameter yang benar-benar sampai ke Orcha pada permintaan daftar terakhir. */
function swParameterTerakhir(): array
{
    $permintaan = collect(Http::recorded())
        ->map(fn ($pasang) => $pasang[0])
        ->filter(fn ($p) => str_contains($p->url(), '/penyewaan')
            && ! str_contains($p->url(), '/rujukan'))
        ->last();

    if (! $permintaan) {
        return [];
    }

    parse_str(parse_url($permintaan->url(), PHP_URL_QUERY) ?? '', $hasil);

    return $hasil;
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
    config()->set('uji.sewa.daftar', null);

    Http::fake(function ($p) {
        if (str_contains($p->url(), '/rujukan')) {
            return Http::response(['data' => ['status_penyewaan' => [
                'baru' => 'Baru', 'dikonfirmasi' => 'Dikonfirmasi', 'dp_masuk' => 'DP Masuk',
                'berjalan' => 'Sedang Berjalan', 'selesai' => 'Selesai', 'batal' => 'Batal',
            ]]]);
        }

        return Http::response([
            'data' => config('uji.sewa.daftar') ?? [swBaris(1)],
            'meta' => [
                'halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1,
                'jumlah_tab' => [
                    'semua' => 27, 'baru' => 4, 'dikonfirmasi' => 6, 'dp_masuk' => 3,
                    'berjalan' => 5, 'selesai' => 8, 'batal' => 1, 'telat' => 2,
                ],
            ],
        ]);
    });
});

test('tab berangka menggantikan pemilih status', function () {
    /*
     | Pemilih menyembunyikan enam pilihan di balik satu kotak dan tidak pernah
     | menyebut berapa isinya; kartu berangka menjawab "ada pekerjaan di sana
     | atau tidak" tanpa ditekan dulu.
     */
    $layar = Livewire::actingAs(swAdmin())->test(OrchaPenyewaanList::class);

    $layar->assertSee('pt-kartu-tab')
        ->assertSee('Keadaan pemesanan')
        ->assertSee('Perlu dikerjakan hari ini')
        ->assertSee('Belum kembali')
        // Angkanya ikut, bukan cuma namanya.
        ->assertSee('27')
        ->assertSee('Sedang dipakai');

    // Pemilih status yang lama sudah tidak ada.
    $layar->assertDontSee('Semua status');
});

test('pindah tab menerjemahkan satu pilihan jadi saringan di balik layar', function () {
    $layar = Livewire::actingAs(swAdmin())
        ->test(OrchaPenyewaanList::class)
        ->call('setTab', 'berjalan');

    $layar->assertSet('filterStatus', 'berjalan')
        ->assertSet('belumKembali', false)
        ->assertSet('halaman', 1);

    expect(swParameterTerakhir()['status'] ?? null)->toBe('berjalan');
});

test('tab belum kembali memakai saringan yang berbeda jenisnya, bukan status', function () {
    /*
     | "Terlambat" dihitung dari tenggat, bukan dari kolom status — unit yang
     | statusnya "Sedang Berjalan" bisa tepat waktu maupun telat. Admin tidak
     | perlu tahu bedanya; ia cuma menekan satu tab.
     */
    $layar = Livewire::actingAs(swAdmin())
        ->test(OrchaPenyewaanList::class)
        ->call('setTab', 'telat');

    $layar->assertSet('belumKembali', true)
        ->assertSet('filterStatus', '');

    $kirim = swParameterTerakhir();

    expect($kirim['telat'] ?? null)->toBe('1');
    expect($kirim)->not->toHaveKey('status');
});

test('telat tidak dikirim saat tabnya tidak menyala', function () {
    /*
     | Mengirim 0 membuat Orcha membaca parameternya ada, dan daftar biasa ikut
     | tersaring.
     */
    Livewire::actingAs(swAdmin())->test(OrchaPenyewaanList::class);

    expect(swParameterTerakhir())->not->toHaveKey('telat');
});

test('bersihkan saringan mengembalikan tab ke Semua', function () {
    /*
     | Tombolnya menjanjikan membersihkan semuanya; tab yang tetap menyala
     | membuat daftarnya terlihat masih tersaring padahal tidak.
     */
    Livewire::actingAs(swAdmin())
        ->test(OrchaPenyewaanList::class)
        ->call('setTab', 'telat')
        ->set('cari', 'budi')
        ->call('bersihkanSaringan')
        ->assertSet('tabAktif', 'semua')
        ->assertSet('belumKembali', false)
        ->assertSet('filterStatus', '')
        ->assertSet('cari', '');
});

test('tabelnya enam kolom, dan tanggal mulai & kembali jadi satu', function () {
    /*
     | Tujuh kolom membuat tabelnya lebih lebar daripada kartunya: terukur pada
     | 1440px, tombol Aksi terpotong di tepi kanan dan judul kolomnya ikut
     | hilang. Mulai dan kembali memang satu hal — rentang pemakaian.
     */
    $html = Livewire::actingAs(swAdmin())->test(OrchaPenyewaanList::class)->html();

    $thead = substr($html, strpos($html, '<thead>'), strpos($html, '</thead>') - strpos($html, '<thead>'));

    // '<th>' dan '<th ', bukan '<th' telanjang: yang terakhir ikut
    // menghitung '<thead>' itu sendiri.
    expect(preg_match_all('/<th[ >]/', $thead))->toBe(6);
    expect($thead)->toContain('Dipakai');
    expect($thead)->not->toContain('>Mulai<');
});

test('tombol aksinya menyebut langkah berikutnya, bukan nama layarnya', function () {
    /*
     | Sederet tombol utama yang semuanya menuntut perhatian sama saja dengan
     | tidak ada yang menuntut perhatian — dan untuk admin yang awam, itu yang
     | membuat ia menekan yang salah.
     */
    config()->set('uji.sewa.daftar', [
        swBaris(1, ['status' => 'berjalan', 'status_label' => 'Sedang Berjalan']),
        swBaris(2, ['status' => 'selesai', 'status_label' => 'Selesai',
            'dikembalikan_pada' => now()->subDay()->toIso8601String()]),
    ]);

    $html = Livewire::actingAs(swAdmin())->test(OrchaPenyewaanList::class)->html();

    // Yang unitnya masih di luar: tombol utama, kalimat perintah.
    expect($html)->toContain('Catat kembali');
    // Yang sudah pulang: varian lembut, kalimat yang tidak menyuruh.
    expect($html)->toContain('Lihat catatannya');
    expect($html)->toContain('sw-lanjut is-lembut');
});

test('pemesanan yang batal tidak diberi tombol serah terima sama sekali', function () {
    config()->set('uji.sewa.daftar', [
        swBaris(1, ['status' => 'batal', 'status_label' => 'Batal']),
    ]);

    $html = Livewire::actingAs(swAdmin())->test(OrchaPenyewaanList::class)->html();

    // Tidak ada unit yang diserahterimakan pada pemesanan yang batal.
    expect($html)->not->toContain('serah-terima');
});

test('pemilih keadaan minta konfirmasi dulu', function () {
    /*
     | Pemilih ini duduk di jalur jempol saat menggulir, dan "Batal" di
     | dalamnya melepas unitnya dari jadwal — tindakan yang tidak bisa
     | dibatalkan dengan memilih ulang. Pola yang sama dipakai Pendaftaran Trip.
     */
    $html = Livewire::actingAs(swAdmin())->test(OrchaPenyewaanList::class)->html();

    expect($html)->toContain('orchaKonfirmasi');
    expect($html)->toContain('Ubah keadaan?');
});

test('tombol lihat detail yang menduplikasi tautan nama sudah dibuang', function () {
    /*
     | Nama penyewanya di kolom pertama sudah menjadi tautan ke halaman yang
     | sama. Dua jalan ke satu tempat membuat admin yang awam ragu apakah
     | keduanya berbeda — sambil memakan lebar yang membuat tombol sebelahnya
     | terpotong.
     */
    $html = Livewire::actingAs(swAdmin())->test(OrchaPenyewaanList::class)->html();

    $tbody = substr($html, strpos($html, '<tbody>'), strpos($html, '</tbody>') - strpos($html, '<tbody>'));

    // Satu tautan ke halaman detail per baris, bukan dua.
    expect(substr_count($tbody, '/admin/orcha/penyewaan/1"'))->toBe(1);
});
