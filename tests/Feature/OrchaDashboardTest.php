<?php

use App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaForm;
use App\Livewire\Pages\Admin\Orcha\Etalase\OrchaDestinasiForm;
use App\Livewire\Pages\Admin\Orcha\Etalase\OrchaEtalaseList;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketForm;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketList;
use App\Livewire\Pages\Admin\Orcha\Pembatalan\OrchaPembatalanDetail;
use App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranList;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaSerahTerimaForm;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Halaman Orcha di lemon.
 *
 * Semua panggilan ke server Orcha dipalsukan (Http::fake) — tidak ada
 * permintaan keluar dan tidak ada data yang disimpan ke basis data lemon.
 */
function adminOrcha(array $permissions = ['akses_orcha']): User
{
    $role = Role::create(['name' => 'uji-orcha-'.uniqid(), 'description' => 'Peran untuk uji Orcha']);

    foreach ($permissions as $nama) {
        $permission = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );
        $role->permissions()->attach($permission->id);
    }

    $user = User::factory()->create(['role_id' => $role->id]);

    // EnsureProfileComplete melempar karyawan berprofil kosong ke halaman
    // profil. Isi seadanya supaya yang diuji tetap soal hak akses Orcha.
    EmployeeDetail::create([
        'user_id' => $user->id,
        'jabatan' => 'Admin Uji',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.perlu-ditindak');
    cache()->forget('orcha.rujukan');
});

/**
 * @param  array<string, mixed>  $tambahan  bagian yang diubah atau ditambahkan uji tertentu
 */
function balasanDashboard(array $tambahan = []): array
{
    return [
        'data' => array_merge([
            'kartu' => [
                ['kunci' => 'paket', 'label' => 'Paket wisata', 'nilai' => 7, 'ikon' => 'map', 'tautan' => 'paket-wisata'],
            ],
            'paket_per_kategori' => [['kunci' => 'open_trip', 'label' => 'Open Trip', 'jumlah' => 3]],
            'kendaraan_per_jenis' => [['kunci' => 'mobil', 'label' => 'Mobil', 'jumlah' => 6, 'tersedia' => 6]],
            'pendaftaran_terbaru' => [],
            'penyewaan_terbaru' => [],
            'perlu_ditindak' => [
                'pendaftaran_baru' => 2,
                'penyewaan_baru' => 1,
                'pembatalan_diajukan' => 0,
                'pesan_belum_dibaca' => 3,
            ],
        ], $tambahan),
        'meta' => ['diperbarui_pada' => now()->toIso8601String()],
    ];
}

/* ---------------------------- HAK AKSES ---------------------------- */

test('tanpa permission akses_orcha ditolak', function () {
    Http::fake();

    $this->actingAs(adminOrcha([]))
        ->get('/admin/orcha/dashboard')
        ->assertForbidden();
});

test('dengan permission akses_orcha bisa membuka dashboard Orcha', function () {
    Http::fake(['*/dashboard' => Http::response(balasanDashboard())]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')
        ->assertOk()
        ->assertSee('Dashboard Orcha Journey')
        ->assertSee('Mode Orcha Journey');
});

test('tamu diarahkan ke login, bukan melihat data Orcha', function () {
    Http::fake();

    $this->get('/admin/orcha/dashboard')->assertRedirect();
});

/* ------------------------- KUNCI & HEADER ------------------------- */

test('panggilan membawa kunci dan nama admin sebagai jejak audit', function () {
    Http::fake(['*/dashboard' => Http::response(balasanDashboard())]);

    $admin = adminOrcha();
    $this->actingAs($admin)->get('/admin/orcha/dashboard')->assertOk();

    // Nama, bukan surel: jejaknya berakhir di tempat yang dibaca manusia —
    // "dicatat oleh" pada riwayat penggantian peserta — dan surel panjang
    // memenuhi baris tanpa memberi tahu siapa orangnya.
    Http::assertSent(function ($request) use ($admin) {
        return $request->hasHeader('X-Orcha-Key', 'kunci-uji')
            && $request->hasHeader('X-Orcha-Admin', $admin->name);
    });
});

test('admin tanpa nama tetap meninggalkan jejak, bukan tanda hubung', function () {
    Http::fake(['*/dashboard' => Http::response(balasanDashboard())]);

    $admin = adminOrcha();
    $admin->forceFill(['name' => ''])->save();

    $this->actingAs($admin)->get('/admin/orcha/dashboard')->assertOk();

    Http::assertSent(fn ($request) => $request->hasHeader('X-Orcha-Admin', $admin->email));
});

/* --------------------------- ORCHA MATI --------------------------- */

test('orcha mati menampilkan pesan jelas, bukan layar error', function () {
    Http::fake(['*' => Http::response('', 500)]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')
        ->assertOk()
        ->assertSee('Data Orcha belum bisa ditampilkan');
});

test('kunci ditolak dijelaskan apa adanya', function () {
    Http::fake(['*' => Http::response(['pesan' => 'Kunci API tidak sah.'], 401)]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')
        ->assertOk()
        ->assertSee('ORCHA_API_KEY sama persis');
});

test('sambungan belum disetel tidak membuat halaman gagal', function () {
    config()->set('orcha.url', '');
    config()->set('orcha.kunci', '');
    Http::fake();

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')
        ->assertOk()
        ->assertSee('belum disetel');

    Http::assertNothingSent();
});

/* ---------------------------- SIDEBAR ----------------------------- */

test('tombol ganti ke orcha hanya tampil bagi yang berhak', function () {
    Http::fake(['*/dashboard' => Http::response(balasanDashboard())]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')
        ->assertOk()
        ->assertSee('Kembali ke lemon');

    // Admin tanpa permission tidak melihat tombolnya di halaman lemon mana pun
    $this->actingAs(adminOrcha([]))
        ->get('/profile')
        ->assertOk()
        ->assertDontSee('Ganti ke Orcha');
});

test('orcha yang mati tidak ikut mematikan sidebar lemon', function () {
    Http::fake(['*' => fn () => throw new \Exception('server tidak terjangkau')]);

    $this->actingAs(adminOrcha())
        ->get('/profile')
        ->assertOk()
        ->assertSee('Ganti ke Orcha');
});

/* ------------------------ RIWAYAT KESEHATAN ------------------------ */

test('riwayat kesehatan tertutup tanpa permission khususnya', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    // Penjagaannya di halamannya, bukan di tombol yang disembunyikan.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/1/riwayat-kesehatan')
        ->assertForbidden();
});

test('riwayat kesehatan terbuka bagi yang punya permission', function () {
    Http::fake([
        '*/riwayat-kesehatan' => Http::response(['data' => [[
            'nama_peserta' => 'Budi Santoso',
            'riwayat_penyakit' => 'Asma ringan',
            'kontak_darurat' => ['nama' => 'Sari', 'hubungan' => 'Istri', 'hp' => '08987654321'],
            'ada_catatan_khusus' => true,
        ]]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->get('/admin/orcha/pendaftaran/1/riwayat-kesehatan')
        ->assertOk()
        ->assertSee('Riwayat Kesehatan Peserta')
        ->assertSee('Asma ringan')
        // Peringatan kerahasiaan berdiri di atas, bukan jadi catatan kaki.
        ->assertSee('Data pribadi peserta');
});

/* --------------------------- UBAH STATUS --------------------------- */

test('ubah status diteruskan ke orcha, bukan disimpan di lemon', function () {
    Http::fake([
        '*/status' => Http::response(['data' => ['status' => 'dp_masuk'], 'pesan' => 'ok']),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPendaftaranList::class)
        ->call('ubahStatus', 9, 'dp_masuk')
        ->assertDispatched('order-updated');

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/pendaftaran/9/status')
        && $request->method() === 'PATCH'
        && $request['status'] === 'dp_masuk');
});

test('penolakan dari orcha ditampilkan, tidak dianggap berhasil', function () {
    Http::fake([
        '*/status' => Http::response(['errors' => ['status' => ['Status tidak sah.']]], 422),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPendaftaranList::class)
        ->call('ubahStatus', 9, 'ngawur')
        ->assertDispatched('toast-error')
        ->assertNotDispatched('order-updated');
});

/* ------------------------ TAMBAH / UBAH DATA ------------------------ */

test('halaman tambah paket dan armada bisa dibuka', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    $admin = adminOrcha();

    $this->actingAs($admin)->get('/admin/orcha/paket-wisata/tambah')
        ->assertOk()->assertSee('Tambah Paket Wisata');

    $this->actingAs($admin)->get('/admin/orcha/armada/tambah')
        ->assertOk()->assertSee('Tambah Kendaraan');
});

test('paket baru dikirim ke orcha, bukan disimpan di lemon', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Karimunjawa')
        ->set('kategori', 'open_trip')
        ->set('minimalPeserta', 6)
        ->set('hargaTeks', '1250000')
        ->call('jungkit', 'destinasi', 'Pantai Ujung Gelam')
        ->set('destinasiBaru', 'Snorkeling Menjangan')
        ->call('tambah', 'destinasi')
        ->call('jungkit', 'fasilitas', 'Homestay / penginapan')
        ->set('hari', [[
            'nama' => 'Day 1',
            'agenda' => [
                ['jam' => '07.00', 'kegiatan' => 'Berangkat dari Jepara'],
                // Baris tanpa kegiatan tidak boleh ikut tersimpan
                ['jam' => '', 'kegiatan' => ''],
            ],
        ]])
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/paket-wisata')
        && $request->method() === 'POST'
        && $request['nama'] === 'Open Trip Karimunjawa'
        && $request['destinasi'] === ['Pantai Ujung Gelam', 'Snorkeling Menjangan']
        && $request['fasilitas'] === ['Homestay / penginapan']
        && $request['itinerary_teks'] === "Day 1\n07.00 | Berangkat dari Jepara");
});

test('destinasi yang sama tidak bisa masuk dua kali', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->call('jungkit', 'destinasi', 'Kawah Ijen')
        ->set('destinasiBaru', 'Kawah Ijen')
        ->call('tambah', 'destinasi')
        ->assertSet('destinasi', ['Kawah Ijen'])
        // Klik kedua pada saran yang sama justru mengeluarkannya
        ->call('jungkit', 'destinasi', 'Kawah Ijen')
        ->assertSet('destinasi', []);
});

test('isian baru ikut tersimpan ke daftar pilihan bersama', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('fasilitasBaru', 'Kaos peserta')
        ->call('tambah', 'fasilitas')
        ->assertSet('fasilitas', ['Kaos peserta'])
        ->assertSet('fasilitasBaru', '');

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && str_ends_with($request->url(), '/saran')
        && $request['jenis'] === 'fasilitas'
        && $request['nama'] === 'Kaos peserta');
});

test('menghapus saran tidak mengubah isi paket yang sedang disusun', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->call('jungkit', 'destinasi', 'Kawah Ijen')
        ->call('hapusSaran', 12)
        ->assertSet('destinasi', ['Kawah Ijen'])
        ->assertDispatched('order-updated');

    Http::assertSent(fn ($request) => $request->method() === 'DELETE'
        && str_ends_with($request->url(), '/saran/12'));
});

test('isian kosong tidak dikirim ke daftar pilihan', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('destinasiBaru', '   ')
        ->call('tambah', 'destinasi')
        ->assertSet('destinasi', []);

    Http::assertNotSent(fn ($request) => $request->method() === 'POST');
});

test('baris itinerary bisa ditambah dan dibuang', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    $halaman = Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->assertCount('hari', 1)
        ->call('tambahHari')
        ->assertCount('hari', 2)
        ->call('tambahAgenda', 0)
        ->call('buangHari', 1)
        ->assertCount('hari', 1);

    expect($halaman->get('hari')[0]['agenda'])->toHaveCount(2);

    // Hari terakhir tidak pernah habis — selalu tersisa satu untuk diisi
    $halaman->call('buangHari', 0)->assertCount('hari', 1);
});

test('paket tanpa nama ditolak sebelum menghubungi orcha', function () {
    Http::fake();

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('nama', '')
        ->call('simpan')
        ->assertHasErrors(['nama']);

    // Halaman ini memang mengambil daftar pilihan saat digambar; yang penting
    // tidak ada pengiriman data.
    Http::assertNotSent(fn ($request) => $request->method() === 'POST');
});

test('kendaraan wajib punya minimal satu transmisi', function () {
    Http::fake();

    Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class)
        ->set('nama', 'Innova Zenix')
        ->set('merek', 'Toyota')
        ->set('transmisi', [])
        ->set('tarifHariTeks', '700000')
        ->call('simpan')
        ->assertHasErrors(['transmisi']);

    Http::assertNotSent(fn ($request) => $request->method() === 'POST');
});

test('penolakan dari orcha saat menghapus ditampilkan apa adanya', function () {
    Http::fake([
        '*/paket-wisata/9' => Http::response(['pesan' => 'Paket ini sudah punya pendaftar, jadi tidak bisa dihapus. Ubah saja isinya.'], 422),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketList::class)
        ->call('hapus', 9)
        ->assertDispatched('toast-error')
        ->assertNotDispatched('order-updated');
});

test('destinasi baru dikirim lengkap dengan wilayahnya', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class)
        ->call('tambah')
        ->set('nama', 'Nusa Penida')
        ->set('wilayah', 'bali_nusa')
        ->set('provinsi', 'Bali')
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertSet('formTerbuka', false);

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && str_ends_with($request->url(), '/destinasi')
        && $request['nama'] === 'Nusa Penida'
        && $request['wilayah'] === 'bali_nusa');
});

test('testimoni butuh isi dan rating', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'testimoni'])
        ->call('tambah')
        ->set('nama', 'Sari')
        ->set('isi', '')
        ->call('simpan')
        ->assertHasErrors(['isi']);
});

/* ------------------------------- DURASI ------------------------------- */

test('durasi terisi sendiri dari tanggal berangkat dan pulang', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('tanggalBerangkat', '2026-10-19')
        ->set('tanggalPulang', '2026-10-21')
        ->assertSet('durasi', '3 Hari 2 Malam')
        // Pulang di hari yang sama = trip sehari
        ->set('tanggalPulang', '2026-10-19')
        ->assertSet('durasi', '1 Hari');
});

test('tanggal pulang sebelum berangkat tidak menghasilkan durasi ngawur', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('tanggalBerangkat', '2026-10-21')
        ->set('tanggalPulang', '2026-10-19')
        ->assertSet('durasi', '');
});

test('tanpa tanggal, durasi memakai jumlah hari itinerary yang ada isinya', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('hari', [
            ['nama' => 'Day 1', 'agenda' => [['jam' => '07.00', 'kegiatan' => 'Berangkat']]],
            ['nama' => 'Day 2', 'agenda' => [['jam' => '08.00', 'kegiatan' => 'Pulang']]],
            // Hari kosong tidak ikut dihitung
            ['nama' => 'Day 3', 'agenda' => [['jam' => '', 'kegiatan' => '']]],
        ])
        ->call('hitungDurasi')
        ->assertSet('durasi', '2 Hari 1 Malam');
});

test('durasi yang ditulis sendiri tidak ditimpa hitungan', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('tanggalBerangkat', '2026-10-19')
        ->set('tanggalPulang', '2026-10-21')
        ->assertSet('durasi', '3 Hari 2 Malam')
        ->set('durasi', '3 Hari 2 Malam (opsional extend)')
        ->assertSet('durasiOtomatis', false)
        // Mengubah tanggal tidak lagi menimpa tulisan admin
        ->set('tanggalPulang', '2026-10-22')
        ->assertSet('durasi', '3 Hari 2 Malam (opsional extend)')
        // Sampai diminta menghitung ulang
        ->call('hitungDurasi')
        ->assertSet('durasi', '4 Hari 3 Malam')
        ->assertSet('durasiOtomatis', true);
});

/* ---------------------------- PENAYANGAN ---------------------------- */

test('lencana penayangan mengikuti pilihan sebelum disimpan', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    $halaman = Livewire::actingAs(adminOrcha())->test(OrchaPaketForm::class);

    expect($halaman->instance()->statusTayang())->toBe('tayang');

    $halaman->set('status', 'draf');
    expect($halaman->instance()->statusTayang())->toBe('draf');

    $halaman->set('status', 'terbit')->set('tayangMulai', now()->addWeek()->format('Y-m-d\TH:i'));
    expect($halaman->instance()->statusTayang())->toBe('terjadwal');

    $halaman->set('tayangMulai', '')->set('tanggalBerangkat', now()->subWeek()->toDateString());
    expect($halaman->instance()->statusTayang())->toBe('berakhir');

    // Dibebaskan dari berakhir otomatis, tayang lagi
    $halaman->set('berakhirOtomatis', false);
    expect($halaman->instance()->statusTayang())->toBe('tayang');
});

test('status dan jadwal ikut terkirim saat menyimpan paket', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Terjadwal')
        ->set('hargaTeks', '1000000')
        ->set('status', 'draf')
        ->set('tayangMulai', '2026-11-01T08:00')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && str_ends_with($request->url(), '/paket-wisata')
        && $request['status'] === 'draf'
        && $request['tayang_mulai'] === '2026-11-01T08:00');
});

test('berhenti tayang sebelum mulai tayang ditolak sebelum dikirim', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Ngawur')
        ->set('hargaTeks', '1000000')
        ->set('tayangMulai', '2026-11-10T08:00')
        ->set('tayangSampai', '2026-11-01T08:00')
        ->call('simpan')
        ->assertHasErrors(['tayangSampai']);

    Http::assertNotSent(fn ($request) => $request->method() === 'POST');
});

/* ------------------------------- DISKON ------------------------------- */

test('diskon terhitung dari selisih harga', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('hargaAsliTeks', '1700000')
        ->set('hargaTeks', '1430000')
        // 15,88% dibulatkan ke bawah supaya potongan tidak dilebih-lebihkan
        ->assertSet('diskonPersen', 15);
});

test('harga jual sama atau lebih mahal berarti tanpa diskon', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('hargaAsliTeks', '1000000')
        ->set('hargaTeks', '1000000')
        ->assertSet('diskonPersen', 0)
        ->set('hargaTeks', '1200000')
        ->assertSet('diskonPersen', 0);
});

test('diskon yang ditulis sendiri tidak ditimpa hitungan', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('hargaAsliTeks', '1700000')
        ->set('hargaTeks', '1430000')
        ->assertSet('diskonPersen', 15)
        // Promo memakai angka bulat
        ->set('diskonPersen', 20)
        ->assertSet('diskonOtomatis', false)
        ->set('hargaTeks', '1400000')
        ->assertSet('diskonPersen', 20)
        ->call('hitungDiskon')
        ->assertSet('diskonPersen', 17)
        ->assertSet('diskonOtomatis', true);
});

/* --------------------------- PESAN SUKSES --------------------------- */

test('setelah simpan, pemberitahuan dan tujuan pindah dikirim bersama', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Baru')
        ->set('hargaTeks', '1000000')
        ->call('simpan')
        ->assertHasNoErrors()
        // Sengaja TIDAK redirect dari server: perpindahan dikerjakan skrip
        // setelah popupnya menutup, supaya popup tidak ikut terbuang saat
        // isi halaman ditukar.
        ->assertNoRedirect()
        ->assertDispatched('orcha-sukses-pindah',
            message: 'Paket wisata ditambahkan.',
            url: route('admin.orcha.paket'));
});

test('mengubah paket juga menampilkan pemberitahuan sebelum berpindah', function () {
    Http::fake(['*' => Http::response(['data' => ['nama' => 'Lama', 'harga' => 1000000], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->set('nama', 'Open Trip Diubah')
        ->set('hargaTeks', '1200000')
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertDispatched('orcha-sukses-pindah', message: 'Paket wisata diperbarui.');
});

test('popup menutup dulu, baru halaman berpindah', function () {
    $skrip = file_get_contents(base_path(
        'resources/views/livewire/pages/admin/orcha/partials/skrip.blade.php'
    ));

    // Perpindahan menempel pada .then(...) milik Swal, bukan dijalankan
    // langsung — itulah yang menjamin popupnya sempat terbaca.
    expect($skrip)->toContain("Livewire.on('orcha-sukses-pindah'")
        ->and($skrip)->toContain('}).then(pindah);')
        ->and($skrip)->toContain('allowOutsideClick: false');

    // Tidak ada lagi jalur titip-sesi yang dulu ikut ditampilkan penampil
    // bawaan layout sampai popupnya tertutup dua kali.
    $trait = file_get_contents(base_path(
        'app/Livewire/Pages/Admin/Orcha/Concerns/MemanggilOrcha.php'
    ));
    expect($trait)->not->toContain('flash(');
});

test('kendaraan baru juga menitipkan pesannya', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'All New Avanza')
        ->set('tarifHariTeks', '350000')
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertDispatched('orcha-sukses-pindah',
            message: 'Kendaraan ditambahkan.',
            url: route('admin.orcha.armada'));
});

/* --------------------------- FORMAT RUPIAH --------------------------- */

test('isian harga tampil bertitik dan tetap terkirim sebagai angka', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Banyuwangi')
        ->set('hargaTeks', '1430000')
        ->assertSet('hargaTeks', '1.430.000')
        ->assertSet('harga', 1430000)
        ->set('hargaAsliTeks', 'Rp 1.700.000')
        ->assertSet('hargaAsliTeks', '1.700.000')
        ->assertSet('hargaAsli', 1700000)
        // Diskon tetap ikut terhitung dari angkanya
        ->assertSet('diskonPersen', 15)
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request['harga'] == 1430000
        && $request['harga_asli'] == 1700000);
});

test('ketikan berantakan tetap terbaca sebagai angka', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('hargaTeks', 'Rp1,430,000.-')
        ->assertSet('harga', 1430000)
        ->assertSet('hargaTeks', '1.430.000')
        // Kosong berarti nol, dan tampil kosong — bukan "0"
        ->set('hargaTeks', '')
        ->assertSet('harga', 0)
        ->assertSet('hargaTeks', '');
});

test('tarif armada juga bertitik', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class)
        ->set('merek', 'Toyota')
        ->set('nama', 'All New Avanza')
        ->set('tarifHariTeks', '350000')
        ->set('tarifJamTeks', '55000')
        ->assertSet('tarifHariTeks', '350.000')
        ->assertSet('tarifHari', 350000)
        ->assertSet('tarifJam', 55000)
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request['tarif_hari'] == 350000
        && $request['tarif_jam'] == 55000);
});

test('setiap isian uang ditandai supaya bisa diformat sambil diketik', function () {
    $berkas = [
        'resources/views/livewire/pages/admin/orcha/paket-wisata/form.blade.php',
        'resources/views/livewire/pages/admin/orcha/armada/form.blade.php',
    ];

    foreach ($berkas as $satu) {
        $isi = file_get_contents(base_path($satu));

        preg_match_all('/<input\b[^>]*wire:model\.blur="\w+Teks"[^>]*>/s', $isi, $isian);

        expect($isian[0])->not->toBeEmpty();

        foreach ($isian[0] as $satuIsian) {
            // Tanpa kelas ini, angkanya baru bertitik setelah pindah kolom.
            expect(str_contains($satuIsian, 'orcha-uang'))->toBeTrue(
                "isian uang tanpa kelas orcha-uang di {$satu}: ".trim(substr($satuIsian, 0, 90))
            );
        }
    }
});

/* ------------------------ BUKTI PEMBAYARAN ------------------------ */

test('halaman bukti pembayaran menampilkan kiriman pelanggan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => [
            'menunggu' => 'Menunggu Dicek', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak',
        ]]]),
        '*' => Http::response(['data' => [[
            'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
            'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
            'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
            'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => '/storage/bukti-bayar/a.webp',
            'catatan' => null, 'status' => 'menunggu', 'status_label' => 'Menunggu Dicek',
            'catatan_admin' => null,
            'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '0812', 'keterangan' => 'Open Trip Banyuwangi'],
            'dibuat_pada' => now()->toIso8601String(),
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran')
        ->assertOk()
        ->assertSee('OT-1508-A7K3')
        ->assertSee('Rp 500.000')
        ->assertSee('Budi Santoso')
        ->assertSee('Menunggu Dicek');
});

test('sewa yang perlu ditindak dihitung di bilah samping dan lonceng', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 2, 'telat' => 1, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Dua hal, sengaja tetap terpisah karena beda urusannya: pemesanan yang
     | belum disentuh, dan unit yang sudah lewat tenggat tetapi belum dicatat
     | kembali. Yang kedua paling mahal dibiarkan — dendanya terus berjalan
     | tanpa pernah ditetapkan, dan unitnya tidak bisa disewakan lagi.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)
        // Bilah samping: satu menu, satu angka, pemecahannya di judul tempel
        ->toContain('2 pemesanan baru · 1 unit belum kembali, lewat tenggat')
        // Lonceng: tiap urusan barisnya sendiri
        ->toContain('2 pemesanan sewa baru')
        ->toContain('Pemesannya sedang menunggu dijawab')
        ->toContain('1 unit belum kembali, sudah lewat tenggat')
        ->toContain('Unitnya masih di luar')
        ->toContain('Catat serah terimanya');
});

test('unit yang telat tapi SUDAH kembali tidak lagi dihitung', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 2]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Sewa yang kembalinya telat DAN dendanya sudah ditetapkan tidak menuntut
     | apa-apa lagi — pekerjaan menagihnya selesai. Yang tersisa dari perkara
     | itu adalah unit yang sudah kembali tetapi dendanya belum ditetapkan:
     | unitnya aman, uangnya belum, dan nota yang dikirim ke penyewa masih
     | menyebut Rp 0.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->toContain('2 denda belum ditetapkan')
        ->toContain('masih menyebut Rp 0')
        ->toContain('Tetapkan dendanya')
        // Yang belum kembali tidak disebut, karena memang tidak ada
        ->not->toContain('belum kembali, sudah lewat tenggat');
});

test('penanda dilupakan begitu admin mengubah sesuatu di orcha', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/status' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 1, 'nominal' => 90000]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 1, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 1]]),
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(balasanPembatalan()),
    ]);

    // Hitungannya diisi dulu, seolah admin baru saja membuka halaman.
    App\Support\OrchaPembatalanPerhatian::ambil();
    App\Support\OrchaMenungguDicek::ambil();
    App\Support\OrchaSewaPerhatian::ambil();
    App\Support\OrchaPesanPerhatian::ambil();

    expect(Cache::has('orcha.pembatalan.perhatian'))->toBeTrue();

    /*
     | Angkanya disimpan semenit supaya tiap perpindahan halaman tidak menembak
     | Orcha lagi — tetapi begitu admin sendiri yang mengubah sesuatu, simpanan
     | itu berubah dari penghemat jadi pembohong: dana yang baru saja ditandai
     | terkirim tetap terhitung "belum dikirim", dan admin mengira tekanannya
     | tidak tersimpan.
     */
    Livewire::actingAs(adminOrcha())
        ->test(OrchaPembatalanDetail::class, ['id' => 6])
        ->set('statusBaru', 'dana_dikirim')
        ->call('simpan');

    // Ketiganya dilupakan, bukan yang kelihatan berhubungan saja: satu
    // perubahan status bisa menggeser lebih dari satu hitungan.
    expect(Cache::has('orcha.pembatalan.perhatian'))->toBeFalse()
        ->and(Cache::has('orcha.pembayaran.menunggu'))->toBeFalse()
        ->and(Cache::has('orcha.penyewaan.perhatian'))->toBeFalse()
        ->and(Cache::has('orcha.pesan.perhatian'))->toBeFalse();
});

test('pesan belum dibaca dihitung di bilah samping dan lonceng', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/pesan/perhatian' => Http::response(['data' => ['belum_dibaca' => 5, 'baru' => 2, 'lama' => 3]]),
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Tanya Open Trip']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Dihitung dari yang BELUM DIBACA, bukan yang belum dibalas: balasannya
     | dikirim lewat WhatsApp, di luar sistem, jadi Orcha tidak pernah tahu
     | sebuah pesan sudah dijawab atau belum. Kalimatnya menyebut itu apa
     | adanya — menulis "belum ditindaklanjuti" akan menjanjikan sesuatu yang
     | angkanya tidak bisa menepatinya.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)
        // Bilah samping: satu menu, satu angka, pemecahannya di judul tempel
        ->toContain('2 baru masuk · 3 belum dibaca lewat sehari')
        // Lonceng: yang baru dan yang menganggur dipisah, karena yang
        // didahulukan admin memang berbeda
        ->toContain('2 pesan kontak baru masuk')
        ->toContain('3 pesan belum dibaca lewat sehari')
        ->toContain('Baca sekarang')
        // Tautannya mendarat di kotak masuk yang SUDAH tersaring
        ->toContain('belumDibaca=1')
        // Lencana emasnya menghitung tiap pesan SEKALI. "baru" dan "lama"
        // pecahan dari angka yang sama, jadi ikut menjumlahkan ketiganya
        // membuat lima pesan terbaca sepuluh.
        ->toContain('5 hal di Orcha menunggu ditindak');
});

test('kotak masuk yang sudah dibaca semua tidak menumbuhkan penanda', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/pesan/perhatian' => Http::response(['data' => ['belum_dibaca' => 0, 'baru' => 0, 'lama' => 0]]),
        '*/rujukan' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->not->toContain('pesan kontak baru masuk')
        ->and($isi)->not->toContain('belum dibaca lewat sehari');
});

test('tautan lonceng mendarat di kotak masuk yang sudah tersaring', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Tanya Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    // Tanpa ini tautannya mendarat di seluruh daftar, dan admin harus menekan
    // penyaringnya sendiri setelah sampai — persis yang sudah dijanjikan
    // kalimat di loncengnya.
    Livewire::actingAs(adminOrcha())
        ->withUrlParams(['belumDibaca' => true])
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->assertSet('hanyaBelumDibaca', true);

    // Dituntut cocok, bukan sekadar "tidak ada yang salah": penjagaan berbentuk
    // "kalau URL-nya /pesan maka harus belum_dibaca=1" lolos dengan sendirinya
    // bila permintaannya tidak pernah berangkat.
    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/pesan?')
        && str_contains($permintaan->url(), 'belum_dibaca=1'));
});

test('pembatalan yang perlu ditindak dihitung di bilah samping dan lonceng', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 3, 'diproses' => 0, 'disetujui' => 2]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Dua hal, sengaja tetap terpisah karena beda perbuatannya: yang keputusannya
     | belum diambil, dan yang sudah disetujui tetapi dananya belum dikirim.
     |
     | Yang kedua paling mahal dibiarkan — uang pelanggan sudah dinyatakan
     | kembali tetapi belum berangkat ke mana-mana, dan yang menunggu bukan lagi
     | jawaban melainkan uangnya sendiri.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)
        // Bilah samping: satu menu, satu angka, pemecahannya di judul tempel
        ->toContain('3 diajukan · 2 disetujui, dana belum dikirim')
        // Lonceng: tiap status barisnya sendiri, dengan ajakan masing-masing
        ->toContain('3 pembatalan baru diajukan')
        ->toContain('Periksa pengajuannya')
        ->toContain('2 pengembalian dana belum dikirim')
        ->toContain('Kirim dananya')
        // Tautannya membuka daftar yang SUDAH tersaring, bukan seluruh daftar
        ->toContain('filterStatus=diajukan')
        ->toContain('filterStatus=disetujui')
        // Lencana emasnya menjumlah seluruhnya
        ->toContain('5 hal di Orcha menunggu ditindak');
});

test('pembatalan yang sudah selesai tidak menumbuhkan penanda', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Yang dananya sudah dikirim dan yang ditolak tidak menuntut apa pun lagi.
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->not->toContain('pembatalan baru diajukan')
        ->and($isi)->not->toContain('pengembalian dana belum dikirim');
});

test('lencana lonceng menjumlah seluruh urusan orcha, bukan salah satunya', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 4, 'nominal' => 3400000]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 2, 'telat' => 1, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->toContain('7 hal di Orcha menunggu ditindak');
});

test('sewa yang tenang tidak menumbuhkan penanda apa pun', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->not->toContain('orcha-hitung-menu')
        ->and($isi)->not->toContain('nb-orcha-judul');
});

test('bukti yang menunggu dicek ikut masuk lonceng, dengan bagiannya sendiri', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 4, 'nominal' => 3400000]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Dibedakan dari notifikasi lemon karena sifatnya memang berbeda.
     |
     | Notifikasi lemon adalah kejadian: sudah terjadi, bisa ditandai dibaca,
     | lalu selesai. Bukti yang menunggu dicek adalah PEKERJAAN — ia tidak
     | hilang karena dibaca, hanya karena dikerjakan.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)
        // Bagiannya sendiri, berlabel Orcha
        ->toContain('nb-orcha-judul')
        ->toContain('Orcha Journey')
        ->toContain('4 bukti transfer menunggu dicek')
        ->toContain('Rp 3.400.000')
        // Lencana emas di sisi berlawanan dari lencana merah lemon
        ->toContain('nb-orcha-badge')
        // Tautan biasa: tidak ada yang perlu ditandai dibaca
        ->toContain('nb-item nb-orcha');
});

test('tanpa bukti menunggu, lonceng tidak menumbuhkan bagian orcha', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->not->toContain('nb-orcha-judul')
        ->and($isi)->not->toContain('nb-orcha-badge');
});

test('admin tanpa izin orcha tidak dibebani hitungan orcha di loncengnya', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    // Sekaligus menghemat: tidak perlu menembak Orcha untuk orang yang memang
    // tidak mengurusnya.
    $isi = tanpaGaya($this->actingAs(adminOrcha([]))
        ->get('/admin/profile')->getContent());

    expect($isi)->not->toContain('nb-orcha-badge');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), '/pembayaran/menunggu'));
});

test('bukti yang menunggu dicek dihitung di bilah samping', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 7, 'nominal' => 4500000]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Sebelumnya tidak ada tanda apa pun: bukti yang masuk hanya ketahuan kalau
     | admin kebetulan membuka halamannya, dan pelanggan yang sudah mentransfer
     | menunggu tanpa tahu bahwa buktinya belum dibuka siapa pun.
     */
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')
        ->assertOk()
        ->assertSee('orcha-hitung-menu', false)
        ->assertSee('7 bukti transfer menunggu dicek');
});

test('tanpa bukti yang menunggu, menunya tidak diberi angka', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Angka nol di bilah samping tidak mengabarkan apa pun, dan lencana yang
    // selalu ada lama-lama berhenti dibaca.
    // Gayanya dibuang dulu: nama kelasnya juga muncul di dalam blok <style>
    // bilah samping dan ikut terhitung.
    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent();

    expect(tanpaGaya($isi))->not->toContain('orcha-hitung-menu');
});

test('orcha yang tidak bisa dihubungi tidak merobohkan bilah samping', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response([], 500),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Bilah samping tergambar di TIAP halaman admin. Gangguan sambungan tidak
    // pantas jadi halaman galat; yang hilang cukup lencananya.
    // Gayanya dibuang dulu: nama kelasnya juga muncul di dalam blok <style>
    // bilah samping dan ikut terhitung.
    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent();

    expect(tanpaGaya($isi))->not->toContain('orcha-hitung-menu');
});

test('hitungan disimpan sebentar, tidak menembak orcha tiap halaman', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 3, 'nominal' => 900000]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->actingAs(adminOrcha())->get('/admin/orcha/dashboard')->assertOk();
    $this->actingAs(adminOrcha())->get('/admin/orcha/dashboard')->assertOk();
    $this->actingAs(adminOrcha())->get('/admin/orcha/dashboard')->assertOk();

    // Tiga halaman, satu permintaan. Tanpa simpanan, tiap perpindahan halaman
    // menembak Orcha lagi hanya untuk satu bilangan yang jarang berubah.
    $tembakan = 0;
    Http::assertSent(function ($p) use (&$tembakan) {
        if (str_contains($p->url(), '/pembayaran/menunggu')) {
            $tembakan++;
        }

        return true;
    });

    expect($tembakan)->toBe(1);
});

test('penomoran halaman bukti pembayaran berupa tautan, bukan tombol', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    $b = fn (int $i) => [
        'id' => $i, 'kode' => 'OT-1608-FXYK', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 858000, 'nominal_formatted' => 'Rp 858.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi '.$i, 'bukti' => null, 'catatan' => null,
        'status' => 'menunggu', 'status_label' => 'Menunggu Dicek', 'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi', 'whatsapp' => '0812', 'keterangan' => 'Open Trip'],
        'dibuat_pada' => now()->toIso8601String(),
    ];

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response([
            'data' => array_map($b, range(1, 10)),
            'meta' => ['halaman' => 2, 'halaman_terakhir' => 5, 'total' => 47, 'per_halaman' => 10],
        ]),
    ]);

    /*
     | Sebagai tombol Livewire, berpindah halaman menuntut JavaScript hidup
     | lebih dulu — dan admin yang tombolnya diam tidak punya cara lain sama
     | sekali untuk melihat data di halaman kedua.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran?halaman=2')->assertOk()->getContent());

    expect($isi)->toContain('halaman=3')
        ->toContain('Menampilkan <strong>11–20</strong> dari')
        ->not->toContain('wire:click="keHalaman');
});

test('judul kolom bukti pembayaran ditengahkan', function () {
    Cache::forget('orcha.pembayaran.menunggu');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran')->assertOk()->getContent();

    // Dua judul memakai .text-end bawaan Bootstrap; tanpa ditimpa, barisnya
    // setengah tengah setengah kanan.
    expect(tanpaGaya($isi))->toContain('orcha-tabel-bayar')
        ->and($isi)->toContain('.orcha-tabel-bayar thead th.text-end');
});

test('tombol cek pembayaran adalah tautan ke lembarnya sendiri', function () {
    $baris = [
        'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => '/storage/bukti-bayar/a.webp',
        'catatan' => 'Transfer dari rekening istri', 'status' => 'menunggu',
        'status_label' => 'Menunggu Dicek', 'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '0812', 'keterangan' => 'Open Trip Banyuwangi'],
        'dibuat_pada' => now()->toIso8601String(),
    ];

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => [
            'menunggu' => 'Menunggu Dicek', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak',
        ]]]),
        '*' => Http::response(['data' => [$baris], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    /*
     | Seluruh baris dulu dititipkan lewat atribut HTML sebagai JSON — kode,
     | nominal, nama pengirim, catatan bebas pelanggan, sampai jalur berkas
     | buktinya — disalin utuh untuk SETIAP baris di halaman.
     |
     | Isinya data pribadi, dan tidak satu pun darinya perlu berkeliling ke
     | peramban hanya supaya admin bisa menekan satu tombol.
     */
    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran')->assertOk()->getContent();

    expect(tanpaGaya($isi))
        ->toContain('/admin/orcha/pembayaran/3/cek')
        ->not->toContain('wire:click="buka(')
        ->not->toContain('buka({')
        ->not->toContain('Transfer dari rekening istri')
        // Isi lembarnya tidak lagi ikut tergambar di daftar
        ->not->toContain('Simpan Status');
});

test('tombol simpan status berganti pemintal selama disimpan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => [
            'menunggu' => 'Menunggu Dicek', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak',
        ]]]),
        '*' => Http::response(['data' => [
            'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
            'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
            'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
            'atas_nama_pengirim' => 'Budi', 'bukti' => null, 'catatan' => null,
            'status' => 'menunggu', 'status_label' => 'Menunggu Dicek', 'catatan_admin' => null,
            'pesanan' => ['nama' => 'Budi', 'whatsapp' => '0812', 'keterangan' => 'Open Trip'],
            'dibuat_pada' => now()->toIso8601String(),
        ]]),
    ]);

    /*
     | Menyimpan status berarti menembak Orcha, dan Orcha sekaligus mengirim
     | email ke pelanggan — jadi jedanya terasa. Tombol yang tidak berubah
     | apa-apa selama itu membuat admin mengira tekanannya tidak masuk lalu
     | menekannya lagi, dan email keduanya sudah berangkat sebelum ia sempat
     | menyesal.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran/3/cek')->assertOk()->getContent());

    expect($isi)->toContain('spinner-border spinner-border-sm')
        ->toContain('Menyimpan...')
        // Menyasar simpan() secara khusus: tanpa itu, tombolnya ikut memintal
        // setiap kali ada permintaan lain di halaman yang sama.
        ->toContain('wire:target="simpan"');

    /*
     | Dan tersembunyi sejak awal lewat GAYA, bukan hanya lewat skrip.
     |
     | Livewire menyembunyikannya dengan menulis style="display:none" saat
     | halaman dipasang. Sebelum skripnya sempat jalan — atau bila ia tidak
     | jalan sama sekali — pemintal dan tulisan "Menyimpan…" tergambar dari
     | awal, berdampingan dengan tulisan tombol yang sebenarnya.
     */
    $halaman = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran/3/cek')->getContent();

    expect($halaman)->toContain('[wire\\:loading] { display: none; }');

    // Kelas display Bootstrap memakai !important dan akan mengalahkan aturan
    // itu, jadi pembungkus pemintalnya tidak boleh memakainya.
    expect($isi)->not->toContain('wire:loading wire:target="simpan" class="d-');
});

test('lembar cek pembayaran mengisi dirinya dari nomor di alamat', function () {
    $baris = [
        'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => null,
        'catatan' => null, 'status' => 'menunggu', 'status_label' => 'Menunggu Dicek',
        'catatan_admin' => 'Sudah dicocokkan dengan mutasi',
        'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '0812', 'keterangan' => 'Open Trip Banyuwangi'],
        'dibuat_pada' => now()->toIso8601String(),
    ];

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => [
            'menunggu' => 'Menunggu Dicek', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak',
        ]]]),
        '*' => Http::response(['data' => $baris]),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran/3/cek')
        ->assertOk()
        ->assertSee('Cek Bukti Pembayaran')
        ->assertSee('OT-1508-A7K3')
        ->assertSee('Rp 500.000')
        ->assertSee('Sudah dicocokkan dengan mutasi', false);
});

test('nomor bukti yang sudah tidak ada memberi jalan kembali, bukan lembar kosong', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => null]),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran/999/cek')
        ->assertOk()
        ->assertSee('tidak bisa dibuka')
        ->assertSee('Kembali ke daftar');
});

test('menyimpan status meneruskan keputusannya ke orcha', function () {
    $baris = [
        'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => null, 'catatan' => null,
        'status' => 'menunggu', 'status_label' => 'Menunggu Dicek', 'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '0812', 'keterangan' => 'Open Trip Banyuwangi'],
        'dibuat_pada' => now()->toIso8601String(),
    ];

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => [
            'menunggu' => 'Menunggu Dicek', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak',
        ]]]),
        '*/status' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*' => Http::response(['data' => $baris]),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranCek::class, ['pembayaran' => 3])
        ->set('statusBaru', 'diterima')
        ->set('catatanAdmin', 'Cocok dengan mutasi 15 Agu')
        ->call('simpan');

    Http::assertSent(fn ($p) => ! str_contains($p->url(), '/status')
        || ($p['status'] === 'diterima' && $p['catatan_admin'] === 'Cocok dengan mutasi 15 Agu'));
});

test('bukti transfer dikelompokkan per kode pesanan', function () {
    $bukti = fn (array $ubah) => array_merge([
        'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => null, 'catatan' => null,
        'status' => 'menunggu', 'status_label' => 'Menunggu Dicek', 'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '0812', 'keterangan' => 'Open Trip Banyuwangi'],
        'dibuat_pada' => '2026-08-15T10:00:00+07:00',
    ], $ubah);

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        // Sengaja diselang-seling seperti yang datang dari Orcha: urutannya
        // menurut waktu kirim, bukan menurut pesanan.
        '*' => Http::response(['data' => [
            $bukti(['id' => 1, 'kode' => 'OT-1508-A7K3', 'nominal' => 500000,
                'nominal_formatted' => 'Rp 500.000', 'status' => 'diterima', 'status_label' => 'Diterima']),
            $bukti(['id' => 2, 'kode' => 'OT-1508-ZZZZ', 'nominal' => 300000, 'nominal_formatted' => 'Rp 300.000']),
            $bukti(['id' => 3, 'kode' => 'OT-1508-A7K3', 'nominal' => 700000,
                'nominal_formatted' => 'Rp 700.000', 'jenis_label' => 'Pelunasan',
                'dibuat_pada' => '2026-08-16T09:00:00+07:00',
                'status' => 'diterima', 'status_label' => 'Diterima']),
        ], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 3]]),
    ]);

    $halaman = $this->actingAs(adminOrcha())->get('/admin/orcha/pembayaran')->assertOk();

    // Dua bukti pesanan yang sama berkumpul, dan totalnya sudah dijumlahkan —
    // pertanyaan "pesanan ini sudah masuk berapa" tidak lagi dihitung manual.
    $halaman->assertSee('diterima Rp 1.200.000')
        ->assertSee('Bukti 1 dari 2')
        ->assertSee('Bukti 2 dari 2')
        // Pesanan lain berdiri sendiri, tidak ikut terjumlah
        ->assertSee('OT-1508-ZZZZ')
        ->assertSee('Bukti 1 dari 1');

    // Yang masih menunggu bukan uang, jadi tidak masuk hitungan diterima
    $halaman->assertSee('belum ada yang diterima');
});

test('kabar whatsapp menyebut sisa tagihan dan emojinya terbaca', function () {
    $baris = [
        'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 858000, 'nominal_formatted' => 'Rp 858.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => null, 'catatan' => null,
        'status' => 'diterima', 'status_label' => 'Diterima', 'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '081234567890',
            'keterangan' => 'Open Trip Banyuwangi',
            'tagihan' => ['sisa_teks' => 'Rp 2.002.000', 'lunas' => false]],
        'dibuat_pada' => '2026-08-15T10:00:00+07:00',
    ];

    Http::fake(['*' => Http::response(['data' => [$baris], 'meta' => []])]);

    $komponen = Livewire::actingAs(adminOrcha())->test(OrchaPembayaranList::class);
    $tautan = $komponen->instance()->tautanWa($baris);
    $pesan = $komponen->instance()->pesanWa($baris);

    // Nomor 08xx harus jadi 62xx; WhatsApp menolak awalan nol
    expect($tautan)->toStartWith('https://api.whatsapp.com/send?phone=6281234567890&text=')
        // Spasi TIDAK boleh jadi "+": WhatsApp menampilkan tanda plus itu apa
        // adanya, dan kalimatnya jadi penuh plus alih-alih spasi
        ->and($tautan)->not->toContain('+')
        ->and($tautan)->toContain('%20');

    // Yang ditanyakan pelanggan begitu buktinya diterima adalah sisanya
    expect($pesan)->toContain('Rp 2.002.000')
        ->and($pesan)->toContain('Budi Santoso')
        ->and($pesan)->toContain('OT-1508-A7K3');
});

test('emoji tidak pernah ikut melewati respons server', function () {
    $baris = [
        'kode' => 'OT-1508-A7K3', 'nominal_formatted' => 'Rp 858.000', 'status' => 'diterima',
        'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi', 'whatsapp' => '081234567890', 'keterangan' => null,
            'tagihan' => ['lunas' => true]],
    ];

    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);
    $komponen = Livewire::actingAs(adminOrcha())->test(OrchaPembayaranList::class)->instance();

    $emoji = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u';

    // Inilah inti perbaikannya. Empat putaran percobaan menunjukkan sisi kami
    // bersih di tiap pemeriksaan, tapi yang sampai ke WhatsApp tetap tanda
    // tanya — jadi emojinya tidak dikirim sama sekali. Server mengirim
    // penanda, peramban yang merakitnya, persis seperti halaman detail order.
    expect($komponen->pesanWa($baris))->not->toMatch($emoji)
        ->and($komponen->pesanWa($baris))->toContain('[[E:1F44B]]')
        // Tautannya pun bersih: yang tertulis di href adalah cadangan tanpa
        // emoji, untuk keadaan ketika skrip perakitnya tidak sempat jalan.
        ->and($komponen->tautanWa($baris))->not->toMatch($emoji)
        ->and($komponen->tautanWa($baris))->not->toContain('%5B%5BE')
        // Cadangan itu harus tetap kalimat utuh, bukan penanda mentah
        ->and($komponen->pesanWaPolos($baris))->not->toContain('[[E:')
        ->and($komponen->pesanWaPolos($baris))->toContain('Kode pesanan: *OT-1508-A7K3*');
});

test('emoji kabar whatsapp bisa dimatikan bila di lapangan tetap rusak', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);
    $komponen = Livewire::actingAs(adminOrcha())->test(OrchaPembayaranList::class)->instance();

    $baris = [
        'kode' => 'OT-1508-A7K3', 'nominal_formatted' => 'Rp 858.000', 'status' => 'diterima',
        'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi', 'whatsapp' => '0812', 'keterangan' => null,
            'tagihan' => ['lunas' => true]],
    ];

    config()->set('orcha.emoji_wa', false);
    $tanpa = $komponen->pesanWa($baris);

    // Tanpa emoji pun kabarnya harus tetap utuh dan terbaca: yang menyusun
    // bentuknya adalah tebal bawaan WhatsApp dan baris baru, bukan emojinya.
    expect($tanpa)->not->toContain('[[E:')
        ->and($tanpa)->toContain('Kode pesanan: *OT-1508-A7K3*')
        ->and($tanpa)->toContain('LUNAS')
        ->and($tanpa)->toContain('Halo Budi');
});

test('pesan whatsapp ikut tersalin supaya emoji tidak bergantung tautan', function () {
    $baris = [
        'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 858000, 'nominal_formatted' => 'Rp 858.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => null, 'catatan' => null,
        'status' => 'diterima', 'status_label' => 'Diterima', 'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '081234567890',
            'keterangan' => 'Open Trip Banyuwangi', 'tagihan' => ['lunas' => true]],
        'dibuat_pada' => '2026-08-15T10:00:00+07:00',
    ];

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['diterima' => 'Diterima']]]),
        '*' => Http::response(['data' => [$baris], 'meta' => []]),
    ]);

    // Sebagian versi aplikasi WhatsApp salah membaca sandi persen pada
    // tautannya, dan tiap emoji berubah jadi tanda tanya sebelum tampil.
    // Teks yang disalin ke papan tempel tidak melewati penerjemahan itu sama
    // sekali — menempel selalu memindahkan karakter yang sama persis.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran')
        ->assertOk()
        ->assertSee('data-wa-pesan', false)
        ->assertSee('[[E:1F44B]]', false)
        ->assertSee('Bila emojinya berantakan di WhatsApp, tempel saja');
});

test('kabar whatsapp berbeda bunyinya menurut status bukti', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);
    $komponen = Livewire::actingAs(adminOrcha())->test(OrchaPembayaranList::class)->instance();

    $baris = fn (array $ubah) => array_merge([
        'kode' => 'OT-1508-A7K3', 'nominal_formatted' => 'Rp 858.000', 'catatan_admin' => null,
        'pesanan' => ['nama' => 'Budi', 'whatsapp' => '0812', 'keterangan' => null, 'tagihan' => []],
    ], $ubah);

    expect($komponen->pesanWa($baris(['status' => 'menunggu'])))->toContain('sedang kami periksa')
        ->and($komponen->pesanWa($baris(['status' => 'diterima', 'pesanan' => [
            'nama' => 'Budi', 'whatsapp' => '0812', 'keterangan' => null,
            'tagihan' => ['lunas' => true, 'sisa_teks' => 'Rp 0'],
        ]])))->toContain('LUNAS')
        // Alasan penolakan ikut terbawa; tanpa itu pelanggan tidak tahu apa
        // yang harus diperbaiki dan hanya tahu buktinya ditolak
        ->and($komponen->pesanWa($baris([
            'status' => 'ditolak', 'catatan_admin' => 'Nominalnya kurang Rp 50.000.',
        ])))->toContain('Nominalnya kurang Rp 50.000.');

    // Tanpa nomor WhatsApp tidak ada tautan yang bisa dibuka — bukan tautan
    // rusak yang menuju wa.me kosong
    expect($komponen->tautanWa($baris(['status' => 'menunggu', 'pesanan' => null])))->toBeNull();
});

test('lembar cek pembayaran menampilkan keputusan yang sedang berlaku', function () {
    $baris = [
        'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 858000, 'nominal_formatted' => 'Rp 858.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => '/storage/bukti-bayar/a.webp',
        'catatan' => 'Transfer dari rekening istri saya.',
        'status' => 'diterima', 'status_label' => 'Diterima', 'catatan_admin' => 'Cocok mutasi.',
        'pesanan' => ['nama' => 'Budi Santoso', 'whatsapp' => '0812', 'keterangan' => 'Open Trip Banyuwangi'],
        'dibuat_pada' => '2026-08-15T10:00:00+07:00',
    ];

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => [
            'menunggu' => 'Menunggu Dicek', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak',
        ]]]),
        // /pembayaran/{id} mengembalikan satu baris, bukan daftar.
        '*' => Http::response(['data' => $baris]),
    ]);

    $lembar = Livewire::actingAs(adminOrcha())
        ->test(App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranCek::class, ['pembayaran' => $baris['id']]);

    // Tanpa penanda "checked" yang ditulis sendiri, tidak ada satu pun pilihan
    // yang tersorot — admin tidak bisa melihat status yang berlaku sekarang,
    // padahal itu titik tolak keputusannya.
    $lembar->assertSeeHtml('value="diterima" checked')
        ->assertSeeHtml('value="menunggu"')
        ->assertSeeHtml('value="ditolak"')
        // Kabar ke pelanggan disebut sebelum tombolnya ditekan, bukan sesudah
        ->assertSee('Pelanggan otomatis dikabari lewat email setelah status disimpan.')
        ->assertSee('Rp 858.000');
});

test('bukti dengan kode tak dikenal ditandai supaya dicocokkan manual', function () {
    Http::fake(['*' => Http::response(['data' => [[
        'id' => 4, 'kode' => 'OT-SALAH', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
        'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
        'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
        'atas_nama_pengirim' => 'Budi', 'bukti' => null, 'catatan' => null,
        'status' => 'menunggu', 'status_label' => 'Menunggu Dicek', 'catatan_admin' => null,
        'pesanan' => null, 'dibuat_pada' => now()->toIso8601String(),
    ]], 'meta' => []]),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran')
        ->assertOk()
        ->assertSee('kode tak dikenal');
});

test('status pembayaran diteruskan ke orcha', function () {
    Http::fake([
        '*/status' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*' => Http::response(['data' => [
            'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
            'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
            'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
            'atas_nama_pengirim' => 'Budi', 'bukti' => null, 'catatan' => null,
            'status' => 'menunggu', 'status_label' => 'Menunggu Dicek', 'catatan_admin' => null,
            'pesanan' => null, 'dibuat_pada' => now()->toIso8601String(),
        ]]),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranCek::class, ['pembayaran' => 3])
        ->set('statusBaru', 'diterima')
        ->set('catatanAdmin', 'Cocok dengan mutasi.')
        ->call('simpan')
        ->assertDispatched('order-updated');

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && str_ends_with($request->url(), '/pembayaran/3/status')
        && $request['status'] === 'diterima');
});

test('daftar pendaftaran mengelompokkan peserta per titik jemput', function () {
    Http::fake(['*' => Http::response(['data' => [[
        'id' => 9, 'kode' => 'OT-1508-A7K3', 'nama' => 'Siti Aminah', 'whatsapp' => '0812',
        'email' => null, 'jumlah_peserta' => 3,
        'peserta' => [
            ['nama' => 'Siti Aminah', 'titik_jemput' => 'Surakarta'],
            ['nama' => 'Budi Santoso', 'titik_jemput' => 'Jogja'],
            ['nama' => 'Rina Wijaya', 'titik_jemput' => 'Surakarta'],
        ],
        'jemput_per_titik' => [
            'Surakarta' => ['Siti Aminah', 'Rina Wijaya'],
            'Jogja' => ['Budi Santoso'],
        ],
        'kesehatan_terisi' => 1, 'kesehatan_lengkap' => false,
        'peserta_belum_isi' => ['Budi Santoso', 'Rina Wijaya'],
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
        'tanggal_berangkat' => '2026-10-19', 'titik_jemput' => 'Surakarta, Jogja',
        'catatan' => null, 'status' => 'baru', 'status_label' => 'Baru',
        'jumlah_riwayat_kesehatan' => 1, 'dibuat_pada' => now()->toIso8601String(),
    ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran')
        ->assertOk()
        // Di daftar yang tampil cukup JUMLAH titiknya. Nama per titik dulu
        // dicetak seluruhnya di sel paket, dan satu pesanan bisa memenuhi
        // setengah layar; sekarang ia ada di tooltip baris, halaman detail, dan
        // manifes tour leader — tempat yang memang dibaca sopir.
        ->assertSee('2 titik jemput')
        ->assertSee('Surakarta: Siti Aminah, Rina Wijaya', false)
        ->assertSee('Jogja: Budi Santoso', false)
        // Kelengkapan kesehatan ikut terlihat tanpa membuka apa pun
        ->assertSee('1/3');
});

/* --------------------- DETAIL PELANGGAN --------------------- */

function balasanDetail(array $ubah = []): array
{
    return ['data' => array_merge([
        'id' => 7,
        'kode' => 'OT-1608-A7KD',
        'nama' => 'Siti Aminah',
        'whatsapp' => '081298765432',
        'email' => 'siti@contoh.test',
        'jumlah_peserta' => 2,
        'peserta' => [
            ['nama' => 'Siti Aminah', 'titik_jemput' => 'Jogja'],
            ['nama' => 'Budi Santoso', 'titik_jemput' => 'Surakarta'],
        ],
        'jemput_per_titik' => ['Jogja' => ['Siti Aminah'], 'Surakarta' => ['Budi Santoso']],
        'kesehatan_terisi' => 1,
        'kesehatan_lengkap' => false,
        'peserta_belum_isi' => ['Budi Santoso'],
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
        'tanggal_berangkat' => '2026-09-12',
        'titik_jemput' => 'Jogja, Surakarta',
        'catatan' => 'Mohon dijemput di gerbang utama.',
        'status' => 'baru',
        'status_label' => 'Baru',
        'jumlah_riwayat_kesehatan' => 1,
        'dibuat_pada' => '2026-08-16T08:00:00+07:00',
        'tagihan' => [
            'total' => 2860000, 'total_teks' => 'Rp 2.860.000',
            'sudah' => 858000, 'sudah_teks' => 'Rp 858.000',
            'sisa' => 2002000, 'sisa_teks' => 'Rp 2.002.000',
            'lunas' => false,
        ],
        'pembayaran' => [[
            'id' => 3, 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
            'nominal' => 858000, 'nominal_formatted' => 'Rp 858.000',
            'tanggal_transfer' => '2026-08-16', 'bank_pengirim' => 'BCA',
            'atas_nama_pengirim' => 'Siti Aminah', 'bukti' => '/storage/bukti.webp',
            'catatan' => null, 'status' => 'menunggu', 'status_label' => 'Menunggu Dicek',
            'catatan_admin' => null, 'dibuat_pada' => '2026-08-16T08:05:00+07:00',
        ]],
        'pembatalan' => null,
    ], $ubah)];
}

test('detail pelanggan menampilkan tagihan, peserta, dan bukti bayarnya', function () {
    Http::fake(['*/pendaftaran/7' => Http::response(balasanDetail())]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7')
        ->assertOk()
        ->assertSee('Siti Aminah')
        ->assertSee('OT-1608-A7KD')
        // Posisi tagihan: yang paling sering ditanyakan lewat WhatsApp
        ->assertSee('Rp 2.860.000')
        ->assertSee('Rp 858.000')
        ->assertSee('Rp 2.002.000')
        // Peserta berikut titik jemput dan status pengisian kesehatannya
        ->assertSee('Budi Santoso')
        ->assertSee('Surakarta')
        ->assertSee('Belum mengisi')
        ->assertSee('Bukti Pembayaran');
});

function balasanArmada(array $ubah = []): array
{
    return ['data' => [array_merge([
        'id' => 1, 'uuid' => 'abc', 'nama' => 'All New Avanza', 'merek' => 'Toyota',
        'jenis' => 'mobil', 'jenis_label' => 'Mobil', 'nopol' => 'AB 1234 CD',
        'kapasitas' => 7, 'transmisi_tersedia' => ['Matic'], 'transmisi_label' => 'Matic',
        'tarif' => ['jam' => 55000, '12jam' => 280000, 'hari' => 500000, 'sopir_per_hari' => 150000],
        'gambar' => null, 'tersedia' => true, 'jumlah_penyewaan' => 12,
        'kondisi' => [
            'diperiksa_pada' => '2026-08-15T10:00:00+07:00', 'rusak' => 1, 'lecet' => 1,
            'hilang' => 0, 'perlu_perhatian' => true,
            'rincian' => [
                ['bagian' => 'Kaca & spion', 'kondisi' => 'Rusak', 'nilai' => 'rusak'],
                ['bagian' => 'Bodi depan & bemper', 'kondisi' => 'Lecet / minor', 'nilai' => 'lecet'],
            ],
        ],
        'jadwal' => [
            'sedang_disewa' => true, 'kode_berjalan' => 'SK-1608-ZGAN',
            'kembali_pada' => '2026-08-18T08:00:00+07:00',
            'kode_berikutnya' => null, 'mulai_berikutnya' => null,
        ],
    ], $ubah)], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]];
}

test('daftar armada menunjukkan kondisi unit dan jadwal pakainya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['jenis_kendaraan' => ['mobil' => 'Mobil']]]),
        '*' => Http::response(balasanArmada()),
    ]);

    // Kondisi dicatat tiap serah terima lalu tidak pernah terbaca lagi —
    // halaman armada dulu hanya menampilkan tarif. Unit yang kacanya retak
    // bisa disewakan lagi tanpa ada yang tahu.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/armada')
        ->assertOk()
        ->assertSee('All New Avanza')
        ->assertSee('AB 1234 CD')
        ->assertSee('Kondisi terakhir')
        ->assertSee('Kaca &amp; spion: rusak', false)
        // Unit yang sedang keluar disebut, berikut kapan kembalinya
        ->assertSee('Sedang disewa')
        ->assertSee('SK-1608-ZGAN');
});

test('unit yang belum pernah diperiksa disebut apa adanya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['jenis_kendaraan' => ['mobil' => 'Mobil']]]),
        '*' => Http::response(balasanArmada([
            'kondisi' => null,
            'jadwal' => ['sedang_disewa' => false, 'kode_berjalan' => null, 'kembali_pada' => null,
                'kode_berikutnya' => null, 'mulai_berikutnya' => null],
        ])),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/armada')
        ->assertOk()
        ->assertSee('Belum pernah diperiksa serah terima')
        ->assertSee('Siap disewakan');
});

test('unit siap disewakan yang bagiannya rusak diperingatkan, bukan diblokir', function () {
    $unit = balasanArmada()['data'][0];
    $unit['kondisi_terkini'] = ['kaca' => 'rusak', 'bodi_depan' => 'baik'];

    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'jenis_kendaraan' => ['mobil' => 'Mobil'],
            'pemeriksaan_kendaraan' => ['kaca' => 'Kaca & spion', 'bodi_depan' => 'Bodi depan & bemper'],
            'kondisi_pemeriksaan' => ['baik' => 'Baik', 'rusak' => 'Rusak'],
        ]]),
        '*/kendaraan/1' => Http::response(['data' => $unit]),
    ]);

    // Sengaja tidak memblokir: kadang unit tetap layak jalan meski spionnya
    // lecet, dan yang tahu itu pemiliknya — bukan sistem.
    $komponen = Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class, ['kendaraan' => 1])
        ->assertSee('siap disewakan')
        ->assertSee('Kaca &amp; spion', false);

    // Peringatannya ikut hilang begitu bagiannya ditandai baik, tanpa perlu
    // disimpan lebih dulu
    $komponen->set('kondisiIsian.kaca', 'baik')
        ->assertDontSee('masih bermasalah');
});

test('pemilik bisa mencatat kondisi unit sesudah diperbaiki', function () {
    $unit = balasanArmada()['data'][0];
    $unit['kondisi_terkini'] = ['kaca' => 'rusak', 'bodi_depan' => 'lecet'];

    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'jenis_kendaraan' => ['mobil' => 'Mobil'],
            'pemeriksaan_kendaraan' => ['kaca' => 'Kaca & spion', 'bodi_depan' => 'Bodi depan & bemper'],
            'kondisi_pemeriksaan' => ['baik' => 'Baik', 'lecet' => 'Lecet / minor', 'rusak' => 'Rusak'],
        ]]),
        '*/kondisi' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*/kendaraan/1' => Http::response(['data' => $unit]),
    ]);

    // Tanpa jalur ini, unit yang kacanya sudah diganti tetap terbaca "rusak"
    // sampai ada penyewa berikutnya yang mengembalikannya.
    Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class, ['kendaraan' => 1])
        ->assertSet('kondisiIsian.kaca', 'rusak')
        ->call('semuaBaik')
        ->assertSet('kondisiIsian.kaca', 'baik')
        ->assertSet('kondisiIsian.bodi_depan', 'baik')
        ->set('kondisiCatatan', 'Kaca diganti di bengkel Slamet.')
        ->call('simpanKondisi');

    Http::assertSent(fn ($permintaan) => ! str_contains($permintaan->url(), '/kondisi')
        || ($permintaan['kondisi']['kaca'] === 'baik'
            && $permintaan['catatan'] === 'Kaca diganti di bengkel Slamet.'));
});

function balasanPesan(array $ubah = []): array
{
    return ['data' => array_merge([
        'id' => 4, 'nama' => 'Rina Wijaya', 'whatsapp' => '081298765432',
        'email' => 'rina@contoh.test', 'keperluan' => 'open_trip',
        'keperluan_label' => 'Open Trip', 'pesan' => "Halo, saya mau tanya jadwal open trip\nBanyuwangi bulan depan.",
        'sudah_dibaca' => false, 'dibaca_pada' => null,
        'dibuat_pada' => '2026-08-16T10:00:00+07:00',
        'pesanan_terkait' => [[
            'kode' => 'OT-1508-A7K3', 'jenis' => 'open_trip', 'jenis_label' => 'Open Trip',
            'keterangan' => 'Open Trip Banyuwangi', 'status' => 'dp_masuk',
            'status_label' => 'DP Masuk', 'mulai' => '2026-09-10',
        ]],
        'pesan_lain' => [[
            'id' => 2, 'keperluan_label' => 'Sewa Kendaraan',
            'pesan' => 'Pertanyaan saya sebelumnya.', 'sudah_dibaca' => false,
            'dibuat_pada' => '2026-08-14T09:00:00+07:00',
        ]],
    ], $ubah)];
}

test('penyaring kotak masuk menyebutkan jumlah yang belum dibaca', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'halaman_terakhir' => 1, 'total' => 0, 'belum_dibaca' => 7,
        ]]),
    ]);

    // Kotak centang menyembunyikan yang justru ingin diketahui admin sebelum
    // menekannya: masih ada berapa yang menunggu.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pesan')
        ->assertOk()
        ->assertSee('Belum dibaca')
        ->assertSee('Semua')
        ->assertSeeInOrder(['Belum dibaca', '7']);
});

test('detail pesan menunjukkan pesanan pengirim dan riwayat pesannya', function () {
    Http::fake(['*/pesan/4' => Http::response(balasanPesan())]);

    // Pertanyaan pertama admin bukan "apa isinya" — itu sudah terbaca di
    // daftar — melainkan "orang ini siapa": calon pelanggan, atau pemesan yang
    // sedang menanyakan pesanannya sendiri.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pesan/4')
        ->assertOk()
        ->assertSee('Rina Wijaya')
        ->assertSee('jadwal open trip')
        ->assertSee('OT-1508-A7K3')
        ->assertSee('DP Masuk')
        ->assertSee('Pesan Lain dari Pengirim Ini')
        ->assertSee('Balas via WA');
});

test('detail pesan dibagi jadi bagian, sebentuk dengan lembar serah terima', function () {
    Http::fake(['*/pesan/4' => Http::response(balasanPesan())]);

    /*
     | Sebelumnya dua kolom dengan judul kecil bergaya sendiri: kolom kanan
     | memanjang jauh melewati isi pesannya begitu pengirimnya sudah delapan
     | kali memesan, dan halamannya berat sebelah tanpa satu pun keterangan
     | tambahan. Sekarang bagian bertumpuk selebar halaman, memakai kepala
     | bagian yang sama dengan lembar serah terima dan detail pembatalan.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pesan/4')->assertOk()->getContent());

    expect($isi)
        ->toContain('orcha-bagian-kepala')
        ->toContain('orcha-bagian-nomor')
        // Keempat bagiannya bernama, berurutan seperti yang dibaca admin
        ->toContain('Isi Pesan')
        ->toContain('Pengirim')
        ->toContain('Pesanan Pengirim')
        ->toContain('Pesan Lain dari Pengirim Ini')
        // Keterangan kontak berkotak, sama dengan medan di lembar serah terima
        ->toContain('orcha-medan')
        // Kolom lamanya benar-benar ditinggalkan
        ->not->toContain('col-12 col-lg-7');
});

test('tiga perbuatan di detail pesan punya kartunya sendiri, seukuran satu sama lain', function () {
    Http::fake(['*/pesan/4' => Http::response(balasanPesan())]);

    /*
     | Menandai dibaca, menyalin balasan, dan membuka WhatsApp bukan keterangan
     | melainkan perbuatan. Berdesakan di pojok kanan nama pengirim, ketiganya
     | terbaca sebagai hiasan judul. Tingginya 34px lewat .orcha-aksi-sewa —
     | sama dengan kartu Tindakan di detail sewa dan detail pembatalan.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pesan/4')->assertOk()->getContent());

    expect($isi)
        ->toContain('orcha-kartu-tindakan')
        ->toContain('Tandai Dibaca')
        ->toContain('Salin Balasan')
        ->toContain('Balas via WA')
        // Ketiganya seukuran
        ->and(substr_count($isi, 'orcha-aksi-sewa'))->toBe(3);

    // Pita emas di tepi kiri selama masih ada yang harus dikerjakan
    expect($isi)->toContain('orcha-kartu-tindakan ada');
});

test('pesan yang sudah dibaca tidak lagi memasang pita emas di kartu tindakan', function () {
    Http::fake(['*/pesan/4' => Http::response(balasanPesan([
        'sudah_dibaca' => true, 'dibaca_pada' => '2026-08-16T11:00:00+07:00',
    ]))]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pesan/4')->assertOk()->getContent());

    // Tinggal dua perbuatan: menandai dibaca sudah tidak berlaku
    expect($isi)->not->toContain('orcha-kartu-tindakan ada')
        ->and(substr_count($isi, 'orcha-aksi-sewa'))->toBe(2);
});

test('pengirim yang belum pernah memesan disebut apa adanya', function () {
    Http::fake(['*/pesan/4' => Http::response(balasanPesan([
        'pesanan_terkait' => [], 'pesan_lain' => [],
    ]))]);

    // Bukan kekurangan data, melainkan keterangan yang berguna: pesannya
    // pertanyaan calon pelanggan, dan nada balasannya berbeda.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pesan/4')
        ->assertOk()
        ->assertSee('Belum pernah memesan')
        ->assertDontSee('Pesan Lain dari Pengirim Ini');
});

test('pesan yang sudah dibaca tidak menawarkan tombol tandai dibaca', function () {
    Http::fake(['*/pesan/4' => Http::response(balasanPesan([
        'sudah_dibaca' => true, 'dibaca_pada' => '2026-08-16T11:00:00+07:00',
    ]))]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pesan/4')
        ->assertOk()
        ->assertDontSee('Tandai Dibaca')
        ->assertSee('dibaca');
});

test('perkiraan pengembalian tampil di daftar pembatalan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(['data' => [[
            'id' => 6, 'kode_pendaftaran' => 'OT-1608-FXYK',
            'jenis' => 'open_trip', 'jenis_label' => 'Open Trip',
            'nama_pemohon' => 'Suparjiman', 'whatsapp' => '0812', 'email' => null,
            'alasan' => 'kondisi_kesehatan', 'alasan_label' => 'Kondisi kesehatan',
            'penjelasan' => null, 'jumlah_dibatalkan' => 2,
            'rekening' => ['bank' => 'BCA', 'nomor' => '123', 'atas_nama' => 'Suparjiman'],
            'status' => 'diajukan', 'status_label' => 'Diajukan', 'catatan_admin' => null,
            'perkiraan' => [
                'jenis' => 'open_trip', 'batas' => '15 – 30 hari sebelum keberangkatan',
                'persen' => 25, 'lewat' => false,
                'total_teks' => 'Rp 2.000.000', 'dibayar_teks' => 'Rp 2.000.000',
                'potongan_teks' => 'Rp 500.000', 'kembali_teks' => 'Rp 1.500.000',
                'kembali' => 1500000,
            ],
            'dibuat_pada' => '2026-08-16T10:00:00+07:00',
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    // Pertanyaan pertama pada tiap pengajuan selalu "kembalinya berapa".
    // Sebelum ini jawabannya dihitung tangan satu per satu.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan')
        ->assertOk()
        ->assertSee('Rp 1.500.000')
        ->assertSee('dipotong 25%')
        ->assertSee('15 – 30 hari sebelum keberangkatan');
});

test('pengembalian nol ditulis sebagai kalimat, bukan angka nol', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(['data' => [[
            'id' => 6, 'kode_pendaftaran' => 'OT-1608-ZT8K', 'jenis' => 'open_trip',
            'jenis_label' => 'Open Trip', 'nama_pemohon' => 'sofyan', 'whatsapp' => '0812',
            'email' => null, 'alasan' => 'jadwal_bentrok', 'alasan_label' => 'Jadwal bentrok',
            'penjelasan' => null, 'jumlah_dibatalkan' => 1,
            'rekening' => ['bank' => 'mandiri', 'nomor' => '12345678', 'atas_nama' => 'sofyan'],
            'status' => 'disetujui', 'status_label' => 'Disetujui', 'catatan_admin' => null,
            'perkiraan' => [
                'jenis' => 'open_trip', 'batas' => 'Kurang dari 7 hari sebelum keberangkatan',
                'persen' => 100, 'lewat' => true, 'total' => 1900000, 'dibayar' => 570000,
                'potongan' => 570000, 'kembali' => 0,
                'total_teks' => 'Rp 1.900.000', 'dibayar_teks' => 'Rp 570.000',
                'potongan_teks' => 'Rp 570.000', 'kembali_teks' => 'Rp 0',
                'ditetapkan' => true, 'usulan' => 570000, 'usulan_teks' => 'Rp 570.000',
            ],
            'dibuat_pada' => '2026-08-16T10:00:00+07:00',
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    /*
     | Angka nol di kolom rupiah terbaca seperti data yang belum terisi, dan
     | admin yang awam menyangka perhitungannya gagal. Yang sebenarnya terjadi:
     | potongannya sebesar seluruh pembayaran, jadi memang tidak ada yang
     | dikirim balik.
     */
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan')
        ->assertOk()
        ->assertSee('Tidak ada pengembalian')
        ->assertSee('Rp 570.000 yang sudah dibayar dipotong')
        ->assertSee('habis')
        ->assertSee('Kurang dari 7 hari sebelum keberangkatan');
});

test('daftar pembatalan dikelompokkan per jenis pesanannya', function () {
    $baris = function (int $i, string $jenis, string $label) {
        return [
            'id' => $i, 'kode_pendaftaran' => 'OT-160'.$i, 'jenis' => $jenis,
            'jenis_label' => $label, 'nama_pemohon' => 'Budi', 'whatsapp' => '0812',
            'email' => null, 'alasan' => 'jadwal_bentrok', 'alasan_label' => 'Jadwal bentrok',
            'penjelasan' => null, 'jumlah_dibatalkan' => 1,
            'rekening' => ['bank' => 'BCA', 'nomor' => '123', 'atas_nama' => 'Budi'],
            'status' => 'diajukan', 'status_label' => 'Diajukan', 'catatan_admin' => null,
            'perkiraan' => null, 'dibuat_pada' => '2026-08-16T10:00:00+07:00',
        ];
    };

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(['data' => [
            // Sengaja diacak urutannya: pengelompokannya yang harus merapikan
            $baris(1, 'sewa_kendaraan', 'Sewa Kendaraan'),
            $baris(2, 'study_tour', 'Study Tour'),
            $baris(3, 'open_trip', 'Open Trip'),
            $baris(4, 'private_trip', 'Private Trip'),
            $baris(5, 'open_trip', 'Open Trip'),
        ], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 5]]),
    ]);

    /*
     | Empat jenis bercampur dalam satu daftar, dan kebijakan potongannya
     | berbeda-beda. Admin yang membacanya berurutan harus memeriksa kolom
     | "Kode" tiap baris hanya untuk tahu aturan mana yang berlaku.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan')->assertOk()->getContent());

    // Dibandingkan di dalam <tbody> saja: "Open Trip" juga nama menu di bilah
    // samping, dan itu muncul jauh lebih dulu di halaman.
    $tubuh = substr($isi, strpos($isi, '<tbody'), strpos($isi, '</tbody>') - strpos($isi, '<tbody'));
    $urut = fn (string $label) => strpos($tubuh, $label);

    expect($tubuh)->toContain('2 pengajuan di halaman ini')
        ->and($tubuh)->toContain('1 pengajuan di halaman ini')
        // Urutannya tetap: open trip, private trip, study tour, sewa kendaraan
        ->and($urut('Open Trip'))->toBeLessThan($urut('Private Trip'))
        ->and($urut('Private Trip'))->toBeLessThan($urut('Study Tour'))
        ->and($urut('Study Tour'))->toBeLessThan($urut('Sewa Kendaraan'));
});

test('satu tombol aksi berlabel, bukan dua ikon ke halaman yang sama', function () {
    $baris = fn (string $status, string $label) => [
        'id' => 6, 'kode_pendaftaran' => 'OT-1608-ZT8K', 'jenis' => 'open_trip',
        'jenis_label' => 'Open Trip', 'nama_pemohon' => 'sofyan', 'whatsapp' => '0812',
        'email' => null, 'alasan' => 'jadwal_bentrok', 'alasan_label' => 'Jadwal bentrok',
        'penjelasan' => null, 'jumlah_dibatalkan' => 1,
        'rekening' => ['bank' => 'mandiri', 'nomor' => '12345678', 'atas_nama' => 'sofyan'],
        'status' => $status, 'status_label' => $label, 'catatan_admin' => null,
        'perkiraan' => null, 'dibuat_pada' => '2026-08-16T10:00:00+07:00',
    ];

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(['data' => [
            $baris('diajukan', 'Diajukan'),
            $baris('dana_dikirim', 'Dana Dikirim'),
        ], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 2]]),
    ]);

    /*
     | Sempat ada dua ikon bersebelahan — mata dan ceklis — yang keduanya
     | menuju halaman yang sama. Admin yang awam menyangka keduanya berbeda
     | lalu menebak-nebak mana yang benar.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan')->assertOk()->getContent());

    expect(substr_count($isi, '/admin/orcha/pembatalan/6'))->toBe(2)
        // Labelnya mengikuti keadaan pengajuannya
        ->and($isi)->toContain('Tindak lanjuti')
        ->and($isi)->toContain('bi bi-eye')
        ->and($isi)->toContain('Lihat')
        // Kelas ikon-saja yang lama sudah tidak dipakai lagi
        ->and($isi)->not->toContain('orcha-aksi orcha-aksi-lihat');
});

test('pembatalan tanpa tanggal berangkat tidak menampilkan angka tebakan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(['data' => [[
            'id' => 7, 'kode_pendaftaran' => 'OT-SALAH', 'jenis' => 'open_trip',
            'jenis_label' => 'Open Trip', 'nama_pemohon' => 'Rina', 'whatsapp' => '0812',
            'email' => null, 'alasan' => 'lainnya', 'alasan_label' => 'Lainnya',
            'penjelasan' => null, 'jumlah_dibatalkan' => 1,
            'rekening' => ['bank' => 'BRI', 'nomor' => '9', 'atas_nama' => 'Rina'],
            'status' => 'diajukan', 'status_label' => 'Diajukan', 'catatan_admin' => null,
            'perkiraan' => null,
            'dibuat_pada' => '2026-08-16T10:00:00+07:00',
        ]], 'meta' => []]),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan')
        ->assertOk()
        ->assertSee('belum bisa dihitung');
});

function balasanPembatalan(array $ubah = []): array
{
    return ['data' => array_merge([
        'id' => 6, 'kode_pendaftaran' => 'OT-1608-FXYK',
        'jenis' => 'open_trip', 'jenis_label' => 'Open Trip',
        'nama_pemohon' => 'Suparjiman', 'whatsapp' => '081234567890',
        'email' => 'suparjiman@contoh.test',
        'alasan' => 'kondisi_kesehatan', 'alasan_label' => 'Kondisi kesehatan',
        'penjelasan' => 'Saya sakit dan disarankan tidak bepergian.',
        'jumlah_dibatalkan' => 2,
        'rekening' => ['bank' => 'BCA', 'nomor' => '1234567890', 'atas_nama' => 'Suparjiman'],
        'status' => 'diajukan', 'status_label' => 'Diajukan', 'catatan_admin' => null,
        'perkiraan' => [
            'jenis' => 'open_trip', 'batas' => '15 – 30 hari sebelum keberangkatan',
            'persen' => 25, 'lewat' => false, 'total' => 2000000, 'dibayar' => 2000000,
            'potongan' => 500000, 'kembali' => 1500000,
            'total_teks' => 'Rp 2.000.000', 'dibayar_teks' => 'Rp 2.000.000',
            'potongan_teks' => 'Rp 500.000', 'kembali_teks' => 'Rp 1.500.000',
            'ditetapkan' => false, 'usulan' => 500000, 'usulan_teks' => 'Rp 500.000',
        ],
        'potongan_ditetapkan' => null,
        'pesanan' => [
            'jenis' => 'open_trip', 'nama' => 'Suparjiman', 'whatsapp' => '081234567890',
            'email' => 'suparjiman@contoh.test', 'status' => 'lunas', 'status_label' => 'Lunas',
            'keterangan' => 'Open Trip Banyuwangi', 'mulai' => '2026-09-10T00:00:00+07:00',
            'jumlah_peserta' => 2, 'durasi_label' => null,
        ],
        'pembayaran' => [[
            'id' => 3, 'jenis_label' => 'Pelunasan', 'nominal' => 2000000,
            'nominal_formatted' => 'Rp 2.000.000', 'tanggal_transfer' => '2026-08-15',
            'bank_pengirim' => 'BCA', 'atas_nama_pengirim' => 'Suparjiman',
            'bukti' => '/storage/bukti-bayar/a.webp', 'status' => 'diterima',
            'status_label' => 'Diterima', 'catatan_admin' => null,
        ]],
        'dibuat_pada' => '2026-08-16T10:00:00+07:00',
    ], $ubah)];
}

test('daftar pembatalan: judul kolom di tengah, status berwarna, penomoran bertautan', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    $baris = fn (int $i, string $status, string $label) => [
        'id' => $i, 'kode_pendaftaran' => 'OT-1608-FXY'.$i,
        'jenis' => 'open_trip', 'jenis_label' => 'Open Trip',
        'nama_pemohon' => 'Suparjiman', 'whatsapp' => '0812', 'email' => null,
        'alasan' => 'kondisi_kesehatan', 'alasan_label' => 'Kondisi kesehatan',
        'penjelasan' => null, 'jumlah_dibatalkan' => 2,
        'rekening' => ['bank' => 'BCA', 'nomor' => '123', 'atas_nama' => 'Suparjiman'],
        'status' => $status, 'status_label' => $label, 'catatan_admin' => null,
        'perkiraan' => null, 'dibuat_pada' => now()->toIso8601String(),
    ];

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => [
            'diajukan' => 'Diajukan', 'dana_dikirim' => 'Dana Dikirim', 'ditolak' => 'Ditolak',
        ]]]),
        '*' => Http::response([
            'data' => [
                $baris(6, 'diajukan', 'Diajukan'),
                $baris(7, 'dana_dikirim', 'Dana Dikirim'),
                $baris(8, 'ditolak', 'Ditolak'),
            ],
            'meta' => ['halaman' => 2, 'halaman_terakhir' => 4, 'total' => 33, 'per_halaman' => 10],
        ]),
    ]);

    /*
     | Statusnya dulu lencana abu-abu yang sama semua: "Diajukan", "Disetujui",
     | dan "Ditolak" terlihat serupa, padahal ketiganya menuntut perbuatan yang
     | sama sekali berbeda.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan?halaman=2')->assertOk()->getContent());

    expect($isi)
        // Judul kolomnya ditengahkan lewat kelas tabelnya sendiri
        ->toContain('orcha-tabel-batal')
        // Tiap status membawa warnanya sendiri
        ->toContain('data-status="diajukan"')
        ->toContain('data-status="dana_dikirim"')
        ->toContain('data-status="ditolak"')
        ->not->toContain('badge bg-light text-dark')
        // Penomoran halaman berupa tautan, bukan tombol Livewire
        ->toContain('halaman=3')
        ->toContain('Menampilkan <strong>11–20</strong> dari')
        ->not->toContain('wire:click="keHalaman');

    /*
     | Judul kolomnya sepasang dengan selnya.
     |
     | "Alasan" sempat berdiri di atas kolom rupiah dan "Perkiraan Kembali" di
     | atas kalimat alasannya. Tidak terlihat selama judulnya rata kiri dan
     | selnya panjang-panjang; begitu judulnya ditengahkan, salah pasangnya
     | langsung kentara.
     */
    $judul = substr($isi, strpos($isi, '<thead'), strpos($isi, '</thead>') - strpos($isi, '<thead'));

    expect(strpos($judul, 'Perkiraan Kembali'))->toBeLessThan(strpos($judul, 'Alasan'));
});

test('tindak lanjut pembatalan dikerjakan di detailnya, bukan di jendela daftar', function () {
    Cache::forget('orcha.pembayaran.menunggu');
    Cache::forget('orcha.penyewaan.perhatian');
    Cache::forget('orcha.pembatalan.perhatian');
    Cache::forget('orcha.pesan.perhatian');

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(['data' => [balasanPembatalan()['data']], 'meta' => []]),
    ]);

    /*
     | Jendela lamanya hanya bisa mengubah status dan catatan — sementara yang
     | menentukan keputusan justru angka pengembaliannya, dan angka itu hanya
     | ada di detail. Admin yang memutuskan dari daftar memutuskan tanpa
     | melihat uangnya.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan')->assertOk()->getContent());

    expect($isi)->toContain('/admin/orcha/pembatalan/6')
        ->not->toContain('wire:click="buka(')
        // Isi jendelanya tidak lagi ikut tergambar di daftar
        ->not->toContain('Tindak Lanjut Pembatalan')
        ->not->toContain('Catatan admin');
});

test('detail pembatalan sebentuk dengan lembar serah terima', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(balasanPembatalan()),
    ]);

    /*
     | Dulu dua kolom: pemohon dan bukti bayar di kiri, pesanan dan rekening di
     | kanan, dan label–nilai ditumpuk polos tanpa batas apa pun. Admin yang
     | awam membacanya seperti membaca daftar kata, dan urutan bacanya
     | melompat-lompat antar kolom.
     |
     | Sekarang satu kolom selebar halaman, tiap urusan satu kartu berkepala
     | bernomor, dan tiap keterangan berkotak — bentuk yang sama dengan lembar
     | serah terima sewa.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan/6')->assertOk()->getContent());

    expect($isi)
        ->toContain('orcha-bagian-nomor')
        ->toContain('orcha-medan')
        // Tombol WhatsApp berdiri di kartunya sendiri, tidak menumpang di kepala:
        // keduanya perbuatan, bukan keterangan, dan berdesakan di pojok kanan
        // nama pemohon membuat keduanya terbaca sebagai hiasan judul.
        ->toContain('orcha-kartu-tindakan')
        ->toContain('Kirimkan perhitungan pengembaliannya ke pemohon lewat WhatsApp')
        // Tingginya disamakan dengan kartu Tindakan di detail sewa: 34px
        ->and(substr_count($isi, 'orcha-btn-lembut orcha-aksi-sewa'))->toBe(1)
        ->and(substr_count($isi, 'orcha-btn-wa orcha-aksi-sewa'))->toBe(1)
        // Empat angka perhitungan berdiri di kartunya sendiri
        ->and($isi)->toContain('Perhitungan Pengembalian')
        ->and($isi)->toContain('Berapa yang harus dikirim balik, dan atas dasar apa')
        ->and($isi)->toContain('Pemohon')
        ->toContain('Bukti Pembayaran')
        ->toContain('Pesanan yang Dibatalkan')
        ->toContain('Rekening Pengembalian')
        ->toContain('Tindak Lanjut');

    /*
     | Kartunya berderet ke bawah, tidak lagi dibagi dua kolom halaman.
     |
     | Diperiksa lewat urutannya, bukan dengan melarang kelas kolom: kartu
     | Tindak Lanjut masih memakai pembagian 5/7 DI DALAM dirinya, dan itu
     | memang tempatnya — isian potongan di kiri, keputusan dan catatannya di
     | kanan.
     */
    $urut = fn (string $judul) => strpos($isi, '<div class="orcha-bagian-judul">'.$judul);

    expect($urut('Perhitungan Pengembalian'))->toBeLessThan($urut('Pemohon'))
        ->and($urut('Pemohon'))->toBeLessThan($urut('Bukti Pembayaran'))
        ->and($urut('Bukti Pembayaran'))->toBeLessThan($urut('Pesanan yang Dibatalkan'))
        ->and($urut('Pesanan yang Dibatalkan'))->toBeLessThan($urut('Rekening Pengembalian'))
        ->and($urut('Rekening Pengembalian'))->toBeLessThan($urut('Tindak Lanjut'));
});

test('pengembalian nol tidak menjanjikan transfer ke rekening', function () {
    $perkiraan = array_merge(balasanPembatalan()['data']['perkiraan'], [
        'persen' => 100, 'dibayar' => 570000, 'potongan' => 570000, 'kembali' => 0,
        'dibayar_teks' => 'Rp 570.000', 'potongan_teks' => 'Rp 570.000',
        'kembali_teks' => 'Rp 0', 'batas' => 'Kurang dari 7 hari sebelum keberangkatan',
    ]);

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['disetujui' => 'Disetujui']]]),
        '*' => Http::response(balasanPembatalan([
            'status' => 'disetujui', 'status_label' => 'Disetujui',
            'perkiraan' => $perkiraan,
            'catatan_admin' => 'Potongan 100% karena sudah lewat batas.',
        ])),
    ]);

    /*
     | Kalimat "Dana dikirim ke BCA ... mohon dikonfirmasi bila rekeningnya
     | sudah benar" sempat ikut terkirim walau pengembaliannya Rp 0 — pelanggan
     | yang potongannya sebesar seluruh pembayarannya membaca dirinya akan
     | menerima transfer, lalu menunggu uang yang tidak pernah berangkat. Yang
     | datang berikutnya bukan uang, melainkan telepon.
     */
    $pesan = Livewire::actingAs(adminOrcha())
        ->test(OrchaPembatalanDetail::class, ['id' => 6])
        ->instance()->pesanWa();

    expect($pesan)->toContain('Dikembalikan: *Rp 0*')
        ->toContain('tidak ada dana yang dikembalikan')
        // Rekeningnya tidak disebut sama sekali
        ->not->toContain('Dana dikirim ke')
        ->not->toContain('1234567890')
        // Alasan yang ditulis admin ikut, karena di situlah penjelasannya
        ->toContain('Potongan 100% karena sudah lewat batas.');
});

test('pengembalian yang benar-benar ada tetap menyebut rekeningnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['disetujui' => 'Disetujui']]]),
        '*' => Http::response(balasanPembatalan(['status' => 'disetujui', 'status_label' => 'Disetujui'])),
    ]);

    $pesan = Livewire::actingAs(adminOrcha())
        ->test(OrchaPembatalanDetail::class, ['id' => 6])
        ->instance()->pesanWa();

    expect($pesan)->toContain('Dikembalikan: *Rp 1.500.000*')
        ->toContain('Dana dikirim ke BCA 1234567890')
        ->not->toContain('tidak ada dana yang dikembalikan');
});

test('pengajuan yang ditolak tidak menyebut angka pengembalian sama sekali', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['ditolak' => 'Ditolak']]]),
        '*' => Http::response(balasanPembatalan([
            'status' => 'ditolak', 'status_label' => 'Ditolak',
            'catatan_admin' => 'Pengajuan di luar masa yang diizinkan.',
        ])),
    ]);

    // Menyebut angka pengembalian pada pengajuan yang ditolak berarti
    // menjanjikan yang tidak akan datang.
    $pesan = Livewire::actingAs(adminOrcha())
        ->test(OrchaPembatalanDetail::class, ['id' => 6])
        ->instance()->pesanWa();

    expect($pesan)->toContain('belum dapat kami setujui')
        ->toContain('Pengajuan di luar masa yang diizinkan.')
        ->not->toContain('Dikembalikan')
        ->not->toContain('Dana dikirim ke');
});

test('tombol simpan tindak lanjut berganti pemintal', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(balasanPembatalan()),
    ]);

    // Menyimpan di sini menembak Orcha dan ikut menandai pesanannya batal, jadi
    // jedanya terasa — dan pembatalan yang terkirim dua kali bukan perkara
    // tampilan.
    $isi = Livewire::actingAs(adminOrcha())
        ->test(OrchaPembatalanDetail::class, ['id' => 6])
        ->html();

    expect($isi)->toContain('spinner-border spinner-border-sm')
        ->toContain('Menyimpan...')
        ->toContain('wire:target="simpan"');
});

test('detail pembatalan memuat perhitungan, pesanan, dan bukti bayarnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => [
            'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak',
        ]]]),
        '*/pembatalan/6' => Http::response(balasanPembatalan()),
    ]);

    // Menindaklanjuti pembatalan berarti menjawab tiga hal sekaligus: siapa
    // yang mengajukan, berapa yang sudah dibayar, dan berapa yang dikirim
    // balik. Sebelum halaman ini ketiganya tersebar di tiga tempat.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan/6')
        ->assertOk()
        ->assertSee('Suparjiman')
        ->assertSee('Rp 1.500.000')
        ->assertSee('15 – 30 hari sebelum keberangkatan')
        ->assertSee('Open Trip Banyuwangi')
        // Rekening tujuan dan riwayat bayarnya ikut, karena itu dasar angkanya
        ->assertSee('1234567890')
        ->assertSee('Pelunasan')
        ->assertSee('Kirim Perhitungan');
});

test('detail pembatalan menandai pemohon yang bukan pemesan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*/pembatalan/6' => Http::response(balasanPembatalan(['nama_pemohon' => 'Rina Wijaya'])),
    ]);

    // Perbedaan nama perlu diperiksa sebelum dana dikirim ke rekening
    // yang belum tentu milik pemesannya.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan/6')
        ->assertOk()
        ->assertSee('Nama pemohon berbeda dari pemesan');
});

test('admin bisa menghitung potongan sendiri, terisi dari usulan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => [
            'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui',
        ]]]),
        '*/status' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*/pembatalan/6' => Http::response(balasanPembatalan()),
    ]);

    $komponen = Livewire::actingAs(adminOrcha())
        ->test(OrchaPembatalanDetail::class, ['id' => 6])
        // Terisi dari usulan kebijakan: admin melanjutkan, bukan menaksir nol
        ->assertSet('potongan', '500.000');

    // Ada biaya yang sudah terlanjur dibayarkan ke pihak ketiga, jadi
    // potongannya ditetapkan lebih besar dari usulan.
    $komponen->set('potongan', '750.000')
        // Akibatnya terlihat seketika, tanpa menunggu jawaban server
        ->assertSeeText('Rp 1.250.000')
        ->assertSee('belum tersimpan')
        ->set('statusBaru', 'disetujui')
        ->call('simpan');

    Http::assertSent(fn ($permintaan) => ! str_contains($permintaan->url(), '/status')
        || ($permintaan['potongan_ditetapkan'] === 750000
            && $permintaan['status'] === 'disetujui'));
});

test('potongan yang sudah ditetapkan yang dipakai, bukan usulannya lagi', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['disetujui' => 'Disetujui']]]),
        '*/pembatalan/6' => Http::response(balasanPembatalan([
            'potongan_ditetapkan' => 300000,
            'perkiraan' => array_merge(balasanPembatalan()['data']['perkiraan'], [
                'ditetapkan' => true, 'potongan' => 300000, 'potongan_teks' => 'Rp 300.000',
                'kembali' => 1700000, 'kembali_teks' => 'Rp 1.700.000',
            ]),
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPembatalanDetail::class, ['id' => 6])
        ->assertSet('potongan', '300.000')
        // Tidak ditandai belum tersimpan, karena memang sudah.
        //
        // Diperiksa lewat atribut hidden, bukan ketiadaan teksnya: lencananya
        // selalu ada di HTML supaya skrip di halaman bisa menyalakannya sambil
        // admin mengetik, tanpa menunggu jawaban server.
        ->assertSee('data-lencana-belum hidden', false)
        ->assertSee('Potongan ditetapkan');
});

test('detail pembatalan tanpa perkiraan tidak menampilkan angka tebakan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*/pembatalan/6' => Http::response(balasanPembatalan([
            'perkiraan' => null, 'pesanan' => null, 'pembayaran' => [],
        ])),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan/6')
        ->assertOk()
        ->assertSee('Perkiraan belum bisa dihitung')
        ->assertSee('Belum ada bukti pembayaran untuk kode ini');
});

test('pembatalan sewa kendaraan tidak dihitung dalam peserta', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembatalan' => ['diajukan' => 'Diajukan']]]),
        '*' => Http::response(['data' => [[
            'id' => 5, 'kode_pendaftaran' => 'SK-1608-ZGAN',
            'jenis' => 'sewa_kendaraan', 'jenis_label' => 'Sewa Kendaraan',
            'nama_pemohon' => 'Rina Wijaya', 'whatsapp' => '081298765432', 'email' => null,
            'alasan' => 'kondisi_kesehatan', 'alasan_label' => 'Kondisi kesehatan',
            'penjelasan' => null, 'jumlah_dibatalkan' => 1,
            'rekening' => ['bank' => 'BCA', 'nomor' => '123', 'atas_nama' => 'Rina Wijaya'],
            'status' => 'diajukan', 'status_label' => 'Diajukan', 'catatan_admin' => null,
            'dibuat_pada' => '2026-08-16T10:00:00+07:00',
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    // "1 peserta" pada sewa kendaraan menyesatkan: yang dibatalkan unitnya,
    // bukan orangnya. Jenisnya pun disebut supaya admin tahu ke mana memeriksa.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembatalan')
        ->assertOk()
        ->assertSee('Sewa Kendaraan')
        ->assertSee('1 unit')
        ->assertDontSee('1 peserta');
});

test('pengajuan pembatalan tampil sebagai peringatan di detail', function () {
    Http::fake(['*/pendaftaran/7' => Http::response(balasanDetail([
        'pembatalan' => [
            'id' => 2, 'nama_pemohon' => 'Siti Aminah', 'alasan_label' => 'Kondisi kesehatan',
            'penjelasan' => 'Sakit mendadak', 'jumlah_dibatalkan' => 1,
            'rekening' => 'BCA · 1234567890 a.n. Siti Aminah',
            'status' => 'diajukan', 'dibuat_pada' => '2026-08-16T09:00:00+07:00',
        ],
    ]))]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7')
        ->assertOk()
        ->assertSee('Ada pengajuan pembatalan')
        ->assertSee('Kondisi kesehatan')
        ->assertSee('1234567890');
});

test('riwayat kesehatan di detail hanya untuk akun berizin', function () {
    Http::fake([
        '*/pendaftaran/7' => Http::response(balasanDetail()),
        '*/riwayat-kesehatan' => Http::response(['data' => []]),
    ]);

    // Tanpa izin: tautannya tidak ada, dan halamannya ditolak di server.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7')
        ->assertOk()
        ->assertSee('Riwayat kesehatan terkunci')
        ->assertDontSee('Lihat Riwayat Kesehatan');

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7/riwayat-kesehatan')
        ->assertForbidden();
});

test('akun berizin bisa membuka riwayat kesehatan dari halaman detail', function () {
    Http::fake([
        '*/pendaftaran/7/riwayat-kesehatan' => Http::response(['data' => [[
            'nama_peserta' => 'Siti Aminah',
            'usia' => 30,
            'jenis_kelamin' => 'Perempuan',
            'golongan_darah' => 'O',
            'riwayat_penyakit' => 'Asma ringan',
            'kondisi_khusus' => ['Asma'],
            'kontak_darurat' => ['nama' => 'Budi', 'hubungan' => 'Suami', 'hp' => '081298765432'],
            'ada_catatan_khusus' => true,
        ]]]),
        '*/pendaftaran/7' => Http::response(balasanDetail()),
    ]);

    $admin = adminOrcha(['akses_orcha', 'view_orcha_kesehatan']);

    // Tautan menuju halamannya, bukan popup yang dibuka di tempat.
    $this->actingAs($admin)
        ->get('/admin/orcha/pendaftaran/7')
        ->assertOk()
        ->assertSee('Lihat Riwayat Kesehatan')
        ->assertSee(route('admin.orcha.pendaftaran.kesehatan', 7), false);

    $this->actingAs($admin)
        ->get('/admin/orcha/pendaftaran/7/riwayat-kesehatan')
        ->assertOk()
        ->assertSee('Asma ringan')
        ->assertSee('Perlu perhatian')
        ->assertSee('Kontak darurat');
});

/* ------------------- PRATINJAU BUKTI TRANSFER ------------------- */

test('bukti transfer dibuka menumpang di halaman, bukan pindah tab', function () {
    Http::fake(['*/pendaftaran/7' => Http::response(balasanDetail())]);

    $html = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7')
        ->assertOk()
        ->assertSee('data-bukti=', false)
        ->assertSee('orcha-pratinjau', false)
        ->getContent();

    // Gambar buktinya tidak lagi dibungkus tautan yang membuka tab baru
    expect($html)->not->toContain('<a href="'.rtrim(str_replace('/api/v1', '', config('orcha.url')), '/').'/storage/bukti.webp');
});

test('daftar bukti pembayaran juga memakai pratinjau', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pembayaran' => ['menunggu' => 'Menunggu Dicek']]]),
        '*' => Http::response(['data' => [[
            'id' => 3, 'kode' => 'OT-1508-A7K3', 'jenis' => 'dp', 'jenis_label' => 'Uang Muka (DP)',
            'nominal' => 500000, 'nominal_formatted' => 'Rp 500.000',
            'tanggal_transfer' => '2026-08-15', 'bank_pengirim' => 'BCA',
            'atas_nama_pengirim' => 'Budi Santoso', 'bukti' => '/storage/bukti-bayar/a.webp',
            'catatan' => null, 'status' => 'menunggu', 'status_label' => 'Menunggu Dicek',
            'catatan_admin' => null, 'dibuat_pada' => '2026-08-15T10:00:00+07:00',
            'pesanan' => null,
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pembayaran')
        ->assertOk()
        ->assertSee('data-bukti=', false)
        ->assertSee('orcha-pratinjau', false)
        // Tombolnya bukan tautan yang membuka tab baru
        ->assertDontSee('target="_blank" rel="noopener"'."\n".'                                                class="btn btn-sm orcha-aksi orcha-aksi-lihat"', false);
});

/* ------------------- EKSPOR EXCEL & PDF ------------------- */

function balasanKesehatan(): array
{
    return ['data' => [
        [
            'nama_peserta' => 'Siti Aminah', 'usia' => 30, 'jenis_kelamin' => 'Perempuan',
            'golongan_darah' => 'O', 'tinggi_badan' => 160, 'berat_badan' => 55,
            'riwayat_penyakit' => 'Asma sejak kecil', 'kondisi_khusus' => ['Asma'],
            'alergi' => 'Udang', 'obat_rutin' => 'Salbutamol', 'pantangan_makanan' => null,
            'pantangan_kegiatan' => null, 'riwayat_operasi' => null,
            'kemampuan_renang' => 'lancar', 'asuransi' => 'BPJS',
            'kontak_darurat' => ['nama' => 'Budi', 'hubungan' => 'Suami', 'hp' => '081298765432'],
            'catatan_tambahan' => null, 'ada_catatan_khusus' => true,
            'tingkat_perhatian' => 'tinggi',
            'alasan_perhatian' => ['Asma', 'Alergi: Udang', 'Obat rutin: Salbutamol'],
            'alasan_catatan' => [],
        ],
    ]];
}

test('excel data lengkap bisa diunduh admin berizin kesehatan', function () {
    Http::fake([
        '*/pendaftaran/7/riwayat-kesehatan' => Http::response(balasanKesehatan()),
        '*/pendaftaran/7' => Http::response(balasanDetail()),
    ]);

    $balasan = $this->actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->get('/admin/orcha/pendaftaran/7/ekspor/excel')
        ->assertOk();

    expect($balasan->headers->get('content-disposition'))->toContain('DATA-LENGKAP-OT-1608-A7KD.xlsx');
});

test('manifes pdf tour leader memuat titik jemput dan penanda perhatian', function () {
    Http::fake([
        '*/pendaftaran/7/riwayat-kesehatan' => Http::response(balasanKesehatan()),
        '*/pendaftaran/7' => Http::response(balasanDetail()),
    ]);

    $balasan = $this->actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->get('/admin/orcha/pendaftaran/7/ekspor/pdf')
        ->assertOk();

    expect($balasan->headers->get('content-disposition'))->toContain('MANIFES-TOUR-LEADER-OT-1608-A7KD.pdf')
        ->and(substr($balasan->getContent(), 0, 5))->toBe('%PDF-');
});

test('ekspor ditolak untuk akun tanpa izin data kesehatan', function () {
    Http::fake();

    $admin = adminOrcha();     // hanya akses_orcha

    $this->actingAs($admin)->get('/admin/orcha/pendaftaran/7/ekspor/excel')->assertForbidden();
    $this->actingAs($admin)->get('/admin/orcha/pendaftaran/7/ekspor/pdf')->assertForbidden();

    // Tidak boleh ada permintaan data yang telanjur berangkat ke Orcha
    Http::assertNothingSent();
});

test('tombol unduh hanya tampil untuk akun berizin', function () {
    Http::fake(['*/pendaftaran/7' => Http::response(balasanDetail())]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7')
        ->assertDontSee('Manifes PDF');

    $this->actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->get('/admin/orcha/pendaftaran/7')
        ->assertSee('Manifes PDF');
});

test('tiga tingkat perhatian tampil beda, bukan merah semua', function () {
    Http::fake([
        '*/pendaftaran/7/riwayat-kesehatan' => Http::response(['data' => [
            ['nama_peserta' => 'Siti Aminah', 'tingkat_perhatian' => 'tinggi',
                'alasan_perhatian' => ['Gangguan jantung'], 'alasan_catatan' => [],
                'kontak_darurat' => ['nama' => 'Budi', 'hubungan' => 'Suami', 'hp' => '0812']],
            ['nama_peserta' => 'Rina', 'tingkat_perhatian' => 'sedang',
                'alasan_perhatian' => [], 'alasan_catatan' => ['Pantangan makanan: Tidak suka pedas'],
                'kontak_darurat' => ['nama' => 'Ani', 'hubungan' => 'Ibu', 'hp' => '0813']],
            ['nama_peserta' => 'Joko', 'tingkat_perhatian' => 'aman',
                'alasan_perhatian' => [], 'alasan_catatan' => [],
                'kontak_darurat' => ['nama' => 'Sri', 'hubungan' => 'Istri', 'hp' => '0814']],
        ]]),
        '*/pendaftaran/7' => Http::response(balasanDetail()),
    ]);

    $this->actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->get('/admin/orcha/pendaftaran/7/riwayat-kesehatan')
        ->assertOk()
        ->assertSee('Perlu perhatian')
        ->assertSee('Menuntut kesiapan sebelum berangkat')
        ->assertSee('Ada catatan')
        ->assertSee('Cukup diingat di lapangan')
        ->assertSee('Tanpa catatan')
        // Yang menuntut kesiapan dikumpulkan di depan, sebelum kartunya sendiri.
        ->assertSee('Perlu disiapkan sebelum berangkat')
        ->assertSee('Gangguan jantung');
});

/* ---------------- SEWA KENDARAAN: SERAH TERIMA & DENDA ---------------- */

/*
|--------------------------------------------------------------------------
| Pesan kontak
|--------------------------------------------------------------------------
|
| Daftarnya dulu berupa kartu dua kolom. Kotak masuk dibaca untuk mencari yang
| belum dikerjakan, dan kartu memaksa mata melompat kiri-kanan untuk itu. Kini
| tabel, dengan yang belum dibaca dibedakan lewat tiga penanda sekaligus —
| bukan warna saja.
*/

/** Dua pesan: satu belum dibaca, satu sudah. */
function balasanDaftarPesan(): array
{
    return [
        'data' => [
            [
                'id' => 91,
                'nama' => 'Andi Prasetyo',
                'whatsapp' => '081234567890',
                'email' => 'andi@contoh.test',
                'keperluan' => 'open_trip',
                'keperluan_label' => 'Open Trip',
                'pesan' => 'Bromo 12 September masih ada kursi?',
                'sudah_dibaca' => false,
                'dibaca_pada' => null,
                'dibuat_pada' => '2026-08-25T08:14:00+07:00',
            ],
            [
                'id' => 89,
                'nama' => 'Budi Santoso',
                'whatsapp' => '085711122233',
                'email' => null,
                'keperluan' => 'study_tour',
                'keperluan_label' => 'Study Tour',
                'pesan' => 'Minta penawaran study tour Yogyakarta.',
                'sudah_dibaca' => true,
                'dibaca_pada' => '2026-08-23T12:00:00+07:00',
                'dibuat_pada' => '2026-08-23T11:40:00+07:00',
            ],
        ],
        'meta' => [
            'halaman' => 1,
            'halaman_terakhir' => 2,
            'total' => 14,
            'per_halaman' => 10,
            'belum_dibaca' => 1,
        ],
    ];
}

test('pesan kontak ditampilkan sebagai tabel, bukan kartu', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->html());

    expect($isi)
        ->toContain('orcha-tabel-pesan')
        ->toContain('<thead>')
        // Judul kolomnya lengkap dan urut, seperti daftar Orcha yang lain
        ->toContain('Keadaan')
        ->toContain('Pengirim')
        ->toContain('Keperluan')
        ->toContain('Isi Pesan')
        ->toContain('Masuk')
        // Kartu lamanya benar-benar ditinggalkan, bukan dibungkus tabel
        ->not->toContain('orcha-pesan-kartu');
});

test('baris yang belum dibaca dibedakan dari yang sudah', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->html());

    expect($isi)
        // Penanda pada barisnya sendiri: garis tepi dan latar dipasang lewat kelas ini
        ->toContain('orcha-baris-pesan belum')
        ->toContain('orcha-baris-pesan sudah')
        // Dan penanda yang bisa DIBACA, bukan hanya dilihat warnanya
        ->toContain('Belum dibaca')
        ->toContain('Dibaca');
});

test('hanya pesan yang belum dibaca menawarkan tombol tandai dibaca', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->html());

    // Satu tombol untuk satu pesan yang belum dibaca — bukan dua.
    expect(substr_count($isi, 'tandaiDibaca('))->toBe(1)
        ->and($isi)->toContain('tandaiDibaca(91)')
        ->and($isi)->not->toContain('tandaiDibaca(89)');
});

test('tombol simpan dan batal armada seukuran tombol simpan paket wisata', function () {
    Http::fake(['*' => Http::response(['data' => []])]);

    /*
     | Terukur di peramban sebelum diperbaiki: tombol formulir paket wisata
     | 38px dengan huruf 16px, tombol formulir armada 34,5px dengan huruf
     | 13,76px. Keduanya formulir tambah data yang sering dikerjakan berurutan,
     | dan tombol yang mengecil sendiri di salah satunya membuat keduanya
     | terasa dari dua aplikasi berbeda.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class)
        ->html());

    // Keduanya, bukan tombol simpannya saja: batal yang lebih pendek daripada
    // simpan di atasnya justru terlihat seperti kesalahan.
    expect(substr_count($isi, 'orcha-btn-besar'))->toBe(2);

    // Ukurannya diikat ke min-height, bukan dihitung mundur dari jarak dalam —
    // hitungan mundur meleset sendiri begitu ukuran hurufnya berubah.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    preg_match('/\.orcha-btn-besar \{(.*?)\}/s', $gaya, $aturan);

    expect($aturan[1] ?? '')->toContain('min-height: 38px')
        ->toContain('font-size: 1rem');
});

test('tiap keperluan punya warnanya sendiri, bukan satu warna untuk semua', function () {
    // Tinggal di lembar gaya bersama, bukan di salah satu halaman: kotak masuk
    // dan halaman detail sama-sama memakainya, dan dua salinan sejajar untuk
    // hal yang sama lambat laun berbeda sendiri.
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    /*
     | Enam keperluan, enam warna. Admin memilah kotak masuknya persis menurut
     | keperluan — study tour disusun per rombongan, sewa kendaraan dicek
     | ketersediaan unitnya, kerja sama diteruskan ke orang lain — jadi satu
     | warna abu-abu untuk keenamnya memaksa tiap barisnya dibaca dulu.
     |
     | Yang diperiksa: tiap kode keperluan punya aturannya sendiri, dan
     | warnanya benar-benar berbeda satu sama lain.
     */
    $warna = [];

    foreach (config('orcha.keperluan_kontak', [
        'open_trip' => null, 'private_trip' => null, 'study_tour' => null,
        'sewa_kendaraan' => null, 'kerja_sama' => null, 'lainnya' => null,
    ]) as $kode => $abaikan) {
        $pola = '/\.orcha-lencana-keperluan\[data-keperluan="'.preg_quote($kode, '/').'"\][^{]*\{([^}]*)\}/';

        expect($isi)->toMatch($pola);

        preg_match($pola, $isi, $cocok);
        $warna[$kode] = trim($cocok[1]);
    }

    expect(count(array_unique($warna)))->toBe(count($warna));
});

test('keperluan ditandai ikon juga, bukan warna saja', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Tanya Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->html());

    // Warna sendirian tidak terbaca oleh yang sulit membedakannya.
    expect($isi)
        ->toContain('data-keperluan="open_trip"')
        ->toContain('bi-signpost-split')
        ->toContain('data-keperluan="study_tour"')
        ->toContain('bi-mortarboard');
});

test('penyaring baca sebesar kotak cari dan pemilih keperluan di sebelahnya', function () {
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/orcha/pesan/index.blade.php'));

    /*
     | Ketiganya satu deret kendali di kartu saringan yang sama. Sebelumnya
     | penyaring baca 46px berbanding 38px milik .form-control/.form-select
     | bawaan Bootstrap, dengan sudut jauh lebih bulat — terbaca seperti benda
     | lain yang kebetulan sebaris.
     */
    preg_match('/\.orcha-saring-baca \{(.*?)\}/s', $isi, $kotak);
    preg_match('/\.orcha-saring-baca > button \{(.*?)\}/s', $isi, $tombol);

    expect($kotak[1])->toContain('height: 38px')
        ->and($kotak[1])->toContain('border-radius: 4px')
        // Tinggi tombol dalamnya tidak boleh mendorong kotaknya melewati 38px
        ->and($kotak[1])->toContain('padding: 3px')
        // Sehuruf dengan pemilih keperluan; beda ukuran huruf membuat kotaknya
        // tetap terlihat lebih kecil walau tingginya sudah sama
        ->and($tombol[1])->toContain('font-size: 1rem');
});

test('balasan wa mengikuti keperluannya, bukan satu kalimat untuk semua', function () {
    /*
     | Pelanggan yang bertanya soal study tour dan yang bertanya soal sewa
     | kendaraan dulu menerima kalimat yang persis sama, lalu harus menunggu
     | satu putaran lagi hanya untuk ditanyai hal yang sudah bisa ditanyakan
     | sejak balasan pertama.
     */
    $balasan = fn (string $kode, string $label) => \App\Support\BalasanPesanKontak::untuk([
        'nama' => 'Andi', 'whatsapp' => '081234567890',
        'keperluan' => $kode, 'keperluan_label' => $label,
        'pesan' => 'Mohon informasinya.',
    ]);

    expect($balasan('study_tour', 'Tanya Study Tour'))
        ->toContain('Jumlah siswa dan pendamping')
        ->toContain('rincian biaya per siswa');

    expect($balasan('sewa_kendaraan', 'Sewa Kendaraan'))
        ->toContain('Perlu sopir atau lepas kunci')
        ->not->toContain('Jumlah siswa');

    expect($balasan('open_trip', 'Tanya Open Trip'))
        ->toContain('Perkiraan tanggal berangkat')
        ->not->toContain('lepas kunci');

    // "Lainnya" sengaja tidak dipandu: pertanyaannya bisa apa saja, dan menebak
    // daftar isian justru membuat balasannya terasa salah alamat.
    expect($balasan('lainnya', 'Lainnya'))
        ->toContain('Ada yang bisa kami bantu jelaskan lebih dulu?')
        ->not->toContain('boleh dibantu:');
});

test('perihal disebut sebagai kata benda, bukan teks pilihan formulir', function () {
    $kalimat = fn (string $kode, string $label) => explode("\n", \App\Support\BalasanPesanKontak::untuk([
        'nama' => 'Andi', 'keperluan' => $kode, 'keperluan_label' => $label, 'pesan' => 'tes',
    ]))[2];

    // Labelnya teks pilihan di formulir, bukan kata benda: dipasang apa adanya
    // kalimatnya jadi "menghubungi Orcha Journey soal Tanya Open Trip".
    expect($kalimat('open_trip', 'Tanya Open Trip'))->toContain('soal Open Trip.')
        ->not->toContain('soal Tanya');

    // "soal Lainnya" tidak berarti apa-apa, jadi perihalnya tidak disebut
    expect($kalimat('lainnya', 'Lainnya'))->toEndWith('*Orcha Journey*.')
        ->not->toContain('Lainnya');

    // Keperluan baru bisa muncul di Orcha tanpa berkas ini ikut diubah;
    // labelnya dipakai setelah awalan "Tanya " dilepas
    expect($kalimat('paket_reuni', 'Tanya Paket Reuni'))->toContain('soal Paket Reuni.');
});

test('tombol wa di daftar dan di detail mengirim pesan yang sama', function () {
    /*
     | Dulu dua kalimat berbeda untuk pesan yang sama: daftar mengirim basa-basi
     | satu baris, detail mengirim kutipan pertanyaannya. Keduanya kini memakai
     | penyusun yang sama, jadi tidak bisa lagi berbeda diam-diam.
     */
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Tanya Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    $daftar = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->html());

    $harusnya = \App\Support\BalasanPesanKontak::tautan(balasanDaftarPesan()['data'][0]);

    expect($harusnya)->toStartWith('https://api.whatsapp.com/send?phone=6281234567890')
        // Persis tautan itu yang terpasang di barisnya
        ->and($daftar)->toContain(e($harusnya))
        // Dan basa-basi lamanya benar-benar hilang
        ->and($daftar)->not->toContain('terima kasih sudah menghubungi Orcha Journey.');
});

test('daftar pesan memakai penomoran halaman yang sama dengan daftar orcha lain', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->html());

    /*
     | Tombol halamannya HANYA muncul bila memang ada halaman kedua — dengan
     | tiga pesan di kotak masuk yang tampil cuma keterangan jumlahnya, dan itu
     | mudah dibaca sebagai "paginasinya tidak ada". Karena itu yang diuji di
     | sini keadaan yang membuatnya muncul: meta menyebut dua halaman.
     */
    expect($isi)
        // Keterangan jumlah: yang ditanya admin lebih sering "ada berapa
        // semuanya", bukan "ini halaman berapa"
        ->toContain('Menampilkan')
        ->toContain('1–10')
        ->toContain('14')
        // Tombolnya benar-benar tergambar, dan alamatnya membawa nomor halaman
        ->toContain('halaman=2')
        ->toContain('pagination');
});

test('penomoran halaman pesan duduk di dalam kartu tabelnya, seperti daftar orcha lain', function () {
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/orcha/pesan/index.blade.php'));

    /*
     | Di luar kartu, penomorannya mengambang sendirian di latar halaman dan
     | tidak terbaca sebagai bagian dari tabel yang baru saja dibaca. Daftar
     | pembatalan dan bukti pembayaran sudah memasukkannya ke dalam kartu.
     |
     | Yang dituntut susunannya, bukan sekadar keberadaannya: tabel, penutup
     | pembungkus gulung, penomoran, baru dua penutup kartu.
     */
    expect($isi)->toMatch(
        '#</table>\s*</div>\s*(\{\{--.*?--\}\}\s*)?'
        .'@include\([^)]*partials\.paginasi[^)]*\)\s*</div>\s*</div>#s'
    );

    // Tabelnya diberi jarak dari tepi kartu, sebentuk dengan daftar pembatalan
    expect($isi)->toContain('<div class="orcha-gulung">')
        ->not->toContain('card-body p-0');
});

test('kotak masuk yang muat satu halaman tidak menumbuhkan tombol halaman', function () {
    $satuHalaman = balasanDaftarPesan();
    $satuHalaman['meta'] = ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 3, 'per_halaman' => 10, 'belum_dibaca' => 1];

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response($satuHalaman),
    ]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->html());

    // Jumlahnya tetap disebut; deretan tombol "1" sendirian tidak berguna.
    expect($isi)->toContain('Menampilkan')
        ->toContain('3')
        ->not->toContain('halaman=2');
});

test('pindah halaman benar-benar meminta halaman itu ke orcha', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['keperluan_kontak' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(balasanDaftarPesan()),
    ]);

    Livewire::actingAs(adminOrcha())
        ->withUrlParams(['halaman' => 2])
        ->test(\App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList::class)
        ->assertSet('halaman', 2);

    /*
     | Dituntut cocok, bukan sekadar "tidak ada yang salah": penjagaan berbentuk
     | "kalau URL-nya /pesan maka harus page=2" lolos dengan sendirinya bila
     | permintaannya tidak pernah berangkat — dan justru itu kegagalan yang
     | ingin ditangkap.
     |
     | Orcha memakai nama "page" (bawaan paginator Laravel), bukan "halaman".
     */
    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/pesan?')
        && str_contains($permintaan->url(), 'page=2')
        && str_contains($permintaan->url(), 'per_halaman=10'));
});

/** Bentuk balasan /penyewaan/{id}: satu baris, bukan daftar. */
function satuSewa(array $ubah = []): array
{
    return ['data' => balasanSewa($ubah)['data'][0]];
}

/**
 * Isi halaman tanpa blok <style>-nya.
 *
 * Gaya halaman Orcha ditulis inline — public/build tidak ikut ter-deploy —
 * sehingga nama kelas dan kalimat di dalam CSS ikut terhitung saat memeriksa
 * markup. Memotong dari </style> TERAKHIR sempat dipakai, lalu rusak sendiri
 * begitu ada partial yang membawa <style> miliknya di dekat penutup halaman.
 */
function tanpaGaya(string $isi): string
{
    return preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $isi);
}

/**
 * Isi halaman tanpa keadaan komponen yang diserialkan Livewire.
 *
 * wire:snapshot memuat seluruh isi properti apa adanya — termasuk yang sengaja
 * TIDAK ditampilkan di layar. Untuk memeriksa apa yang benar-benar tergambar,
 * bagian itu harus dibuang lebih dulu; kalau tidak, penjagaan "jangan
 * tampilkan nomor rekening" selalu gagal walaupun layarnya sudah benar.
 */
function tanpaSnapshot(string $isi): string
{
    return preg_replace('/wire:snapshot="[^"]*"/is', '', $isi);
}

function balasanSewa(array $ubah = []): array
{
    return ['data' => [array_merge([
        'id' => 12, 'kode' => 'SK-1608-B2M9', 'nama' => 'Budi Santoso',
        'whatsapp' => '081234567890', 'email' => 'budi@contoh.test',
        'kendaraan' => ['id' => 1, 'nama' => 'Avanza', 'transmisi' => 'Matic'],
        'satuan' => 'hari', 'satuan_label' => 'Per hari (24 jam)',
        'durasi' => 1, 'durasi_label' => '1 hari',
        'tanggal_mulai' => '2026-09-10', 'jam_mulai' => '08:00',
        'tanggal_selesai' => '2026-09-11', 'jam_selesai' => '08:00',
        'jadwal_selesai' => '2026-09-11T08:00:00+07:00',
        'terlambat' => true, 'terlambat_menit' => 180,
        'denda_keterlambatan_usulan' => 150000,
        'dengan_sopir' => false,
        'lokasi_antar' => 'Bandara YIA', 'lokasi_kembali' => 'Kantor Orcha',
        'diserahkan_pada' => null, 'dikembalikan_pada' => null,
        'kilometer_awal' => null, 'kilometer_akhir' => null,
        'bahan_bakar_awal' => null, 'bahan_bakar_akhir' => null,
        'jaminan' => null, 'kondisi_awal' => [], 'kondisi_akhir' => [], 'kerusakan_baru' => [],
        'estimasi_biaya' => 500000, 'denda_keterlambatan' => 0, 'denda_kerusakan' => 0,
        'denda_lain' => 0, 'catatan_denda' => null, 'total_denda' => 0, 'total_tagihan' => 500000,
        'catatan' => null, 'status' => 'berjalan', 'status_label' => 'Sedang Berjalan',
        'dibuat_pada' => '2026-09-01T10:00:00+07:00',
    ], $ubah)], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]];
}

function rujukanSewa(): array
{
    return ['data' => [
        'status_penyewaan' => ['berjalan' => 'Sedang Berjalan', 'selesai' => 'Selesai'],
        'pemeriksaan_kendaraan' => ['bodi_depan' => 'Bodi depan & bemper', 'kaca' => 'Kaca & spion'],
        'kondisi_pemeriksaan' => ['baik' => 'Baik', 'lecet' => 'Lecet / minor', 'rusak' => 'Rusak'],
        // Aturan dendanya dibaca dari rujukan, bukan ditulis ulang di lemon:
        // kalau berubah di Orcha, kalimat yang dikirim ke penyewa ikut berubah.
        'denda_sewa' => ['tenggang_menit' => 30, 'persen_tarif_harian_per_jam' => 10],
    ]];
}

test('daftar sewa menandai unit yang telat kembali', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(balasanSewa()),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan')
        ->assertOk()
        ->assertSee('telat 3 jam')
        ->assertSee('Serah terima');
});

test('lembar serah terima mengisi usulan denda keterlambatan', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa()),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        // Usulan sistem terisi supaya admin melanjutkan, bukan mengetik ulang,
        // dan angkanya bertitik: salah baca satu digit di kolom denda berarti
        // salah tagih sepuluh kali lipat
        ->assertSet('dendaKeterlambatan', '150.000')
        ->assertSee('Pemeriksaan Fisik')
        ->assertSee('Usulan sistem untuk keterlambatan');
});

test('kerusakan baru ditandai, lecet lama tidak', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        // Bodi depan SUDAH lecet sebelum diserahkan; kaca baru rusak
        '*' => Http::response(satuSewa(['kondisi_awal' => ['bodi_depan' => 'lecet', 'kaca' => 'baik']])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->set('kondisiAkhir.bodi_depan', 'lecet')
        ->set('kondisiAkhir.kaca', 'rusak')
        ->assertSeeHtml('kerusakan baru');
});

test('serah terima diteruskan ke orcha, lalu admin dikembalikan ke daftar', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*/serah-terima' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*' => Http::response(satuSewa()),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->set('dikembalikanPada', '2026-09-11T11:00')
        ->set('dendaKerusakan', 300000)
        ->set('catatanDenda', 'Spion kanan retak')
        ->call('simpanSerahTerima')
        // Lembarnya halaman tersendiri: yang menutupnya adalah kepindahan
        ->assertRedirect(route('admin.orcha.penyewaan'));

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/serah-terima')
        && $permintaan['denda_kerusakan'] === 300000
        && $permintaan['catatan_denda'] === 'Spion kanan retak');
});

test('dp yang buktinya belum dicek disebut apa adanya, bukan dianggap belum bayar', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'status' => 'baru', 'status_label' => 'Baru', 'estimasi_biaya' => 3300000,
            'tagihan' => ['total' => 3300000, 'sudah' => 0, 'sisa' => 3300000, 'lunas' => false],
            'menunggu_dicek' => ['nominal' => 990000, 'berkas' => 1],
        ])),
    ]);

    /*
     | Status pesanan hanya maju ke "DP Masuk" setelah buktinya DITERIMA admin,
     | bukan saat penyewa mengunggahnya — kalau tidak, siapa pun bisa memajukan
     | statusnya sendiri hanya dengan mengunggah gambar.
     |
     | Akibatnya penyewa yang benar-benar sudah mentransfer tetap berstatus
     | "Baru", dan admin di loket menyimpulkan orangnya belum bayar.
     */
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Posisi Pembayaran')
        ->assertSee('Rp 990.000 sudah dilaporkan penyewa, tapi buktinya belum dicek.')
        ->assertSee('bukan berarti penyewa belum membayar')
        // Dan ada jalan ke tempat memeriksanya
        ->assertSee('Buka Bukti Pembayaran')
        // Yang belum diterima tidak ikut dihitung sebagai uang masuk
        ->assertDontSee('Belum ada pembayaran yang masuk');
});

test('sewa tanpa bukti apa pun ditegur sebelum kunci diserahkan', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'tagihan' => ['total' => 3300000, 'sudah' => 0, 'sisa' => 3300000, 'lunas' => false],
            'menunggu_dicek' => ['nominal' => 0, 'berkas' => 0],
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Belum ada pembayaran yang masuk')
        ->assertDontSee('belum dicek.');
});

test('sewa yang sudah lunas tidak ditegur apa pun', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'status' => 'dp_masuk', 'status_label' => 'DP Masuk',
            'tagihan' => ['total' => 3300000, 'sudah' => 3300000, 'sisa' => 0, 'lunas' => true],
            'menunggu_dicek' => ['nominal' => 0, 'berkas' => 0],
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Lunas')
        ->assertSee('Rp 3.300.000')
        ->assertDontSee('Belum ada pembayaran yang masuk')
        ->assertDontSee('belum dicek.');
});

test('sewa lama tanpa data tagihan tidak memunculkan kartu pembayaran', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa(['tagihan' => []])),
    ]);

    // Kartu yang isinya nol semua lebih menyesatkan daripada tidak ada.
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertDontSee('Posisi Pembayaran')
        ->assertSee('Keadaan Unit');
});

/*
|--------------------------------------------------------------------------
| Bagian pemeriksaan kendaraan
|--------------------------------------------------------------------------
|
| Dua belas bagian yang dicek saat serah terima dulu dipatok di config Orcha.
| Sekarang data, dikelola admin sendiri, dan dipilah per jenis unit.
*/

function balasanBagian(array $ubah = []): array
{
    return ['data' => [array_merge([
        'id' => 4, 'kunci' => 'pintu_bagasi', 'label' => 'Pintu bagasi samping',
        'jenis' => ['bus'], 'jenis_label' => 'Bus',
        'biaya_lecet' => 150000, 'biaya_rusak' => 900000, 'biaya_hilang' => 1800000,
        'urutan' => 40, 'aktif' => true, 'pernah_dipakai' => false,
    ], $ubah)], 'meta' => [
        'halaman' => 1, 'per_halaman' => 10, 'total' => 14, 'halaman_terakhir' => 2,
        'jenis_kendaraan' => ['mobil' => 'Mobil', 'hiace' => 'HiAce', 'bus' => 'Bus'],
        'kondisi_pemeriksaan' => ['baik' => 'Baik', 'lecet' => 'Lecet / minor', 'rusak' => 'Rusak', 'hilang' => 'Hilang'],
    ]];
}

test('halaman bagian pemeriksaan menyebut apa yang tidak bisa diubah', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    /*
     | Yang membuka halaman ini akan bertanya kenapa tingkat kondisinya tidak
     | ikut bisa diubah. Dijawab di muka supaya tidak ditemukan sebagai
     | penolakan saat tombolnya sudah ditekan.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->html());

    expect($isi)->toContain('Baik → Lecet / minor → Rusak → Hilang')
        ->toContain('tetap dikunci');
});

test('tarif diketik bertitik dan dikirim ke orcha sebagai angka polos', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    /*
     | Nol tetap tergambar "0", tidak dikosongkan seperti di formulir armada.
     |
     | Di sana nol berarti "tarif ini tidak dijual" dan menampilkannya hanya
     | mengganggu. Di sini nol nilai yang berarti dan ketikannya WAJIB ada,
     | jadi mengosongkannya sendiri membuat ketikan admin lenyap lalu ditolak
     | sebagai kosong pada tekan berikutnya.
     */
    $komponen = Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaTambah')
        ->set('uang.biaya_rusak', '1500000')
        ->set('uang.biaya_lecet', '0');

    $komponen->assertSet('uang.biaya_rusak', '1.500.000')
        ->assertSet('uang.biaya_lecet', '0')
        // Kotak yang benar-benar dikosongkan tetap kosong, supaya penjagaan
        // "required" masih punya yang ditangkap
        ->set('uang.biaya_hilang', '')
        ->assertSet('uang.biaya_hilang', '');

    $komponen->set('isian.label', 'Wiper belakang')
        ->set('uang.biaya_hilang', '2.000.000')
        ->set('jenisPilihan', ['bus'])
        ->call('simpan')
        ->assertHasNoErrors();

    // Titik pemisahnya dilepas sebelum berangkat: yang tersimpan angka, bukan teks
    Http::assertSent(fn ($p) => $p->method() !== 'POST'
        || ($p['biaya_rusak'] === 1500000 && $p['biaya_lecet'] === 0 && $p['biaya_hilang'] === 2000000));
});

test('tarif nol yang tersimpan tetap tergambar nol saat disunting', function () {
    Http::fake(['*' => Http::response(balasanBagian(['biaya_lecet' => 0]))]);

    $baris = balasanBagian(['biaya_lecet' => 0])['data'][0];

    Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaSunting', $baris['id'], $baris)
        // Kalau dikosongkan, admin yang cuma mengubah namanya akan ditolak
        // karena tarif yang tidak pernah ia sentuh terbaca kosong.
        ->assertSet('uang.biaya_lecet', '0')
        ->assertSet('uang.biaya_rusak', '900.000');
});

test('keadaan aktif dipegang properti tersendiri, bukan anggota array', function () {
    Http::fake(['*' => Http::response(balasanBagian(['aktif' => false]))]);

    $baris = balasanBagian(['aktif' => false])['data'][0];

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaSunting', $baris['id'], $baris)
        ->assertSet('aktif', false)
        ->html());

    // Sakelarnya ada di formulir tambah maupun ubah — sebelumnya hanya muncul
    // saat menyunting, dan admin tidak pernah tahu keadaan ini ada, apalagi
    // bahwa MENONAKTIFKAN adalah jalan keluar untuk bagian yang tidak bisa
    // dihapus.
    expect($isi)->toContain('orcha-sakelar-kartu')
        ->toContain('Tidak diperiksa lagi');

    $tambah = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaTambah')
        ->assertSet('aktif', true)
        ->html());

    expect($tambah)->toContain('orcha-sakelar-kartu')->toContain('Ikut diperiksa');
});

test('jenis unit dipilih lewat kartu, sebentuk dengan pemilih transmisi armada', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaTambah')
        ->set('jenisPilihan', ['bus'])
        ->html());

    // Sasaran kliknya seluruh kartu, bukan kotak 16px — dan yang terpilih
    // ditandai warna SEKALIGUS tanda centang.
    expect($isi)->toContain('orcha-pilih-kartu')
        ->toContain('orcha-kartu-pilihan aktif')
        ->toContain('bi-bus-front-fill');

    // Gayanya tinggal di lembar bersama, dipakai dua halaman
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    expect($gaya)->toContain('.orcha-kartu-pilihan')->toContain('.orcha-sakelar-kartu');
});

/** Rujukan yang memuat bagian pemeriksaan dipilah per jenis unit. */
function rujukanBagian(): array
{
    return ['data' => [
        'pemeriksaan_kendaraan' => [
            'kaca' => 'Kaca & spion',
            'ban' => 'Ban & pelek',
            'pintu_bagasi' => 'Pintu bagasi samping',
            'ac_lama' => 'AC blower atas',
        ],
        'pemeriksaan_per_jenis' => [
            'mobil' => ['kaca' => 'Kaca & spion', 'ban' => 'Ban & pelek'],
            'bus' => ['kaca' => 'Kaca & spion', 'pintu_bagasi' => 'Pintu bagasi samping'],
        ],
        'kondisi_pemeriksaan' => ['baik' => 'Baik', 'lecet' => 'Lecet', 'rusak' => 'Rusak', 'hilang' => 'Hilang'],
        'jenis_kendaraan' => ['mobil' => 'Mobil', 'bus' => 'Bus'],
        'status_penyewaan' => ['selesai' => 'Selesai'],
    ]];
}

test('lembar serah terima memakai bagian menurut jenis unit yang disewa', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanBagian()),
        '*' => Http::response(satuSewa(['kendaraan' => [
            'id' => 1, 'nama' => 'Bus Pariwisata', 'transmisi' => 'Manual', 'jenis' => 'bus',
        ]])),
    ]);

    $bagian = Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->instance()->bagianPeriksa();

    // Bus tidak punya ban serep sebagaimana mobil
    expect($bagian)->toHaveKey('pintu_bagasi')
        ->and($bagian)->toHaveKey('kaca')
        ->and($bagian)->not->toHaveKey('ban');
});

test('bagian yang sudah tercatat tidak lenyap walau dicabut dari jenisnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanBagian()),
        '*' => Http::response(satuSewa([
            'kendaraan' => ['id' => 1, 'nama' => 'Bus Pariwisata', 'transmisi' => 'Manual', 'jenis' => 'bus'],
            // "ac_lama" sudah dinonaktifkan dan tidak lagi berlaku untuk bus,
            // tetapi hasil pemeriksaannya terlanjur tercatat di lembar ini.
            'kondisi_awal' => ['kaca' => 'baik', 'ac_lama' => 'baik'],
            'kondisi_akhir' => ['kaca' => 'rusak', 'ac_lama' => 'rusak'],
        ])),
    ]);

    $bagian = Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->instance()->bagianPeriksa();

    /*
     | Kalau ia lenyap dari layar, satu-satunya bukti kerusakan ikut lenyap —
     | dan itu terjadi tepat pada dokumen yang dipakai berbantahan dengan
     | penyewa.
     */
    expect($bagian)->toHaveKey('ac_lama')
        ->and($bagian['ac_lama'])->toBe('AC blower atas');
});

test('detail sewa menampilkan bagian unit itu saja, bukan seluruh daftar', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanBagian()),
        '*' => Http::response(satuSewa([
            'kendaraan' => ['id' => 1, 'nama' => 'Bus Pariwisata', 'transmisi' => 'Manual', 'jenis' => 'bus'],
            'kondisi_awal' => ['kaca' => 'baik'],
            'kondisi_akhir' => ['kaca' => 'rusak'],
        ])),
    ]);

    // Sewa bus yang menampilkan baris "—" untuk bagian mobil membuat yang
    // benar-benar diperiksa tenggelam di antaranya.
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent());

    expect($isi)->toContain('Pintu bagasi samping')
        ->not->toContain('Ban &amp; pelek');
});

test('bagian baru ikut terkirim walau admin tidak menyentuhnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanBagian()),
        '*/kondisi' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*' => Http::response(['data' => [
            'id' => 5, 'nama' => 'Avanza', 'merek' => 'Toyota', 'jenis' => 'mobil',
            'transmisi_tersedia' => ['Manual'], 'kapasitas' => 7, 'tarif_hari' => 350000,
            // Unit ini terakhir disimpan sebelum "ban" ditambahkan
            'kondisi_terkini' => ['kaca' => 'baik'],
        ]]),
    ]);

    /*
     | kondisiIsian diisi dari kondisi_terkini unit, jadi bagian yang baru
     | ditambahkan setelah unit ini terakhir disimpan tidak ada di dalamnya.
     | Di layar ia tetap tergambar — bladenya memakai `?? 'baik'` — tetapi
     | tidak pernah ikut terkirim selama admin tidak menyentuhnya, dan catatan
     | kondisi unit berlubang tepat pada bagian yang baru dianggap penting.
     */
    Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->call('simpanKondisi');

    /*
     | Dituntut COCOK, bukan "tidak ada yang salah". Penjagaan berbentuk "kalau
     | URL-nya /kondisi maka ..." dipenuhi begitu saja oleh permintaan lain di
     | permintaan yang sama — rujukan dan pemuatan unit — sehingga lolos
     | walaupun bagian barunya tidak pernah ikut terkirim.
     */
    Http::assertSent(fn ($p) => str_contains($p->url(), '/kondisi')
        && ($p['kondisi']['ban'] ?? null) === 'baik'
        && ($p['kondisi']['kaca'] ?? null) === 'baik'
        // Dan yang tidak berlaku untuk mobil tidak ikut terkirim
        && ! array_key_exists('pintu_bagasi', $p['kondisi']));
});

test('keadaan diperiksa dan nonaktif dibedakan warnanya, dan bukan merah', function () {
    Http::fake(['*' => Http::response(balasanBagian(['aktif' => false]))]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->html());

    /*
     | Lencananya tidak lagi meminjam dari layar lain. Yang aktif dulu memakai
     | lencana "sudah dibaca" milik pesan kontak — abu pudar, terbaca seperti
     | sesuatu yang dimatikan — dan yang nonaktif memakai lencana "ditolak"
     | milik pembatalan, merah, seolah ada yang salah. Menonaktifkan bagian
     | justru tindakan yang benar dan disengaja.
     */
    expect($isi)->toContain('data-keadaan="nonaktif"')
        ->not->toContain('orcha-status-batal')
        ->not->toContain('orcha-status-pesan');

    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    preg_match('/\.orcha-cip-keadaan\[data-keadaan="aktif"\][^{]*\{([^}]*)\}/', $gaya, $aktif);
    preg_match('/\.orcha-cip-keadaan\[data-keadaan="nonaktif"\][^{]*\{([^}]*)\}/', $gaya, $mati);

    expect(trim($aktif[1] ?? ''))->not->toBe('')
        ->and(trim($mati[1] ?? ''))->not->toBe('')
        // Benar-benar berbeda satu sama lain
        ->and(trim($aktif[1]))->not->toBe(trim($mati[1]))
        // Hijau untuk yang sedang berlaku
        ->and($aktif[1])->toContain('#1f7a44')
        // Merah disimpan untuk hal yang memang keliru, bukan untuk ini
        ->and(strtolower($mati[1]))->not->toContain('#9b25')
        ->and(strtolower($mati[1]))->not->toContain('#fee2e2');
});

test('tiap jenis unit punya warna dan ikonnya sendiri di daftar bagian', function () {
    Http::fake(['*' => Http::response(balasanBagian(['jenis' => ['mobil', 'hiace', 'bus']]))]);

    /*
     | Tiga lencana biru yang sama memaksa tiap katanya dieja ulang baris demi
     | baris. Yang dibaca admin di kolom ini bukan "unit apa saja" melainkan
     | "bagian ini berlaku untuk yang mana".
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->html());

    expect($isi)
        ->toContain('data-jenis="mobil"')
        ->toContain('data-jenis="hiace"')
        ->toContain('data-jenis="bus"')
        // Ikonnya ikut berbeda, jadi pemilahannya tetap terbaca oleh yang
        // sulit membedakan warna
        ->toContain('bi-car-front-fill')
        ->toContain('bi-truck-front-fill')
        ->toContain('bi-bus-front-fill');

    // Warnanya benar-benar berbeda satu sama lain
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));
    $warna = [];

    foreach (['mobil', 'hiace', 'bus'] as $jenis) {
        preg_match('/\.orcha-cip-jenis\[data-jenis="'.$jenis.'"\][^{]*\{([^}]*)\}/', $gaya, $cocok);
        $warna[$jenis] = trim($cocok[1] ?? '');
    }

    expect(array_filter($warna))->toHaveCount(3)
        ->and(count(array_unique($warna)))->toBe(3);
});

test('daftar bagian pemeriksaan punya saringan dan penomoran halaman', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->html());

    expect($isi)
        ->toContain('Menampilkan')
        ->toContain('halaman=2')
        // Dua belas bagian muat di layar, tetapi begitu admin menambahkan
        // bagiannya sendiri mencari satu nama jadi menggulung terus
        ->toContain('Cari nama bagian')
        ->toContain('Semua jenis unit');

    // Penomorannya duduk di DALAM kartu tabelnya, seperti daftar Orcha lain.
    // Diperiksa pada sumbernya: di keluaran jadi, penutup kartu tidak lagi
    // bisa dibedakan dari penutup pembungkus mana pun.
    $sumber = file_get_contents(resource_path('views/livewire/pages/admin/orcha/bagian-pemeriksaan/index.blade.php'));

    expect($sumber)->toMatch(
        '#</table>\s*</div>\s*(\{\{--.*?--\}\}\s*)?'
        .'@include\([^)]*partials\.paginasi[^)]*\)\s*</div>\s*</div>#s'
    );
});

test('saringan jenis dikirim sebagai jenis, bukan status', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->set('filterStatus', 'bus');

    // Halaman ini menyaring per jenis unit; "status" tidak berarti apa pun di sini.
    Http::assertSent(fn ($p) => ! str_contains($p->url(), 'jenis=bus') || ! str_contains($p->url(), 'status='));
    Http::assertSent(fn ($p) => str_contains($p->url(), 'jenis=bus'));
});

test('bagian baru memindahkan pandangan ke halaman tempat barisnya mendarat', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    /*
     | Daftarnya urut dari yang terbaru, jadi bagian baru mendarat di halaman
     | satu. Yang menambahkannya sambil membuka halaman dua tidak akan melihat
     | barisnya di mana pun dan mengira simpanannya gagal.
     */
    $komponen = Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->set('halaman', 2)
        ->set('cari', 'kaca')
        ->set('filterStatus', 'mobil')
        ->call('bukaTambah')
        ->set('isian.label', 'Wiper belakang')
        ->set('uang.biaya_lecet', '150.000')
        ->set('uang.biaya_rusak', '900.000')
        ->set('uang.biaya_hilang', '1.800.000')
        ->set('jenisPilihan', ['bus'])
        ->call('simpan');

    $komponen->assertSet('halaman', 1)
        // Saringannya ikut dilepas: bagian bus yang baru ditambahkan sementara
        // daftarnya sedang disaring "Mobil" juga tidak akan terlihat.
        ->assertSet('cari', '')
        ->assertSet('filterStatus', '');
});

test('bagian yang sudah tercatat di serah terima tidak ditawari tombol hapus', function () {
    Http::fake(['*' => Http::response(balasanBagian(['pernah_dipakai' => true]))]);

    /*
     | Menawarkan tombolnya lalu menolak saat ditekan hanya memindahkan
     | kekecewaan. Yang perlu diketahui admin adalah bahwa jalannya memang
     | menonaktifkan, bukan menghapus — dan itu terlihat sebelum ditekan.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->html());

    expect($isi)->toContain('orcha-aksi-mati')
        ->not->toContain('hapus(4)');
});

test('bagian yang belum pernah dipakai tetap boleh dihapus', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->html());

    /*
     | Sebentuk dengan tombol ubah di sebelahnya, dan dengan tombol hapus di
     | daftar armada: .orcha-aksi-hapus, bukan .tombol-bahaya yang bentuknya
     | lain sendiri.
     */
    expect($isi)->toContain('orcha-aksi-hapus')
        ->not->toContain('tombol-bahaya')
        ->not->toContain('orcha-aksi-mati');

    /*
     | Konfirmasinya lewat pcek-konfirmasi — SweetAlert seragam yang dipakai
     | seluruh tindakan merusak di lemon — bukan wire:confirm bawaan, yang
     | memunculkan kotak peramban polos di tengah halaman yang sudah bergaya.
     */
    expect($isi)->toContain('pcek-konfirmasi')
        ->toContain('data-action="hapus"')
        ->toContain('data-arg="4"')
        ->not->toContain('wire:confirm');
});

test('tarif wajib diisi walau nol, dan jenis unit tidak boleh kosong', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    /*
     | Bagian tanpa tarif membuat usulan denda kerusakan diam-diam melewatinya:
     | perhitungannya tetap jalan, angkanya kurang, dan tidak ada yang memberi
     | tahu. Bagian tanpa jenis tidak akan pernah muncul di formulir siapa pun.
     */
    Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaTambah')
        ->set('isian.label', 'Wiper belakang')
        ->set('uang.biaya_rusak', '')
        ->set('jenisPilihan', [])
        ->call('simpan')
        ->assertHasErrors(['uang.biaya_rusak', 'jenisPilihan']);

    // Nol yang ditulis sadar berbeda dari nol karena lupa — dan yang sadar lolos
    Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaTambah')
        ->set('isian.label', 'Wiper belakang')
        ->set('uang.biaya_lecet', '0')
        ->set('uang.biaya_rusak', '0')
        ->set('uang.biaya_hilang', '0')
        ->set('jenisPilihan', ['bus'])
        ->call('simpan')
        ->assertHasNoErrors();
});

test('menyimpan bagian melupakan rujukan supaya armada memakai daftar baru', function () {
    Http::fake(['*' => Http::response(balasanBagian())]);

    cache()->put('orcha.rujukan', ['pemeriksaan_kendaraan' => ['lama' => 'Lama']], 600);

    /*
     | Daftar rujukan memuat bagian pemeriksaan. Tanpa dilupakan, formulir
     | armada dan lembar serah terima masih memakai daftar lama sampai
     | simpanannya kedaluwarsa sendiri — dan admin mengira bagian yang baru
     | saja ditambahkannya tidak tersimpan.
     */
    Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan\OrchaBagianList::class)
        ->call('bukaTambah')
        ->set('isian.label', 'Wiper belakang')
        ->set('uang.biaya_lecet', '150.000')
        ->set('uang.biaya_rusak', '900.000')
        ->set('uang.biaya_hilang', '1.800.000')
        ->set('jenisPilihan', ['bus'])
        ->call('simpan');

    expect(cache()->has('orcha.rujukan'))->toBeFalse();
});

test('ceklis armada hanya memuat bagian yang berlaku untuk jenis unitnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'pemeriksaan_kendaraan' => ['ban' => 'Ban & pelek', 'pintu_bagasi' => 'Pintu bagasi samping'],
            'pemeriksaan_per_jenis' => [
                'mobil' => ['ban' => 'Ban & pelek'],
                'bus' => ['pintu_bagasi' => 'Pintu bagasi samping'],
            ],
            'kondisi_pemeriksaan' => ['baik' => 'Baik'],
            'jenis_kendaraan' => ['mobil' => 'Mobil', 'bus' => 'Bus'],
        ]]),
        '*' => Http::response(['data' => []]),
    ]);

    // Bus tidak punya ban serep sebagaimana mobil, dan ceklis yang memuat
    // bagian tak berlaku hanya akan diisi "Baik" tanpa pernah diperiksa.
    $komponen = Livewire::actingAs(adminOrcha())->test(OrchaArmadaForm::class);

    expect($komponen->instance()->bagianUntukJenis())->toBe(['ban' => 'Ban & pelek']);

    $komponen->set('jenis', 'bus');

    expect($komponen->instance()->bagianUntukJenis())->toBe(['pintu_bagasi' => 'Pintu bagasi samping']);
});

test('orcha yang belum mengirim pemilahan jenis tidak mengosongkan ceklis', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'pemeriksaan_kendaraan' => ['ban' => 'Ban & pelek', 'kaca' => 'Kaca & spion'],
            'kondisi_pemeriksaan' => ['baik' => 'Baik'],
            'jenis_kendaraan' => ['mobil' => 'Mobil'],
        ]]),
        '*' => Http::response(['data' => []]),
    ]);

    // Selama jeda antara lemon dan Orcha ter-deploy: ceklis yang terlalu
    // panjang masih bisa dikerjakan, ceklis kosong tidak.
    $komponen = Livewire::actingAs(adminOrcha())->test(OrchaArmadaForm::class);

    expect($komponen->instance()->bagianUntukJenis())
        ->toBe(['ban' => 'Ban & pelek', 'kaca' => 'Kaca & spion']);
});

test('kedua tombol simpan armada berganti pemintal, dan diam sebelum ditekan', function () {
    Http::fake(['*' => Http::response(['data' => [
        'id' => 5, 'nama' => 'Avanza', 'merek' => 'Toyota', 'jenis' => 'mobil',
        'transmisi_tersedia' => ['Manual'], 'kapasitas' => 7, 'tarif_hari' => 350000,
        'kondisi_terkini' => ['kaca' => 'baik'],
    ]])]);

    /*
     | Menyimpan unit menembak Orcha dengan seluruh isinya — identitas, tarif
     | tiap satuan, aturan biaya dalam dan luar kota, sampai fotonya — jadi
     | jedanya terasa. Tombol yang hanya berganti kalimat masih terlihat diam:
     | yang menekannya cenderung menekan lagi, dan unit yang terkirim dua kali
     | bukan perkara tampilan.
     */
    $isi = Livewire::actingAs(adminOrcha())
        ->test(OrchaArmadaForm::class, ['kendaraan' => 5])
        ->html();

    // Dua tombol simpan di halaman ini: unitnya, dan kondisinya
    expect(substr_count($isi, 'spinner-border spinner-border-sm'))->toBe(2);

    expect($isi)->toContain('Menyimpan ke Orcha…')
        ->toContain('Menyimpan…')
        // Masing-masing menyasar metodenya sendiri, supaya yang satu tidak ikut
        // memintal saat yang lain sedang berjalan
        ->toContain('wire:target="simpan"')
        ->toContain('wire:target="simpanKondisi"')
        // Kelas display Bootstrap memakai !important dan akan mengalahkan
        // aturan penyembunyi, jadi pembungkusnya tidak boleh memakainya
        ->not->toContain('wire:loading wire:target="simpan" class="d-')
        ->not->toContain('wire:loading wire:target="simpanKondisi" class="d-');

    // Ikon simpannya berganti MENJADI pemintal, bukan berdiri di sebelahnya:
    // keduanya tidak boleh tergambar bersamaan
    expect($isi)->not->toMatch('/<i class="bi bi-save"><\/i>\s*<span wire:loading\b/');

    // Dan tersembunyi sejak awal lewat gaya, bukan hanya lewat skrip Livewire
    $halaman = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/armada/5/ubah')->assertOk()->getContent();

    expect($halaman)->toContain('[wire\\:loading] { display: none; }');
});

test('tombol destinasi seukuran formulir orcha lain, dan berganti pemintal', function () {
    Http::fake(['*' => Http::response(['data' => []])]);

    /*
     | Terukur di peramban: paket wisata dan armada 38px dengan huruf 16px,
     | destinasi masih memakai ukuran .orcha-btn apa adanya. Ketiganya formulir
     | tambah data yang dikerjakan berurutan, dan tombol yang mengecil sendiri
     | di salah satunya membuat ketiganya terasa dari aplikasi berbeda-beda.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaDestinasiForm::class)
        ->html());

    // Keduanya, bukan tombol simpannya saja
    expect(substr_count($isi, 'orcha-btn-besar'))->toBe(2);

    /*
     | Pemintal, bukan sekadar tulisan yang berganti: menyimpan destinasi
     | menembak Orcha berikut gambarnya, jadi jedanya terasa.
     */
    expect($isi)->toContain('spinner-border spinner-border-sm')
        ->toContain('Menyimpan ke Orcha…')
        ->toContain('wire:target="simpan"')
        // Ikon simpannya berganti MENJADI pemintal, bukan berdiri di sebelahnya
        ->not->toMatch('/<i class="bi bi-save"><\/i>\s*<span wire:loading\b/')
        // Kelas display Bootstrap memakai !important dan akan mengalahkan
        // aturan penyembunyi
        ->not->toContain('wire:loading wire:target="simpan" class="d-');

    // Dan tersembunyi sejak awal lewat gaya, bukan hanya lewat skrip Livewire
    $halaman = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/destinasi/tambah')->assertOk()->getContent();

    expect($halaman)->toContain('[wire\\:loading] { display: none; }');
});

test('ikon penanda foto kosong ditengahkan, bukan meluber ke kanan bawah', function () {
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    /*
     | Terukur di peramban sebelum diperbaiki: glifnya duduk 7,75px di bawah
     | dan 1,15px di kanan titik tengah kotaknya. Sesudah: 0,25px dan 0,05px.
     |
     | Sebabnya sudah tertulis di lembar gaya ini: kotak elemen <i>-nya dipatok
     | 16 x 16 px oleh gaya bawaan ikon, sedangkan glifnya digambar sebesar
     | font kotaknya — 18,4px di sini. Kotak <i> itu sendiri sudah tepat di
     | tengah; yang salah letak luberannya, yang jatuh seluruhnya ke bawah dan
     | ke kanan karena isi blok mulai dari pojok kiri atas.
     |
     | Obatnya sama dengan yang sudah dipakai .orcha-ikon dan .orcha-hero-ikon:
     | ikonnya dijadikan wadah flex dan ::before-nya dijadikan blok, sehingga
     | luberannya terbagi rata ke keempat sisinya.
     */
    preg_match('/([^{}]*)\{\s*display: flex;\s*align-items: center;\s*justify-content: center;\s*line-height: 1;\s*\}/', $gaya, $wadah);
    preg_match('/([^{}]*)::before[^{]*\{\s*display: block;\s*line-height: 1;\s*\}/', $gaya, $sebelum);

    foreach (['.orcha-foto-kosong > i', '.orcha-etalase-foto .kosong > i'] as $pemilih) {
        expect($wadah[1] ?? '')->toContain($pemilih);
        expect($sebelum[1] ?? '')->toContain($pemilih);
    }

    /*
     | .stat-icon-wrapper dan .empty-state-icon-wrapper SENGAJA tidak ikut.
     | Keduanya sudah menengahkan glifnya dengan tepat karena ikonnya sendiri
     | sudah jadi wadah flex — aturan ini sempat diberlakukan ke sana dan
     | justru merusaknya, glifnya turun 12px sekaligus bergeser 12px ke kanan.
     */
    expect($wadah[1] ?? '')->not->toContain('.stat-icon-wrapper')
        ->not->toContain('.empty-state-icon-wrapper');
});

test('daftar testimoni dipenggal per halaman dan mencari lewat orcha', function () {
    Http::fake(['*' => Http::response([
        'data' => [[
            'id' => 1, 'nama' => 'Siti Nurhaliza', 'isi' => 'Liburan terbaik.',
            'rating' => 5, 'foto' => null,
        ]],
        'meta' => ['halaman' => 1, 'per_halaman' => 10, 'total' => 14, 'halaman_terakhir' => 2],
    ])]);

    $komponen = Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'testimoni']);

    expect(tanpaGaya($komponen->html()))
        ->toContain('Menampilkan')
        ->toContain('halaman=2');

    /*
     | Pencariannya dititipkan ke Orcha, TIDAK dikerjakan di sini.
     |
     | Penyaring di sisi lemon hanya melihat baris yang kebetulan sedang
     | tampil, sehingga yang dicari admin akan "tidak ditemukan" padahal ada di
     | halaman lain.
     */
    $komponen->set('cari', 'Siti');

    Http::assertSent(fn ($p) => str_contains($p->url(), '/testimoni?')
        && str_contains($p->url(), 'cari=Siti')
        // Sembilan, bukan sepuluh: kartunya berjajar tiga per baris, jadi
        // sepuluh menyisakan satu kartu sendirian di baris keempat.
        && str_contains($p->url(), 'per_halaman=9'));
});

test('kotak cari etalase punya tombol pengosong, seperti daftar orcha lain', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    /*
     | Sebelumnya kotaknya ditulis sendiri di halaman ini tanpa tombol silang,
     | sehingga satu-satunya cara mengembalikan daftar utuh adalah menghapus
     | ketikannya huruf demi huruf.
     */
    $komponen = Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'testimoni']);

    // Tombolnya hanya muncul saat ada isinya — tombol yang selalu ada tetapi
    // tidak selalu berguna hanya menambah benda yang harus diabaikan mata.
    expect(tanpaGaya($komponen->html()))->not->toContain('bersihkanCari');

    $komponen->set('cari', 'Siti');

    expect(tanpaGaya($komponen->html()))->toContain('bersihkanCari')
        ->toContain('orcha-cari-bersih');

    $komponen->call('bersihkanCari')->assertSet('cari', '');
});

test('daftar partner dipenggal per halaman', function () {
    Http::fake(['*' => Http::response([
        'data' => [['id' => 1, 'nama' => 'Homestay Ijen', 'logo' => null]],
        'meta' => ['halaman' => 1, 'per_halaman' => 9, 'total' => 13, 'halaman_terakhir' => 2],
    ])]);

    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'partner'])
        ->html());

    expect($isi)->toContain('Menampilkan')->toContain('halaman=2');
});

/** Detail pendaftaran dengan satu pengajuan pembatalan; $kembali menentukan nasibnya. */
function balasanPendaftaranBatal(int $kembali, string $status = 'disetujui'): array
{
    return ['data' => [
        'id' => 8, 'kode' => 'OT-1608-ZT8K', 'nama' => 'sofyan', 'whatsapp' => '0895',
        'email' => null, 'jumlah_peserta' => 1,
        'peserta' => [['nama' => 'sofyan', 'titik_jemput' => 'Jogja']],
        'jemput_per_titik' => ['Jogja' => ['sofyan']],
        'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => ['sofyan'],
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
        'tanggal_berangkat' => '2026-10-19', 'titik_jemput' => 'Jogja',
        'catatan' => null, 'status' => 'batal', 'status_label' => 'Batal',
        'jumlah_riwayat_kesehatan' => 0, 'dibuat_pada' => now()->toIso8601String(),
        'tagihan' => [
            'total' => 1900000, 'sudah' => 570000, 'sisa' => 1330000, 'lunas' => false,
            'total_teks' => 'Rp 1.900.000', 'sudah_teks' => 'Rp 570.000', 'sisa_teks' => 'Rp 1.330.000',
        ],
        'pembayaran' => [],
        'pembatalan' => [
            'id' => 6, 'nama_pemohon' => 'sofyan', 'alasan_label' => 'Jadwal bentrok',
            'penjelasan' => null, 'jumlah_dibatalkan' => 1,
            'rekening' => 'mandiri · 12345678 a.n. sofyan',
            'status' => $status, 'status_label' => 'Disetujui',
            'perkiraan' => [
                'persen' => 100, 'dibayar' => 570000, 'potongan' => 570000 - $kembali, 'kembali' => $kembali,
                'total' => 1900000, 'total_teks' => 'Rp 1.900.000',
                'dibayar_teks' => 'Rp 570.000',
                'potongan_teks' => 'Rp '.number_format(570000 - $kembali, 0, ',', '.'),
                'kembali_teks' => 'Rp '.number_format($kembali, 0, ',', '.'),
                'batas' => 'Kurang dari 7 hari', 'lewat' => true, 'jenis' => 'open_trip',
            ],
            'dibuat_pada' => now()->toIso8601String(),
        ],
    ]];
}

/**
 * Balasan /keuntungan secukupnya untuk kartu uang dan grafiknya di dashboard.
 *
 * Namanya dibedakan dari balasanKeuntungan() milik OrchaKeuntunganTest —
 * fungsi tingkat atas di berkas uji Pest berbagi satu ruang nama, jadi dua
 * berkas yang memakai nama sama akan bertabrakan saat suite dijalankan penuh.
 */
function balasanUangDashboard(): array
{
    $bulan = fn ($ym, $label, $omzet, $untung) => [
        'bulan' => $ym, 'bulan_label' => $label, 'omzet' => $omzet,
        'modal' => $omzet - $untung, 'keuntungan' => $untung,
        'omzet_teks' => 'Rp '.number_format($omzet, 0, ',', '.'),
        'keuntungan_teks' => 'Rp '.number_format($untung, 0, ',', '.'),
    ];

    return ['data' => [
        'ringkasan' => [
            'omzet_teks' => 'Rp 48.200.000', 'modal_teks' => 'Rp 31.400.000',
            'keuntungan_teks' => 'Rp 16.800.000', 'potensi_omzet_teks' => 'Rp 9.750.000',
            'belum_lengkap' => 3,
        ],
        'per_bulan' => [
            $bulan('2026-07', 'Jul 2026', 8300000, 2900000),
            $bulan('2026-08', 'Agt 2026', 11400000, 4200000),
        ],
    ]];
}

test('dashboard menggambar omzet, modal, dan keuntungan dari laporan yang sama', function () {
    Http::fake([
        '*/keuntungan' => Http::response(balasanUangDashboard()),
        '*' => Http::response(['data' => ['kartu' => [], 'perlu_ditindak' => []]]),
    ]);

    /*
     | Angkanya dibaca dari /keuntungan — laporan yang SAMA dengan halaman
     | Keuntungan Paket — bukan dihitung ulang di dashboard. Dua tempat yang
     | menghitung omzet sendiri-sendiri akan berbeda angkanya suatu saat,
     | biasanya tepat ketika ada yang menanyakannya.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)
        ->toContain('Omzet masuk')->toContain('Rp 48.200.000')
        ->toContain('Modal keluar')->toContain('Rp 31.400.000')
        ->toContain('Keuntungan')->toContain('Rp 16.800.000')
        ->toContain('Potensi omzet')
        /*
         | Grafiknya ApexCharts, sama seperti grafik keuangan di dashboard
         | lemon — bukan batang HTML buatan tangan.
         |
         | Pustakanya boleh dipakai di sini: berkasnya ada di public/mazer dan
         | terlacak git, jadi ikut ter-deploy. Yang TIDAK ikut ter-deploy adalah
         | public/build hasil bundel Vite — keduanya sempat saya samakan.
         */
        ->toContain('apexcharts/apexcharts.min.js')
        ->toContain('orcha-grafik-uang')
        // Kotaknya dijauhkan dari morph Livewire, kalau tidak kanvasnya
        // dibuang tiap kali komponennya digambar ulang
        ->toContain('wire:ignore')
        /*
         | Dimuat LANGSUNG, bukan lewat @push('scripts'): layout templateindex
         | — yang dipakai seluruh halaman Orcha — tidak punya @stack('scripts'),
         | jadi apa pun yang di-push ke sana dibuang diam-diam. Pustakanya
         | tidak pernah termuat dan grafiknya tidak pernah tergambar, tanpa
         | satu pun galat di layar maupun di konsol.
         */
        ->not->toContain('@push')
        /*
         | Yang belum bisa dihitung disebut apa adanya. Laporan yang diam soal
         | paket tanpa harga modal membuat keuntungannya terbaca lebih kecil
         | daripada yang sebenarnya, tanpa satu pun tanda.
         */
        ->toContain('3 pendaftaran belum ikut terhitung keuntungannya');
});

test('grafik uang tetap tergambar walau baru satu bulan yang ada isinya', function () {
    $sekarang = now()->format('Y-m');

    Http::fake([
        '*/keuntungan' => Http::response(['data' => [
            'ringkasan' => ['omzet_teks' => 'Rp 2.860.000'],
            'per_bulan' => [[
                'bulan' => $sekarang, 'bulan_label' => 'Bulan ini',
                'omzet' => 2860000, 'modal' => 2800000, 'keuntungan' => 60000,
                'omzet_teks' => 'Rp 2.860.000', 'keuntungan_teks' => 'Rp 60.000',
            ]],
        ]]),
        '*' => Http::response(['data' => ['kartu' => [], 'perlu_ditindak' => []]]),
    ]);

    /*
     | /keuntungan hanya mengirim bulan yang ADA isinya. Pada armada yang baru
     | jalan itu berarti satu titik — dan grafik satu titik bukan grafik, ia
     | cuma noktah yang tidak memberi tahu naik atau turun. Lebih buruk lagi,
     | penjagaan lama "gambar bila lebih dari satu bulan" membuatnya tidak
     | tergambar sama sekali, dan admin bertanya di mana grafiknya.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->toContain('orcha-grafik-uang')->toContain('Bulan ini');

    // Enam bulan penuh, yang kosong diisi nol — jendelanya sama dengan grafik
    // tren di atasnya supaya keduanya bisa dibaca berpasangan.
    $komponen = Livewire::actingAs(adminOrcha())
        ->test(\App\Livewire\Pages\Admin\Orcha\Dashboard\OrchaDashboard::class);

    expect($komponen->viewData('uangPerBulan'))->toHaveCount(6);

    $terakhir = collect($komponen->viewData('uangPerBulan'))->last();

    expect($terakhir['omzet'])->toBe(2860000)
        ->and(collect($komponen->viewData('uangPerBulan'))->first()['omzet'])->toBe(0);
});

test('grafik tren memakai apexcharts, bukan svg buatan tangan', function () {
    Http::fake([
        '*/keuntungan' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [
            'kartu' => [], 'perlu_ditindak' => [],
            'tren_bulanan' => [
                ['label' => 'Jul', 'label_panjang' => 'Juli 2026', 'pendaftaran' => 3, 'penyewaan' => 1],
                ['label' => 'Agt', 'label_panjang' => 'Agustus 2026', 'pendaftaran' => 6, 'penyewaan' => 3],
            ],
        ]]),
    ]);

    /*
     | Dulu digambar SVG sendiri, dengan alasan pustaka grafik tidak bisa
     | dipakai karena aset Vite tidak ikut ter-deploy. Alasannya keliru:
     | ApexCharts bukan hasil bundel Vite — berkasnya ada di public/mazer,
     | terlacak git, dan ikut terkirim ke server.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->toContain('orcha-grafik-tren')
        ->toContain('apexcharts/apexcharts.min.js')
        // SVG buatan tangannya benar-benar ditinggalkan
        ->not->toContain('aria-label="Grafik tren enam bulan"');
});

test('orcha yang belum mengirim laporan keuntungan tidak merobohkan dashboard', function () {
    // Hanya /keuntungan yang gagal; sisanya sehat.
    Http::fake([
        '*/keuntungan' => Http::response(['pesan' => 'Tidak ditemukan.'], 404),
        '*' => Http::response(['data' => [
            'kartu' => [['kunci' => 'paket', 'label' => 'Paket wisata', 'nilai' => 5]],
            'perlu_ditindak' => [],
        ]]),
    ]);

    /*
     | Dashboard tanpa grafik uang masih berguna; dashboard yang roboh tidak.
     | Orcha dipasang terpisah dan boleh tertinggal sekian rilis.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->toContain('Paket wisata')
        ->not->toContain('Omzet masuk')
        ->not->toContain('orcha-grafik-uang');
});

test('status di dua daftar terbaru berwarna, dengan peta yang terpisah', function () {
    Http::fake([
        '*/keuntungan' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [
            'kartu' => [], 'perlu_ditindak' => [],
            'pendaftaran_terbaru' => [[
                'kode' => 'OT-1', 'nama' => 'sofyan', 'status' => 'lunas', 'status_label' => 'Lunas',
                'paket' => ['nama' => 'Open Trip'], 'jumlah_peserta' => 1,
            ]],
            'penyewaan_terbaru' => [[
                'kode' => 'SK-1', 'nama' => 'budi', 'status' => 'berjalan', 'status_label' => 'Berjalan',
                'kendaraan' => ['nama' => 'Bus'], 'durasi_label' => '2 hari',
            ]],
        ]]),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    // Lencana abu seragam tidak memberi tahu apa pun sampai tulisannya dibaca
    expect($isi)->toContain('orcha-cip-status-daftar status-lunas')
        ->toContain('orcha-cip-status-sewa" data-status="berjalan"')
        ->not->toContain('badge bg-light text-dark');

    /*
     | Dua peta TERPISAH: status pendaftaran dan status penyewaan punya kata
     | yang sama ("baru", "batal") tetapi daftar keadaannya berbeda —
     | "berjalan" hanya ada di sewa, "dihubungi" hanya ada di pendaftaran.
     | Menyatukannya membuat satu status kehilangan warnanya diam-diam begitu
     | daftar yang lain berubah.
     */
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    expect($gaya)->toContain('.orcha-cip-status-daftar.status-dihubungi')
        ->toContain('.orcha-cip-status-sewa[data-status="berjalan"]');
});

test('pendaftaran yang perlu ditindak dihitung di bilah samping dan lonceng', function () {
    foreach (\App\Support\HitunganOrcha::semua() as $penanda) {
        $penanda::lupakan();
    }

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/pesan/perhatian' => Http::response(['data' => ['belum_dibaca' => 0, 'baru' => 0, 'lama' => 0]]),
        '*/pendaftaran/perhatian' => Http::response(['data' => ['baru' => 2, 'dihubungi' => 1, 'telat_lunas' => 3]]),
        '*/rujukan' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Tiga keadaan, tiga perbuatan yang berbeda — dan yang ketiga paling mahal
     | dibiarkan: kursinya tertahan atas nama orang yang belum tentu berangkat,
     | dan makin dekat hari-H makin sulit dijual ulang.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)
        // Bilah samping: satu menu, satu angka, pemecahannya di judul tempel
        ->toContain('2 pendaftaran baru · 1 sudah dihubungi, belum bayar · 3 lewat tenggat pelunasan')
        // Lonceng: tiap keadaan barisnya sendiri, dengan ajakan masing-masing
        ->toContain('2 pendaftaran open trip baru')
        ->toContain('Hubungi pemesannya')
        ->toContain('1 sudah dihubungi, belum bayar')
        ->toContain('Tagih uang mukanya')
        ->toContain('3 pendaftaran lewat tenggat pelunasan')
        ->toContain('Tagih pelunasannya')
        // Tautannya membuka daftar yang SUDAH tersaring
        ->toContain('filterStatus=dp_masuk')
        // Lencana emasnya menjumlah seluruhnya
        ->toContain('6 hal di Orcha menunggu ditindak');
});

test('pendaftaran yang tenang tidak menumbuhkan penanda apa pun', function () {
    foreach (\App\Support\HitunganOrcha::semua() as $penanda) {
        $penanda::lupakan();
    }

    Http::fake([
        '*/pembayaran/menunggu' => Http::response(['data' => ['jumlah' => 0, 'nominal' => 0]]),
        '*/penyewaan/perhatian' => Http::response(['data' => ['baru' => 0, 'telat' => 0, 'denda' => 0]]),
        '*/pembatalan/perhatian' => Http::response(['data' => ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0]]),
        '*/pesan/perhatian' => Http::response(['data' => ['belum_dibaca' => 0, 'baru' => 0, 'lama' => 0]]),
        '*/pendaftaran/perhatian' => Http::response(['data' => ['baru' => 0, 'dihubungi' => 0, 'telat_lunas' => 0]]),
        '*/rujukan' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Yang lunas dan yang batal sudah selesai — keduanya tidak menuntut apa pun.
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->not->toContain('pendaftaran open trip baru')
        ->and($isi)->not->toContain('lewat tenggat pelunasan');
});

test('kolom aksi tetap sejajar walau riwayat kesehatannya belum ada', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pendaftaran' => ['baru' => 'Baru']]]),
        '*' => Http::response(['data' => [[
            'id' => 9, 'kode' => 'OT-1508-0VCZ', 'nama' => 'joko', 'whatsapp' => '0812',
            'email' => null, 'jumlah_peserta' => 4,
            'peserta' => [['nama' => 'joko', 'titik_jemput' => 'Jogja']],
            'jemput_per_titik' => ['Jogja' => ['joko']],
            'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => ['joko'],
            'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
            'tanggal_berangkat' => '2026-10-19', 'titik_jemput' => 'Jogja',
            'catatan' => null, 'status' => 'baru', 'status_label' => 'Baru',
            'jumlah_riwayat_kesehatan' => 0, 'dibuat_pada' => now()->toIso8601String(),
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    /*
     | Sebaris tombol yang salah satu selnya berisi teks polos membuat kolom
     | Aksi terlihat bolong dan barisnya tidak lagi sejajar.
     |
     | Sengaja tidak dibuat bisa ditekan: halamannya memang tidak punya apa-apa
     | untuk ditampilkan, dan tombol yang membuka halaman kosong lebih
     | mengecewakan daripada tombol yang jelas mati.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->get('/admin/orcha/pendaftaran')->assertOk()->getContent());

    expect($isi)->toContain('orcha-aksi-mati')
        ->toContain('Belum ada riwayat')
        // Bukan lagi tulisan telanjang
        ->not->toContain('<span class="text-muted" style="font-size:.76rem">Belum ada riwayat</span>')
        // Dan bukan tautan yang membuka halaman kosong
        ->not->toMatch('/<a[^>]*orcha-aksi-mati/');

    /*
     | Keduanya selebar sama.
     |
     | Terukur di peramban sebelum diperbaiki: "Belum ada riwayat" 147px,
     | "Riwayat (2)" 106px. Karena keduanya berdampingan dengan tombol Detail
     | dan dirapatkan ke kanan, selisih 41px itu MENGGESER seluruh pasangannya
     | — barisnya tidak lagi sejajar dengan baris di atas dan di bawahnya.
     | Sesudah: keduanya 148px, mulai di titik yang sama.
     */
    expect($isi)->toContain('orcha-aksi-riwayat');

    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    preg_match('/\.orcha-aksi-riwayat\s*\{([^}]*)\}/', $gaya, $aturan);

    expect($aturan[1] ?? '')->toContain('min-width: 9.25rem');
});

test('kartu pembatalan tidak menyodorkan rekening saat tidak ada yang dikembalikan', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pendaftaran' => ['batal' => 'Batal']]]),
        '*' => Http::response(balasanPendaftaranBatal(0)),
    ]);

    /*
     | Dulu kartunya SELALU menulis "Rekening pengembalian: mandiri · 12345678".
     | Pada pengajuan yang potongannya sebesar seluruh pembayaran — kembali
     | Rp 0 — kalimat itu terbaca sebagai perintah mentransfer ke sana, padahal
     | di fitur Pembatalan memang tidak ada yang dikembalikan. Admin yang awam
     | mengerjakannya, dan uangnya benar-benar berangkat.
     |
     | Yang menentukan ANGKANYA, bukan ada tidaknya rekening.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/8')->assertOk()->getContent());

    expect($isi)->toContain('Tidak ada dana yang dikembalikan')
        ->toContain('tidak ada yang perlu ditransfer');

    /*
     | Nomor rekeningnya benar-benar tidak DIGAMBAR.
     |
     | Diperiksa setelah keadaan komponen yang diserialkan Livewire dibuang:
     | wire:snapshot memuat seluruh isi properti apa adanya, termasuk yang
     | sengaja tidak ditampilkan. Ia tidak terlihat admin dan tidak menambah
     | siapa pun yang bisa membacanya — yang diuji di sini apa yang tergambar
     | di layar.
     */
    expect(tanpaSnapshot($isi))->not->toContain('12345678');
});

test('kartu pembatalan menyebut rekening hanya saat memang ada yang dikirim', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pendaftaran' => ['batal' => 'Batal']]]),
        '*' => Http::response(balasanPendaftaranBatal(400000)),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/8')->assertOk()->getContent());

    expect($isi)->toContain('Perlu ditransfer ke pemohon')
        ->toContain('Rp 400.000')
        ->toContain('12345678')
        ->not->toContain('Tidak ada dana yang dikembalikan');
});

test('dana yang sudah dikirim tidak disuruh ditransfer lagi', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pendaftaran' => ['batal' => 'Batal']]]),
        '*' => Http::response(balasanPendaftaranBatal(400000, 'dana_dikirim')),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/8')->assertOk()->getContent());

    expect($isi)->toContain('Dana sudah ditandai terkirim')
        ->toContain('Tidak perlu ditransfer lagi')
        ->not->toContain('Perlu ditransfer ke pemohon');
});

test('bilah perkakas detail pendaftaran seukuran pemilih statusnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['status_pendaftaran' => ['batal' => 'Batal']]]),
        '*' => Http::response(balasanPendaftaranBatal(0)),
    ]);

    /*
     | Terukur di peramban: keempat tombol dan pemilih statusnya kini 34px.
     | Satu bilah perkakas yang tombolnya berbeda-beda tinggi terbaca seperti
     | kumpulan tombol yang kebetulan bersebelahan.
     */
    // Manifes PDF dan Excel memuat data kesehatan, jadi keduanya hanya tergambar
    // untuk akun yang berizin — dan itulah tampilan yang dilihat admin Orcha.
    $isi = tanpaGaya($this->actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->get('/admin/orcha/pendaftaran/8')->assertOk()->getContent());

    // Empat tombol: WA, Kwitansi, Manifes PDF, Excel
    expect(substr_count($isi, 'orcha-aksi-sewa'))->toBeGreaterThanOrEqual(4);

    // Pemilih statusnya memakai ukuran ringkas yang sama dengan sewa kendaraan
    expect($isi)->toContain('form-select-sm orcha-pilih-status');
});

test('status tetap terbaca walau daftar pilihannya belum sampai dari orcha', function () {
    // Rujukan kosong: persis keadaan saat Orcha sedang tidak bisa dihubungi.
    Http::fake([
        '*/rujukan' => Http::response(['data' => []]),
        '*' => Http::response(['data' => [[
            'id' => 7, 'kode' => 'OT-1608-ZT8K', 'nama' => 'sofyan', 'whatsapp' => '0895',
            'email' => null, 'jumlah_peserta' => 1,
            'peserta' => [['nama' => 'sofyan', 'titik_jemput' => 'Jogja']],
            'jemput_per_titik' => ['Jogja' => ['sofyan']],
            'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false,
            'peserta_belum_isi' => ['sofyan'],
            'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
            'tanggal_berangkat' => '2026-10-19', 'titik_jemput' => 'Jogja',
            'catatan' => null, 'status' => 'baru', 'status_label' => 'Baru',
            'jumlah_riwayat_kesehatan' => 0, 'dibuat_pada' => now()->toIso8601String(),
        ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    /*
     | Kotak pilih tanpa satu pun pilihan tergambar sebagai pil berwarna yang
     | benar-benar KOSONG: statusnya lenyap dari layar tanpa satu pun kalimat
     | yang menjelaskan sebabnya, dan admin membacanya sebagai data yang hilang.
     |
     | Statusnya sendiri selalu ada — ia datang bersama barisnya, bukan dari
     | rujukan. Jadi yang benar: tetap tunjukkan statusnya, dan katakan bahwa
     | yang belum bisa dilakukan hanya mengubahnya.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran')->assertOk()->getContent());

    expect($isi)->toContain('orcha-status-diam')
        ->toContain('Baru')
        ->toContain('belum bisa diubah dari sini')
        // Kotak pilih kosongnya tidak digambar sama sekali
        ->not->toContain('orcha-pilih-status');
});

test('ukuran pemilih status pendaftaran disamakan dengan sewa kendaraan', function () {
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    /*
     | Sebelumnya yang di pendaftaran berbentuk pil 999px setinggi bawaan
     | <select>, sementara yang di sewa kendaraan kotak membulat 34px — dua
     | layar yang dikerjakan berurutan dengan kendali yang sama gunanya.
     */
    preg_match('/\.orcha-status-ringkas,\s*\.orcha-pilih-status\s*\{([^}]*)\}/', $gaya, $ukuran);

    expect($ukuran[1] ?? '')->toContain('height: 34px')
        ->toContain('border-radius: 9px')
        ->toContain('min-width: 8.5rem');

    // Bentuk pil lamanya benar-benar ditinggalkan
    expect($gaya)->not->toMatch('/\.orcha-pilih-status\s*\{[^}]*999px/');

    /*
     | WARNANYA tetap terpisah: status pendaftaran (baru, dihubungi, dp masuk,
     | lunas, batal) dan status penyewaan (baru, dikonfirmasi, dp masuk,
     | berjalan, selesai, batal) bukan daftar yang sama, dan menyatukannya
     | membuat satu status kehilangan warnanya diam-diam begitu daftar yang
     | lain berubah.
     */
    expect($gaya)->toContain('.orcha-pilih-status.status-dihubungi')
        ->toContain('.orcha-status-ringkas[data-status="berjalan"]');
});

test('gambar partner diperlakukan sama dengan testimoni, bukan varian sendiri', function () {
    Http::fake(['*' => Http::response([
        'data' => [['id' => 1, 'nama' => 'Cahaya Travel', 'logo' => '/storage/partner/a.png']],
        'meta' => [],
    ])]);

    /*
     | Partner dulu punya varian .logo sendiri: gambarnya dimuat UTUH di atas
     | latar putih, supaya logo lebar tidak kehilangan tulisannya. Alasannya
     | masih benar untuk logo — tetapi yang diunggah ke sini pada praktiknya
     | poster dan foto, dan yang utuh di antara yang terpotong membuat sebaris
     | kartu terlihat tidak sejenis.
     |
     | Terukur di peramban sesudah disamakan: nisbah 16/10, tinggi 293px,
     | object-fit cover, tanpa jarak dalam — sama persis dengan testimoni.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'partner'])
        ->html());

    expect($isi)->toContain('orcha-etalase-foto')
        ->not->toContain('orcha-etalase-foto logo');

    // Aturannya ikut dihapus, bukan ditinggalkan menganggur: gaya yang tidak
    // menempel pada apa pun akan dipakai ulang keliru oleh yang berikutnya.
    $sumber = file_get_contents(resource_path('views/livewire/pages/admin/orcha/etalase/index.blade.php'));

    expect($sumber)->not->toContain('.orcha-etalase-foto.logo {')
        ->not->toContain('.orcha-etalase-foto.logo img');
});

test('kartu partner tidak menjanjikan keterangan yang tidak punya tempat diisi', function () {
    Http::fake(['*' => Http::response(['data' => [
        ['id' => 1, 'nama' => 'Bakso Urat Pak Shomad', 'logo' => null],
    ], 'meta' => []])]);

    /*
     | Partner TIDAK punya keterangan di mana pun: tidak di tabelnya (hanya
     | nama dan foto), tidak di balasan API-nya, dan tidak di formulirnya.
     |
     | Kartunya dulu ikut membaca 'deskripsi' yang tidak pernah ada, sehingga
     | TIAP partner selamanya bertuliskan "Keterangan belum ditulis." —
     | mengumumkan kekurangan yang tidak punya tempat untuk diperbaiki, dan
     | admin mencari-cari isian yang memang tidak pernah dibuat.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'partner'])
        ->html());

    expect($isi)->toContain('Bakso Urat Pak Shomad')
        ->not->toContain('Keterangan belum ditulis');
});

test('testimoni tanpa isi tetap ditandai, karena di sana isian itu memang ada', function () {
    Http::fake(['*' => Http::response(['data' => [
        ['id' => 1, 'nama' => 'Siti', 'rating' => 5, 'isi' => '', 'foto' => null],
    ], 'meta' => []])]);

    /*
     | Berkas ujinya sendiri, bukan menyambung uji partner di atas: Http::fake()
     | yang dipanggil dua kali dalam satu uji TIDAK menimpa yang pertama —
     | render keduanya akan menerima balasan partner dan tersandung kunci yang
     | tidak ada.
     |
     | Testimoni memang punya isian "Isi testimoni" di formulirnya, jadi
     | kalimat penandanya di sini benar-benar berarti: ada yang belum ditulis,
     | dan tempatnya ada.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'testimoni'])
        ->html());

    expect($isi)->toContain('Keterangan belum ditulis');
});

test('unggahan gambar partner juga memintal di pratinjau dan tombol simpan', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    // Jendelanya sama dengan testimoni — satu berkas, dipakai keduanya —
    // jadi yang diuji di sini bahwa jalur partner benar-benar melewatinya.
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'partner'])
        ->call('tambah')
        ->html());

    expect($isi)->toContain('orcha-foto-naik')
        ->toContain('wire:target="simpan,gambar"')
        ->toContain('Mengunggah gambar…');
});

test('unggahan gambar testimoni memintal di pratinjau dan di tombol simpan', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    /*
     | Unggahan gambar 4 MB lewat sambungan lambat bisa memakan belasan detik.
     | Selama itu kotak pratinjau diam memperlihatkan gambar LAMA — atau kotak
     | kosong — dan yang memilih berkas menyangka pilihannya tidak masuk lalu
     | memilih ulang, membuang unggahan yang sedang jalan dari awal.
     |
     | Tombol simpannya ikut menunggu: menekannya selagi gambarnya masih naik
     | mengirim testimoni TANPA foto, dan admin baru sadar setelah kartunya
     | tampil kosong.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'testimoni'])
        ->call('tambah')
        ->html());

    // Pratinjaunya ditumpangi pemintal, menyasar `gambar` secara khusus supaya
    // ia tidak ikut memintal saat testimoninya sedang disimpan
    expect($isi)->toContain('orcha-foto-naik')
        ->toMatch('/wire:loading wire:target="gambar"[^>]*orcha-foto-naik|orcha-foto-naik[^>]*wire:target="gambar"/');

    // Tombolnya mati dan memintal untuk KEDUA hal
    expect($isi)->toContain('wire:target="simpan,gambar"')
        ->toContain('Mengunggah gambar…')
        ->toContain('Menyimpan…');
});

test('bungkus pemintal di tombol besar tetap diam sebelum ditekan', function () {
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    /*
     | Penjagaan untuk regresi yang pernah terjadi: aturan `.orcha-btn-besar >
     | span` dipasang untuk memaku tinggi tombol, dan karena ia menyebut kelas
     | DAN elemen, ia mengalahkan penyembunyi [wire:loading] di lembar yang
     | sama. Akibatnya tiap tombol besar menggambar "Simpan Menyimpan…"
     | berjajar sebelum disentuh — terbaca sebagai halaman yang sedang bekerja
     | padahal diam.
     |
     | Dikembalikan dengan pemilih yang lebih kuat, BUKAN dengan !important:
     | Livewire menampilkannya lewat gaya sebaris, dan gaya sebaris kalah oleh
     | !important — pemintalnya justru tidak akan pernah muncul.
     */
    expect($gaya)->toContain('.orcha-btn-besar > span[wire\\:loading]')
        ->and($gaya)->toMatch('/\.orcha-btn-besar > span\[wire\\\\:loading\]\s*\{\s*display: none;\s*\}/');

    // Aturan pemakunya harus datang LEBIH DULU, kalau tidak yang menang
    // justru penyembunyinya dan tombolnya tidak pernah memintal
    expect(strpos($gaya, '.orcha-btn-besar > span {'))
        ->toBeLessThan(strpos($gaya, '.orcha-btn-besar > span[wire\\:loading]'));

    // Penutup pemintal pratinjau TIDAK boleh menyentuh display sama sekali —
    // aturan display di sana akan mengalahkan penyembunyi yang sama.
    preg_match('/\.orcha-foto-naik\s*\{([^}]*)\}/', $gaya, $penutup);

    expect($penutup[1] ?? '')->not->toContain('display:')
        ->and($penutup[1] ?? '')->toContain('position: absolute');
});

test('tombol di modal etalase memakai keluarga tombol orcha, bukan bootstrap polos', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    /*
     | Jendela ini BUKAN milik destinasi — tambah() untuk destinasi mengalihkan
     | ke halaman formulirnya sendiri karena isiannya terlalu panjang untuk
     | sebuah jendela. Yang memakainya testimoni, partner, dan galeri; tombolnya
     | disamakan sekalian supaya seluruh etalase terbaca satu keluarga.
     */
    $isi = tanpaGaya(Livewire::actingAs(adminOrcha())
        ->test(OrchaEtalaseList::class, ['jenis' => 'testimoni'])
        ->call('tambah')
        ->html());

    /*
     | Diperiksa pada kaki jendelanya saja, bukan seluruh halaman: tombol
     | "Tambah" di kepala daftar juga memakai kelas Bootstrap dan bukan bagian
     | dari yang diminta — penjagaan yang menyapu seluruh halaman akan ikut
     | menuntutnya berubah.
     */
    $kaki = substr($isi, strpos($isi, 'modal-footer'));

    expect($kaki)->toContain('orcha-btn orcha-btn-utama orcha-btn-besar')
        ->toContain('orcha-btn orcha-btn-lembut orcha-btn-besar')
        ->not->toContain('btn btn-primary rounded-3')
        ->not->toContain('btn orcha-bahaya')
        // Pemintalnya ikut
        ->toContain('spinner-border spinner-border-sm');
});

test('tombol simpan paket wisata berganti pemintal, dan diam sebelum ditekan', function () {
    Http::fake(['*' => Http::response(['data' => []])]);

    /*
     | Menyimpan paket menembak Orcha dengan seluruh isinya — jadwal, harga,
     | fasilitas, dan gambarnya — jadi jedanya terasa. Sebelumnya tombolnya
     | hanya berganti kalimat, dan tombol yang cuma berganti tulisan masih
     | terlihat diam: yang menekannya cenderung menekan lagi, dan paket yang
     | terkirim dua kali bukan perkara tampilan.
     */
    $isi = Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->html();

    expect($isi)->toContain('spinner-border spinner-border-sm')
        ->toContain('Menyimpan ke Orcha…')
        // Menyasar metodenya secara khusus, supaya tombol ini tidak ikut
        // memintal saat unggahan gambar sedang berjalan.
        ->toContain('wire:target="simpan"')
        // Kelas display Bootstrap memakai !important dan akan mengalahkan
        // aturan penyembunyi, jadi pembungkusnya tidak boleh memakainya.
        ->not->toContain('wire:loading wire:target="simpan" class="d-');

    // Dan tersembunyi sejak awal lewat gaya, bukan hanya lewat skrip Livewire —
    // kalau tidak, pemintalnya tergambar sebelum tombolnya ditekan.
    $halaman = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/paket-wisata/tambah')->assertOk()->getContent();

    expect($halaman)->toContain('[wire\\:loading] { display: none; }');
});

test('tombol simpan serah terima berganti pemintal, dan diam sebelum ditekan', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa()),
    ]);

    /*
     | Menyimpan lembar ini menembak Orcha dengan seluruh isinya — kilometer,
     | bahan bakar, hasil pemeriksaan tiap bagian, dan rincian dendanya — jadi
     | jedanya terasa. Tombol yang tidak berubah apa-apa selama itu membuat
     | admin mengira tekanannya tidak masuk lalu menekannya lagi, dan lembar
     | sepanjang ini terkirim dua kali.
     */
    $isi = Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->html();

    expect($isi)->toContain('spinner-border spinner-border-sm')
        ->toContain('Menyimpan...')
        // Menyasar metodenya secara khusus: tanpa itu, tombol ini ikut memintal
        // saat unggahan foto jaminan sedang berjalan.
        ->toContain('wire:target="simpanSerahTerima"')
        // Kelas display Bootstrap memakai !important dan akan mengalahkan
        // aturan penyembunyi, jadi pembungkusnya tidak boleh memakainya.
        ->not->toContain('wire:loading wire:target="simpanSerahTerima" class="d-');

    // Dan tersembunyi sejak awal lewat gaya, bukan hanya lewat skrip Livewire.
    $halaman = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12/serah-terima')->assertOk()->getContent();

    expect($halaman)->toContain('[wire\\:loading] { display: none; }');
});

test('tombol penutup lembar dibagi dua sama lebar, bukan sepasang tombol kecil', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa()),
    ]);

    // Menutup lembar tanpa menekan Simpan berarti dendanya tidak pernah
    // ditagihkan — jadi tombolnya tidak boleh terbaca sederajat dengan
    // perkakas kecil di tengah lembar.
    $isi = Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->html();

    $kaki = substr($isi, strpos($isi, 'card-footer'));

    expect(substr_count($kaki, 'orcha-tombol-lembar'))->toBe(2)
        ->and(substr_count($kaki, 'col-6'))->toBe(2)
        // Tidak lagi didorong ke kanan sebagai sepasang tombol kecil
        ->and($kaki)->not->toContain('justify-content-end');
});

test('lembar serah terima terbagi jadi bagian, bukan satu kartu panjang', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa()),
    ]);

    /*
     | Sebagai satu kartu datar, waktu, kilometer, pemeriksaan bodi, dan uang
     | terbaca sederajat — dan admin kehilangan tempat saat menggulung.
     |
     | Keadaan unit yang paling penting: keenam isiannya dulu berderet
     | menyamping (waktu, waktu, km awal, km akhir, BBM awal, BBM akhir),
     | sehingga pasangan "saat diserahkan" dan "saat kembali" terputus dan yang
     | sejajar di layar justru dua hal yang tidak dibandingkan.
     */
    $isi = Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Keadaan Unit')
        ->assertSee('Jaminan Penyewa')
        ->assertSee('Pemeriksaan Fisik')
        ->assertSee('Denda &amp; Tagihan', false)
        ->html();

    // Dua kolom kembar, bentuknya sama dengan tabel pemeriksaan di bawahnya
    expect($isi)
        ->toContain('orcha-keadaan')
        ->toContain('orcha-keadaan kembali')
        ->toContain('Saat diserahkan')
        ->toContain('Saat kembali')
        // Bukan lagi label yang menyebut awal/akhir berderet
        ->not->toContain('BBM saat diserahkan')
        ->not->toContain('Kilometer awal');
});

test('jarak tempuh dihitungkan, bukan dikurangkan admin di kepala', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa(['kilometer_awal' => 84120, 'kilometer_akhir' => 85340])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('1.220 km ditempuh');
});

test('kilometer akhir yang belum masuk akal tidak memunculkan jarak', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa(['kilometer_awal' => 84120, 'kilometer_akhir' => null])),
    ]);

    // Unit yang baru diserahkan belum punya kilometer akhir; angka jarak yang
    // muncul dari pengurangan setengah data hanya menyesatkan.
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertDontSee('km ditempuh');
});

test('lembar serah terima merinci asal biaya sewanya, bukan satu angka', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 3300000,
            'rincian_estimasi' => [
                ['label' => 'Tarif luar kota', 'keterangan' => 'Rp 900.000 × 3 hari', 'jumlah' => 2700000],
                ['label' => 'Sopir', 'keterangan' => 'Rp 200.000 × 3 hari', 'jumlah' => 600000],
            ],
        ])),
    ]);

    // Dendanya sudah dirinci sejak awal — bagian mana, berapa, kenapa — tetapi
    // biaya sewanya sendiri cuma satu bilangan. Padahal saat menagih keduanya
    // ditanya dengan pertanyaan yang sama, dan admin yang membacakan lembar ini
    // hanya punya jawaban untuk separuhnya.
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Rincian tagihan')
        ->assertSee('Tarif luar kota')
        ->assertSee('Rp 900.000 × 3 hari')
        ->assertSee('Rp 2.700.000')
        ->assertSee('Sopir')
        ->assertSee('Rp 600.000');
});

test('dp yang sudah diterima dikurangkan dari tagihannya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 300000,
            'denda_keterlambatan' => 1200000, 'denda_kerusakan' => 650000,
            'total_denda' => 1850000, 'total_tagihan' => 2150000,
            'tagihan' => ['total' => 2150000, 'sudah' => 90000, 'sisa' => 2060000, 'lunas' => false],
            'menunggu_dicek' => ['nominal' => 0, 'berkas' => 0],
        ])),
    ]);

    /*
     | Tanpa pengurangan ini rinciannya berhenti di total, seolah penyewa belum
     | membayar sepeser pun — padahal DP-nya sudah diterima. Admin yang
     | membacakan angka itu saat menagih akan menagih lebih dari yang
     | seharusnya.
     */
    $lembar = Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12]);

    $lembar->assertSee('Sudah dibayar')
        ->assertSee('Rp 90.000')
        ->assertSee('Sisa yang harus dibayar')
        ->assertSee('Rp 2.060.000');

    // Kartu Posisi Pembayaran di atas menyebut angka yang sama persis. Dulu
    // keduanya menghitung sendiri-sendiri dari sumber berbeda, dan hasilnya dua
    // angka untuk satu tagihan yang sama di layar yang sama.
    expect(substr_count(tanpaGaya($lembar->html()), 'Rp 2.060.000'))->toBe(2);
});

test('pembayaran dirinci per jenis: uang muka dan pelunasan disebut namanya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 3000000,
            'terlambat' => false, 'terlambat_menit' => 0, 'denda_keterlambatan_usulan' => 0,
            'total_denda' => 0, 'total_tagihan' => 3000000,
            'tagihan' => ['total' => 3000000, 'sudah' => 2100000, 'sisa' => 900000, 'lunas' => false],
            'menunggu_dicek' => ['nominal' => 0, 'berkas' => 0],
            'pembayaran_diterima' => [
                ['jenis' => 'dp', 'label' => 'Uang Muka (DP)', 'nominal' => 900000, 'berkas' => 1],
                ['jenis' => 'pelunasan', 'label' => 'Pelunasan', 'nominal' => 1200000, 'berkas' => 2],
            ],
        ])),
    ]);

    // Satu baris "sudah dibayar" menjawab berapa, tetapi tidak menjawab yang
    // ditanyakan berikutnya — itu DP atau pelunasan — dan jawabannya menentukan
    // kalimat yang dipakai admin saat menagih.
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Uang Muka (DP)')
        ->assertSee('Rp 900.000')
        ->assertSee('Pelunasan')
        ->assertSee('Rp 1.200.000')
        // Yang terkirim lebih dari sekali disebut berapa buktinya
        ->assertSee('2 bukti transfer')
        ->assertSee('Sisa yang harus dibayar');
});

test('sewa lama tanpa rincian jenis tetap dapat satu baris gabungan', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 3000000,
            'terlambat' => false, 'terlambat_menit' => 0, 'denda_keterlambatan_usulan' => 0,
            'total_denda' => 0, 'total_tagihan' => 3000000,
            'tagihan' => ['total' => 3000000, 'sudah' => 900000, 'sisa' => 2100000, 'lunas' => false],
            'menunggu_dicek' => ['nominal' => 0, 'berkas' => 0],
            'pembayaran_diterima' => [],
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Sudah dibayar')
        ->assertSee('Rp 900.000');
});

test('sewa yang belum dibayar sama sekali tidak menampilkan baris pengurang', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 300000,
            // Usulan dimatikan: lembar mengisi isian dendanya dari usulan saat
            // dibuka, dan angka itu ikut terhitung ke total di layar.
            'terlambat' => false, 'terlambat_menit' => 0, 'denda_keterlambatan_usulan' => 0, 'total_denda' => 0, 'total_tagihan' => 300000,
            'tagihan' => ['total' => 300000, 'sudah' => 0, 'sisa' => 300000, 'lunas' => false],
            'menunggu_dicek' => ['nominal' => 0, 'berkas' => 0],
        ])),
    ]);

    // Baris "Sudah dibayar Rp 0" hanya menambah panjang tanpa menambah kabar.
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertDontSee('Sudah dibayar')
        ->assertSee('Total tagihan');
});

test('tagihan yang sudah tertutup penuh disebut lunas, bukan sisa nol', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 300000,
            // Usulan dimatikan: lembar mengisi isian dendanya dari usulan saat
            // dibuka, dan angka itu ikut terhitung ke total di layar.
            'terlambat' => false, 'terlambat_menit' => 0, 'denda_keterlambatan_usulan' => 0, 'total_denda' => 0, 'total_tagihan' => 300000,
            'tagihan' => ['total' => 300000, 'sudah' => 300000, 'sisa' => 0, 'lunas' => true],
            'menunggu_dicek' => ['nominal' => 0, 'berkas' => 0],
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Sudah dibayar')
        ->assertSee('Lunas')
        ->assertDontSee('Sisa yang harus dibayar');
});

test('rincian tagihan menyatukan biaya sewa dengan tiap dendanya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 300000,
            'terlambat' => true, 'terlambat_menit' => 4658,
            'denda_keterlambatan' => 1200000, 'denda_kerusakan' => 650000,
            'total_denda' => 1850000, 'total_tagihan' => 2150000,
            'rincian_estimasi' => [
                ['label' => 'Tarif sewa', 'keterangan' => 'Rp 300.000 × 1 hari', 'jumlah' => 300000],
            ],
            'rincian_denda_kerusakan' => [
                ['kunci' => 'bodi_kiri', 'bagian' => 'Bodi samping kiri', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 650000],
            ],
        ])),
    ]);

    /*
     | Angka besarnya dulu berdiri sendiri dengan keterangan "Biaya sewa +
     | denda" di pojok — benar, tetapi tidak menjawab apa pun. Admin yang
     | ditanya penyewa "kok segitu?" harus membuka tiga kotak berbeda di layar
     | untuk menyusun jawabannya.
     */
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Rincian tagihan')
        // Biaya sewanya, baris demi baris
        ->assertSee('Tarif sewa')
        ->assertSee('Rp 300.000 × 1 hari')
        // Lalu tiap dendanya, dengan sebabnya
        ->assertSee('Denda keterlambatan')
        ->assertSee('77 jam 38 menit lewat tenggat')
        ->assertSee('Denda kerusakan')
        ->assertSee('Bodi samping kiri')
        // Ditutup satu total
        ->assertSee('Total tagihan')
        ->assertSee('Rp 2.150.000');

    // Denda yang nol tidak ikut jadi baris: daftar penuh "Rp 0" membuat yang
    // benar-benar ditagih jadi sulit ditemukan. Diperiksa di dalam tabel
    // rinciannya saja — "Denda lain" juga nama isian di atasnya.
    $isi = Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])->html();

    $tabel = substr($isi, strpos($isi, 'Rincian tagihan'));
    $tabel = substr($tabel, 0, strpos($tabel, '</table>'));

    expect($tabel)->not->toContain('Denda lain')
        ->and($tabel)->not->toContain('Rp 0');
});

test('sewa lama tanpa perincian tetap merinci tagihannya sebisanya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa(['rincian_estimasi' => []])),
    ]);

    // Pesanan yang dibuat sebelum perinciannya disimpan tetap punya totalnya —
    // yang tampil satu baris "Biaya sewa" seperti sedia kala, bukan daftar
    // kosong dan bukan angka besar tanpa penjelasan apa pun.
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Rincian tagihan')
        ->assertSee('Biaya sewa')
        ->assertSee('Total tagihan penyewa');
});

test('rincian yang sudah ditetapkan tetap tampil walau usulannya sudah habis', function () {
    // Sesudah unit diperiksa, keadaan barunya jadi patokan dan selisihnya nol,
    // jadi Orcha tidak lagi mengusulkan apa pun. Dendanya sendiri sudah
    // ditagihkan — kalau baris-barisnya ikut hilang, admin melihat angka tanpa
    // alasan, dan alasan itulah yang ditanya penyewa.
    $baris = balasanSewa([
        'rincian_denda_kerusakan' => [],
        // Unitnya kembali tepat waktu, supaya yang diuji hanya soal kerusakan
        'terlambat' => false, 'terlambat_menit' => 0, 'denda_keterlambatan_usulan' => 0,
        'denda_kerusakan' => 450000, 'total_denda' => 450000, 'total_tagihan' => 950000,
        'rincian_denda' => [
            ['bagian' => 'Bodi depan & bemper', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 250000],
            ['bagian' => 'Bodi samping kiri', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 200000],
        ],
    ]);

    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(['data' => $baris['data'][0]]),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('Bodi depan & bemper')
        ->assertSee('Bodi samping kiri')
        // Disebut apa adanya: ini ketetapan yang sudah ditagihkan, bukan usulan
        ->assertSee('Denda kerusakan yang sudah ditetapkan')
        ->assertDontSee('Usulan denda kerusakan')
        // Dan tidak dituduh belum tersimpan, karena memang sudah
        ->assertDontSee('Angka di bawah ini belum tersimpan')
        ->assertSet('biayaKerusakan.bodi_depan_bemper', '250.000');
});

test('rincian denda yang ditetapkan ikut terkirim saat serah terima disimpan', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*/serah-terima' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*' => Http::response(satuSewa(['rincian_denda_kerusakan' => [
            ['kunci' => 'bodi_depan', 'bagian' => 'Bodi depan & bemper',
                'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 250000],
        ]])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        // Nota bengkelnya ternyata lebih mahal dari daftar tarif
        ->set('biayaKerusakan.bodi_depan', '400.000')
        ->call('simpanSerahTerima');

    // Yang disimpan angka di layar, bukan tarif usulannya — kalau berbeda,
    // rincian yang ditunjukkan ke penyewa tidak cocok dengan yang ditagih.
    Http::assertSent(fn ($permintaan) => ! str_contains($permintaan->url(), '/serah-terima')
        || ($permintaan['rincian_denda'][0]['biaya'] === 400000
            && $permintaan['rincian_denda'][0]['bagian'] === 'Bodi depan & bemper'));
});

test('tombol kwitansi tersedia tanpa izin data kesehatan', function () {
    Http::fake(['*/pendaftaran/7' => Http::response(balasanDetail())]);

    // Justru inilah yang perlu cepat dikirim ulang saat pelanggan mengeluh
    // suratnya tidak masuk — jadi tidak dikunci di balik izin data medis.
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7')
        ->assertOk()
        ->assertSee('Kwitansi');
});

test('detail penyewaan menampilkan jadwal, kerusakan, dan tombol nota', function () {
    Http::fake(['*/penyewaan/12' => Http::response(['data' => balasanSewa([
        'dikembalikan_pada' => '2026-09-11T11:00:00+07:00',
        'denda_keterlambatan' => 150000, 'denda_kerusakan' => 900000,
        'total_denda' => 1050000, 'total_tagihan' => 1550000,
        'kondisi_awal' => ['bodi_depan' => 'lecet', 'kaca' => 'baik'],
        'kondisi_akhir' => ['bodi_depan' => 'lecet', 'kaca' => 'rusak'],
        'kerusakan_baru' => [['bagian' => 'Kaca & spion', 'dari' => 'Baik', 'jadi' => 'Rusak']],
        'catatan_denda' => 'Spion kanan retak',
    ])['data'][0]]),
        '*/rujukan' => Http::response(rujukanSewa()),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')
        ->assertOk()
        ->assertSee('SK-1608-B2M9')
        ->assertSee('Kerusakan baru selama masa sewa')
        ->assertSee('Kaca &amp; spion', false)
        // Nota akhir bisa diunduh langsung dari halaman ini
        ->assertSee('Nota Akhir')
        ->assertSee('Spion kanan retak')
        ->assertSee('Rp 1.550.000');
});

test('tombol wa detail sewa membuka pilihan pesan lewat api.whatsapp.com', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'konfirmasi_pembayaran_tautan' => 'https://orchajourney.com/konfirmasi-pembayaran?kode=SK-1608-B2M9',
            'tagihan' => ['total' => 3300000, 'sudah' => 990000, 'sisa' => 2310000, 'lunas' => false],
        ])),
    ]);

    // Lewat wa.me, emoji dan sebagian tanda baca sering tidak terbaca di
    // WhatsApp Web maupun Desktop — pesan sampai dalam keadaan rusak, dan admin
    // tidak pernah tahu karena di layarnya sendiri tampak baik-baik saja.
    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent();

    $badan = tanpaGaya($isi);

    expect($badan)->toContain('api.whatsapp.com/send?phone=')
        ->toContain("orchaBukaLembar('pilihanWa')")
        // Emoji dikirim sebagai penanda titik kode, dirakit di peramban
        ->toContain('[[E:1F44B]]')
        // Tagihannya membawa tautan kirim bukti dan sisanya, bukan totalnya
        ->toContain('Tagih sisa pembayaran')
        ->toContain('konfirmasi-pembayaran')
        // href cadangan memuat versi polos: kalimat utuh tanpa penanda
        ->toContain('Halo Kak Budi')
        // Tidak ada satu tautan pun yang tersisa memakai wa.me
        ->not->toContain('wa.me');
});

test('pesan sewa mengikuti keadaan pesanannya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            // Sudah lunas, sudah kembali, tanpa denda
            'tagihan' => ['total' => 3300000, 'sudah' => 3300000, 'sisa' => 0, 'lunas' => true],
            'diserahkan_pada' => '2026-08-26T08:00:00+07:00',
            'dikembalikan_pada' => '2026-08-31T11:00:00+07:00',
            'total_denda' => 0,
        ])),
    ]);

    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent();

    $badan = tanpaGaya($isi);

    // Menagih orang yang sudah lunas adalah cara tercepat kehilangan
    // kepercayaannya; mengingatkan pengembalian unit yang sudah kembali hanya
    // membingungkan.
    expect($badan)->not->toContain('Tagih pembayaran')
        ->not->toContain('Tagih sisa pembayaran')
        ->not->toContain('Ingatkan jadwal pengembalian')
        ->not->toContain('Kirim tagihan denda')
        ->toContain('Ucapkan terima kasih');
});

test('unit yang masih di tangan penyewa bisa diingatkan, dan dendanya ditagih', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'tagihan' => ['total' => 3300000, 'sudah' => 3300000, 'sisa' => 0, 'lunas' => true],
            'diserahkan_pada' => '2026-08-26T08:00:00+07:00',
            'dikembalikan_pada' => null,
        ])),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()
        ->assertSee('Ingatkan jadwal pengembalian')
        // Aturan dendanya diambil dari rujukan Orcha, bukan ditulis ulang
        ->assertSee('30 menit')
        ->assertSee('10% tarif harian per jam');
});

test('kerusakan baru ditampilkan per bagian, bukan tiga kalimat berderet', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'dikembalikan_pada' => '2026-08-20T14:38:00+07:00',
            'kerusakan_baru' => [
                ['bagian' => 'Bodi samping kiri', 'dari' => 'Baik', 'jadi' => 'Lecet / minor'],
                ['bagian' => 'Bodi depan & bemper', 'dari' => 'Baik', 'jadi' => 'Lecet / minor'],
                ['bagian' => 'Bodi samping kanan', 'dari' => 'Baik', 'jadi' => 'Rusak'],
            ],
        ])),
    ]);

    /*
     | Sebelumnya tiga kalimat berderet — "Bodi samping kiri : baik → lecet /
     | minor" — yang harus dibaca satu per satu sampai habis. Nama bagian,
     | kondisi awal, dan kondisi akhir bercampur dalam satu baris tanpa kolom,
     | jadi mata tidak bisa menyusurinya ke bawah.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent());

    expect($isi)
        // Jumlahnya disebut di kepala, jadi tidak perlu dihitung sendiri
        ->toContain('3 bagian')
        // Tiap bagian punya barisnya sendiri
        ->and(substr_count($isi, 'orcha-rusak-baris'))->toBe(3)
        // Kondisi awal dan akhir jadi dua lencana, bukan kalimat
        ->and($isi)->toContain('orcha-cip-kondisi awal')
        ->and($isi)->toContain('orcha-cip-kondisi akhir');
});

test('kerusakan lama tanpa catatan perubahan tetap disebut namanya', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'kerusakan_baru' => [],
            // Ketetapan lama belum tentu menyimpan perubahan kondisinya
            'rincian_denda' => [['bagian' => 'Kaca & spion', 'biaya' => 650000]],
            'total_denda' => 650000,
        ])),
    ]);

    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent());

    expect($isi)->toContain('Kaca &amp; spion')
        // Tanpa lencana kondisi, karena memang tidak ada yang bisa disebut
        ->not->toContain('orcha-cip-kondisi awal');
});

test('detail sewa merinci tagihannya dan mengurangi yang sudah dibayar', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'estimasi_biaya' => 300000,
            'terlambat' => true, 'terlambat_menit' => 4658,
            'denda_keterlambatan' => 1200000, 'denda_kerusakan' => 650000,
            'total_denda' => 1850000, 'total_tagihan' => 2150000,
            'dikembalikan_pada' => '2026-08-20T14:38:00+07:00',
            'rincian_estimasi' => [
                ['label' => 'Tarif sewa', 'keterangan' => 'Rp 300.000 × 1 hari', 'jumlah' => 300000],
            ],
            'kerusakan_baru' => [['bagian' => 'Bodi samping kiri', 'dari' => 'Baik', 'jadi' => 'Lecet / minor']],
            'tagihan' => ['total' => 2150000, 'sudah' => 90000, 'sisa' => 2060000, 'lunas' => false],
            'pembayaran_diterima' => [
                ['jenis' => 'dp', 'label' => 'Uang Muka (DP)', 'nominal' => 90000, 'berkas' => 1],
            ],
        ])),
    ]);

    /*
     | Halaman ini dulu berhenti di kartu "Total tagihan Rp 2.150.000" — benar
     | sebagai angka, tetapi tidak menyebut bahwa penyewa sudah membayar uang
     | muka. Admin yang membacakan angka itu saat menagih menagih lebih dari
     | yang seharusnya, dan penyewa mendengar dirinya diminta membayar DP dua
     | kali.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent());

    expect($isi)->toContain('Rincian tagihan')
        ->toContain('Tarif sewa')
        ->toContain('Denda keterlambatan')
        ->toContain('Total tagihan')
        ->toContain('Uang Muka (DP)')
        ->toContain('Sisa yang harus dibayar')
        ->toContain('Rp 2.060.000');
});

test('detail sewa dibagi jadi bagian, sebentuk dengan lembar serah terima', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'kilometer_awal' => 84120, 'kilometer_akhir' => 85340,
            'diserahkan_pada' => '2026-08-26T08:00:00+07:00',
            'dikembalikan_pada' => '2026-08-31T11:00:00+07:00',
        ])),
    ]);

    /*
     | Keduabelas keterangannya dulu berderet dalam satu kartu bernama "Jadwal &
     | Lokasi" — merek unit, kapasitas, aturan sopir, pos biaya, tanggal, jam,
     | lokasi — padahal separuhnya bukan jadwal dan bukan lokasi. Label dan
     | nilainya ditumpuk polos tanpa batas apa pun, jadi mata tidak punya
     | pegangan mana pasangan yang mana.
     */
    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent();

    $badan = tanpaGaya($isi);

    expect($badan)->toContain('Unit &amp; Layanan')
        ->toContain('Jadwal &amp; Lokasi')
        ->toContain('Penyewa')
        ->toContain('Serah Terima')
        ->toContain('Pemeriksaan Fisik')
        // Kepala bernomor dan medan berkotak, sama seperti lembar serah terima
        ->toContain('orcha-bagian-nomor')
        ->toContain('orcha-medan')
        // Serah terimanya dua kolom kembar, bukan enam medan berderet
        ->toContain('orcha-keadaan kembali')
        // Dan jarak tempuhnya dihitungkan, tidak dikurangkan admin di kepala
        ->toContain('1.220 km ditempuh');
});

test('detail sewa yang belum diserahkan tidak menampilkan jarak tempuh', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa(['kilometer_awal' => 84120, 'kilometer_akhir' => null])),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()
        ->assertDontSee('km ditempuh');
});

test('kedua halaman sewa menyebut apa saja yang sudah tercakup harganya', function () {
    $unit = [
        'id' => 1, 'nama' => 'Bus OH 1526', 'transmisi' => 'Manual',
        'sebutan' => 'Mercedes-Benz OH 1526 (2019)', 'kapasitas' => 58, 'kursi_total' => 59,
        'sopir_label' => 'Harga sudah termasuk sopir',
        'operasional_label' => 'BBM dan tol termasuk (+Rp 300.000/hari)',
        'termasuk' => [
            ['label' => 'Sopir', 'termasuk' => true, 'catatan' => 'sudah menyatu di tarif'],
            ['label' => 'BBM', 'termasuk' => true, 'catatan' => 'terhitung Rp 300.000/hari'],
            ['label' => 'Parkir', 'termasuk' => false, 'catatan' => null],
        ],
    ];

    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa(['kendaraan' => $unit, 'luar_kota' => true])),
    ]);

    /*
     | Keterangannya sudah ada berupa kalimat, tapi harus dibaca sampai habis.
     | Yang ditanya di loket cuma satu pos: BBM ditanggung siapa.
     |
     | Yang ditanggung penyewa ikut disebut, tidak disembunyikan — menyebut yang
     | termasuk saja membuat penyewa mengira sisanya juga ditanggung, dan itu
     | baru ketahuan saat menagih.
     */
    foreach ([
        '/admin/orcha/penyewaan/12',
        '/admin/orcha/penyewaan/12/serah-terima',
    ] as $alamat) {
        $this->actingAs(adminOrcha())->get($alamat)->assertOk()
            ->assertSee('orcha-cip-termasuk ya', false)
            ->assertSee('orcha-cip-termasuk tidak', false)
            ->assertSee('sudah menyatu di tarif')
            ->assertSee('terhitung Rp 300.000/hari')
            ->assertSee('ditanggung penyewa');
    }
});

test('sewa lama tanpa daftar termasuk tidak memunculkan barisan kosong', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa()),
    ]);

    // Gayanya dibuang dulu: ditulis inline, jadi nama kelasnya juga muncul di
    // dalam CSS dan ikut terhitung.
    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent();

    $badan = tanpaGaya($isi);

    expect($badan)->not->toContain('orcha-cip-termasuk');
});

test('tiga kendali detail sewa pindah ke kartunya sendiri, seukuran dan berwarna', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*/penyewaan/12' => Http::response(['data' => balasanSewa(['status' => 'berjalan'])['data'][0]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Ketiganya dulu berdesakan di pojok kanan kepala, berhimpit dengan nama
     | penyewa dan kodenya — terbaca seperti bilah penyaring, padahal dua di
     | antaranya tindakan dan yang ketiga MENGUBAH keadaan pesanan.
     |
     | Ukurannya pun tidak seragam: layout memaksa SETIAP .form-select setinggi
     | 48px, jadi benda yang paling besar di baris itu justru yang paling jarang
     | disentuh.
     */
    $isi = $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')->assertOk()->getContent();

    // Kartunya sendiri, berlatar bilah perkakas — bukan menumpang di kepala
    expect($isi)->toContain('orcha-kartu-tindakan')
        ->and($isi)->toContain('Hubungi penyewa, unduh notanya');

    // Ditambatkan pada kartu tindakannya. Sempat ditambatkan pada tautan
    // wa.me — lalu tombolnya berhenti jadi tautan sama sekali begitu ia
    // membuka pilihan pesan.
    $kepala = substr(tanpaGaya($isi), strpos(tanpaGaya($isi), 'orcha-kartu-tindakan'), 1800);

    expect(substr_count($kepala, 'orcha-aksi-sewa'))->toBe(2)
        ->and($kepala)->toContain('orcha-status-ringkas')
        // Warnanya mengikuti keadaan, sama seperti di daftar sewa
        ->and($kepala)->toContain('data-status="berjalan"');
});

test('usulan denda kerusakan tampil dirinci per bagian', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'denda_kerusakan_usulan' => 1900000,
            'rincian_denda_kerusakan' => [
                ['kunci' => 'kaca', 'bagian' => 'Kaca & spion', 'dari' => 'Baik', 'jadi' => 'Rusak', 'biaya' => 900000],
                ['kunci' => 'bodi_kanan', 'bagian' => 'Bodi samping kanan', 'dari' => 'Lecet / minor', 'jadi' => 'Rusak', 'biaya' => 1000000],
            ],
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        // Terisi usulan, bertitik, dan tiap bagian punya isiannya sendiri
        // supaya admin bisa menyesuaikan dengan nota bengkel
        ->assertSet('dendaKerusakan', '1.900.000')
        // Kuncinya slug bagian, bukan namanya: nama mengandung spasi dan "&",
        // dan Livewire tidak bisa mengikat isian ke kunci seperti itu
        ->assertSet('biayaKerusakan.kaca', '900.000')
        ->assertSet('biayaKerusakan.bodi_kanan', '1.000.000')
        ->assertSee('Usulan denda kerusakan')
        // Mengubah satu baris membuat totalnya ikut berubah, dan ketikannya
        // tidak hilang begitu isiannya ditinggalkan
        ->set('biayaKerusakan.kaca', '450000')
        ->assertSet('biayaKerusakan.kaca', '450.000')
        ->assertSet('dendaKerusakan', '1.450.000');
});

test('ketikan biaya kerusakan bertahan walau nama bagiannya berspasi', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(satuSewa([
            'denda_kerusakan_usulan' => 650000,
            'rincian_denda_kerusakan' => [
                ['kunci' => 'bodi_kiri', 'bagian' => 'Bodi samping kiri', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 200000],
                ['kunci' => 'bodi_depan', 'bagian' => 'Bodi depan & bemper', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 250000],
                ['kunci' => 'bodi_kanan', 'bagian' => 'Bodi samping kanan', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 200000],
            ],
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSet('dendaKerusakan', '650.000')
        // "Bodi depan & bemper" — nama dengan spasi DAN ampersand, yang dulu
        // membuat ketikan admin lenyap begitu isiannya ditinggalkan
        ->set('biayaKerusakan.bodi_depan', '1250000')
        ->assertSet('biayaKerusakan.bodi_depan', '1.250.000')
        ->assertSet('dendaKerusakan', '1.650.000')
        // Catatan yang dibacakan ke penyewa ikut memakai angka barunya
        ->assertSet('catatanDenda', fn ($catatan) => str_contains($catatan, 'Bodi depan & bemper')
            && str_contains($catatan, 'Rp 1.250.000'));
});

test('usulan denda yang belum disimpan ditandai, bukan dibiarkan menyesatkan', function () {
    // Persis keadaan SK-1608-ZGAN: unit sudah kembali, sistem punya usulan,
    // tapi tidak ada satu rupiah pun yang ditetapkan — sehingga lembar serah
    // terima terlihat berbeda dengan nota dan halaman detail.
    $baris = balasanSewa([
        'dikembalikan_pada' => '2026-08-20T14:38:00+07:00',
        'denda_keterlambatan' => 0, 'denda_kerusakan' => 0, 'denda_lain' => 0,
        'total_denda' => 0, 'total_tagihan' => 300000, 'estimasi_biaya' => 300000,
        'denda_keterlambatan_usulan' => 1200000,
        'denda_kerusakan_usulan' => 650000,
        'rincian_denda_kerusakan' => [
            ['kunci' => 'bodi_kiri', 'bagian' => 'Bodi samping kiri', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 200000],
        ],
    ]);

    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*/penyewaan/12' => Http::response(['data' => $baris['data'][0]]),
        '*' => Http::response($baris),
    ]);

    // Di lembar serah terima: peringatan bahwa angkanya belum tersimpan
    Livewire::actingAs(adminOrcha())
        ->test(OrchaSerahTerimaForm::class, ['penyewaan' => 12])
        ->assertSee('belum tersimpan')
        ->assertSee('Simpan Serah Terima');

    // Di halaman detail: penanda bahwa ada usulan yang belum ditetapkan
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')
        ->assertOk()
        ->assertSee('Ada usulan denda yang belum ditetapkan')
        ->assertSee('Rp 1.200.000')
        ->assertSee('Rp 650.000');
});

test('denda yang sudah ditetapkan tidak lagi ditandai belum tersimpan', function () {
    $baris = balasanSewa([
        'dikembalikan_pada' => '2026-08-20T14:38:00+07:00',
        'denda_keterlambatan' => 1200000, 'denda_kerusakan' => 650000, 'denda_lain' => 0,
        'total_denda' => 1850000, 'total_tagihan' => 2150000, 'estimasi_biaya' => 300000,
        'denda_keterlambatan_usulan' => 1200000, 'denda_kerusakan_usulan' => 650000,
    ]);

    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*/penyewaan/12' => Http::response(['data' => $baris['data'][0]]),
        '*' => Http::response($baris),
    ]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/penyewaan/12')
        ->assertOk()
        ->assertDontSee('Ada usulan denda yang belum ditetapkan')
        ->assertSee('Rp 2.150.000');
});

test('dashboard menggambar tren bulanan, bukan hanya angka', function () {
    Http::fake(['*/dashboard' => Http::response(balasanDashboard([
        'tren_bulanan' => [
            ['bulan' => '2026-07', 'label' => 'Jul', 'label_panjang' => 'Juli 2026',
                'pendaftaran' => 4, 'penyewaan' => 1],
            ['bulan' => '2026-08', 'label' => 'Agu', 'label_panjang' => 'Agustus 2026',
                'pendaftaran' => 9, 'penyewaan' => 3],
        ],
    ]))]);

    /*
     | Angka tunggal menjawab "berapa hari ini"; yang tidak dijawabnya adalah
     | "sedang naik atau turun" — dan itu pertanyaan yang membuat orang membuka
     | dashboard dua kali sehari.
     |
     | Dulu digambar SVG sendiri, dan uji ini memeriksa <title> tiap batangnya.
     | Alasan memilih SVG ternyata keliru: ApexCharts BUKAN hasil bundel Vite —
     | berkasnya ada di public/mazer, terlacak git, dan ikut ter-deploy. Yang
     | tidak ikut adalah public/build.
     |
     | Sekarang angkanya berangkat sebagai data seri, jadi itulah yang diuji.
     */
    $isi = tanpaGaya($this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')->assertOk()->getContent());

    expect($isi)->toContain('Tren enam bulan terakhir')
        ->toContain('orcha-grafik-tren')
        ->toContain('apexcharts/apexcharts.min.js')
        ->toContain("name: 'Pendaftaran'")
        ->toContain("name: 'Sewa kendaraan'")
        // Angkanya benar-benar berangkat ke grafiknya
        ->toContain('[4,9]')
        ->toContain('[1,3]');
});

test('orcha tanpa tren tidak merobohkan dashboard', function () {
    // Orcha dipasang terpisah dan boleh tertinggal sekian rilis: yang belum
    // mengirim tren menghasilkan dashboard tanpa grafik, bukan halaman galat.
    Http::fake(['*/dashboard' => Http::response(balasanDashboard())]);

    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/dashboard')
        ->assertOk()
        ->assertDontSee('Tren enam bulan terakhir');
});
