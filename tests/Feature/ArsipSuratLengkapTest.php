<?php

use App\Exports\SuratExport;
use App\Livewire\Pages\Admin\Surat\PemusnahanList;
use App\Livewire\Pages\Admin\Surat\SuratDetail;
use App\Livewire\Pages\Admin\Surat\SuratForm;
use App\Livewire\Pages\Admin\Surat\SuratList;
use App\Models\Surat\Surat;
use App\Models\Surat\SuratDisposisi;
use App\Models\Surat\SuratKlasifikasi;
use App\Models\Surat\SuratPemusnahan;
use App\Notifications\DisposisiBaru;
use App\Notifications\DisposisiLewatTenggat;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * Pelengkap arsip surat: riwayat, kaitan antar surat, pemulihan, ekspor,
 * notifikasi disposisi, dan penyusutan arsip.
 *
 * Penolongnya bernama sendiri (berakhiran -Lkp) karena penolong Pest tinggal
 * di ruang nama GLOBAL: nama yang sama di dua berkas uji membuat rangkaian
 * penuh mati sementara satu berkas tetap hijau.
 */
function peranLkp(array $izin): \App\Models\Role
{
    $role = \App\Models\Role::create(['name' => 'uji-lkp-'.uniqid(), 'description' => 'Peran uji arsip lengkap']);

    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'arsip_surat', 'description' => 'uji']
        );
        $role->permissions()->attach($p->id);
    }

    return $role;
}

function orangLkp(array $izin = ['akses_arsip_surat', 'view_surat', 'view_surat_rahasia', 'create_surat', 'edit_surat', 'delete_surat', 'kelola_pemusnahan_surat']): \App\Models\User
{
    $user = \App\Models\User::factory()->create(['role_id' => peranLkp($izin)->id]);

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

function suratLkp(array $ubah = []): Surat
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

beforeEach(function () {
    $this->aku = orangLkp();
    $this->actingAs($this->aku);
});

/* ============================== RIWAYAT ============================== */

it('mencatat riwayat sejak surat pertama kali dicatat', function () {
    $surat = suratLkp();

    expect($surat->jejak()->pluck('aksi')->all())->toBe(['dicatat'])
        ->and($surat->jejak()->value('oleh'))->toBe($this->aku->id);
});

it('menyebut KOLOM MANA yang diubah, bukan cuma "ada perubahan"', function () {
    $surat = suratLkp();
    $surat->update(['perihal' => 'Permohonan kerja sama pelatihan karyawan', 'dari' => 'Dinas Pendidikan Kab. Banyuwangi']);

    $ket = $surat->jejak()->where('aksi', 'diubah')->value('keterangan');

    expect($ket)->toContain('perihal')->toContain('pengirim');
});

it('tidak membuat baris riwayat untuk tanggal musnah yang dihitung sendiri', function () {
    $k = SuratKlasifikasi::create(['kode' => 'D', 'nama' => 'Dokumen', 'retensi_tahun' => 5, 'aktif' => true, 'urutan' => 1]);
    $surat = suratLkp(['klasifikasi_id' => $k->id]);

    // Persis yang dilakukan borang: simpan, lalu hitung ulang musnah_pada.
    $surat->load('klasifikasi');
    $surat->musnah_pada = $surat->hitungMusnah();
    $surat->save();

    // Kolom turunan yang punya baris riwayat sendiri membuat satu perubahan
    // terbaca seperti dua.
    expect($surat->jejak()->where('aksi', 'diubah')->count())->toBe(0);
});

it('mencatat perubahan status terpisah dari perubahan data', function () {
    $surat = suratLkp();
    $surat->update(['status' => 'selesai']);

    expect($surat->jejak()->where('aksi', 'status')->value('keterangan'))
        ->toContain('Belum ditindak')->toContain('Selesai');
});

it('mencatat disposisi, penghapusan, dan pemulihan di riwayat suratnya', function () {
    $surat = suratLkp();

    SuratDisposisi::create(['surat_id' => $surat->id, 'kepada_nama' => 'Pak Budi', 'instruksi' => 'Siapkan balasan.', 'status' => 'menunggu']);
    $surat->delete();
    $surat->restore();

    expect($surat->jejak()->pluck('aksi')->all())
        ->toContain('disposisi')->toContain('dihapus')->toContain('dipulihkan');
});

/* ============================== KAITAN ANTAR SURAT ============================== */

it('mengisi borang balasan dari surat yang ditanggapi', function () {
    $k = SuratKlasifikasi::create(['kode' => 'B', 'nama' => 'Kerja Sama', 'retensi_tahun' => 10, 'aktif' => true, 'urutan' => 1]);
    $induk = suratLkp(['klasifikasi_id' => $k->id, 'dari' => 'PT Mitra Sejahtera']);

    Livewire::withQueryParams(['balas' => $induk->id])
        ->test(SuratForm::class)
        // Arahnya TERBALIK dari surat yang ditanggapi, tujuannya pengirimnya.
        ->assertSet('f.arah', 'keluar')
        ->assertSet('f.kepada', 'PT Mitra Sejahtera')
        ->assertSet('f.klasifikasi_id', (string) $k->id)
        ->assertSet('f.induk_id', (string) $induk->id)
        // Perihal TIDAK ditebak: itu satu-satunya kalimat yang dipakai mencari.
        ->assertSet('f.perihal', '');
});

it('menyimpan kaitan dan tembusan', function () {
    $induk = suratLkp();

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('f.perihal', 'Balasan permohonan kerja sama')
        ->set('f.tanggal_surat', '2026-11-05')
        ->set('f.induk_id', (string) $induk->id)
        ->set('f.tembusan', 'Direktur; Bagian Keuangan')
        ->call('save');

    $balasan = Surat::where('perihal', 'Balasan permohonan kerja sama')->firstOrFail();

    expect($balasan->induk_id)->toBe($induk->id)
        ->and($balasan->tembusan)->toBe('Direktur; Bagian Keuangan')
        ->and($induk->tanggapan()->pluck('id')->all())->toBe([$balasan->id]);
});

it('tidak menawarkan surat searah sebagai surat yang ditanggapi', function () {
    suratLkp(['perihal' => 'Surat masuk lain']);
    suratLkp(['arah' => 'keluar', 'perihal' => 'Surat keluar lain', 'kepada' => 'PT A']);

    // Surat keluar menanggapi surat MASUK, bukan surat keluar lain.
    $pilihan = Livewire::test(SuratForm::class, ['arah' => 'keluar'])->viewData('daftarInduk');

    expect($pilihan->pluck('perihal')->all())->toBe(['Surat masuk lain']);
});

/* ============================== TERHAPUS & PULIHKAN ============================== */

it('menampilkan surat terhapus hanya di tabnya sendiri', function () {
    $hidup = suratLkp(['perihal' => 'Surat yang masih ada']);
    $mati = suratLkp(['perihal' => 'Surat yang dibuang']);
    $mati->delete();

    Livewire::test(SuratList::class)
        ->assertSee('Surat yang masih ada')
        ->assertDontSee('Surat yang dibuang')
        ->call('pilihTab', 'terhapus')
        ->assertSee('Surat yang dibuang')
        ->assertDontSee('Surat yang masih ada');

    expect($hidup->fresh())->not->toBeNull();
});

it('mengembalikan surat terhapus dengan nomor agenda yang sama', function () {
    $surat = suratLkp();
    $nomor = $surat->nomor_agenda;
    $surat->delete();

    Livewire::test(SuratList::class)->call('pulihkan', $surat->id);

    expect(Surat::find($surat->id)?->nomor_agenda)->toBe($nomor);
});

it('menghapus selamanya hanya kalau diminta', function () {
    $surat = suratLkp();
    $surat->delete();

    Livewire::test(SuratList::class)->call('hapusPermanen', $surat->id);

    expect(Surat::withTrashed()->find($surat->id))->toBeNull();
});

it('menolak memulihkan surat bagi yang tidak boleh menghapus', function () {
    $surat = suratLkp();
    $surat->delete();

    $this->actingAs(orangLkp(['akses_arsip_surat', 'view_surat']));

    Livewire::test(SuratList::class)->call('pulihkan', $surat->id)->assertForbidden();
});

/* ============================== NOTIFIKASI DISPOSISI ============================== */

it('memberi tahu orang yang didisposisi', function () {
    Notification::fake();

    $surat = suratLkp();
    $penerima = orangLkp(['akses_arsip_surat', 'view_surat']);

    Livewire::test(SuratDetail::class, ['surat' => $surat->id])
        ->set('disKepadaUser', (string) $penerima->id)
        ->set('disInstruksi', 'Siapkan balasannya minggu ini.')
        ->call('tambahDisposisi');

    Notification::assertSentTo($penerima, DisposisiBaru::class);
});

it('tidak memberi tahu diri sendiri', function () {
    Notification::fake();

    $surat = suratLkp();

    Livewire::test(SuratDetail::class, ['surat' => $surat->id])
        ->set('disKepadaUser', (string) $this->aku->id)
        ->set('disInstruksi', 'Saya kerjakan sendiri.')
        ->call('tambahDisposisi');

    Notification::assertNothingSent();
});

it('mengirim pengingat tenggat SEKALI saja', function () {
    Notification::fake();

    $surat = suratLkp();
    $penerima = orangLkp(['akses_arsip_surat', 'view_surat']);

    $disposisi = SuratDisposisi::create([
        'surat_id' => $surat->id,
        'kepada_user_id' => $penerima->id,
        'instruksi' => 'Tagih jawabannya.',
        'batas' => now()->subWeek()->toDateString(),
        'status' => 'menunggu',
    ]);

    $this->artisan('surat:ingatkan-disposisi')->assertSuccessful();
    $this->artisan('surat:ingatkan-disposisi')->assertSuccessful();

    // Pengingat harian untuk hal yang sama membuat orang berhenti membacanya.
    Notification::assertSentToTimes($penerima, DisposisiLewatTenggat::class, 1);
    expect($disposisi->fresh()->telat_diberitahu_pada)->not->toBeNull();
});

it('tidak mengingatkan disposisi yang sudah selesai', function () {
    Notification::fake();

    $surat = suratLkp();
    $penerima = orangLkp(['akses_arsip_surat', 'view_surat']);

    SuratDisposisi::create([
        'surat_id' => $surat->id,
        'kepada_user_id' => $penerima->id,
        'instruksi' => 'Sudah dikerjakan.',
        'batas' => now()->subWeek()->toDateString(),
        'status' => 'selesai',
    ]);

    $this->artisan('surat:ingatkan-disposisi')->assertSuccessful();

    Notification::assertNothingSent();
});

/* ============================== EKSPOR ============================== */

it('mengekspor dengan saringan yang SAMA PERSIS dengan layarnya', function () {
    suratLkp(['perihal' => 'Undangan rapat', 'sifat' => 'biasa']);
    suratLkp(['perihal' => 'Panggilan mendesak', 'sifat' => 'segera']);

    $saringan = ['tab' => 'semua', 'cari' => '', 'klasifikasi' => '', 'sifat' => 'segera',
        'status' => '', 'dari' => '', 'sampai' => '', 'urut' => 'terbaru'];

    $isi = (new SuratExport($this->aku, $saringan))->view()->getData()['surat'];

    // Berkas yang memuat surat yang sengaja disaring keluar jauh lebih
    // berbahaya daripada tombol yang gagal: tidak ada gejalanya.
    expect($isi->pluck('perihal')->all())->toBe(['Panggilan mendesak']);
});

it('tidak membocorkan surat rahasia lewat pintu ekspor', function () {
    suratLkp(['perihal' => 'Hasil audit internal', 'sifat' => 'rahasia']);
    suratLkp(['perihal' => 'Undangan rapat']);

    $biasa = orangLkp(['akses_arsip_surat', 'view_surat']);

    $saringan = ['tab' => 'semua', 'cari' => '', 'klasifikasi' => '', 'sifat' => '',
        'status' => '', 'dari' => '', 'sampai' => '', 'urut' => 'terbaru'];

    expect((new SuratExport($biasa, $saringan))->view()->getData()['surat']->pluck('perihal')->all())
        ->toBe(['Undangan rapat']);
});

it('membuka buku agenda dan lembar disposisi sebagai PDF', function () {
    $surat = suratLkp();

    $this->get(route('admin.surat.ekspor.pdf', ['tab' => 'semua']))
        ->assertOk()->assertHeader('content-type', 'application/pdf');

    $this->get(route('admin.surat.lembar-disposisi', $surat))
        ->assertOk()->assertHeader('content-type', 'application/pdf');
});

it('menolak mencetak lembar disposisi surat rahasia', function () {
    $surat = suratLkp(['sifat' => 'rahasia']);

    $this->actingAs(orangLkp(['akses_arsip_surat', 'view_surat']))
        ->get(route('admin.surat.lembar-disposisi', $surat))
        ->assertForbidden();
});

/* ============================== PENYUSUTAN ARSIP ============================== */

it('menawarkan hanya surat yang masa simpannya sudah habis', function () {
    $jatuh = suratLkp(['perihal' => 'Arsip lama', 'musnah_pada' => now()->subMonth()->toDateString()]);
    suratLkp(['perihal' => 'Arsip baru', 'musnah_pada' => now()->addYear()->toDateString()]);
    suratLkp(['perihal' => 'Tanpa masa simpan']);

    Livewire::test(PemusnahanList::class)
        ->assertSee('Arsip lama')
        ->assertDontSee('Arsip baru')
        ->assertDontSee('Tanpa masa simpan')
        ->assertViewHas('jumlahCalon', 1);

    expect($jatuh->fresh()->status)->toBe('baru');
});

it('membuat berita acara dan menandai suratnya dimusnahkan', function () {
    $surat = suratLkp(['musnah_pada' => now()->subMonth()->toDateString()]);

    Livewire::test(PemusnahanList::class)
        ->set('terpilih', [$surat->id])
        ->set('catatan', 'Dihancurkan dengan mesin, disaksikan Bagian Umum.')
        ->call('buatBeritaAcara');

    $ba = SuratPemusnahan::firstOrFail();
    $surat->refresh();

    expect($ba->nomor)->toStartWith('BA-')
        ->and($surat->status)->toBe('dimusnahkan')
        ->and($surat->pemusnahan_id)->toBe($ba->id)
        // Catatannya TETAP ADA: itu yang menjawab "ke mana surat nomor sekian?"
        ->and(Surat::find($surat->id))->not->toBeNull()
        ->and($surat->jejak()->where('aksi', 'dimusnahkan')->count())->toBe(1);
});

it('tidak menawarkan surat yang sudah masuk berita acara', function () {
    $surat = suratLkp(['musnah_pada' => now()->subMonth()->toDateString()]);

    Livewire::test(PemusnahanList::class)->set('terpilih', [$surat->id])->call('buatBeritaAcara');

    Livewire::test(PemusnahanList::class)->assertViewHas('jumlahCalon', 0);
});

it('menolak surat yang masa simpannya BELUM habis walau idnya dikirim', function () {
    $belum = suratLkp(['musnah_pada' => now()->addYear()->toDateString()]);

    // Centang datang dari peramban; daftarnya harus dibaca ulang dari basis data.
    Livewire::test(PemusnahanList::class)
        ->set('terpilih', [$belum->id])
        ->call('buatBeritaAcara')
        ->assertHasErrors('terpilih');

    expect($belum->fresh()->status)->not->toBe('dimusnahkan')
        ->and(SuratPemusnahan::count())->toBe(0);
});

it('menolak membuat berita acara tanpa izinnya', function () {
    $surat = suratLkp(['musnah_pada' => now()->subMonth()->toDateString()]);

    $this->actingAs(orangLkp(['akses_arsip_surat', 'view_surat']));

    Livewire::test(PemusnahanList::class)
        ->set('terpilih', [$surat->id])
        ->call('buatBeritaAcara')
        ->assertForbidden();
});

it('mencetak berita acara lengkap dengan jumlah dalam huruf', function () {
    $surat = suratLkp(['musnah_pada' => now()->subMonth()->toDateString()]);

    Livewire::test(PemusnahanList::class)->set('terpilih', [$surat->id])->call('buatBeritaAcara');

    $this->get(route('admin.surat.berita-acara', SuratPemusnahan::firstOrFail()))
        ->assertOk()->assertHeader('content-type', 'application/pdf');
});

it('tidak mencetak berita acara yang memuat surat rahasia bagi yang tak berizin', function () {
    $rahasia = suratLkp(['sifat' => 'rahasia', 'musnah_pada' => now()->subMonth()->toDateString()]);

    Livewire::test(PemusnahanList::class)->set('terpilih', [$rahasia->id])->call('buatBeritaAcara');

    /*
     | Berita acara yang jumlahnya berubah-ubah menurut siapa yang
     | mencetaknya bukan lagi dokumen — jadi ia ditolak seluruhnya, bukan
     | dicetak dengan satu baris dihilangkan diam-diam.
     */
    $this->actingAs(orangLkp(['akses_arsip_surat', 'view_surat']))
        ->get(route('admin.surat.berita-acara', SuratPemusnahan::firstOrFail()))
        ->assertForbidden();
});

/* ============================== TEMBUSAN SEBAGAI KEPING ============================== */

it('memecah tembusan jadi keping saat dipisah titik koma', function () {
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->call('tambahTembusan', 'Direktur; Bagian Keuangan; Arsip')
        ->assertSet('f.tembusan', 'Direktur; Bagian Keuangan; Arsip')
        ->assertSee('Bagian Keuangan');
});

it('memecah tembusan yang disalin menurun per baris', function () {
    // Daftar tembusan di surat aslinya ditulis menurun; yang disalin pun begitu.
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->call('tambahTembusan', "Direktur\nBagian Keuangan\n\nArsip")
        ->assertSet('f.tembusan', 'Direktur; Bagian Keuangan; Arsip');
});

it('menambah keping tanpa membuang yang sudah ada', function () {
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->call('tambahTembusan', 'Direktur')
        ->call('tambahTembusan', 'Bagian Keuangan')
        ->assertSet('f.tembusan', 'Direktur; Bagian Keuangan');
});

it('mengabaikan tembusan yang sudah ada di daftar', function () {
    // Menolak dengan pesan galat untuk nama yang MEMANG sudah ada hanya menghalangi.
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->call('tambahTembusan', 'Direktur; Direktur ; Arsip')
        ->assertSet('f.tembusan', 'Direktur; Arsip')
        ->assertHasNoErrors('f.tembusan');
});

it('membuang satu keping tanpa menggeser yang lain', function () {
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->call('tambahTembusan', 'Direktur; Bagian Keuangan; Arsip')
        ->call('hapusTembusan', 1)
        ->assertSet('f.tembusan', 'Direktur; Arsip')
        ->assertDontSee('Bagian Keuangan');
});

it('membatasi jumlah pihak yang ditembusi dan mengatakan sebabnya', function () {
    $banyak = collect(range(1, 20))->map(fn ($i) => 'Pihak '.$i)->join('; ');

    $borang = Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->call('tambahTembusan', $banyak)
        ->assertHasErrors('f.tembusan');

    expect(count(explode(';', $borang->get('f.tembusan'))))->toBe(15);
});

it('menyimpan tembusan tetap sebagai satu teks dipisah titik koma', function () {
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('f.perihal', 'Surat bertembusan')
        ->set('f.tanggal_surat', '2026-11-20')
        ->call('tambahTembusan', 'Direktur; Arsip')
        ->call('save');

    // Yang tercetak di kaki surat adalah teks ini apa adanya.
    expect(Surat::where('perihal', 'Surat bertembusan')->value('tembusan'))->toBe('Direktur; Arsip');
});

it('menggambar tombol buang pada tiap keping', function () {
    /*
     | Memeriksa HTML, bukan propertinya.
     |
     | Daftar kepingnya dirender server; kalau tombolnya tidak tergambar,
     | $f['tembusan'] tetap benar dan ujinya tetap hijau — sementara di layar
     | tembusan yang salah ketik tidak bisa dibuang sama sekali.
     */
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->call('tambahTembusan', 'Direktur')
        ->assertSeeHtml('wire:click="hapusTembusan(0)"');
});

/* ============================== KARYAWAN PENANGGUNG JAWAB ============================== */

it('menawarkan karyawan lemon, bukan seluruh akun', function () {
    $karyawan = orangLkp(['akses_arsip_surat', 'view_surat']);

    // Akun tanpa data karyawan: bukan orang yang bisa ditunjuk memegang surat.
    $akunLayanan = \App\Models\User::factory()->create(['role_id' => peranLkp([])->id, 'name' => 'Akun Layanan']);

    $daftar = Livewire::test(SuratForm::class, ['arah' => 'masuk'])->viewData('daftarKaryawan');

    expect($daftar->pluck('id')->all())->toContain($karyawan->id)
        ->and($daftar->pluck('id')->all())->not->toContain($akunLayanan->id);
});

it('menyimpan karyawan yang dipilih dari jendela pilih', function () {
    $karyawan = orangLkp(['akses_arsip_surat', 'view_surat']);

    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', 'Surat bertuan')
        ->set('f.tanggal_surat', '2026-11-20')
        // Jendela pilih mengirim nilainya sebagai TEKS dari peramban.
        ->call('pilihPenanggungJawab', (string) $karyawan->id)
        ->assertSet('f.penanggung_jawab_id', (string) $karyawan->id)
        ->call('save');

    expect(Surat::where('perihal', 'Surat bertuan')->value('penanggung_jawab_id'))->toBe($karyawan->id);
});

it('menolak id yang bukan karyawan walau dikirim dari peramban', function () {
    $akunLayanan = \App\Models\User::factory()->create(['role_id' => peranLkp([])->id, 'name' => 'Akun Layanan']);

    /*
     | Jendela pilih hidup di sisi pengguna; id apa pun bisa dikirim ke sini.
     | Yang menentukan sah atau tidak adalah daftar di server, bukan daftar
     | yang kebetulan tergambar di layar.
     */
    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->call('pilihPenanggungJawab', (string) $akunLayanan->id)
        ->assertSet('f.penanggung_jawab_id', '');
});

it('mengosongkan karyawan yang sudah ditunjuk', function () {
    $karyawan = orangLkp(['akses_arsip_surat', 'view_surat']);

    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->call('pilihPenanggungJawab', (string) $karyawan->id)
        ->call('kosongkanPenanggungJawab')
        ->assertSet('f.penanggung_jawab_id', '');
});

it('mencatat pergantian karyawan penanggung jawab di riwayat', function () {
    $karyawan = orangLkp(['akses_arsip_surat', 'view_surat']);
    $surat = suratLkp();

    $surat->update(['penanggung_jawab_id' => $karyawan->id]);

    expect($surat->jejak()->where('aksi', 'diubah')->value('keterangan'))
        ->toContain('karyawan yang menangani');
});

it('menyebut karyawan penanggung jawab di halaman suratnya', function () {
    $karyawan = orangLkp(['akses_arsip_surat', 'view_surat']);
    $surat = suratLkp(['penanggung_jawab_id' => $karyawan->id]);

    $this->get('/admin/arsip-surat/'.$surat->id)
        ->assertOk()
        ->assertSee('Ditangani oleh')
        ->assertSee($karyawan->name);
});

it('membawa karyawan penanggung jawab ke berkas ekspor', function () {
    $karyawan = orangLkp(['akses_arsip_surat', 'view_surat']);
    suratLkp(['penanggung_jawab_id' => $karyawan->id]);

    $saringan = ['tab' => 'semua', 'cari' => '', 'klasifikasi' => '', 'sifat' => '',
        'status' => '', 'dari' => '', 'sampai' => '', 'urut' => 'terbaru'];

    expect((new SuratExport($this->aku, $saringan))->view()->render())
        ->toContain('Ditangani Oleh')
        ->toContain($karyawan->name);
});

it('menyediakan daftar karyawan sebagai data, bukan dirangkai ke dalam skrip', function () {
    /*
     | Nama orang bisa memuat tanda kutip. Merangkainya ke dalam kode
     | membuat satu nama mematahkan seluruh halaman — dan gejalanya bukan
     | galat PHP, melainkan tombol yang diam saja.
     */
    orangLkp(['akses_arsip_surat', 'view_surat']);

    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->assertSeeHtml('<script type="application/json" id="sr-daftar-karyawan">')
        ->assertSeeHtml('data-sr-pilih-karyawan');
});
