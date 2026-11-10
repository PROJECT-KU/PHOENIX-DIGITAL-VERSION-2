<?php

use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanList;
use App\Models\Lowongan;
use Livewire\Livewire;

/*
 | Nama pembantunya berawalan "Daftar" dengan sengaja: `lowonganUji()` sudah
 | dipakai SitusAsthanaTest, dan dua berkas uji yang mendeklarasikan fungsi
 | bernama sama membuat seluruh rangkaian mati dengan "Cannot redeclare" —
 | hijau saat berkasnya dijalankan sendirian, fatal saat dijalankan bersama.
 */

/**
 * Layar Data Lowongan, sesudah disamakan dengan Pesanan Toko.
 *
 * Yang dijaga terutama keadaan KETIGA: lowongan yang masih bertanda aktif
 * tetapi batas lamarannya sudah lewat. Ia berhenti tampil di situs tanpa
 * satu pun tanda di layar ini sebelumnya — dan yang mengiranya masih
 * menarik pelamar menunggu lamaran yang tidak akan datang.
 */
function adminDaftarLowongan(): \App\Models\User
{
    return adminRab(['akses_asthana', 'view_lowongan', 'create_lowongan', 'edit_lowongan', 'delete_lowongan']);
}

function lowonganDaftarUji(array $ubah = []): Lowongan
{
    return Lowongan::create(array_merge([
        'title' => 'Programmer Laravel',
        'slug' => 'programmer-laravel-'.uniqid(),
        'is_active' => 'active',
        'unit' => 'reka',
        'jenis' => 'penuh_waktu',
        'pola_kerja' => 'kantor',
        'lokasi' => 'Yogyakarta',
        'requirements' => 'Uji',
        'descriptions' => 'Uji',
    ], $ubah));
}

it('memisahkan lewat batas dari yang sedang dibuka', function () {
    $dibuka = lowonganDaftarUji(['title' => 'Desainer Grafis', 'batas_lamaran' => now()->addDays(5)]);
    $lewat = lowonganDaftarUji(['title' => 'Admin Gudang', 'batas_lamaran' => now()->subDays(3)]);
    $ditutup = lowonganDaftarUji(['title' => 'Kurir', 'is_active' => 'non-active']);

    $komponen = Livewire::actingAs(adminDaftarLowongan())->test(LowonganPekerjaanList::class);

    $jumlah = $komponen->instance()->jumlahTab();

    expect($jumlah['semua'])->toBe(3)
        ->and($jumlah['terbuka'])->toBe(1)
        ->and($jumlah['lewat_batas'])->toBe(1)
        ->and($jumlah['nonaktif'])->toBe(1);

    // Tab "lewat batas" hanya berisi yang tanggalnya sudah lewat.
    $komponen->call('setTab', 'lewat_batas')
        ->assertSee('Admin Gudang')
        ->assertDontSee('Desainer Grafis')
        ->assertDontSee('Kurir');

    /*
     | Lowongan yang sudah ditutup TIDAK boleh ikut tab "lewat batas",
     | walau tanggalnya juga lewat: ia memang sengaja ditutup, dan
     | mencampurnya membuat daftar pekerjaan berisi hal yang sudah beres.
     */
    lowonganDaftarUji(['title' => 'Satpam', 'is_active' => 'non-active', 'batas_lamaran' => now()->subDays(9)]);
    expect($komponen->instance()->jumlahTab()['lewat_batas'])->toBe(1);
});

it('angka tab mengikuti pencarian yang sedang hidup', function () {
    lowonganDaftarUji(['title' => 'Desainer Grafis', 'batas_lamaran' => now()->addDays(5)]);
    lowonganDaftarUji(['title' => 'Admin Gudang', 'batas_lamaran' => now()->addDays(5)]);

    /*
     | Angka tab yang mengabaikan pencarian menjanjikan isi yang tidak ada
     | saat ditekan: "Masih dibuka 2" lalu daftarnya cuma satu baris.
     */
    $komponen = Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->set('search', 'Gudang');

    expect($komponen->instance()->jumlahTab()['terbuka'])->toBe(1);
});

it('menyebut berapa orang sudah melamar', function () {
    $lowongan = lowonganDaftarUji(['title' => 'Desainer Grafis']);

    \App\Models\JobApplication::create([
        'job_id' => $lowongan->id, 'name' => 'Budi', 'email' => 'budi@contoh.test',
        'phone' => '081234567890', 'cv_path' => 'uji.pdf',
    ]);

    // Pertanyaan pertama yang dibawa orang saat membuka layar ini, dan dulu
    // tidak terjawab di sini sama sekali.
    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertSee('1 orang');
});

it('saringan perusahaan mempersempit daftarnya', function () {
    lowonganDaftarUji(['title' => 'Desainer Grafis', 'unit' => 'reka']);
    lowonganDaftarUji(['title' => 'Tour Leader', 'unit' => 'orcha']);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->set('filterUnit', 'orcha')
        ->assertSee('Tour Leader')
        ->assertDontSee('Desainer Grafis');
});

it('membersihkan saringan mengembalikan tab ke semua', function () {
    lowonganDaftarUji(['title' => 'Desainer Grafis']);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->set('search', 'zzz')
        ->set('filterUnit', 'orcha')
        ->call('setTab', 'nonaktif')
        ->call('bersihkanSaringan')
        ->assertSet('search', '')
        ->assertSet('filterUnit', '')
        ->assertSet('tabAktif', 'semua')
        ->assertSee('Desainer Grafis');
});

it('kelas rupa yang dipakai layar ini punya definisinya', function () {
    /*
     | Penjaga "rupa hilang tanpa galat".
     |
     | .pt-daftar-kepala sempat HANYA ada di berkas gaya Pesanan Toko,
     | sementara Pendaftaran Trip dan layar ini memakainya lewat partial
     | kartu tab. Keduanya menggambarnya tanpa gaya, halamannya tetap 200,
     | dan tidak ada satu pun tanda bahwa rupanya hilang.
     */
    $isi = Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->html();

    foreach (['pt-daftar-kepala', 'pt-kartu-tab', 'dsb-tabel', 'dsb-kosong'] as $kelas) {
        expect($isi)->toContain('.'.$kelas);
    }
});

/* =============== TINDAKAN DI BARISNYA =============== */

it('menutup lowongan lewat batas sekali tekan', function () {
    /*
     | Lowongan lewat batas sudah BERHENTI tampil di situs. Membuka layar
     | ubah hanya untuk satu kotak pilih adalah lima langkah untuk keputusan
     | yang sudah jelas — lalu dibiarkan menumpuk karena repot.
     */
    $lowongan = lowonganDaftarUji(['batas_lamaran' => now()->subDays(3)]);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->call('tutup', $lowongan->id);

    expect($lowongan->fresh()->is_active)->toBe('non-active');
});

it('tombol tutup hanya tergambar untuk yang lewat batas', function () {
    // Pada lowongan yang sedang dibuka, "tutup" adalah keputusan yang belum
    // tentu benar — dan tombol sekali tekan untuk keputusan yang belum jelas
    // adalah tombol yang salah tekan.
    lowonganDaftarUji(['title' => 'Masih Dibuka', 'batas_lamaran' => now()->addDays(10)]);

    /*
     | Diperiksa lewat MARKAH tombolnya, bukan nama kelasnya saja: nama itu
     | juga muncul di penangan JS di halaman yang sama (.closest(...)), jadi
     | assertDontSee atas namanya merah walau tidak ada satu tombol pun
     | tergambar. Jebakan yang sama dengan teks CSS di dalam <style>.
     */
    $markah = 'dsb-tombol is-lembut tutup-lowongan-btn';

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertDontSee($markah, false);

    lowonganDaftarUji(['title' => 'Sudah Lewat', 'batas_lamaran' => now()->subDays(2)]);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertSee($markah, false);
});

it('menolak menutup tanpa izin ubah', function () {
    $lowongan = lowonganDaftarUji(['batas_lamaran' => now()->subDays(3)]);
    $tanpaIzin = adminRab(['akses_asthana', 'view_lowongan']);

    Livewire::actingAs($tanpaIzin)
        ->test(LowonganPekerjaanList::class)
        ->call('tutup', $lowongan->id);

    // Tombolnya memang tidak tergambar, tetapi layar boleh dilewati.
    expect($lowongan->fresh()->is_active)->toBe('active');
});

it('menggandakan lowongan jadi salinan yang BELUM tayang', function () {
    /*
     | Salinan yang langsung hidup berarti dua lowongan kembar muncul
     | bersamaan di situs sebelum sempat disunting.
     */
    $asli = lowonganDaftarUji([
        'title' => 'Programmer Laravel',
        'batas_lamaran' => now()->addDays(10),
        'requirements' => 'Minimal 2 tahun pengalaman',
    ]);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->call('gandakan', $asli->id)
        ->assertRedirect();

    $salinan = Lowongan::where('title', 'Programmer Laravel (salinan)')->first();

    expect($salinan)->not->toBeNull()
        ->and($salinan->is_active)->toBe('non-active')
        ->and($salinan->batas_lamaran)->toBeNull()
        // Isinya ikut, karena itu justru gunanya.
        ->and($salinan->requirements)->toBe('Minimal 2 tahun pengalaman')
        // Slug WAJIB beda: slug kembar membuat tautan situs menunjuk yang salah.
        ->and($salinan->slug)->not->toBe($asli->slug);
});

it('angka pelamar menautkan ke pelamar lowongan itu saja', function () {
    $lowongan = lowonganDaftarUji(['title' => 'Desainer Grafis']);
    \App\Models\JobApplication::create([
        'job_id' => $lowongan->id, 'name' => 'Budi', 'email' => 'budi@contoh.test',
        'phone' => '081234567890', 'cv_path' => 'uji.pdf',
    ]);

    // Yang membaca "1 orang" berikutnya mau membukanya.
    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertSeeHtml(route('admin.pelamar.index', ['lowongan' => $lowongan->id]));
});

it('memisahkan pelamar yang belum dibuka dari totalnya', function () {
    /*
     | "7 pelamar" bisa berarti tujuh berkas menumpuk belum dibaca, atau
     | tujuh yang sudah tuntas diproses — dua keadaan yang menuntut hal
     | berbeda dari admin, dan satu angka tidak bisa membedakannya.
     */
    $lowongan = lowonganDaftarUji(['title' => 'Desainer Grafis']);

    foreach (['baru', 'baru', 'ditolak'] as $i => $status) {
        \App\Models\JobApplication::create([
            'job_id' => $lowongan->id, 'name' => 'Pelamar '.$i,
            'email' => "p{$i}@contoh.test", 'phone' => '081234567890',
            'cv_path' => 'uji.pdf', 'status' => $status,
        ]);
    }

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertSee('3 orang')
        ->assertSee('2 belum dibuka');
});

it('menautkan lowongan yang tayang ke halaman situsnya', function () {
    /*
     | Rutenya sudah lama ada (meta OG lalu dialihkan ke situs), tetapi tidak
     | ada satu layar admin pun yang menautkannya — jadi untuk menyebarkan
     | lowongan ke WhatsApp, tautannya harus dirakit sendiri.
     */
    $tayang = lowonganDaftarUji(['title' => 'Masih Dibuka', 'batas_lamaran' => now()->addDays(10)]);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertSeeHtml(route('situs.lowongan', $tayang->slug));
});

it('tidak menawarkan bagikan untuk lowongan yang tidak tampil di situs', function () {
    // Tautan yang dibagikan akan mendarat di daftar lowongan, bukan di
    // posisinya — dan yang membagikannya tidak tahu sampai ada yang mengeluh.
    $ditutup = lowonganDaftarUji(['title' => 'Sudah Ditutup', 'is_active' => 'non-active']);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->assertDontSeeHtml(route('situs.lowongan', $ditutup->slug));
});

/* =============== BORANG =============== */

it('borang ubah menggambar nilai yang tersimpan', function () {
    /*
     | wire:model sendiri TIDAK menggambar nilainya di HTML dari peladen.
     |
     | Tanpa value= eksplisit, layar Ubah membuka isian kosong — dan isian
     | kosong di layar ubah terbaca sebagai "lowongan ini memang belum punya
     | judul", lalu disimpan begitu saja. Pola yang sama sudah dipakai layar
     | peserta Orcha dan borang tulisan Asthana.
     */
    $lowongan = lowonganDaftarUji([
        'title' => 'Programmer Laravel',
        'lokasi' => 'Yogyakarta',
        'gaji' => 'Rp 5-8 juta',
        'jumlah_posisi' => 2,
        'batas_lamaran' => now()->addDays(10)->toDateString(),
    ]);

    $html = Livewire::actingAs(adminDaftarLowongan())
        ->test(\App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanForm::class, ['lowongan' => $lowongan])
        ->html();

    expect($html)
        ->toContain('value="Programmer Laravel"')
        ->toContain('value="Yogyakarta"')
        ->toContain('value="Rp 5-8 juta"')
        ->toContain('value="2"')
        ->toContain('value="'.now()->addDays(10)->format('Y-m-d').'"')
        // Kotak pilih memakai @selected, bukan value=.
        ->toContain('value="reka" selected');
});

it('borang tidak membungkus dirinya dengan .dsb lagi', function () {
    /*
     | .dsb BERSARANG menggandakan padding sampingnya.
     |
     | Borang ini selalu dipasang di dalam halaman create/edit yang
     | pembungkusnya sudah ber-.dsb. Dipasang dua kali, kartu borang menjorok
     | ~18px lebih sempit daripada kartu di layar daftar — dan dua layar yang
     | seharusnya sebaris tidak pernah segaris. Tidak ada galat apa pun;
     | ketahuan hanya kalau ditempelkan berdampingan.
     */
    $lowongan = lowonganDaftarUji();

    $html = Livewire::actingAs(adminDaftarLowongan())
        ->test(\App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanForm::class, ['lowongan' => $lowongan])
        ->html();

    expect($html)->not->toContain('class="dsb lwg-borang"');
});

it('tiap rak di layar daftar punya jarak ke rak berikutnya', function () {
    /*
     | .dsb-rak sendiri tidak punya margin bawah — jaraknya datang dari
     | .dsb-bagian. Rak pertama yang kehilangan kelas itu membuat kartu
     | saringan dan kartu daftar BERSENTUHAN: diukur di peramban, 553px dan
     | 553px. Halaman tetap 200, jadi tidak ada satu pun tanda.
     */
    $berkas = file_get_contents(resource_path(
        'views/livewire/pages/admin/lowongan-pekerjaan/lowongan-pekerjaan-list.blade.php'
    ));

    preg_match_all('/<div class="dsb-rak([^"]*)"/', $berkas, $cocok);

    expect($cocok[1])->not->toBeEmpty();

    foreach ($cocok[1] as $sisa) {
        expect($sisa)->toContain('dsb-bagian');
    }
});

/* =============== SINKRON DENGAN SITUS =============== */

it('membetulkan judul TIDAK mengubah slug lowongan yang sudah tayang', function () {
    /*
     | Slug adalah ALAMAT lowongan di situs.
     |
     | updatingTitle() merakit ulang slug tiap kali judul diketik — termasuk
     | saat MENGUBAH lowongan yang sudah tayang. Admin yang membetulkan satu
     | huruf salah ketik ("Programer" → "Programmer") diam-diam memindahkan
     | alamatnya, dan setiap tautan yang sudah terlanjur disebar — WhatsApp,
     | LinkedIn, bio Instagram, indeks Google — menunjuk slug yang tidak ada
     | lagi. /lowongan/{slug} lalu melemparkan orangnya ke daftar lowongan,
     | dan yang membagikan tidak pernah tahu.
     |
     | Pada lowongan BARU slug memang harus ikut judulnya; yang tidak boleh
     | adalah mengubahnya sesudah ia punya alamat.
     */
    $lowongan = lowonganDaftarUji(['title' => 'Programer Laravel']);
    $slugAsli = $lowongan->slug;

    Livewire::actingAs(adminDaftarLowongan())
        ->test(\App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanForm::class, ['lowongan' => $lowongan])
        ->set('title', 'Programmer Laravel')
        ->call('save');

    $sesudah = $lowongan->fresh();
    expect($sesudah->title)->toBe('Programmer Laravel')
        ->and($sesudah->slug)->toBe($slugAsli);
});

it('lowongan baru tetap mendapat slug dari judulnya', function () {
    // Penjaga arah sebaliknya: kalau slug dibekukan untuk semua keadaan,
    // lowongan baru lahir tanpa alamat.
    Livewire::actingAs(adminDaftarLowongan())
        ->test(\App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanForm::class)
        ->set('title', 'Desainer Grafis')
        ->set('isActive', 'active')
        ->set('unit', 'reka')
        ->set('descriptions', 'Uji')
        ->set('requirements', 'Uji')
        ->call('save');

    expect(Lowongan::where('title', 'Desainer Grafis')->value('slug'))->toBe('desainer-grafis');
});
