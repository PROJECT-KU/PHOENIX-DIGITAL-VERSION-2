<?php

use App\Livewire\Pages\Admin\Surat\KlasifikasiList;
use App\Livewire\Pages\Admin\Surat\SuratDashboard;
use App\Livewire\Pages\Admin\Surat\SuratDetail;
use App\Livewire\Pages\Admin\Surat\SuratForm;
use App\Livewire\Pages\Admin\Surat\SuratList;
use App\Models\Surat\Surat;
use App\Models\Surat\SuratDisposisi;
use App\Models\Surat\SuratKlasifikasi;
use App\Support\Surat\ModeSurat;
use Livewire\Livewire;

/**
 * Arsip Surat: buku agenda surat masuk & keluar.
 *
 * Yang dijaga di sini bukan "layarnya terbuka", melainkan hal-hal yang kalau
 * salah akan merusak catatan resmi tanpa suara: nomor agenda yang dipakai dua
 * kali, masa simpan yang berubah sendiri, klasifikasi yang terlepas dari
 * surat lama, dan pindaian surat rahasia yang bisa diambil lewat alamat surat
 * lain.
 */
function adminSurat(array $izin = ['akses_arsip_surat', 'view_surat', 'view_surat_rahasia', 'create_surat', 'edit_surat', 'delete_surat', 'kelola_klasifikasi_surat']): \App\Models\User
{
    $role = \App\Models\Role::create(['name' => 'uji-surat-'.uniqid(), 'description' => 'Peran untuk uji arsip surat']);

    foreach ($izin as $nama) {
        $permission = \App\Models\Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'arsip_surat', 'description' => 'uji']
        );
        $role->permissions()->attach($permission->id);
    }

    $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

    /*
     | EmployeeDetail WAJIB ada, walau arsip surat tidak memakainya.
     |
     | lemon mengalihkan admin yang datanya belum lengkap ke /admin/profile
     | sebelum izin apa pun diperiksa. Tanpa baris ini, uji izin akan
     | melihat 302 ke profil dan menyimpulkan "ditolak" untuk alasan yang
     | sama sekali berbeda — lalu lubang izin yang sungguhan lolos.
     */
    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id,
        'jabatan' => 'Staf Administrasi Uji',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Banyuwangi',
    ]);

    return $user;
}

function klasifikasiUji(array $ubah = []): SuratKlasifikasi
{
    return SuratKlasifikasi::create(array_merge([
        'kode' => '400',
        'nama' => 'Keuangan & Pembayaran',
        'retensi_tahun' => 10,
        'aktif' => true,
        'urutan' => 50,
    ], $ubah));
}

function suratUji(array $ubah = []): Surat
{
    return Surat::create(array_merge([
        'arah' => 'masuk',
        'perihal' => 'Permohonan kerja sama pelatihan',
        'tanggal_surat' => '2026-11-01',
        'dari' => 'Dinas Pendidikan',
        'sifat' => 'biasa',
        'status' => 'baru',
    ], $ubah));
}

/* ----------------------------- NOMOR AGENDA ----------------------------- */

test('nomor agenda berurut per arah dan per tahun', function () {
    $a = suratUji(['tanggal_surat' => '2026-11-01']);
    $b = suratUji(['tanggal_surat' => '2026-11-02']);
    $c = suratUji(['arah' => 'keluar', 'kepada' => 'PT Mitra']);

    expect($a->nomor_agenda)->toBe('SM-'.now()->format('Y').'-0001');
    expect($b->nomor_agenda)->toBe('SM-'.now()->format('Y').'-0002');
    // Surat keluar punya deret sendiri, seperti bukunya di kertas.
    expect($c->nomor_agenda)->toBe('SK-'.now()->format('Y').'-0001');
});

test('nomor agenda surat yang DIHAPUS tidak pernah dipakai ulang', function () {
    $a = suratUji();
    $b = suratUji();

    /*
     | Hapus lembut tetap memegang nomornya.
     |
     | Dihitung dari jumlah baris, nomor berikutnya akan kembali ke 0002 dan
     | dua surat berbeda memegang nomor agenda yang sama — di arsip resmi
     | itu berarti dua berkas yang tidak bisa dibedakan dalam surat-menyurat.
     */
    $b->delete();

    expect(Surat::nomorAgendaBaru('masuk'))->toBe('SM-'.now()->format('Y').'-0003');
    expect($a->fresh()->nomor_agenda)->toBe('SM-'.now()->format('Y').'-0001');
});

/* ----------------------------- MASA SIMPAN ----------------------------- */

test('tanggal boleh musnah dihitung dari tanggal SURAT, bukan tanggal dicatat', function () {
    $k = klasifikasiUji(['retensi_tahun' => 10]);

    Livewire::actingAs(adminSurat())
        ->test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', 'Tagihan listrik November')
        ->set('f.tanggal_surat', '2020-03-15')
        ->set('f.klasifikasi_id', (string) $k->id)
        ->call('save');

    $surat = Surat::latest('id')->first();

    /*
     | Masa simpan arsip dihitung dari umur dokumennya. Surat lama yang baru
     | dicatat hari ini tidak jadi muda karena pencatatannya terlambat — dan
     | menghitungnya dari hari pencatatan akan menahan arsip 10 tahun lebih
     | lama daripada yang diwajibkan.
     */
    expect($surat->musnah_pada->toDateString())->toBe('2030-03-15');
});

test('klasifikasi tanpa masa simpan TIDAK menghasilkan tanggal karangan', function () {
    $k = klasifikasiUji(['kode' => '900', 'nama' => 'Lain-lain', 'retensi_tahun' => null]);

    Livewire::actingAs(adminSurat())
        ->test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', 'Undangan rapat')
        ->set('f.tanggal_surat', '2026-11-01')
        ->set('f.klasifikasi_id', (string) $k->id)
        ->call('save');

    // null, BUKAN tanggal surat itu sendiri: "belum ditentukan" tidak sama
    // dengan "boleh dimusnahkan hari suratnya dibuat".
    expect(Surat::latest('id')->first()->musnah_pada)->toBeNull();
});

test('masa simpan surat lama tidak berubah saat retensi masternya disunting', function () {
    $k = klasifikasiUji(['retensi_tahun' => 10]);
    $surat = suratUji(['klasifikasi_id' => $k->id, 'musnah_pada' => '2036-11-01']);

    // Master datanya diubah orang lain, bertahun-tahun kemudian.
    Livewire::actingAs(adminSurat())
        ->test(KlasifikasiList::class)
        ->call('mulaiSunting', $k->id)
        ->set('retensi', '2')
        ->call('simpan');

    /*
     | Arsip yang sudah berjalan tidak boleh berubah masa simpannya diam-diam
     | karena master datanya disunting: itu akan memajukan jadwal musnah
     | ratusan berkas sekaligus tanpa satu pun tanda.
     */
    expect($surat->fresh()->musnah_pada->toDateString())->toBe('2036-11-01');
});

/* ----------------------------- BORANG ----------------------------- */

test('yang wajib hanya tiga: arah, perihal, tanggal surat', function () {
    Livewire::actingAs(adminSurat())
        ->test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', 'Surat tanpa nomor dan tanpa pengirim')
        ->set('f.tanggal_surat', '2026-11-20')
        ->call('save')
        ->assertHasNoErrors();

    expect(Surat::count())->toBe(1);
});

test('perihal kosong ditolak dengan alasan yang terbaca', function () {
    Livewire::actingAs(adminSurat())
        ->test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', '')
        ->call('save')
        ->assertHasErrors(['f.perihal' => 'required']);

    expect(Surat::count())->toBe(0);
});

test('surat keluar menyimpan KEPADA, surat masuk menyimpan DARI', function () {
    $admin = adminSurat();

    Livewire::actingAs($admin)
        ->test(SuratForm::class, ['arah' => 'keluar'])
        ->set('f.perihal', 'Balasan penawaran')
        ->set('f.tanggal_surat', '2026-11-20')
        ->set('f.kepada', 'PT Mitra Sejahtera')
        ->call('save');

    $keluar = Surat::latest('id')->first();

    expect($keluar->arah)->toBe('keluar');
    expect($keluar->kepada)->toBe('PT Mitra Sejahtera');
    // pihak & pihak_label itulah yang dipakai tabel, jadi keduanya dijaga.
    expect($keluar->pihak)->toBe('PT Mitra Sejahtera');
    expect($keluar->pihak_label)->toBe('Kepada');
    expect(suratUji()->pihak_label)->toBe('Dari');
});

/* ----------------------------- DAFTAR ----------------------------- */

test('tab memisahkan surat masuk, surat keluar, dan yang belum ditindak', function () {
    suratUji(['perihal' => 'Masuk belum ditindak', 'status' => 'baru']);
    suratUji(['perihal' => 'Masuk sudah selesai', 'status' => 'selesai']);
    suratUji(['arah' => 'keluar', 'perihal' => 'Keluar selesai', 'status' => 'selesai', 'kepada' => 'PT Mitra']);

    $komponen = Livewire::actingAs(adminSurat())->test(SuratList::class);

    // viewData, bukan get(): jumlahTab dirakit di render() dan dikirim ke
    // pandangan, ia bukan properti komponen.
    expect($komponen->viewData('jumlahTab'))->toMatchArray([
        'semua' => 3,
        'masuk' => 2,
        'keluar' => 1,
        'baru' => 1,
    ]);

    $komponen->call('pilihTab', 'keluar')
        ->assertSee('Keluar selesai')
        ->assertDontSee('Masuk belum ditindak');
});

test('pencarian satu kotak menjangkau nomor agenda, perihal, dan pengirim', function () {
    $surat = suratUji(['perihal' => 'Tagihan listrik', 'dari' => 'PLN Banyuwangi']);
    suratUji(['perihal' => 'Undangan rapat', 'dari' => 'Dinas Pendidikan']);

    $komponen = Livewire::actingAs(adminSurat())->test(SuratList::class);

    foreach ([$surat->nomor_agenda, 'listrik', 'PLN'] as $kata) {
        $komponen->set('cari', $kata)
            ->assertSee('Tagihan listrik')
            ->assertDontSee('Undangan rapat');
    }
});

test('tab "masa simpan habis" melewatkan yang sudah diarsipkan', function () {
    $lewat = ['musnah_pada' => now()->subDay()->toDateString()];

    suratUji($lewat + ['perihal' => 'Masa simpan habis, belum ditutup', 'status' => 'selesai']);
    suratUji($lewat + ['perihal' => 'Sudah diarsipkan di lemari', 'status' => 'diarsipkan']);

    /*
     | Surat yang sudah diarsipkan memang sedang menunggu di lemari sampai
     | masa simpannya lewat. Menghitungnya terus membuat angka ini tidak
     | pernah bisa dinolkan, dan angka yang tidak pernah nol berhenti dibaca.
     */
    Livewire::actingAs(adminSurat())
        ->test(SuratList::class)
        ->call('pilihTab', 'retensi')
        ->assertSee('Masa simpan habis, belum ditutup')
        ->assertDontSee('Sudah diarsipkan di lemari');
});

test('kosongkan saringan TIDAK ikut mengembalikan tab', function () {
    suratUji(['arah' => 'keluar', 'kepada' => 'PT Mitra']);

    /*
     | Tab itu penanda tempat, bukan penyempitan. Tombol yang juga
     | mengembalikan tab akan melempar admin ke daftar yang berbeda dari
     | yang ia kira sedang dibukanya.
     */
    Livewire::actingAs(adminSurat())
        ->test(SuratList::class)
        ->call('pilihTab', 'keluar')
        ->set('cari', 'apa pun')
        ->call('kosongkanSaringan')
        ->assertSet('tab', 'keluar')
        ->assertSet('cari', '');
});

/* ----------------------------- DISPOSISI ----------------------------- */

test('disposisi pertama mengangkat surat dari "belum ditindak" ke "diproses"', function () {
    $surat = suratUji(['status' => 'baru']);

    Livewire::actingAs(adminSurat())
        ->test(SuratDetail::class, ['surat' => $surat->id])
        ->set('disKepadaNama', 'Pak Budi — Bagian Umum')
        ->set('disInstruksi', 'Siapkan balasannya.')
        ->call('tambahDisposisi')
        ->assertHasNoErrors();

    // Memberi tugas ADALAH menindak suratnya.
    expect($surat->fresh()->status)->toBe('diproses');
    expect(SuratDisposisi::count())->toBe(1);
});

test('disposisi TIDAK menurunkan status yang sudah lebih maju', function () {
    $surat = suratUji(['status' => 'selesai']);

    Livewire::actingAs(adminSurat())
        ->test(SuratDetail::class, ['surat' => $surat->id])
        ->set('disKepadaNama', 'Pak Budi')
        ->set('disInstruksi', 'Arsipkan berkasnya.')
        ->call('tambahDisposisi');

    // 'selesai' adalah keputusan yang sudah diambil orang; sistem tidak
    // membatalkannya hanya karena ada tugas susulan.
    expect($surat->fresh()->status)->toBe('selesai');
});

test('disposisi tanpa penerima ditolak, baik akun maupun nama', function () {
    $surat = suratUji();

    Livewire::actingAs(adminSurat())
        ->test(SuratDetail::class, ['surat' => $surat->id])
        ->set('disInstruksi', 'Kerjakan.')
        ->call('tambahDisposisi')
        ->assertHasErrors('disKepadaNama');

    expect(SuratDisposisi::count())->toBe(0);
});

test('disposisi lewat tenggat yang SUDAH selesai tidak dihitung telat', function () {
    $surat = suratUji();
    $lewat = ['surat_id' => $surat->id, 'instruksi' => 'x', 'batas' => now()->subWeek()->toDateString()];

    $menggantung = SuratDisposisi::create($lewat + ['kepada_nama' => 'A', 'status' => 'menunggu']);
    $tuntas = SuratDisposisi::create($lewat + ['kepada_nama' => 'B', 'status' => 'selesai']);

    /*
     | Dua syarat, bukan satu. Disposisi yang selesai bulan lalu tetap punya
     | tenggat di masa lalu; menandainya merah membuat SELURUH riwayat
     | terlihat seperti kegagalan.
     */
    expect($menggantung->telat)->toBeTrue();
    expect($tuntas->telat)->toBeFalse();
    expect(ModeSurat::perlu(adminSurat())['disposisi_telat'])->toBe(1);
});

/* ----------------------------- KLASIFIKASI ----------------------------- */

test('klasifikasi yang masih dipakai DINONAKTIFKAN, bukan dihapus', function () {
    $k = klasifikasiUji();
    $surat = suratUji(['klasifikasi_id' => $k->id]);

    Livewire::actingAs(adminSurat())
        ->test(KlasifikasiList::class)
        ->call('hapus', $k->id);

    /*
     | Menghapusnya akan melepas klasifikasi dari surat lama (nullOnDelete),
     | dan arsip yang kehilangan kodenya kehilangan dasar masa simpannya —
     | tanpa satu pun tanda bahwa itu pernah terjadi.
     */
    expect(SuratKlasifikasi::find($k->id))->not->toBeNull();
    expect($k->fresh()->aktif)->toBeFalse();
    expect($surat->fresh()->klasifikasi_id)->toBe($k->id);
});

test('klasifikasi yang belum dipakai boleh dihapus permanen', function () {
    $k = klasifikasiUji();

    Livewire::actingAs(adminSurat())
        ->test(KlasifikasiList::class)
        ->call('hapus', $k->id);

    expect(SuratKlasifikasi::find($k->id))->toBeNull();
});

test('masa simpan kosong dan nol dibedakan', function () {
    $komponen = Livewire::actingAs(adminSurat())->test(KlasifikasiList::class);

    $komponen->set('kode', '111')->set('nama', 'Tanpa masa simpan')->set('retensi', '')->call('simpan');
    $komponen->set('kode', '222')->set('nama', 'Boleh musnah segera')->set('retensi', '0')->call('simpan');

    // Nol tahun berarti boleh dimusnahkan hari ini; kosong berarti belum
    // diputuskan. Menyamakannya menandai seluruh arsip yang belum diputuskan
    // sebagai siap musnah.
    expect(SuratKlasifikasi::where('kode', '111')->value('retensi_tahun'))->toBeNull();
    expect(SuratKlasifikasi::where('kode', '222')->value('retensi_tahun'))->toBe(0);
});

test('kode klasifikasi kembar ditolak', function () {
    klasifikasiUji(['kode' => '400']);

    Livewire::actingAs(adminSurat())
        ->test(KlasifikasiList::class)
        ->set('kode', '400')
        ->set('nama', 'Keuangan lagi')
        ->call('simpan')
        ->assertHasErrors(['kode' => 'unique']);

    expect(SuratKlasifikasi::where('kode', '400')->count())->toBe(1);
});

/* ----------------------------- DASBOR & IZIN ----------------------------- */

test('dasbor menghitung yang perlu ditindak', function () {
    suratUji(['status' => 'baru']);
    suratUji(['status' => 'baru']);
    suratUji(['status' => 'selesai', 'musnah_pada' => now()->subDay()->toDateString()]);

    $komponen = Livewire::actingAs(adminSurat())
        ->test(SuratDashboard::class)
        ->assertSee('Buku Agenda Surat');

    expect($komponen->viewData('perlu'))->toMatchArray([
        'surat_baru' => 2,
        'retensi_habis' => 1,
    ]);
});

test('tanpa izin view_surat angka dasbor NOL, bukan bocor', function () {
    suratUji(['status' => 'baru']);

    /*
     | Perihal surat bisa menyebut nama orang dan urusan rahasia. Pemegang
     | izin gerbang tanpa izin layar tidak boleh melihat angkanya pun —
     | "ada 7 surat baru" sendiri sudah keterangan.
     */
    $terbatas = adminSurat(['akses_arsip_surat']);

    expect(ModeSurat::perlu($terbatas))->toBe([
        'surat_baru' => 0, 'disposisi_telat' => 0, 'retensi_habis' => 0,
    ]);
});

test('mode arsip surat hanya aktif di rutenya sendiri', function () {
    $this->get(route('admin.surat.dashboard'));
    // Tidak diuji lewat request: ModeSurat::aktif() membaca rute saat ini,
    // dan yang dijaga di sini daftar awalan rutenya tidak melebar.
    expect(ModeSurat::RUTE)->toBe(['admin.surat.*']);
    expect(ModeSurat::IZIN)->toContain('akses_arsip_surat', 'view_surat', 'kelola_klasifikasi_surat');
});

test('layar arsip surat ditolak tanpa izin gerbang', function () {
    // Datanya lengkap, HANYA izinnya yang tidak ada — supaya yang terukur
    // benar-benar penjagaan izin, bukan pengalihan ke halaman profil.
    $tanpa = adminSurat([]);

    foreach ([
        route('admin.surat.dashboard'),
        route('admin.surat.index'),
        route('admin.surat.klasifikasi'),
    ] as $alamat) {
        $this->actingAs($tanpa)->get($alamat)->assertForbidden();
    }
});

test('izin gerbang saja TIDAK cukup untuk membuka layar di dalamnya', function () {
    /*
     | Dua lapis, dan lapis kedua yang sering terlupa.
     |
     | Pemegang 'akses_arsip_surat' boleh berpindah ke mode ini, tetapi
     | daftar surat menuntut 'view_surat' dan layar klasifikasi menuntut
     | 'kelola_klasifikasi_surat'. Tanpa uji ini, menambah rute baru ke grup
     | yang salah akan membuka arsip ke siapa pun yang punya izin gerbang.
     */
    $gerbangSaja = adminSurat(['akses_arsip_surat']);

    $this->actingAs($gerbangSaja)->get(route('admin.surat.dashboard'))->assertOk();
    $this->actingAs($gerbangSaja)->get(route('admin.surat.index'))->assertForbidden();
    $this->actingAs($gerbangSaja)->get(route('admin.surat.klasifikasi'))->assertForbidden();
});

/* ----------------------------- PINDAIAN ----------------------------- */

test('pindaian surat lain TIDAK bisa diambil lewat alamat surat ini', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    $rahasia = suratUji(['perihal' => 'Hasil audit internal', 'sifat' => 'rahasia']);
    $biasa = suratUji(['perihal' => 'Undangan rapat']);

    \Illuminate\Support\Facades\Storage::disk('local')->put('surat/rahasia.pdf', '%PDF-1.7 rahasia');

    $lampiranRahasia = \App\Models\Surat\SuratLampiran::create([
        'surat_id' => $rahasia->id,
        'nama' => 'audit.pdf',
        'jalur' => 'surat/rahasia.pdf',
        'ukuran' => 16,
        'jenis' => 'application/pdf',
    ]);

    $admin = adminSurat();

    // Lewat suratnya sendiri: boleh.
    $this->actingAs($admin)
        ->get(route('admin.surat.lampiran', [$rahasia, $lampiranRahasia]))
        ->assertOk();

    /*
     | Lewat surat LAIN: 404.
     |
     | Tanpa penjagaan ini, id lampiran yang ditebak-tebak akan menyajikan
     | pindaian surat mana pun — termasuk surat bersifat rahasia — meski
     | alamat suratnya sendiri terlihat wajar dan sah.
     */
    $this->actingAs($admin)
        ->get(route('admin.surat.lampiran', [$biasa, $lampiranRahasia]))
        ->assertNotFound();
});

test('nama unduhan pindaian memakai nomor agenda, bukan nama dari pemindai', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    $surat = suratUji();
    \Illuminate\Support\Facades\Storage::disk('local')->put('surat/scan.pdf', '%PDF-1.7');

    $lampiran = \App\Models\Surat\SuratLampiran::create([
        'surat_id' => $surat->id,
        'nama' => 'Scan_20261120_0001.pdf',
        'jalur' => 'surat/scan.pdf',
        'jenis' => 'application/pdf',
    ]);

    /*
     | "Scan_20261120_0001.pdf" tidak memberi tahu apa pun begitu berkasnya
     | tersimpan di komputer orang. Nomor agenda membuatnya bisa ditelusuri
     | balik ke buku agenda bertahun-tahun kemudian.
     */
    $balasan = $this->actingAs(adminSurat())
        ->get(route('admin.surat.lampiran', [$surat, $lampiran]))
        ->assertOk();

    /*
     | Disajikan INLINE, bukan sebagai unduhan paksa — itu disengaja:
     | tautan pindaian dibuka di tab baru untuk DILIHAT, dan memaksa unduh
     | membuat admin menumpuk berkas di folder Download setiap kali ia cuma
     | ingin memeriksa isi suratnya. Namanya tetap nama agenda, supaya
     | berkas yang memang disimpan masih bisa ditelusuri balik.
     */
    $disposisi = (string) $balasan->headers->get('Content-Disposition');

    expect($disposisi)->toContain('surat-'.$surat->nomor_agenda.'-'.$lampiran->id.'.pdf');
    expect($disposisi)->not->toContain('Scan_20261120_0001');
});

test('centang "Aktif" TERGAMBAR saat menyunting klasifikasi yang aktif', function () {
    $aktif = klasifikasiUji(['kode' => '401', 'aktif' => true]);
    $mati = klasifikasiUji(['kode' => '402', 'aktif' => false]);

    $komponen = Livewire::actingAs(adminSurat())->test(KlasifikasiList::class);

    /*
     | Yang diperiksa HTML-nya, bukan propertinya.
     |
     | assertSet('aktif', true) SELALU hijau — propertinya memang benar; yang
     | salah penggambarannya. wire:model pada kotak centang hanya menyalin
     | DOM → properti saat ditekan, dan tidak pernah menulis `checked`
     | sendiri. Tanpa @checked, membuka klasifikasi yang aktif menampilkan
     | kotak kosong, lalu menyimpannya menonaktifkan kode itu diam-diam.
     */
    $komponen->call('mulaiSunting', $aktif->id)
        ->assertSeeHtml('wire:model="aktif" checked');

    $komponen->call('mulaiSunting', $mati->id)
        ->assertDontSeeHtml('wire:model="aktif" checked');
});
