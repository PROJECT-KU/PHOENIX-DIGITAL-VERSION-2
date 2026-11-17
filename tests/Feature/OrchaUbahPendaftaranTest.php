<?php

/**
 * Membetulkan, memindahkan, dan menghapus pendaftaran dari layar detail.
 *
 * Sebelum ini tidak ada satu pun jalannya. Nomor WhatsApp yang salah satu
 * angka, jumlah peserta yang berubah, atau pelanggan yang minta pindah tanggal
 * semuanya hanya bisa diselesaikan dengan membatalkan lalu mendaftar ulang —
 * yang membuang kode pemesanan, riwayat pembayaran, angsuran, dan riwayat
 * kesehatan yang sudah diisi peserta.
 */

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function adminUbahDaftar(): User
{
    $role = Role::create(['name' => 'uji-ubah-'.uniqid(), 'description' => 'Peran uji ubah pendaftaran']);

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

function barisUbahPalsu(array $ubah = []): array
{
    return array_merge([
        'id' => 9, 'kode' => 'OT-1711-UBAH01', 'nama' => 'Bu Sri',
        'whatsapp' => '081234567890', 'email' => 'sri@contoh.test',
        'jumlah_peserta' => 4, 'pendamping_gratis' => 0, 'peserta_dibayar' => 4,
        'peserta' => [
            ['nama' => 'Budi', 'titik_jemput' => 'Terminal', 'bus' => null, 'kamar' => null,
                'kursi' => null, 'jenis_kelamin' => 'Laki-laki'],
            ['nama' => 'Siti', 'titik_jemput' => 'Terminal', 'bus' => null, 'kamar' => null,
                'kursi' => null, 'jenis_kelamin' => null],
        ],
        'jemput_per_titik' => [], 'bus_per_kelompok' => [], 'kamar_per_kelompok' => [],
        // Dua riwayat kesehatan tersimpan, tetapi hanya SATU yang namanya cocok
        // dengan peserta di daftar — yang satu lagi milik peserta yang diganti.
        'kesehatan_terisi' => 2, 'kesehatan_lengkap' => false,
        'peserta_belum_isi' => ['Siti'],
        'paket' => ['id' => 5, 'nama' => 'Open Trip Bromo', 'titik_jemput' => []],
        'keberangkatan_id' => 11,
        'keberangkatan' => ['id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026', 'aktif' => true],
        'tanggal_berangkat' => now()->addDays(20)->toDateString(),
        'hari_ke_berangkat' => 20, 'pengingat_pelunasan_pada' => null,
        'titik_jemput' => 'Terminal', 'catatan' => null, 'catatan_internal' => null,
        'status' => 'baru', 'status_label' => 'Baru',
        'riwayat_penggantian' => [], 'surat_penggantian' => null, 'surat_penggantian_pada' => null,
        'tautan_kesehatan' => 'https://orchajourney.com/riwayat-kesehatan?kode=OT-1711-UBAH01',
        'dibuat_pada' => now()->toIso8601String(),
        'tagihan' => [
            'total' => 8000000, 'total_teks' => 'Rp 8.000.000',
            'sudah' => 0, 'sudah_teks' => 'Rp 0',
            'sisa' => 8000000, 'sisa_teks' => 'Rp 8.000.000',
            'dp' => 2000000, 'dp_teks' => 'Rp 2.000.000', 'dp_persen' => 25,
            'lunas' => false, 'jenis_disarankan' => 'dp',
        ],
        'pembayaran' => [], 'pembatalan' => null,
    ], $ubah);
}

/** Permintaan yang tertangkap, supaya uji bisa memeriksa APA yang dikirim. */
function jejakUbah(): object
{
    return new class
    {
        public static array $patch = [];

        public static array $hapus = [];
    };
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    config()->set('uji.ubah.tolak', null);
    config()->set('uji.ubah.baris', []);
    config()->set('uji.ubah.satu_tanggal', false);

    Http::fake(function ($permintaan) {
        if (str_contains($permintaan->url(), '/rujukan')) {
            return Http::response(['data' => [
                'status_pendaftaran' => ['baru' => 'Baru', 'dp_masuk' => 'DP Masuk', 'batal' => 'Batal'],
                'jenis_pembayaran' => ['dp' => 'Uang Muka (DP)', 'pelunasan' => 'Pelunasan'],
                'pembayaran' => ['pelunasan_hari_sebelum' => 5],
                'paket_wisata' => [[
                    'id' => 5, 'nama' => 'Open Trip Bromo', 'kategori' => 'open_trip',
                    'keberangkatan' => config('uji.ubah.satu_tanggal')
                        ? [['id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026', 'aktif' => true,
                            'penuh' => false, 'sisa_kursi' => 12]]
                        : [
                            ['id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026', 'aktif' => true,
                                'penuh' => false, 'sisa_kursi' => 12],
                            ['id' => 12, 'rentang_tanggal' => '11 – 13 Des 2026', 'aktif' => true,
                                'penuh' => false, 'sisa_kursi' => 3],
                            ['id' => 13, 'rentang_tanggal' => '18 – 20 Des 2026', 'aktif' => false,
                                'penuh' => true, 'sisa_kursi' => 0],
                        ],
                ]],
            ]]);
        }

        if ($permintaan->method() === 'PATCH') {
            $tolak = config('uji.ubah.tolak');

            if ($tolak) {
                return Http::response($tolak, 422);
            }

            return Http::response(['pesan' => 'Data pendaftaran tersimpan.', 'data' => barisUbahPalsu()]);
        }

        if ($permintaan->method() === 'DELETE') {
            return Http::response(['pesan' => 'Pendaftaran OT-1711-UBAH01 dihapus.']);
        }

        return Http::response(['data' => barisUbahPalsu(config('uji.ubah.baris') ?: [])]);
    });
});

test('mengubah data, pindah tanggal, dan catatan internal ada di laci Ubah', function () {
    /*
     | Bilah perkakas sempat memuat sepuluh tombol sejajar — dan sepuluh
     | pilihan setara berarti sepuluh hal yang harus dibaca sebelum menekan
     | satu. Sekarang yang dipakai tiap hari tinggal di luar, sisanya di dua
     | laci berlabel.
     */
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->assertSee('Betulkan data pemesan')
        ->assertSee('Pindah ke tanggal lain')
        ->assertSee('Catatan internal')
        // Tombol harian tetap di luar laci.
        ->assertSee('Catat Uang Masuk');
});

test('pindah tanggal tidak ditawarkan kalau paketnya hanya punya satu tanggal', function () {
    /*
     | Dikendalikan lewat config, bukan dengan memasang Http::fake kedua.
     |
     | Http::fake() yang dipanggil dua kali MENAMBAH stub, tidak
     | menggantikannya — dan yang didaftarkan lebih dulu yang dipakai. Stub
     | kedua lalu tidak pernah jalan, sementara ujinya tetap terlihat seperti
     | sudah mengganti jawaban servernya.
     */
    config()->set('uji.ubah.satu_tanggal', true);
    cache()->forget('orcha.rujukan');

    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->assertDontSee('Pindah ke tanggal lain');
});

test('formulir ubah terisi dari data yang ada', function () {
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('bukaFormulirUbah')
        ->assertSet('ubah.nama', 'Bu Sri')
        ->assertSet('ubah.whatsapp', '081234567890')
        ->assertSet('ubah.jumlah_peserta', '4');
});

test('pendamping gratis tidak boleh menghabiskan seluruh peserta', function () {
    /*
     | Kalau lewat, peserta yang ditagih jadi nol atau negatif — dan tagihan
     | negatif tidak ditolak di mana pun, ia cuma muncul sebagai total minus di
     | kwitansi yang sudah dikirim ke sekolah.
     */
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('bukaFormulirUbah')
        ->set('ubah.jumlah_peserta', '4')
        ->set('ubah.pendamping_gratis', '4')
        ->call('simpanUbah')
        ->assertHasErrors('ubah.pendamping_gratis');
});

test('penolakan kuota muncul sebagai pilihan, bukan toast yang hilang', function () {
    config()->set('uji.ubah.tolak', [
        'pesan' => 'Tanggal 11 – 13 Des 2026 tinggal 3 kursi, sedangkan rombongan ini 4 orang.',
        'butuh_paksa' => true,
    ]);

    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('bukaFormulirPindah')
        ->set('rombonganTujuan', '12')
        ->call('pindahRombongan', false)
        // Tidak dilempar sebagai toast: panelnya tetap terbuka dengan tombol
        // lanjutannya.
        ->assertSet('bukaPindah', true)
        ->assertSet('peringatanKuota', 'Tanggal 11 – 13 Des 2026 tinggal 3 kursi, sedangkan rombongan ini 4 orang.')
        ->assertSee('Lanjutkan, lebihi kuota');
});

test('penolakan selain kuota tetap jadi pesan gagal', function () {
    config()->set('uji.ubah.tolak', ['pesan' => 'Tanggal itu bukan milik paket Open Trip Bromo.']);

    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('bukaFormulirPindah')
        ->set('rombonganTujuan', '12')
        ->call('pindahRombongan', false)
        ->assertSet('peringatanKuota', '')
        ->assertDispatched('toast-error');
});

test('memindahkan ke rombongan yang sedang dipakai ditolak di layar', function () {
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('bukaFormulirPindah')
        // 11 adalah rombongan yang sekarang.
        ->call('pindahRombongan', false)
        ->assertDispatched('toast-error')
        ->assertSet('bukaPindah', true);
});

test('rombongan yang ditutup tetap bisa dipilih tetapi ditandai', function () {
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('bukaFormulirPindah')
        ->assertSee('18 – 20 Des 2026')
        ->assertSee('DITUTUP');
});

test('catatan internal tersimpan lalu tampil di kartu pemesan', function () {
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('bukaFormulirCatatan')
        ->set('catatanInternal', 'Sudah ditelepon 2x, minta ditelepon sore.')
        ->call('simpanCatatanInternal')
        ->assertSet('bukaCatatan', false)
        ->assertDispatched('order-updated');

    config()->set('uji.ubah.baris', ['catatan_internal' => 'Sudah ditelepon 2x, minta ditelepon sore.']);

    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->assertSee('tidak terbaca pelanggan')
        ->assertSee('Sudah ditelepon 2x, minta ditelepon sore.');
});

test('menghapus pendaftaran memindahkan admin kembali ke daftar', function () {
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('hapusPendaftaran')
        ->assertDispatched('orcha-sukses-pindah');
});

test('tombol hapus ada di dasar halaman dengan sebabnya disebut', function () {
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->assertSeeInOrder([
            'Catat Uang Masuk',
            'Hapus pendaftaran ini',
            'Hapus Pendaftaran',
        ])
        ->assertSee('pernah menerima uang');
});

test('angka kesehatan di kepala kartu menghitung peserta terdata, bukan baris riwayat', function () {
    /*
     | kesehatan_terisi = 2 (dua baris riwayat tersimpan), tetapi hanya satu
     | nama yang cocok dengan peserta di daftar — yang satu lagi milik peserta
     | yang sudah diganti orang lain. Kepala kartu harus membaca yang pertama
     | sebagai keterangan tambahan, bukan sebagai jawaban.
     */
    Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->assertSee('1 dari 4 sudah mengisi kesehatan')
        ->assertSee('tanpa nama yang cocok');
});

test('halaman dipecah jadi tiga tab, bukan satu gulungan panjang', function () {
    /*
     | Bilah "loncat ke" sebelumnya hanya memindahkan mata; isinya tetap
     | seluruhnya terpampang, dan admin yang mencari satu angka harus melewati
     | enam kartu yang tidak ia perlukan. Untuk orang yang tidak setiap hari
     | memakai aplikasi, halaman sepanjang itu terbaca sebagai "banyak sekali
     | yang harus saya mengerti".
     |
     | Dipakai x-show (display:none), BUKAN opacity: panel yang disembunyikan
     | dengan opacity tetap memakan tinggi, dan halamannya tidak pernah
     | memendek.
     */
    $layar = Livewire::actingAs(adminUbahDaftar())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9]);

    $layar->assertSee('pd-tab-deret')
        ->assertSee('Pemesan &amp; Catatan', false)
        ->assertSee("x-show=\"tab === 'uang'\"", false)
        ->assertSee("x-show=\"tab === 'peserta'\"", false)
        ->assertSee("x-show=\"tab === 'pemesan'\"", false);

    // Tidak ada lagi bilah loncat berbasis jangkar.
    $layar->assertDontSee('Loncat ke');
});
