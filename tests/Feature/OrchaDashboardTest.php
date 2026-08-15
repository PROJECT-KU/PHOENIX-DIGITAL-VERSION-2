<?php

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
