<?php

use App\Livewire\Pages\Admin\Orcha\JejakAudit\OrchaJejakAuditList;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanDetail;
use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Dari pesanan ke jejaknya, dan sebaliknya.
 *
 * Pertanyaan "siapa yang mengubah nominalnya?" muncul saat admin sedang
 * menatap SATU pesanan yang bermasalah — bukan saat ia kebetulan membuka
 * halaman audit. Tanpa jalan di antara keduanya, jawabannya menuntut: hafal
 * kodenya, buka menu lain, ketik ulang, cocokkan sendiri.
 *
 * Dua sisinya diuji bersama karena keduanya harus benar agar satu pun
 * berguna: tombol yang membawa kode ke alamat TIDAK ada gunanya bila halaman
 * tujuan tidak membaca kode dari alamatnya — dan kegagalan itu tidak
 * menerbitkan galat apa pun, cuma daftar penuh yang terlihat wajar.
 */
function adminJejak(): User
{
    $role = Role::create(['name' => 'uji-jejak-'.uniqid(), 'description' => 'Peran uji jalan ke jejak']);

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
});

/* ------------------------------------------------------------------ *
 | Sisi satu: tombolnya ada dan membawa kode pesanannya
 * ------------------------------------------------------------------ */

test('detail pendaftaran punya jalan ke jejak pesanan itu saja', function () {
    Http::fake([
        '*/rujukan*' => Http::response(['data' => []]),
        '*/angsuran*' => Http::response(['data' => ['boleh_diangsur' => false, 'rencana' => null, 'pilihan' => []]]),
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

    Livewire::actingAs(adminJejak())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 5])
        // Kodenya ikut di alamat: yang terbuka sudah tersaring, bukan seluruh
        // jejak seisi sistem.
        ->assertSee(route('admin.orcha.jejak-audit', ['cari' => 'OT-1508-0VCZ']), false);
});

test('detail penyewaan punya jalan ke jejak pesanan itu saja', function () {
    Http::fake([
        '*/rujukan*' => Http::response(['data' => ['status_penyewaan' => ['baru' => 'Baru']]]),
        '*' => Http::response(['data' => [
            'id' => 7, 'kode' => 'SK-1708-AAAA', 'nama' => 'Budi Santoso',
            'whatsapp' => '081234567890', 'email' => 'budi@contoh.test',
            'kendaraan' => ['id' => 1, 'nama' => 'HiAce Commuter', 'transmisi' => 'Manual'],
            'satuan' => 'hari', 'satuan_label' => 'Per hari (24 jam)',
            'durasi' => 2, 'durasi_label' => '2 hari',
            'tanggal_mulai' => '2026-08-25', 'jam_mulai' => '07:00',
            'tanggal_selesai' => '2026-08-27', 'jam_selesai' => '07:00',
            'jadwal_selesai' => '2026-08-27T07:00:00+07:00',
            'terlambat' => false, 'terlambat_menit' => 0, 'denda_keterlambatan_usulan' => 0,
            'dengan_sopir' => true,
            'lokasi_antar' => 'Hotel Malioboro', 'lokasi_kembali' => 'Hotel Malioboro',
            'tujuan' => 'Borobudur — Dieng',
            'diserahkan_pada' => null, 'dikembalikan_pada' => null,
            'kilometer_awal' => null, 'kilometer_akhir' => null,
            'bahan_bakar_awal' => null, 'bahan_bakar_akhir' => null,
            'jaminan' => null, 'kondisi_awal' => [], 'kondisi_akhir' => [], 'kerusakan_baru' => [],
            'estimasi_biaya' => 2400000, 'denda_keterlambatan' => 0, 'denda_kerusakan' => 0,
            'denda_lain' => 0, 'catatan_denda' => null, 'total_denda' => 0, 'total_tagihan' => 2400000,
            'rincian_denda' => [], 'berkas_jaminan' => null,
            'catatan' => null, 'status' => 'baru', 'status_label' => 'Baru',
            'dibuat_pada' => '2026-08-17T10:00:00+07:00',
        ]]),
    ]);

    Livewire::actingAs(adminJejak())
        ->test(OrchaPenyewaanDetail::class, ['penyewaan' => 7])
        ->assertSee(route('admin.orcha.jejak-audit', ['cari' => 'SK-1708-AAAA']), false);
});

/* ------------------------------------------------------------------ *
 | Sisi dua: halaman tujuan benar-benar membaca kodenya
 * ------------------------------------------------------------------ */

test('tiap daftar Orcha mengisi kotak carinya dari alamat', function (string $komponen) {
    Http::fake([
        '*/rujukan*' => Http::response(['data' => []]),
        '*/admin' => Http::response(['data' => []]),
        '*/aksi' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 0, 'halaman_terakhir' => 1]]),
    ]);

    Livewire::actingAs(adminJejak())
        ->withQueryParams(['cari' => 'SK-1708-AAAA'])
        ->test($komponen)
        ->assertSet('cari', 'SK-1708-AAAA');

    // Dan kodenya benar-benar sampai ke Orcha — kotak cari yang terisi tetapi
    // tidak ikut dikirim cuma memindahkan kebingungannya satu langkah.
    Http::assertSent(fn ($p) => str_contains(urldecode($p->url()), 'cari=SK-1708-AAAA'));
})->with([
    OrchaJejakAuditList::class,
    OrchaPenyewaanList::class,
    OrchaPendaftaranList::class,
]);

test('tanpa cari di alamat kotaknya tetap kosong', function () {
    Http::fake([
        '*/admin' => Http::response(['data' => []]),
        '*/aksi' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 0, 'halaman_terakhir' => 1]]),
    ]);

    Livewire::actingAs(adminJejak())
        ->test(OrchaJejakAuditList::class)
        ->assertSet('cari', '');
});

test('mengosongkan kotak cari tidak dibatalkan oleh alamat lamanya', function () {
    /*
     | Alamat yang memuat ?cari= tetap melekat pada komponen di setiap
     | permintaan Livewire berikutnya. Kalau nilainya dibaca ulang tanpa
     | syarat, tombol "Kosongkan pencarian" akan terlihat ditekan lalu
     | kembali sendiri — cacat yang sulit dipercaya saat dilaporkan.
     */
    Http::fake([
        '*/admin' => Http::response(['data' => []]),
        '*/aksi' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 0, 'halaman_terakhir' => 1]]),
    ]);

    Livewire::actingAs(adminJejak())
        ->withQueryParams(['cari' => 'SK-1708-AAAA'])
        ->test(OrchaJejakAuditList::class)
        ->call('bersihkanCari')
        ->assertSet('cari', '');
});
