<?php

use App\Livewire\Pages\Admin\Orcha\OrchaArmadaForm;
use App\Livewire\Pages\Admin\Orcha\OrchaEtalaseList;
use App\Livewire\Pages\Admin\Orcha\OrchaKatalogList;
use App\Livewire\Pages\Admin\Orcha\OrchaPaketForm;
use App\Livewire\Pages\Admin\Orcha\OrchaPendaftaranList;
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
        ->set('harga', 1250000)
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
        ->set('tarifHari', 700000)
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
        ->test(OrchaKatalogList::class)
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
        ->set('harga', 1000000)
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
        ->set('harga', 1000000)
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
        ->set('hargaAsli', 1700000)
        ->set('harga', 1430000)
        // 15,88% dibulatkan ke bawah supaya potongan tidak dilebih-lebihkan
        ->assertSet('diskonPersen', 15);
});

test('harga jual sama atau lebih mahal berarti tanpa diskon', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('hargaAsli', 1000000)
        ->set('harga', 1000000)
        ->assertSet('diskonPersen', 0)
        ->set('harga', 1200000)
        ->assertSet('diskonPersen', 0);
});

test('diskon yang ditulis sendiri tidak ditimpa hitungan', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('hargaAsli', 1700000)
        ->set('harga', 1430000)
        ->assertSet('diskonPersen', 15)
        // Promo memakai angka bulat
        ->set('diskonPersen', 20)
        ->assertSet('diskonOtomatis', false)
        ->set('harga', 1400000)
        ->assertSet('diskonPersen', 20)
        ->call('hitungDiskon')
        ->assertSet('diskonPersen', 17)
        ->assertSet('diskonOtomatis', true);
});

/* --------------------------- PESAN SUKSES --------------------------- */

test('pesan sukses dititipkan ke sesi supaya tetap tampil setelah berpindah', function () {
    Http::fake(['*' => Http::response(['data' => [], 'pesan' => 'ok'])]);

    Livewire::actingAs(adminOrcha())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Baru')
        ->set('harga', 1000000)
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.orcha.paket'));

    expect(session('orcha_sukses'))->toBe('Paket wisata ditambahkan.');
});

test('halaman daftar menampilkan pesan titipan itu', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => []])]);

    $this->actingAs(adminOrcha())
        ->withSession(['orcha_sukses' => 'Paket wisata ditambahkan.'])
        ->get('/admin/orcha/paket-wisata')
        ->assertOk()
        ->assertSee('Paket wisata ditambahkan.');
});
