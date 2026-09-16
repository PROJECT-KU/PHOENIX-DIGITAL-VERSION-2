<?php

use Illuminate\Support\Facades\Blade;

/**
 * Tampilan dasbor panel (admin & karyawan) dengan bahasa rupa dsb-*.
 *
 * Dasbor pengurus tidak bisa dirender utuh di tes: kuerinya memakai MONTH()
 * milik MySQL, sedangkan tes memakai SQLite. Karena itu penjaganya membaca
 * hasil kompilasi Blade — cukup untuk menangkap kelas galat yang benar-benar
 * pernah terjadi saat membangunnya.
 */
dataset('tampilan dasbor', [
    'livewire/pages/admin/dashboard.blade.php',
    'livewire/pages/admin/dashboard-karyawan.blade.php',
    'livewire/pages/admin/online-users.blade.php',
    'livewire/pages/admin/partials/dasbor-gaya.blade.php',
]);

it('hasil kompilasinya PHP yang sah', function (string $berkas) {
    // Komentar CSS yang menyebut "<x-nama-komponen>" dikompilasi Blade menjadi
    // pemanggilan komponen sungguhan, lalu halaman jatuh dengan ParseError
    // "unexpected end of file" — tanpa menyebut baris CSS penyebabnya.
    $php = Blade::compileString(file_get_contents(resource_path('views/'.$berkas)));

    expect(fn () => token_get_all($php, TOKEN_PARSE))->not->toThrow(ParseError::class);
})->with('tampilan dasbor');

it('dasbor karyawan tampil dengan kerangka baru', function () {
    $peran = \App\Models\Role::create(['name' => 'uji-dasbor-'.uniqid(), 'description' => 'uji']);
    $peran->permissions()->attach(\App\Models\Permission::firstOrCreate(
        ['name' => 'view_dashboard'],
        ['display_name' => 'view_dashboard', 'group' => 'uji', 'description' => 'uji']
    )->id);
    $user = \App\Models\User::factory()->create(['role_id' => $peran->id, 'name' => 'Dewi Lestari']);
    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Staf Operasional', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    $this->actingAs($user)->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('class="dsb-hero"', false)
        ->assertSee(', Dewi', false)
        ->assertSee('Gaji &amp; Pinjaman', false)
        // Salam dari jam server, bukan skrip peramban yang membaca jam laptop.
        ->assertDontSee('getGreeting', false);
});

it('waktu relatif di dasbor ditulis dalam bahasa Indonesia', function () {
    // APP_LOCALE=en: diffForHumans() tanpa locale('id') mencetak "2 hours ago".
    $sumber = file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php'));

    preg_match_all('/->diffForHumans\(\)/', $sumber, $semua);
    preg_match_all("/->locale\('id'\)->diffForHumans\(\)/", $sumber, $berlocale);

    expect(count($semua[0]))->toBeGreaterThan(0)
        ->and(count($berlocale[0]))->toBe(count($semua[0]));
});

it('kotak ikon huruf dilepas dari aturan ikon SVG bawaan template', function () {
    // Template membawa `.bi { width: 1em; height: 1em }` — aturan untuk ikon
    // SVG. Pada ikon HURUF, ia mengunci kotak elemen di 16px sementara glifnya
    // digambar 24px, sehingga glif meluber ke kanan-bawah dan seluruh ikon di
    // dasbor tampak meleset dari pusat ubinnya. Diukur piksel per piksel:
    // sebelum +4,75/+3,42 px, sesudah di bawah 0,4/1,5 px.
    //
    // Dijaga di SUMBER karena gejalanya halus — tidak ada yang rusak, hanya
    // "kelihatan agak turun" — dan mudah hilang saat gaya dirapikan.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    expect($gaya)->toContain('.dsb i.bi,')
        ->and($gaya)->toContain('width: auto; height: auto;');
});

it('ikon yang menemani teks tidak memakai vertical-align sub bawaan template', function () {
    // Template menyetel `body .bi:before { vertical-align: sub }` — 'sub'
    // menurunkan glif setinggi posisi subskrip, jadi ikon di tengah kalimat
    // duduk ~2px di bawah garis alas teksnya dan terbaca melorot.
    //
    // Diukur terhadap pita huruf kapital (patokan yang dipakai mata): sesudah
    // diperbaiki, SELURUH ikon di dasbor meleset kurang dari 0,75 px.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    expect($gaya)->toContain('vertical-align: -.125em;')
        ->and($gaya)->toContain('vertical-align: baseline;');
});

it('pemilih periode menggeser seluruh angka periode, dan dibatasi rentangnya', function () {
    // Kueri grafik dasbor memakai MONTH()/YEAR() milik MySQL; pengujian jalan
    // di SQLite, jadi fungsinya didaftarkan seadanya khusus untuk tes ini.
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    $pdo->sqliteCreateFunction('MONTH', fn ($d) => $d ? (int) date('n', strtotime($d)) : null, 1);
    $pdo->sqliteCreateFunction('YEAR', fn ($d) => $d ? (int) date('Y', strtotime($d)) : null, 1);

    $peran = \App\Models\Role::create(['name' => 'uji-periode-'.uniqid(), 'description' => 'uji']);
    foreach (['view_dashboard', 'view_all_dashboard'] as $n) {
        $peran->permissions()->attach(\App\Models\Permission::firstOrCreate(
            ['name' => $n], ['display_name' => $n, 'group' => 'uji', 'description' => 'uji']
        )->id);
    }
    $admin = \App\Models\User::factory()->create(['role_id' => $peran->id]);

    $ini = \App\Support\PeriodeGaji::dariTanggal(now());
    $labelIni = \App\Support\PeriodeGaji::label($ini['bulan'], $ini['tahun']);

    $lalu = \App\Support\PeriodeGaji::dariTanggal(
        \App\Support\PeriodeGaji::mulai($ini['bulan'], $ini['tahun'])->subDay()
    );
    $labelLalu = \App\Support\PeriodeGaji::label($lalu['bulan'], $lalu['tahun']);

    \Livewire\Livewire::actingAs($admin->fresh())
        ->test(\App\Livewire\Pages\Admin\Dashboard::class)
        ->assertSee($labelIni)
        ->call('pilihPeriode', 1)
        ->assertSet('mundur', 1)
        ->assertSee($labelLalu)
        // Nilai dari peramban tidak boleh menyeret kueri ke rentang sembarang.
        ->call('pilihPeriode', 999)
        ->assertSet('mundur', \App\Livewire\Pages\Admin\Dashboard::MUNDUR_MAKS)
        ->call('pilihPeriode', -5)
        ->assertSet('mundur', 0)
        ->assertSee($labelIni);
});

it('aksi cepat punya kartunya sendiri dan menghormati izin', function () {
    // Di dalam kartu sapaan, dua tombol ini berdesakan dengan kartu identitas
    // dan tombol Logout — lima hal berjajar, dan yang paling sering diklik
    // justru paling sulit dikenali.
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    $pdo->sqliteCreateFunction('MONTH', fn ($d) => $d ? (int) date('n', strtotime($d)) : null, 1);
    $pdo->sqliteCreateFunction('YEAR', fn ($d) => $d ? (int) date('Y', strtotime($d)) : null, 1);

    $buatAdmin = function (array $izin) {
        $peran = \App\Models\Role::create(['name' => 'uji-aksi-'.uniqid(), 'description' => 'uji']);
        foreach ($izin as $n) {
            $peran->permissions()->attach(\App\Models\Permission::firstOrCreate(
                ['name' => $n], ['display_name' => $n, 'group' => 'uji', 'description' => 'uji']
            )->id);
        }

        return \App\Models\User::factory()->create(['role_id' => $peran->id])->fresh();
    };

    \Livewire\Livewire::actingAs($buatAdmin(['view_dashboard', 'view_all_dashboard', 'create_pemesanantoko', 'create_spending']))
        ->test(\App\Livewire\Pages\Admin\Dashboard::class)
        ->assertSeeHtml('class="dsb-aksi"')
        ->assertSee('Aksi Cepat')
        ->assertSee('Pesanan Baru')
        ->assertSee('Catat Pengeluaran');

    // Tanpa izin apa pun, kartunya tidak dibuat — bukan kartu kosong.
    \Livewire\Livewire::actingAs($buatAdmin(['view_dashboard', 'view_all_dashboard']))
        ->test(\App\Livewire\Pages\Admin\Dashboard::class)
        ->assertDontSeeHtml('class="dsb-aksi"')
        ->assertDontSee('Catat Pengeluaran');
});

it('tanggal grafik harian dijarangkan menurut lebar wadahnya', function () {
    // tickAmount milik Apex hanya PERKIRAAN: pada periode 31 hari di kolom
    // selebar ~590px ia tetap mencetak delapan label bersentuhan, sehingga
    // sumbunya terbaca menyambung — "21 Agt23 Agt25 Agt". Jaraknya kini
    // dihitung sendiri dari lebar wadah (satu label dijatah 72px) dan tanggal
    // di antaranya dikosongkan lewat formatter.
    //
    // Diukur ulang sesudahnya: jarak terkecil antar label 28,6px pada 590px
    // dan 30,2px pada 330px — sebelumnya nol.
    $dasbor = file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php'));

    expect($dasbor)->toContain('const LEBAR_LABEL = 72;')
        ->and($dasbor)->toContain('i % langkah === 0')
        // Kategorinya TIDAK boleh ikut dikosongkan: tooltip membaca tanggalnya
        // dari daftar yang sama, jadi label kosong tidak boleh menghapus judul.
        ->and($dasbor)->toContain('tanggal[opsi.dataPointIndex]')
        ->and($dasbor)->not->toContain('tickAmount: Math.min(tanggal.length');
});
