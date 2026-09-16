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
    'livewire/pages/admin/bot-turnitin/panel-bot-turnitin.blade.php',
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

it('status luring dan non-member punya warnanya sendiri, bukan abu-abu', function () {
    // Abu-abu di dasbor ini berarti KETIADAAN data: pesanan 'draft', target
    // 'Belum Ada'. OFFLINE dan NON-MEMBER bukan data yang belum lengkap —
    // keduanya keadaan yang pasti — jadi keduanya tidak boleh ikut abu-abu.
    //
    // Kelasnya sendiri, bukan menumpang is-merah/is-kuning milik status
    // pesanan: satu kelas dengan dua arti akan saling menular saat salah
    // satunya disetel ulang.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));
    $dasbor = file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php'));
    $daring = file_get_contents(resource_path('views/livewire/pages/admin/online-users.blade.php'));

    expect($gaya)->toContain('.dsb-lencana.is-luring')
        ->and($gaya)->toContain('.dsb-lencana.is-tamu')
        // Titik di foto memakai warna yang sama dengan lencananya — satu
        // keadaan tidak boleh muncul dalam dua warna di baris yang sama.
        ->and($gaya)->toContain('.dsb-titik.is-luring { background: #ef4444; }')
        ->and($daring)->toContain("\$user->online ? 'is-hijau' : 'is-luring'")
        ->and($dasbor)->toContain("'is-hijau' : 'is-tamu'")
        // Termasuk lencana yang ditulis ulang oleh Echo saat status berubah:
        // tanpa ini, baris yang berubah realtime kembali jadi abu-abu.
        ->and($dasbor)->toContain("daring ? 'is-hijau' : 'is-luring'");
});

it('dasbor karyawan memuat pekerjaan hari ini, bukan hanya gaji', function () {
    // Dasbor ini sebelumnya hanya berisi gaji, pinjaman, dan data diri —
    // semuanya hal yang dibuka sebulan sekali. Presensi dan task, dua hal
    // yang dibuka tiap hari, tidak ada sama sekali.
    $karyawan = file_get_contents(resource_path('views/livewire/pages/admin/dashboard-karyawan.blade.php'));

    expect($karyawan)->toContain('Presensi &amp; Task Saya')
        ->and($karyawan)->toContain('$presensiHariIni')
        ->and($karyawan)->toContain('$taskSaya')
        // Aksi cepat: sama dengan dasbor pengurus, supaya yang paling sering
        // dibuka tidak perlu dicari di menu samping lebih dulu.
        ->and($karyawan)->toContain('Aksi Cepat');
});

it('penanda memuat memakai nama metode, bukan aksi ajaib', function () {
    // wire:target hanya cocok dengan NAMA METODE. Dengan wire:target="$refresh"
    // penandanya tidak pernah muncul — dan tidak ada yang tahu, karena
    // halamannya tetap bekerja.
    $dasbor = file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php'));

    expect($dasbor)->toContain('wire:target="muatUlang"')
        ->and($dasbor)->not->toContain('wire:target="$refresh"')
        // Livewire menyetel display jadi 'inline-block' kecuali diberi
        // modifier; pada pil yang seharusnya inline-flex, itu merusak jarak
        // ikon dengan teksnya.
        ->and($dasbor)->toContain('wire:loading.inline-flex wire:target="pilihPeriode"')
        ->and(method_exists(\App\Livewire\Pages\Admin\Dashboard::class, 'muatUlang'))->toBeTrue();
});

it('kartu hari ini menandai dirinya saat periode digeser ke belakang', function () {
    // "Pendapatan Hari Ini" duduk satu bagian dengan kartu-kartu periode.
    // Saat periode digeser, empat kartu berubah dan kartu ini tetap hari ini.
    $dasbor = file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php'));

    expect($dasbor)->toContain('dsb-tanda-kini')
        ->and($dasbor)->toContain('Selalu hari ini');
});

it('gaya sendiri tidak mengalahkan aturan penyembunyi wire:loading', function () {
    // Livewire menyembunyikan elemen wire:loading lewat CSS berbobot DUA
    // pemilih atribut ([wire\:loading][wire\:loading]). Aturan sendiri yang
    // memakai dua kelas + satu elemen berbobot lebih tinggi dan mengalahkannya
    // — penanda "Memuat…" lalu berputar terus sejak halaman dibuka, padahal
    // tidak ada permintaan yang berjalan.
    //
    // Karena itu display untuk isi tombol ditulis pada SATU kelas (0,1,0),
    // yang kalah dari aturan Livewire (0,2,0). Diukur di peramban sesudahnya:
    // span "Memuat…" computed display = none saat diam.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));
    $dasbor = file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php'));

    expect($gaya)->toContain('.dsb-segar-isi { display: inline-flex;')
        ->and($gaya)->not->toContain('.dsb-segar.is-tombol > span { display:')
        ->and($dasbor)->toContain('class="dsb-segar-isi" wire:loading.inline-flex');
});

it('lencana status bot setinggi tombol di sebelahnya', function () {
    // Lencana berdiri SEBARIS dengan "Jeda", "Pasang skrip", dan "Token baru".
    // Dengan padding pilnya sendiri ia setinggi 21px di antara tombol 36px dan
    // terbaca seperti label yang tercecer, bukan status kartunya.
    // Diukur sesudahnya: keduanya 36px, tepi atas & bawah sama persis.
    $panel = file_get_contents(resource_path('views/livewire/pages/admin/bot-turnitin/panel-bot-turnitin.blade.php'));

    expect($panel)->toContain('box-sizing: border-box; min-height: 36px;')
        // Tepi bening supaya kotaknya sama dengan .bt-btn yang bertepi 1px.
        ->and($panel)->toContain('border: 1px solid transparent; border-radius: 999px;');
});

it('judul baris tidak dipotong di layar sempit', function () {
    // Nomor pesanan adalah IDENTITAS. Terpotong jadi "INV-20260915-00…" ia
    // tidak bisa dicocokkan maupun dicari. Diukur: pada 768px empat nomor
    // pesanan terpotong sebelum aturan ini ada.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    expect($gaya)->toContain("@media (max-width: 1199.98px) {\n        .dsb-baris-judul { white-space: normal; overflow-wrap: anywhere; }");
});

it('sasaran sentuh di layar sempit cukup besar untuk jempol', function () {
    // Diukur: "Muat ulang" 19px, "Hubungi" 25px, tab bot 26px — semuanya jauh
    // di bawah ~36px. Sesudah aturan ini, sasaran terkecil di bawah 992px
    // adalah 35px.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));
    $panel = file_get_contents(resource_path('views/livewire/pages/admin/bot-turnitin/panel-bot-turnitin.blade.php'));

    expect($gaya)->toContain('@media (max-width: 991.98px), (pointer: coarse) {')
        ->and($gaya)->toContain('.dsb-segar.is-tombol { min-height: 36px;')
        ->and($gaya)->toContain('.dsb-baris-aksi { min-height: 36px;')
        ->and($panel)->toContain('.bt-tab { min-height: 36px;');
});
