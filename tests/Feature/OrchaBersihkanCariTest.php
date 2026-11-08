<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function adminCari(): User
{
    $role = Role::create(['name' => 'uji-cari-'.uniqid(), 'description' => 'Peran untuk uji cari']);

    /*
     | Dua izin, bukan satu. Catatan "daftar sedang disaring" menyangkut
     | tombol Unduh Daftar Peserta, dan tombol itu hanya ada bagi pemegang
     | view_orcha_kesehatan — catatan tentang tombol yang tidak terlihat
     | hanya menambah kalimat yang tidak bisa ditindaklanjuti.
     */
    foreach (['akses_orcha', 'view_orcha_kesehatan'] as $nama) {
        $permission = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );
        $role->permissions()->attach($permission->id);
    }

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

    Http::fake([
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['baru' => 'Baru', 'lunas' => 'Lunas'],
            'keperluan_kontak' => ['umum' => 'Umum'],
            'paket_wisata' => [['id' => 1, 'nama' => 'Open Trip Banyuwangi',
                'kategori' => 'open_trip', 'tanggal_berangkat' => '2026-09-10']],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 0, 'halaman_terakhir' => 1]]),
    ]);
});

test('tombol pengosong hanya muncul saat kotak cari ada isinya', function () {
    $halaman = Livewire::actingAs(adminCari())->test(OrchaPendaftaranList::class);

    // Tombol yang selalu ada tetapi tidak selalu berguna hanya menambah benda
    // yang harus diabaikan mata.
    $halaman->assertDontSee('Kosongkan pencarian')
        ->set('cari', 'budi')
        ->assertSee('Kosongkan pencarian');
});

test('mengosongkan pencarian mengembalikan daftar ke halaman satu', function () {
    Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('cari', 'budi')
        ->call('keHalaman', 3)
        ->assertSet('halaman', 3)
        ->call('bersihkanCari')
        ->assertSet('cari', '')
        ->assertSet('halaman', 1);
});

test('bersihkan saringan mengosongkan ketiganya sekaligus', function () {
    Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('cari', 'budi')
        ->set('filterStatus', 'lunas')
        ->set('filterPaket', '1')
        // Tombolnya duduk di baris saringan, bukan di bawahnya. Kalimatnya
        // menyebut AKIBATNYA ("Tampilkan semua lagi"), bukan tindakannya
        // ("Bersihkan") — admin yang awam membaca yang akan terjadi, bukan
        // nama operasinya.
        ->assertSee('Tampilkan semua lagi')
        ->call('bersihkanSaringan')
        ->assertSet('cari', '')
        ->assertSet('filterStatus', '')
        // Saringan ketiga milik halaman ini ikut — tombol yang menyisakan satu
        // saringan hidup justru membingungkan.
        ->assertSet('filterPaket', '')
        ->assertDontSee('Tampilkan semua lagi');
});

test('kotak cari yang sama tersedia di daftar orcha lainnya', function () {
    // Partial bersama: sekali diperbaiki, seluruh daftar ikut.
    Livewire::actingAs(adminCari())
        ->test(OrchaPesanList::class)
        ->set('cari', 'budi')
        ->assertSee('Kosongkan pencarian')
        ->call('bersihkanCari')
        ->assertSet('cari', '');
});

test('pemilih saringan menandai pilihannya di markup, bukan hanya di ingatan komponen', function () {
    $halaman = Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '1')
        ->set('filterStatus', 'lunas');

    /*
     | Tanpa penanda ini, "Tampilkan semua lagi" mengosongkan keadaan komponen
     | tetapi kotak pilihannya tetap memajang paket lama — layar yang berbohong
     | tentang keadaannya sendiri.
     |
     | Status TIDAK lagi berupa kotak pilih: ia pindah ke tab. Penanda yang
     | setara di sana aria-pressed, dan itu diuji di berkas tab-nya sendiri.
     */
    $halaman->assertSeeHtml('value="1" selected')
        ->call('bersihkanSaringan')
        ->assertDontSeeHtml('value="1" selected');
});

test('pengosong duduk di baris saringan, bukan di bawahnya', function () {
    /*
     | Tombol yang jauh dari benda yang diubahnya menuntut mata berpindah dua
     | kali untuk mengerti hubungannya. Sebelumnya ia berbaris sendiri di
     | bawah, terpisah dari tiga kotak yang justru jadi urusannya.
     |
     | Diperiksa lewat URUTAN: pengosongnya harus muncul sesudah kotak status
     | dan SEBELUM tombol Daftarkan — di dalam baris saringan, bukan sesudah
     | seluruh barisnya selesai.
     */
    Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('filterStatus', 'lunas')
        // Sesudah kotak keberangkatan, di dalam kartu saringan yang sama.
        ->assertSeeInOrder(['Semua keberangkatan', 'Tampilkan semua lagi']);
});

test('keterangan saringan tetap disebut walau tombolnya sudah naik', function () {
    /*
     | Akibat yang tidak terlihat dari layar: manifes yang diunduh mengikuti
     | saringan yang sedang hidup. Tour leader yang membawa manifes hasil
     | saringan lupa akan berdiri di parkiran dengan daftar yang kurang satu
     | rombongan.
     */
    Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('filterStatus', 'lunas')
        // Kalimatnya dibuat lebih terang: menyebut NAMA tombolnya dan apa
        // yang harus dilakukan, bukan sekadar "mengikuti saringan ini".
        ->assertSee('ikut tersaring juga');
});

test('bilah saringan tidak kembali ke grid dua belas kolom', function () {
    /*
     | Grid-nya sudah DUA KALI menjatuhkan tombol terakhir ke baris kedua, dan
     | dua kali sebabnya berbeda: sekali karena jumlah kolomnya lewat dua
     | belas, sekali karena ms-auto menyerap sisa ruang jadi margin sementara
     | kolomnya sendiri baru sebelas.
     |
     | Keduanya lolos dari penjaga yang menghitung kolom — penjaga itu sempat
     | hijau sambil tata letaknya patah — dan keduanya baru ketahuan lewat
     | potret layar. Yang dijaga sekarang KEPUTUSANNYA: bilah ini memakai flex,
     | di mana tiap benda menyebut lebar dasarnya sendiri dan boleh menyusut,
     | sehingga tidak ada jatah dua belas yang harus dihitung ulang tiap kali
     | satu tombol ditambahkan.
     |
     | Ini pun tidak membuktikan tata letaknya benar. Yang membuktikannya
     | melihat layarnya.
     */
    $isi = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/pendaftaran/index.blade.php')
    );

    // Hanya bilah saringan — berhenti sebelum kartu tabelnya.
    // Penandanya ikut berubah saat layar ini pindah ke bahasa rupa dasbor:
    // tabelnya kini dibungkus .dsb-tabel-bungkus, bukan .orcha-gulung.
    $bilah = substr($isi, 0, strpos($isi, 'dsb-tabel-bungkus'));

    $this->assertStringNotContainsString('col-lg-', $bilah, implode("\n", [
        'Bilah saringan kembali memakai grid dua belas kolom.',
        '',
        'Grid-nya sudah dua kali menjatuhkan tombol terakhir ke baris kedua,',
        'dengan dua sebab berbeda yang sama-sama lolos dari uji.',
        '',
        'Pakai grid .pd-saring yang kolomnya disebut sendiri, atau flex.',
    ]));
});

test('tombol di bilah saringan sama lebarnya', function () {
    /*
     | Lebarnya mengikuti panjang tulisannya sendiri kalau dibiarkan —
     | "Manifes" keluar 112px sedangkan "Daftarkan" 126px. Bedanya kecil satu
     | per satu, tetapi mata membaca tepi kanan sebuah deretan sebagai satu
     | garis, dan garis yang bergerigi itu yang terlihat.
     |
     | Yang dijaga di sini KETIGANYA memakai penyeragamnya. Satu tombol baru
     | yang lupa memakainya tidak menghasilkan galat apa pun — cuma satu tepi
     | yang meleset, dan itu jenis cacat yang bertahan bertahun-tahun karena
     | tidak pernah cukup mengganggu untuk dilaporkan.
     */
    $bilah = substr(
        file_get_contents(resource_path('views/livewire/pages/admin/orcha/pendaftaran/index.blade.php')),
        0,
        strpos(file_get_contents(resource_path('views/livewire/pages/admin/orcha/pendaftaran/index.blade.php')), 'orcha-gulung')
    );

    preg_match_all('/class="orcha-btn[^"]*"/', $bilah, $cocok);

    $tanpaSeragam = array_values(array_filter(
        $cocok[0],
        fn ($kelas) => ! str_contains($kelas, 'orcha-btn-seragam')
    ));

    $this->assertSame([], $tanpaSeragam, implode("\n", array_merge(
        ['Tombol di bilah saringan tidak memakai penyeragam lebar:'],
        $tanpaSeragam,
        ['', 'Tambahkan orcha-btn-seragam supaya tepi kanannya rata.'],
    )));
});

test('tombol tindakan duduk di kepala halaman, bukan di antara saringan', function () {
    /*
     | Dulu Daftarkan & Unduh berbaris bersama kartu sakelar "Perlu ditagih",
     | dan tingginya harus disamakan tangan supaya tidak terbaca seperti
     | menempel belakangan (39px vs 70px).
     |
     | Sejak layar ini memakai bahasa rupa dasbor, keduanya pindah ke
     | .dsb-hero-aksi — tempat yang sama dengan seluruh layar lain, dengan
     | tinggi yang sudah seragam dari sananya. Sakelarnya sendiri jadi tab.
     |
     | Yang dijaga sekarang LETAKNYA: keduanya harus di kepala, bukan
     | tercecer kembali ke bilah saringan di mana tingginya harus diurus
     | sendiri lagi.
     */
    $isi = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/pendaftaran/index.blade.php')
    );

    $kepala = substr($isi, 0, strpos($isi, 'pt-grup-deret'));

    $this->assertStringContainsString('dsb-hero-aksi', $kepala,
        'Tombol tindakan tidak lagi di kepala halaman.');
    $this->assertStringContainsString('Daftarkan Rombongan', $kepala);
    $this->assertStringContainsString('Unduh Daftar Peserta', $kepala);
});
