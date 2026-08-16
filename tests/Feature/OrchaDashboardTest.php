<?php

use App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaForm;
use App\Livewire\Pages\Admin\Orcha\Etalase\OrchaEtalaseList;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketForm;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketList;
use App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranList;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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

function balasanDashboard(): array
{
    return [
        'data' => [
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
        ],
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

test('panggilan membawa kunci dan email admin sebagai jejak audit', function () {
    Http::fake(['*/dashboard' => Http::response(balasanDashboard())]);

    $admin = adminOrcha();
    $this->actingAs($admin)->get('/admin/orcha/dashboard')->assertOk();

    Http::assertSent(function ($request) use ($admin) {
        return $request->hasHeader('X-Orcha-Key', 'kunci-uji')
            && $request->hasHeader('X-Orcha-Admin', $admin->email);
    });
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
    Http::fake(['*/pendaftaran*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPendaftaranList::class)
        ->call('bukaRiwayat', 1, 'Budi')
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

    Livewire::actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->test(OrchaPendaftaranList::class)
        ->call('bukaRiwayat', 1, 'Budi Santoso')
        ->assertSet('riwayatUntuk', 1)
        ->assertSee('Asma ringan');
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
        ->set('nama', 'All New Avanza')
        ->set('merek', 'Toyota')
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
        ->set('nama', 'All New Avanza')
        ->set('merek', 'Toyota')
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
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPembayaranList::class)
        ->call('buka', ['id' => 3, 'status' => 'menunggu', 'catatan_admin' => null])
        ->set('statusBaru', 'diterima')
        ->set('catatanAdmin', 'Cocok dengan mutasi.')
        ->call('simpan')
        ->assertDispatched('order-updated')
        ->assertSet('sedangDicek', null);

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
        ->assertSee('Surakarta:')
        ->assertSee('Siti Aminah, Rina Wijaya')
        ->assertSee('Jogja:')
        // Kelengkapan kesehatan ikut terlihat tanpa membuka apa pun
        ->assertSee('1/3 riwayat kesehatan');
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

    // Tanpa izin: tombolnya tidak ada, dan pemanggilannya ditolak di server
    $this->actingAs(adminOrcha())
        ->get('/admin/orcha/pendaftaran/7')
        ->assertOk()
        ->assertSee('hanya bisa dibuka akun berizin')
        ->assertDontSee('Lihat Riwayat Kesehatan');

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->call('bukaRiwayat')
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

    Livewire::actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->call('bukaRiwayat')
        ->assertSet('riwayatTerbuka', true)
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
        ->assertDontSee('target="_blank" rel="noopener"' . "\n" . '                                                class="btn btn-sm orcha-aksi orcha-aksi-lihat"', false);
});
