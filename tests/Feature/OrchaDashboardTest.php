<?php

use App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaForm;
use App\Livewire\Pages\Admin\Orcha\Etalase\OrchaEtalaseList;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketForm;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketList;
use App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranList;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanList;
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

    // Nomor 08xx harus jadi 62xx; wa.me menolak awalan nol
    expect($tautan)->toStartWith('https://api.whatsapp.com/send?phone=6281234567890&text=')
        // Emoji disandikan sebagai UTF-8 persen, bukan dibuang atau jadi "?"
        ->and($tautan)->toContain(rawurlencode('👋'))
        // Spasi TIDAK boleh jadi "+": WhatsApp menampilkan tanda plus itu apa
        // adanya, dan kalimatnya jadi penuh plus alih-alih spasi
        ->and($tautan)->not->toContain('+')
        ->and($tautan)->toContain('%20');

    // Yang ditanyakan pelanggan begitu buktinya diterima adalah sisanya
    expect($pesan)->toContain('Rp 2.002.000')
        ->and($pesan)->toContain('Budi Santoso')
        ->and($pesan)->toContain('OT-1508-A7K3');

    // Emoji yang dipakai harus yang sudah lama ada di Unicode. Yang terbaru
    // digambar sebagai kotak kosong di ponsel lama — dan pemakai ponsel lama
    // justru yang paling perlu membaca kabar ini.
    expect($pesan)->not->toContain('🧾')
        // Penanda ragam (U+FE0F) tidak kelihatan tapi ikut disandikan, dan
        // justru bagian itu yang paling sering rusak di perjalanan.
        ->and($pesan)->not->toContain("\u{FE0F}");
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
    expect($tanpa)->not->toMatch('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u')
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
        ->assertSee('👋')
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
        '*' => Http::response(['data' => [$baris], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);

    $lembar = Livewire::actingAs(adminOrcha())
        ->test(OrchaPembayaranList::class)
        ->call('buka', $baris);

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

    Livewire::actingAs(adminOrcha(['akses_orcha', 'view_orcha_kesehatan']))
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 7])
        ->call('bukaRiwayat')
        ->assertSee('Perlu perhatian')
        ->assertSee('Menuntut kesiapan sebelum berangkat')
        ->assertSee('Ada catatan')
        ->assertSee('Cukup diingat di lapangan')
        ->assertSee('Tanpa catatan');
});

/* ---------------- SEWA KENDARAAN: SERAH TERIMA & DENDA ---------------- */

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
        '*' => Http::response(balasanSewa()),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPenyewaanList::class)
        ->call('buka', balasanSewa()['data'][0])
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
        '*' => Http::response(balasanSewa()),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPenyewaanList::class)
        // Bodi depan SUDAH lecet sebelum diserahkan; kaca baru rusak
        ->call('buka', balasanSewa(['kondisi_awal' => ['bodi_depan' => 'lecet', 'kaca' => 'baik']])['data'][0])
        ->set('kondisiAkhir.bodi_depan', 'lecet')
        ->set('kondisiAkhir.kaca', 'rusak')
        ->assertSeeHtml('kerusakan baru');
});

test('serah terima diteruskan ke orcha lalu lembarnya menutup', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*/serah-terima' => Http::response(['data' => [], 'pesan' => 'ok']),
        '*' => Http::response(balasanSewa()),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPenyewaanList::class)
        ->call('buka', balasanSewa()['data'][0])
        ->set('dikembalikanPada', '2026-09-11T11:00')
        ->set('dendaKerusakan', 300000)
        ->set('catatanDenda', 'Spion kanan retak')
        ->call('simpanSerahTerima')
        ->assertSet('serahTerimaUntuk', null);

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/serah-terima')
        && $permintaan['denda_kerusakan'] === 300000
        && $permintaan['catatan_denda'] === 'Spion kanan retak');
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

    Http::fake(['*/rujukan' => Http::response(rujukanSewa()), '*' => Http::response($baris)]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPenyewaanList::class)
        ->call('buka', $baris['data'][0])
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
        '*' => Http::response(balasanSewa()),
    ]);

    $baris = balasanSewa(['rincian_denda_kerusakan' => [
        ['kunci' => 'bodi_depan', 'bagian' => 'Bodi depan & bemper',
            'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 250000],
    ]])['data'][0];

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPenyewaanList::class)
        ->call('buka', $baris)
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

test('usulan denda kerusakan tampil dirinci per bagian', function () {
    Http::fake([
        '*/rujukan' => Http::response(rujukanSewa()),
        '*' => Http::response(balasanSewa([
            'denda_kerusakan_usulan' => 1900000,
            'rincian_denda_kerusakan' => [
                ['kunci' => 'kaca', 'bagian' => 'Kaca & spion', 'dari' => 'Baik', 'jadi' => 'Rusak', 'biaya' => 900000],
                ['kunci' => 'bodi_kanan', 'bagian' => 'Bodi samping kanan', 'dari' => 'Lecet / minor', 'jadi' => 'Rusak', 'biaya' => 1000000],
            ],
        ])),
    ]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPenyewaanList::class)
        ->call('buka', balasanSewa([
            'denda_kerusakan_usulan' => 1900000,
            'rincian_denda_kerusakan' => [
                ['kunci' => 'kaca', 'bagian' => 'Kaca & spion', 'dari' => 'Baik', 'jadi' => 'Rusak', 'biaya' => 900000],
                ['kunci' => 'bodi_kanan', 'bagian' => 'Bodi samping kanan', 'dari' => 'Lecet / minor', 'jadi' => 'Rusak', 'biaya' => 1000000],
            ],
        ])['data'][0])
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
        '*' => Http::response(balasanSewa()),
    ]);

    $baris = balasanSewa([
        'denda_kerusakan_usulan' => 650000,
        'rincian_denda_kerusakan' => [
            ['kunci' => 'bodi_kiri', 'bagian' => 'Bodi samping kiri', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 200000],
            ['kunci' => 'bodi_depan', 'bagian' => 'Bodi depan & bemper', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 250000],
            ['kunci' => 'bodi_kanan', 'bagian' => 'Bodi samping kanan', 'dari' => 'Baik', 'jadi' => 'Lecet / minor', 'biaya' => 200000],
        ],
    ])['data'][0];

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPenyewaanList::class)
        ->call('buka', $baris)
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
        ->test(OrchaPenyewaanList::class)
        ->call('buka', $baris['data'][0])
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
