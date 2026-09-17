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

it('task saya memakai kerangka dasbor dan tabel bersama', function () {
    // Layar ini sebelumnya memakai dialeknya sendiri (kartu Bootstrap +
    // gradient-text), sehingga lemon terbaca seperti dua aplikasi berbeda.
    $task = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));
    $tabel = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-tabel.blade.php'));

    expect($task)->toContain("@include('livewire.pages.admin.partials.dasbor-gaya')")
        ->and($task)->toContain('<div class="dsb">')
        ->and($tabel)->toContain('class="dsb-tabel"')
        // Satu baris = satu TASK, bukan satu penerima: satu pekerjaan yang
        // diberikan ke lima orang tetap satu pekerjaan.
        ->and($tabel)->toContain('$gtasks->first()');
});

it('daftar task diurutkan yang terbaru di atas', function () {
    // Urutan lama mendahulukan yang jatuh tempo hari ini lalu progresnya,
    // sehingga task yang baru diberikan bisa mendarat di tengah daftar.
    // Yang mendesak tetap ditandai — pita warna di tepi baris dan kartu
    // ringkasan di atas — tapi tidak lagi mengatur urutannya.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($sumber)->not->toContain("FIELD(progress,'dikerjakan','belum','selesai')")
        ->and($sumber)->not->toContain('DATE(deadline_selesai) = CURDATE()')
        ->and($sumber)->toContain('->latest()');
});

it('gaya dasbor tidak menumpang reset kotak milik kerangka', function () {
    // Kartu angka memakai height:100% di dalam kisi. Dengan box-sizing bawaan
    // (content-box), tinggi itu dihitung DI LUAR padding dan isinya meluber
    // ~34px keluar kartunya — terlihat sebagai kartu yang saling tindih.
    // Gejalanya hanya muncul di halaman yang tidak memuat reset Bootstrap.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    expect($gaya)->toContain('.dsb, .dsb *, .dsb *::before, .dsb *::after { box-sizing: border-box; }');
});

it('jendela detail task memakai kerangka jendela bersama', function () {
    // Jendela yang terbuka saat baris task diklik dulu memakai kepala gradasi
    // ungu dan badge Bootstrap — dialek yang tidak ada di layar lain.
    $task = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    expect($task)->toContain('class="ts-modal-card dsb is-datar"')
        ->and($task)->toContain('dsb-jendela-kepala')
        ->and($task)->toContain('dsb-jendela-kaki')
        // .dsb membawa padding halaman; di dalam jendela padding itu harus mati.
        ->and($gaya)->toContain('.dsb.is-datar { padding: 0; }')
        ->and($gaya)->toContain('.dsb-jendela-kepala');
});

it('tanggal di layar task ditulis dalam bahasa Indonesia', function () {
    // APP_LOCALE=en, jadi translatedFormat() TANPA locale('id') menulis "Aug",
    // bukan "Agu" — lihat pola yang sama di CLAUDE.md. Gejalanya halus: tanggal
    // tetap benar, hanya bahasanya yang bocor.
    $berkas = glob(resource_path('views/livewire/pages/admin/task/*.blade.php'))
        + glob(resource_path('views/livewire/pages/admin/task/partials/*.blade.php'));

    $lalai = [];
    foreach ($berkas as $b) {
        foreach (preg_split('/\R/', (string) file_get_contents($b)) as $no => $baris) {
            if (str_contains($baris, 'translatedFormat(') && ! str_contains($baris, "locale('id')->translatedFormat(")) {
                // task-card & task-folder sudah tidak dipakai sejak daftar jadi
                // tabel; dibiarkan apa adanya sampai benar-benar dihapus.
                if (str_contains($b, 'task-card') || str_contains($b, 'task-folder')) {
                    continue;
                }
                $lalai[] = basename($b).':'.($no + 1);
            }
        }
    }

    expect($lalai)->toBe([]);
});

it('task grup menyimpan penerimanya sebagai sub-baris yang bisa dibuka', function () {
    // Daftar utama tetap sependek jumlah PEKERJAAN, bukan sepanjang jumlah
    // orang — tapi siapa saja yang menerimanya tetap bisa dilihat tanpa
    // berpindah halaman.
    $tabel = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-tabel.blade.php'));
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    // Satu <tbody> per task: itulah satu-satunya cara melipat baris tanpa
    // membungkusnya dengan <div>, yang tidak sah di dalam tabel.
    expect($tabel)->toContain('<tbody class="ts-grup"')
        ->and($tabel)->toContain('x-data="{ buka: false }"')
        ->and($tabel)->toContain('class="ts-sub is-klik')
        ->and($tabel)->toContain('x-show="buka" x-cloak')
        // Tanpa x-cloak seluruh penerima berkedip tampil sekejap tiap halaman
        // dimuat, sebelum Alpine sempat menyembunyikannya.
        ->and($gaya)->toContain('[x-cloak] { display: none !important; }')
        // Pemisah antar-task harus bertahan walau tiap task punya tbody sendiri.
        ->and($gaya)->toContain('.dsb-tabel tbody:last-child tr:last-child td { border-bottom: 0; }');
});

it('nilai rupiah bonus tidak pernah sampai ke karyawan', function () {
    // Bonus penyelesaian task dibagi dari satu pool anggaran. Karyawan boleh
    // melihat POIN-nya, tetapi besaran rupiahnya urusan penggajian — dijaga
    // izin view_all_gajikaryawan, izin yang sama yang memisahkan "boleh melihat
    // gaji orang lain" dari "boleh melihat gaji sendiri".
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));
    $tampilan = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));

    expect($sumber)->toContain("hasPermission('view_all_gajikaryawan')")
        // Angkanya hanya dihitung bila izinnya ada; tanpa izin nilainya null,
        // sehingga tidak ada apa pun yang bisa bocor lewat markup.
        ->and($sumber)->toContain('$bonusRupiah = null;')
        // Poin memakai konstanta yang SAMA dengan perhitungan uangnya, jadi
        // keduanya tidak akan pernah bercerita berbeda.
        ->and($sumber)->toContain('BonusTaskPeriodeAction::STATUS_PERSEN');

    // Tiap "Rp" di layar ini harus berada SESUDAH penjagaan $bonusRupiah —
    // yaitu di dalam cabang yang hanya hidup bila izinnya ada. Diperiksa
    // begini, bukan dengan mencocokkan satu bentuk @if tertentu, supaya
    // penjagaannya tetap teruji walau susunan cabangnya berubah.
    $penjagaPertama = strpos($tampilan, '$bonusRupiah');
    expect($penjagaPertama)->not->toBeFalse();
    expect(substr($tampilan, 0, $penjagaPertama))->not->toContain('Rp ');
});

it('daftar task punya cari, saring, urut, dan halaman', function () {
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));
    $tabel = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-tabel.blade.php'));

    expect($sumber)->toContain('public string $cari')
        ->and($sumber)->toContain('public string $saringStatus')
        ->and($sumber)->toContain('public string $saringArah')
        ->and($sumber)->toContain('public function urutkan(')
        // Halaman dipenggal per GRUP: memenggal per baris bisa memotong satu
        // task grup di tengah, sehingga sebagian penerimanya pindah halaman
        // tanpa induknya.
        ->and($sumber)->toContain('$semuaGrup = $tasks->groupBy(')
        ->and($tabel)->toContain('dsb-tabel-urut')
        ->and($tabel)->toContain('wire:click="keHalaman(');
});

it('ketiga cara pandang task memakai bahasa rupa yang sama', function () {
    // Sebelumnya satu layar memuat tiga dialek: dsb (tabel), scrum-*, dan akt-*.
    // Menekan tab "Papan Scrum" terasa seperti pindah aplikasi.
    $scrum = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-scrum.blade.php'));
    $akt = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-aktivitas.blade.php'));

    expect($scrum)->toContain('dsb-kartu k-4')
        ->and($scrum)->toContain('dsb-lencana')
        ->and($scrum)->not->toContain('scrum-col-head')
        ->and($akt)->toContain('dsb-stat k-3')
        ->and($akt)->toContain('dsb-daftar')
        ->and($akt)->not->toContain('akt-stat-angka');

    // Partial lama yang sudah tidak dipanggil siapa pun ikut dibuang.
    expect(file_exists(resource_path('views/livewire/pages/admin/task/partials/task-card.blade.php')))->toBeFalse()
        ->and(file_exists(resource_path('views/livewire/pages/admin/task/partials/task-folder.blade.php')))->toBeFalse();
});

it('sistem desain punya medan isian sendiri', function () {
    // Sampai sekarang sistem ini hanya punya cara MENAMPILKAN, belum cara
    // MEMINTA — jadi tiap layar berformulir jatuh kembali ke kotak isian
    // bawaan Bootstrap dan formulirnya tidak mirip apa pun di sekitarnya.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));
    $tampilan = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));

    expect($gaya)->toContain('.dsb-isian {')
        ->and($gaya)->toContain('.dsb-label {')
        ->and($gaya)->toContain('.dsb-cari {')
        // Jendela beri/edit task, buka kembali, dan diskusi grup ikut memakainya.
        ->and($tampilan)->not->toContain('class="ts-modal-head"')
        ->and($tampilan)->not->toContain('form-select');
});

it('layar task memakai irama bagian yang sama dengan dasbor', function () {
    // Yang membuat sebuah layar "terasa seperti dasbor" bukan kelasnya saja,
    // melainkan IRAMANYA: sapaan berikut kartu identitas, lalu tiap kelompok
    // dibuka kepala bagian (kicker, judul, deret chip) di dalam rak yang sama.
    // Tanpa itu halaman terbaca sebagai kartu-kartu yang mengambang.
    $task = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));
    $tabel = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-tabel.blade.php'));

    // Tiga kepala bagian: Ringkasan, Tampilan, Daftar.
    expect(substr_count($task, 'class="dsb-kepala"'))->toBeGreaterThanOrEqual(3)
        ->and($task)->toContain('class="dsb-kicker"')
        // Kartu identitas di kepala halaman — tanpa ini sisi kanannya kosong
        // di layar lebar, dan itulah beda paling kentara dengan dasbor.
        ->and($task)->toContain('class="dsb-aku"')
        // Susunan kartu angka 2 utama + 3 kecil, sama dengan Ringkasan Keuangan.
        ->and($task)->toContain('dsb-stat is-utama k-6')
        // Judul kartu tabel dibuang: kepala bagian di atasnya sudah menyebut
        // nama daftar, jumlah, dan urutannya.
        ->and($tabel)->not->toContain('Daftar Task</h3>');
});

it('jendela task yang menggulung hanya jendelanya, bukan halamannya', function () {
    // Dulu lapisan pembungkusnya yang menggulung: kepala dan tombol keputusan
    // ikut menghilang ke atas layar, dan halaman di belakangnya tetap bisa
    // ikut tergulung — menutup jendela lalu mendaratkan pembacanya di tempat
    // yang berbeda dari tempat ia menekan tadi.
    //
    // Diukur sesudahnya (jendela "Beri Task" pada layar setinggi 700px):
    // kartunya muat utuh di layar, kepala & kakinya tetap di dalam kartu, dan
    // yang menggulung hanya badannya.
    //
    // Cangkang jendelanya kini milik bersama (partials/dasbor-gaya) karena
    // jendela unduh Pemesanan RSC memakainya juga; layar Task tetap
    // memuat berkas itu.
    $task = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    expect($task)->toContain("@include('livewire.pages.admin.partials.dasbor-gaya')")
        ->and($gaya)->toContain('max-height: 94vh;')
        ->and($gaya)->toContain('.ts-modal-card > .dsb-jendela-isi { flex: 1 1 auto; min-height: 0; overflow-y: auto;')
        ->and($gaya)->toContain('body.ts-terkunci { overflow: hidden; }')
        // Penguncinya PENGAMAT, bukan tempelan di tiap tombol: jendela di layar
        // ini dibuka & ditutup Livewire, jadi satu-satunya yang pasti tahu
        // keadaannya adalah DOM.
        ->and($gaya)->toContain('new MutationObserver(perbarui)')
        // Berpindah halaman lewat wire:navigate harus melepas kuncinya; tanpa
        // ini halaman berikutnya tidak bisa digulung sama sekali.
        ->and($gaya)->toContain('livewire:navigating')
        // Tombol keputusan di kaki jendela, bukan di ujung badan yang tergulung.
        ->and($task)->not->toContain('class="px-4 pb-4 d-flex justify-content-end gap-2"');
});

it('baris kartu ringkasan task selalu penuh dan tidak ada kartu yang hilang', function () {
    // Versi rantai @if-nya punya satu keadaan yang tidak terpikir:
    // ADMINISTRATOR TANPA TASK SENDIRI. Kartu bonus mengambil slot utama,
    // lalu syarat kartu "Selesai" ikut gagal — kartunya hilang sama sekali
    // dan barisnya menyisakan sepertiga lebar yang kosong di ujung.
    $kelas = \App\Livewire\Pages\Admin\Task\TaskSayaList::class;

    foreach ([[true, true], [true, false], [false, true], [false, false]] as [$adaPoin, $adaBonus]) {
        $s = $kelas::susunanKartu($adaPoin, $adaBonus);
        $lebar = (int) substr($s['lebar_kecil'], 2);

        // Barisnya penuh: jumlah kartu kecil × lebarnya = 12 kolom.
        expect(count($s['kecil']) * $lebar)->toBe(12);

        // "Selesai" selalu ada — entah di slot utama, entah di antara yang kecil.
        expect($s['utama_kedua'] === 'selesai' || in_array('selesai', $s['kecil'], true))->toBeTrue();

        // Bonus muncul tepat sekali saat izinnya ada, dan tidak pernah saat tidak.
        $jumlahBonus = ($s['utama_kedua'] === 'bonus' ? 1 : 0) + (int) in_array('bonus', $s['kecil'], true);
        expect($jumlahBonus)->toBe($adaBonus ? 1 : 0);

        // Tidak ada kartu yang dipakai dua kali.
        expect($s['kecil'])->toBe(array_values(array_unique($s['kecil'])))
            ->and(in_array($s['utama_kedua'], $s['kecil'], true))->toBeFalse();
    }
});

it('layar task menyimpan keadaannya di alamat halaman', function () {
    // Tanpa #[Url], hasil saringan tidak bisa dikirim ke orang lain dan hilang
    // tiap kali halaman dimuat ulang.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    foreach (["as: 'q'", "as: 'status'", "as: 'orang'", "as: 'kategori'", "as: 'arah'",
        "as: 'urut'", "as: 'hal'", "as: 'tampilan'", "as: 'tutup'"] as $alias) {
        expect($sumber)->toContain($alias);
    }

    // `except` menjaga alamatnya pendek: nilai bawaan tidak ikut ditulis.
    expect($sumber)->toContain("except: 'semua'")
        ->and($sumber)->toContain('except: 1');
});

it('grafik aktivitas ikut saringan yang sedang aktif', function () {
    // dataAktivitas() dulu hanya menghormati tahun: menyaring "Penerima: X"
    // lalu berpindah ke tab Aktivitas menampilkan aktivitas SEMUA orang tanpa
    // satu pun tanda — angkanya benar untuk pertanyaan yang tidak diajukan
    // siapa pun. Diukur sesudahnya: 48 task jadi 20 saat disaring.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    // Satu rantai saringan, dipakai dua tempat.
    expect(substr_count($sumber, '$this->saring('))->toBeGreaterThanOrEqual(2)
        // Status dilewati di grafik: ia memang hanya tentang yang sudah selesai.
        ->and($sumber)->toContain('denganStatus: false');
});

it('aksi massal memeriksa ulang kelayakan tiap task di server', function () {
    // Centangnya datang dari peramban; satu-satunya yang tahu apakah sebuah
    // task boleh ditutup atau dihapus adalah server.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($sumber)->toContain('public function selesaikanTerpilih')
        ->and($sumber)->toContain("->where('user_id', auth()->id())")
        ->and($sumber)->toContain('if ($t->isLocked())')
        // Hapus massal memakai aturan hak kelola yang sama dengan hapus satuan.
        ->and($sumber)->toContain('$anggota->contains(fn ($t) => ! $this->bolehKelolaTask($t))')
        // Pilihan dibersihkan saat saringan berubah: tanpa itu, baris yang
        // tercentang lalu tersaring keluar tetap ikut terkena.
        ->and($sumber)->toContain('$this->bersihkanPilihan();');
});

it('checklist tidak ikut menentukan bobot maupun poin', function () {
    // Bobot adalah penilaian PEMBERI atas beratnya pekerjaan; checklist adalah
    // cara PENERIMA memecahnya. Kalau centang menambah poin, siapa pun bisa
    // menaikkan bonusnya sendiri dengan memecah langkah lebih halus.
    $model = file_get_contents(app_path('Models/TaskChecklist.php'));
    $komponen = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($model)->toContain('TIDAK ikut menentukan bobot maupun poin')
        // poinTaskSaya hanya membaca bobot task, tidak pernah checklist.
        ->and($komponen)->toContain('$poin = $t->bobotPoin();')
        ->and($komponen)->not->toContain('checklists->where(\'selesai\', true)->count() * ');
});

it('task berulang punya penanda anti-ganda', function () {
    // Penjadwal berjalan tiap hari; tanpa penanda, satu task berulang akan
    // disalin berkali-kali dalam sehari.
    $perintah = file_get_contents(app_path('Console/Commands/SalinTaskBerulang.php'));
    $jadwal = file_get_contents(base_path('routes/console.php'));

    expect($perintah)->toContain('ulang_terakhir_at')
        ->and($perintah)->toContain('addMonthNoOverflow')
        // group_id baru: salinan adalah pekerjaan tersendiri; ikut grup induknya
        // akan memunculkan komentar periode lalu di task baru.
        ->and($perintah)->toContain("'group_id' => Str::uuid(),")
        ->and($jadwal)->toContain('tasks:salin-berulang');
});

it('riwayat task tidak pernah menggagalkan perubahannya sendiri', function () {
    // Riwayat adalah catatan pinggir. Menolak menyelesaikan task karena
    // catatannya gagal ditulis adalah pertukaran yang salah.
    $model = file_get_contents(app_path('Models/Task.php'));

    expect($model)->toContain('public function catat(')
        ->and($model)->toContain('} catch (\Throwable $e) {');
});

it('hak kelola task punya dua jalur, dan penolakannya dikatakan terus terang', function () {
    // Task yang dibuat dari layar Penyelesaian Task ber-assigned_by NULL, dan
    // NULL tidak pernah cocok dengan whereIn(). Akibatnya seluruh task semacam
    // itu tidak bisa dihapus SIAPA PUN — termasuk administrator — sementara
    // tombolnya memang tidak pernah muncul, jadi tidak ada yang menyadari.
    // Di basis data ini: 71 dari 71 task ber-assigned_by NULL.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($sumber)->toContain('public function bolehKelolaTask(?Task $task): bool')
        // Jalur 1: pemegang manage_task — izin yang sama yang membuka layar
        // Penyelesaian Task.
        ->and($sumber)->toContain("auth()->user()?->hasPermission('manage_task')")
        // Jalur 2: pemberinya, atau atasan pemberinya.
        ->and($sumber)->toContain('in_array($task->assigned_by, $this->manageableGiverIds(), true)')
        // Memberi task juga terbuka bagi manage_task walau tanpa bawahan:
        // canAssignTask() mensyaratkan bawahan, dan itu menutup administrator
        // yang memang tidak ada di dalam struktur.
        ->and($sumber)->toContain('public function bolehBeriTask(): bool');

    // Versi lama selalu menjawab "Task dihapus." walau kuerinya menghapus nol
    // baris. Sekarang penolakannya dikatakan.
    expect($sumber)->toContain("message: 'Anda tidak berhak menghapus task ini.'");

    // Tampilan memakai ATURAN YANG SAMA, lewat bendera yang dikirim komponen —
    // bukan menyalin syaratnya sendiri dan menyimpang diam-diam.
    $tabel = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-tabel.blade.php'));
    $layar = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));

    expect($tabel)->toContain('$bolehKelolaSemua')
        ->and($layar)->toContain('$bolehKelolaSemua')
        ->and($sumber)->toContain("'bolehKelolaSemua' =>");
});

it('langkah bisa disiapkan sejak task dibuat, dan tidak pernah menggandakan', function () {
    // Tiap penerima mendapat SALINANNYA SENDIRI — satu orang mencentang
    // langkahnya tidak boleh ikut mencentang milik orang lain.
    //
    // Diuji lewat transaksi yang di-rollback: satu task ke dua penerima,
    // masing-masing menerima dua langkah yang sama; menyimpan ulang dengan
    // langkah yang sudah ada tidak menambah apa pun, langkah baru masuk ke
    // keduanya.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($sumber)->toContain('public array $t_langkah = [];')
        ->and($sumber)->toContain('protected function pasangLangkah(Task $task): void')
        // Hanya MENAMBAH: menyimpan ulang jendela edit tidak boleh menggandakan
        // daftar milik orang lain.
        ->and($sumber)->toContain('if (in_array(mb_strtolower($teks), $sudahAda, true)) {')
        // Dipasang di dua jalur: saat dibuat dan saat diperbarui.
        ->and(substr_count($sumber, '$this->pasangLangkah('))->toBe(2);

    // Langkah yang SUDAH ada ditampilkan sebagai keterangan, bukan daftar yang
    // bisa disunting: menghapusnya dari sini berarti menghapusnya dari semua
    // penerima termasuk yang sudah mencentangnya, dan catatan bahwa ia sudah
    // mengerjakannya ikut hilang.
    $tampilan = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));

    expect($tampilan)->toContain('ts-langkah-ada')
        ->and($tampilan)->toContain('Menghapus langkah');
});

it('menyimpan grup task tidak lagi gagal diam-diam pada task tanpa pemberi', function () {
    // Jebakan yang sama dengan hapus: updateGroup() menyaring
    // whereIn('assigned_by', …), dan task dari layar Penyelesaian Task
    // ber-assigned_by NULL — grup semacam itu gagal disimpan tanpa satu pun
    // pesan.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($sumber)->toContain("\$existing = Task::visibleTo()->where('group_id', \$this->editingGroupId)->get();")
        ->and($sumber)->not->toContain("->whereIn('assigned_by', \$this->manageableGiverIds())");
});

it('medan bawah jendela beri task disusun dua-dua, tanpa kolom kosong', function () {
    // Sebelum ada "Ulangi", jumlahnya tiga dan pas satu baris. Begitu jadi
    // empat, yang keempat (Deadline Selesai) turun sendirian dan menyisakan
    // dua pertiga baris kosong di sebelahnya. Diukur sesudahnya pada lebar
    // 1440 & 768: dua baris berisi dua medan selebar sama, tanpa sisa.
    $tampilan = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));

    // Keempatnya seperdua lebar, bukan sepertiga.
    foreach (['Ulangi', 'Bobot', 'Deadline Mulai', 'Deadline Selesai'] as $label) {
        $i = strpos($tampilan, '>'.$label.'</label>');
        expect($i)->not->toBeFalse();

        $pembuka = strrpos(substr($tampilan, 0, $i), '<div class="col-md-');
        expect(substr($tampilan, $pembuka, 22))->toContain('col-md-6');
    }
});

it('rantai task berulang tidak pernah bercabang', function () {
    // Versi pertamanya membuat induk DAN salinannya sama-sama berulang, dan
    // tiap salinan ikut beranak: sekali jalan jadi 2, besoknya 4, lalu 8, 16,
    // 32 — diuji persis begitu. Perintahnya berjalan otomatis tiap pagi, jadi
    // sebulan tanpa ada yang melihat sudah cukup untuk membanjiri tabelnya.
    //
    // Sekarang tongkatnya PINDAH: sesudah menyalin, induknya berhenti
    // berulang. Pada satu saat hanya ada SATU task di rantai itu yang
    // berulang, dan ia selalu yang terbaru — yang juga task yang dilihat orang
    // kalau ingin menghentikan rantainya. Diukur ulang: 2, 3, 4, 5, 6, lalu
    // berhenti saat menyusul hari ini.
    $perintah = file_get_contents(app_path('Console/Commands/SalinTaskBerulang.php'));

    expect($perintah)->toContain("\$t->forceFill(['ulang' => 'tidak', 'ulang_terakhir_at' => \$berikutnya])->save();")
        ->and($perintah)->toContain('TONGKAT ESTAFETNYA PINDAH');
});

it('riwayat mencatat tenggat yang digeser dan task yang dibuka kembali', function () {
    // Dua kejadian yang paling sering disengketakan saat bonus dihitung.
    // Kalimatnya sudah disiapkan model sejak awal, tetapi tidak ada yang
    // pernah menuliskannya. Diuji lewat transaksi yang di-rollback:
    // "Tenggat digeser 15 Sep 2026 → 17 Okt 2026" dan "Dibuka kembali untuk
    // revisi — <alasannya>".
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($sumber)->toContain("\$t->catat('tenggat', \$tenggatLama, \$tenggatBaru);")
        ->and($sumber)->toContain("'dibuka-kembali',")
        // Dibaca SEBELUM update: sesudahnya sudah tertimpa, dan riwayatnya
        // akan mencatat "dari X ke X".
        ->and(strpos($sumber, '$tenggatLama = $t->deadline_selesai'))
        ->toBeLessThan(strpos($sumber, '$t->update($shared);'));
});

it('pilih semua hanya menyentuh halaman yang sedang terlihat', function () {
    // Mencentang 300 baris yang tidak terlihat lalu menekan Hapus bukan
    // sesuatu yang orang maksudkan.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));
    $tabel = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-tabel.blade.php'));

    expect($sumber)->toContain('public function alihkanSemuaHalaman(): void')
        ->and($sumber)->toContain('$this->gidHalaman = array_map(')
        // Setengah-tercentang tidak punya atribut HTML — dipasang lewat properti.
        ->and($tabel)->toContain('$el.indeterminate');
});

it('penerima dan pemberi bisa diurutkan tanpa fungsi khusus MySQL', function () {
    // Keduanya relasi; subkueri nama berjalan di MySQL maupun SQLite, jadi
    // pengujian tetap menguji hal yang sama dengan yang dijalankan.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));

    expect($sumber)->toContain("'penerima', 'pemberi'];")
        ->and($sumber)->toContain("User::select('name')->whereColumn('users.id', 'tasks.user_id')")
        ->and($sumber)->toContain("User::select('name')->whereColumn('users.id', 'tasks.assigned_by')");
});

it('badge task di sidebar hanya menghitung yang mendesak', function () {
    // Badge yang selalu menampilkan angka berhenti dibaca — yang tidak pernah
    // kosong tidak pernah berarti.
    $sidebar = file_get_contents(resource_path('views/livewire/layout/sidebar.blade.php'));

    expect($sidebar)->toContain('$taskMendesak')
        ->and($sidebar)->toContain("->whereDate('deadline_selesai', '<=', today())")
        ->and($sidebar)->toContain("->where('progress', '!=', 'selesai')");

    // Angkanya ikut segar saat task dimulai / diselesaikan.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));
    expect(substr_count($sumber, "dispatch('sidebar-badge-updated')"))->toBeGreaterThanOrEqual(3);
});

it('sub-baris grup menunjukkan siapa sudah sampai mana', function () {
    // Dua orang yang sama-sama "dikerjakan" bisa berada di langkah 1 dan 5.
    // Komentar sengaja TIDAK dihitung per orang: diskusinya satu untuk grup,
    // jadi angkanya akan sama di tiap baris.
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));
    $tabel = file_get_contents(resource_path('views/livewire/pages/admin/task/partials/task-tabel.blade.php'));

    expect($sumber)->toContain("'checklists as checklists_selesai_count'")
        // Lampiran perintah disalin ke tiap penerima — yang per orang hanya HASIL-nya.
        ->and($sumber)->toContain("'attachments as hasil_count'")
        ->and($tabel)->toContain('$m->checklists_selesai_count')
        ->and($tabel)->toContain('$m->hasil_count');
});

it('jendela detail punya alat urut langkah, saringan riwayat, dan tanda berulang', function () {
    $sumber = file_get_contents(app_path('Livewire/Pages/Admin/Task/TaskSayaList.php'));
    $layar = file_get_contents(resource_path('views/livewire/pages/admin/task/task-saya-list.blade.php'));

    expect($sumber)->toContain('public function geserChecklist(string $id, string $arah): void')
        ->and($layar)->toContain("geserChecklist('{{ \$langkah->id }}', 'naik')")
        // Disaring di peramban: daftarnya sudah ada di halaman.
        ->and($layar)->toContain("x-data=\"{ saring: 'semua' }\"")
        ->and($layar)->toContain('Salinan berikutnya');
});

it('semua layar pemesanan rsc memakai bahasa rupa dasbor', function () {
    $dir = resource_path('views/livewire/pages/admin/pemesanan-r-s-c/');

    foreach (['pemesananrsc-list', 'pemesananrsc-detail', 'pemesananrsc-create', 'pemesananrsc-edit'] as $n) {
        $isi = file_get_contents($dir.$n.'.blade.php');

        expect($isi)->toContain("@include('livewire.pages.admin.partials.dasbor-gaya')")
            ->and($isi)->toContain('class="dsb-hero"')
            // Kartu di dalam kartu dan judul bergradasi dari versi lama.
            ->and($isi)->not->toContain('gradient-text')
            ->and($isi)->not->toContain('fixed-header-card')
            // Seperti Dasbor & Task Saya: baris tanggal, bukan breadcrumb.
            ->and($isi)->not->toContain('x-breadcrumb')
            ->and($isi)->toContain("translatedFormat('l, d F Y')");
    }

    // Form: markup lama dipertahankan (JS picker bergantung padanya), kulitnya
    // yang diganti ke ikon lembut berwarna.
    $form = file_get_contents($dir.'pemesananrsc-form.blade.php');
    expect($form)->toContain('background: color-mix(in srgb, var(--c) 12%, #fff);')
        ->and($form)->toContain('class="rsc-form-samping"')
        ->and($form)->toContain('window.rscAkunPicker')
        // Kartu akun tambahan: kepala (pilihan, harga, hapus) + kredensial
        // yang melebar sendiri, bukan empat kolom col-md sempit.
        ->and($form)->toContain('class="rsc-akun-card-isi"')
        ->and($form)->not->toContain('<div class="col-md-2">')
        ->and($form)->not->toContain('background: linear-gradient(135deg, #6c63ff, #4e46e5);');
});

it('daftar pemesanan rsc hanya punya satu paginasi dan tanpa jendela wa mati', function () {
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/pemesanan-r-s-c/pemesananrsc-list.blade.php'));

    expect(substr_count($isi, '->links('))->toBe(1)
        // Jendela WA lama tidak pernah bisa dibuka, tetapi membawa isian
        // password akun di HTML setiap halaman.
        ->and($isi)->not->toContain('kirimWa');
});

it('detail pemesanan rsc tidak menaruh data batch di snapshot livewire', function () {
    $kelas = new ReflectionClass(\App\Livewire\Pages\Admin\PemesananRSC\PemesananrscDetail::class);
    $publik = collect($kelas->getProperties(ReflectionProperty::IS_PUBLIC))
        ->filter(fn ($p) => $p->class === $kelas->getName())
        ->map->getName()->sort()->values()->all();

    // Username & password akun dulu ikut terkirim sebagai properti publik.
    expect($publik)->toBe(['batch_camp', 'nama_camp'])
        ->and($kelas->hasMethod('unduhInvoice'))->toBeTrue()
        ->and($kelas->hasMethod('unduhExcel'))->toBeTrue();

    $isi = file_get_contents(resource_path('views/livewire/pages/admin/pemesanan-r-s-c/pemesananrsc-detail.blade.php'));
    expect($isi)->toContain("->locale('id')->translatedFormat")
        ->and($isi)->toContain('wire:click="unduhInvoice"');
});
