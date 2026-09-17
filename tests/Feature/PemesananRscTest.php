<?php

use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscForm;
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscList;
use App\Models\DataAkun;
use App\Models\PemesananRsc;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RscBatchAkun;
use App\Models\User;
use Livewire\Livewire;

function adminRsc(): User
{
    $peran = Role::firstOrCreate(['name' => 'uji-rsc'], ['description' => 'Peran uji RSC']);

    foreach (['view_pesananrsc', 'create_pesananrsc', 'edit_pesananrsc', 'delete_pesananrsc'] as $nama) {
        $izin = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']
        );
        if (! $peran->permissions()->where('permissions.id', $izin->id)->exists()) {
            $peran->permissions()->attach($izin->id);
        }
    }

    return User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function akunRsc(string $nama = 'Grammarly Uji', int $harga = 15000): DataAkun
{
    return DataAkun::create([
        'nama_akun' => $nama,
        'username_akun' => 'user@contoh.test',
        'password_akun' => 'rahasia-uji',
        'link_login_akun' => 'https://contoh.test/masuk',
        'harga_satuan' => $harga,
        'status' => 'active',
    ]);
}

function batchRsc(User $pic, DataAkun $akun, string $nama, string $batch, array $peserta, array $ubah = []): void
{
    foreach ($peserta as [$namaPeserta, $telp]) {
        PemesananRsc::create(array_merge([
            'id_transaksi' => strtoupper(substr(uniqid(), -5)),
            'nama_camp' => $nama,
            'batch_camp' => $batch,
            'tanggal_mulai_camp' => '2026-09-01',
            'tanggal_akhir_camp' => '2026-09-03',
            'jumlah_pemesanan' => 1,
            'tanggal_pemesanan' => '2026-09-01',
            'tanggal_berakhir' => '2026-10-01',
            'harga_satuan' => 15000,
            'total' => 15000,
            'akun' => $akun->id,
            'username' => 'user@contoh.test',
            'password' => 'rahasia-uji',
            'link_akses' => 'https://contoh.test/masuk',
            'pic' => $pic->id,
            'status' => 'baru',
            'nama_pembeli' => $namaPeserta,
            'telp_pembeli' => $telp,
        ], $ubah));
    }
}

function isiFormRsc($lw, DataAkun $akun, User $pic, string $nama, string $batch, array $peserta)
{
    $lw->set('nama_camp', $nama)
        ->set('batch_camp', $batch)
        ->set('tanggal_mulai_camp', '2026-09-20')
        ->set('tanggal_akhir_camp', '2026-09-22')
        ->set('jumlah_pemesanan', 1)
        ->set('tanggal_pemesanan', '2026-09-20')
        ->set('akun', $akun->id)
        ->set('pic', $pic->id)
        ->set('status', 'baru');

    $baris = [];
    foreach ($peserta as $i => [$n, $t]) {
        $baris['p'.$i] = ['tmp_id' => 'p'.$i, 'nama_pembeli' => $n, 'telp_pembeli' => $t];
    }

    return $lw->set('peserta', $baris);
}

it('menolak batch baru yang nama dan nomornya sudah dipakai', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Uji', '7', [['Ani', '+6281234567890']]);

    $lw = isiFormRsc(Livewire::actingAs($admin)->test(PemesananrscForm::class), $akun, $admin, 'Camp Uji', '7', [['Budi', '081298765432']]);
    $lw->call('save')
        ->assertHasErrors(['batch_camp'])
        ->assertDispatched('rsc-form-galat');

    // Peserta tidak diam-diam tergabung ke batch lama.
    expect(PemesananRsc::where('nama_camp', 'Camp Uji')->where('batch_camp', '7')->count())->toBe(1);
});

it('menyimpan batch baru dengan nomor yang belum dipakai', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Uji', '7', [['Ani', '+6281234567890']]);

    $lw = isiFormRsc(Livewire::actingAs($admin)->test(PemesananrscForm::class), $akun, $admin, 'Camp Uji', '8', [['Budi', '081298765432']]);
    $lw->call('save')->assertHasNoErrors()->assertRedirect(route('admin.pesananrsc.index'));

    expect(PemesananRsc::where('batch_camp', '8')->value('telp_pembeli'))->toBe('+6281298765432');
});

it('edit batch tidak boleh dipindah ke nama dan nomor batch lain yang sudah ada', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Uji', '7', [['Ani', '+6281234567890']]);
    batchRsc($admin, $akun, 'Camp Uji', '8', [['Budi', '+6281298765432']]);

    $rows = PemesananRsc::where('batch_camp', '8')->get();
    Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
        ->assertSet('mode', 'edit')
        ->set('batch_camp', '7')
        ->call('save')
        ->assertHasErrors(['batch_camp']);

    // Menyimpan tanpa mengganti nomor tetap boleh.
    Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
        ->set('status', 'baru')
        ->call('save')
        ->assertHasNoErrors();
});

it('menolak nomor telepon yang terlalu pendek dan peserta yang sama persis', function () {
    $admin = adminRsc();
    $akun = akunRsc();

    $lw = isiFormRsc(Livewire::actingAs($admin)->test(PemesananrscForm::class), $akun, $admin, 'Camp Uji', '1', [
        ['Ani', '0812'],
        ['Budi', '081298765432'],
        ['budi ', '0812-9876-5432'],
    ]);

    $lw->call('save')->assertHasErrors(['peserta.p0.telp_pembeli', 'peserta.p2.nama_pembeli']);
    expect(PemesananRsc::count())->toBe(0);
});

it('nomor telepon yang sama pada peserta berbeda hanya diperingatkan', function () {
    $admin = adminRsc();
    $akun = akunRsc();

    $lw = isiFormRsc(Livewire::actingAs($admin)->test(PemesananrscForm::class), $akun, $admin, 'Camp Uji', '1', [
        ['Ani', '081298765432'],
        ['Budi', '+62 812-9876-5432'],
    ]);

    expect($lw->instance()->telpGanda())->toBe([['telp' => '+6281298765432', 'nomor' => [1, 2]]]);
    $lw->assertSee('Nomor telepon yang sama dipakai beberapa peserta');

    $lw->call('save')->assertHasNoErrors();
    expect(PemesananRsc::count())->toBe(2);
});

it('salin batch mengisi form dengan data batch sumber dan nomor batch berikutnya', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    $tambahan = akunRsc('DeepL Uji', 20000);
    batchRsc($admin, $akun, 'Camp Uji', '7', [['Ani', '+6281234567890'], ['Budi', '+6281298765432']], ['deskripsi' => 'Catatan batch']);
    batchRsc($admin, $akun, 'Camp Uji', '9', [['Caca', '+6281200000000']]);
    RscBatchAkun::create([
        'nama_camp' => 'Camp Uji', 'batch_camp' => '7', 'akun_id' => $tambahan->id,
        'nama_akun' => 'DeepL Uji', 'username' => 'u', 'password' => 'p', 'link_akses' => 'l',
    ]);

    $lw = Livewire::actingAs($admin)->test(PemesananrscForm::class, ['salinDari' => 'Camp Uji|7'])
        ->assertSet('mode', 'create')
        ->assertSet('nama_camp', 'Camp Uji')
        ->assertSet('batch_camp', '10')
        ->assertSet('tanggal_mulai_camp', null)
        ->assertSet('pic', $admin->id)
        ->assertSet('deskripsi', 'Catatan batch')
        ->assertSet('status', 'baru');

    $f = $lw->instance();
    expect(collect($f->peserta)->pluck('nama_pembeli')->all())->toBe(['Ani', 'Budi'])
        // Kunci baru: disimpan sebagai peserta BARU, bukan memindahkan baris lama.
        ->and(collect($f->peserta)->keys()->every(fn ($k) => ! PemesananRsc::whereKey($k)->exists()))->toBeTrue()
        ->and(collect($f->akunTambahan)->pluck('nama_akun')->all())->toBe(['DeepL Uji']);

    $lw->set('tanggal_mulai_camp', '2026-10-01')->set('tanggal_akhir_camp', '2026-10-03')
        ->call('save')->assertHasNoErrors();

    expect(PemesananRsc::where('batch_camp', '7')->count())->toBe(2)
        ->and(PemesananRsc::where('batch_camp', '10')->count())->toBe(2)
        ->and(RscBatchAkun::where('batch_camp', '10')->count())->toBe(1);
});

it('salin batch dengan sumber yang tidak ada membuka form kosong', function () {
    $admin = adminRsc();

    Livewire::actingAs($admin)->test(PemesananrscForm::class, ['salinDari' => 'Tidak Ada|1'])
        ->assertSet('salinDari', null)
        ->assertSet('nama_camp', null);
});

it('daftar menghitung dan menyaring batch yang akunnya segera berakhir', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Segera', '1', [['Ani', '+6281234567890'], ['Budi', '+6281298765432']], ['tanggal_berakhir' => today()->addDays(3)]);
    batchRsc($admin, $akun, 'Camp Lewat', '1', [['Caca', '+6281200000000']], ['tanggal_berakhir' => today()->subDays(2)]);
    batchRsc($admin, $akun, 'Camp Habis', '1', [['Dedi', '+6281200000001']], ['tanggal_berakhir' => today()->addDay(), 'status' => 'habis']);
    batchRsc($admin, $akun, 'Camp Lama', '1', [['Euis', '+6281200000002']], ['tanggal_berakhir' => today()->addDays(30)]);

    $lw = Livewire::actingAs($admin)->test(PemesananrscList::class);
    expect($lw->viewData('ringkas')['segera'])->toBe(1)
        ->and($lw->viewData('ringkas')['lewat'])->toBe(1);

    $lw->call('saringMasa', 'segera');
    expect($lw->viewData('pemesananrsc')->pluck('nama_camp')->all())->toBe(['Camp Segera'])
        // Kartunya tetap menunjukkan jumlah saat saringannya dipakai.
        ->and($lw->viewData('ringkas')['segera'])->toBe(1);

    $lw->call('saringMasa', 'segera');
    expect($lw->viewData('pemesananrsc')->total())->toBe(4);

    $lw->set('masaFilter', 'lewat');
    expect($lw->viewData('pemesananrsc')->pluck('nama_camp')->all())->toBe(['Camp Lewat']);
});

it('daftar bisa diurutkan dan disaring per pic', function () {
    $admin = adminRsc();
    $lain = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Kecil', '1', [['Ani', '+6281234567890']]);
    batchRsc($lain, $akun, 'Camp Besar', '1', [['Budi', '+6281298765432'], ['Caca', '+6281200000000'], ['Dedi', '+6281200000001']]);

    $lw = Livewire::actingAs($admin)->test(PemesananrscList::class)->call('urutkan', 'peserta');
    expect($lw->viewData('pemesananrsc')->pluck('nama_camp')->all())->toBe(['Camp Besar', 'Camp Kecil']);

    $lw->call('urutkan', 'peserta');
    expect($lw->viewData('pemesananrsc')->pluck('nama_camp')->all())->toBe(['Camp Kecil', 'Camp Besar']);

    // Kolom di luar daftar putih diabaikan.
    $lw->call('urutkan', 'password');
    expect($lw->get('urut'))->toBe('peserta');

    $lw->set('picFilter', $lain->id);
    expect($lw->viewData('pemesananrsc')->pluck('nama_camp')->all())->toBe(['Camp Besar']);
});

it('menampilkan badge rsc segera berakhir di menu samping', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Segera', '1', [['Ani', '+6281234567890'], ['Budi', '+6281298765432']], ['tanggal_berakhir' => today()->addDays(2)]);

    Livewire::actingAs($admin)->test('layout.sidebar')
        ->assertViewHas('rscSegera', 1)
        ->assertSee('1 batch RSC akunnya berakhir dalam 7 hari');
});

it('form menampilkan nama akun utama yang sudah terpilih', function () {
    $admin = adminRsc();
    $akun = akunRsc('Grammarly Terpilih');
    batchRsc($admin, $akun, 'Camp Uji', '1', [['Ani', '+6281234567890']]);
    $rows = PemesananRsc::all();

    // ID akun berupa UUID; dulu dicocokkan sebagai angka sehingga tombol
    // pilihnya selalu tertulis "-- Pilih Akun --".
    Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
        ->assertSeeHtml('<span class="text-dark">Grammarly Terpilih</span>');
});

it('form menampilkan status berwarna, bar simpan, dan pemilih bergaya jendela lemon', function () {
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/pemesanan-r-s-c/pemesananrsc-form.blade.php'));

    expect($isi)->toContain('role="radiogroup"')
        ->and($isi)->toContain('class="rsc-bar-simpan"')
        ->and($isi)->toContain('<div class="ts-modal-card dsb is-datar" role="dialog"')
        ->and($isi)->toContain("window.addEventListener('beforeunload'")
        ->and($isi)->toContain("window.addEventListener('rsc-form-galat'")
        // Pemilih lama berbasis SweetAlert sudah tidak dipakai.
        ->and($isi)->not->toContain('html: `<input id="rscPickSearch"')
        ->and($isi)->not->toContain('<select wire:model="status"');

    $detail = file_get_contents(resource_path('views/livewire/pages/admin/pemesanan-r-s-c/pemesananrsc-detail.blade.php'));
    expect($detail)->toContain("route('admin.pesananrsc.create', ['salin' =>")
        ->and($detail)->toContain('href="{{ $wa[\'url\'] }}"')
        ->and($detail)->toContain('rsc-salin');
});

it('pemulihan data lama hanya mencatat status yang dipilih', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Baru', '1', [['Eka', '+6281200000009']]);
    batchRsc($admin, $akun, 'Camp Habis', '1', [['Ani', '+6281234567890'], ['Budi', '+6281298765432']], ['status' => 'habis']);
    batchRsc($admin, $akun, 'Camp Ganti', '1', [['Caca', '+6281200000000']], ['status' => 'pengganti']);

    $pemasukan = fn () => \App\Models\CashFlow::where('sourceable_type', PemesananRsc::class)->where('type', 'income');

    // Data lama: belum ada baris cash flow sama sekali.
    $this->artisan('rsc:sinkron-cashflow', ['--kering' => true])->assertSuccessful();
    expect($pemasukan()->count())->toBe(0);

    // Bawaan: baru + habis dipulihkan, pengganti dilewati.
    $this->artisan('rsc:sinkron-cashflow')
        ->expectsOutputToContain('dilewati: 1 batch berstatus pengganti')
        ->assertSuccessful();
    expect($pemasukan()->count())->toBe(2)
        ->and((int) $pemasukan()->sum('amount'))->toBe(45000);

    // Idempoten.
    $this->artisan('rsc:sinkron-cashflow')->assertSuccessful();
    expect($pemasukan()->count())->toBe(2);

    // Pengganti hanya dipulihkan bila diminta.
    $this->artisan('rsc:sinkron-cashflow', ['--status' => 'habis,pengganti'])->assertSuccessful();
    expect($pemasukan()->count())->toBe(3);

    // Kartu nilai di daftar = yang tercatat di Cash Flow.
    expect(Livewire::actingAs($admin)->test(PemesananrscList::class)->viewData('ringkas')['nilai'])->toBe(60000.0);
});

it('batch yang dibuat bukan berstatus baru tidak dicatat sebagai pemasukan', function () {
    $admin = adminRsc();
    $akun = akunRsc();

    foreach (['pengganti' => '1', 'habis' => '2'] as $status => $nomor) {
        isiFormRsc(Livewire::actingAs($admin)->test(PemesananrscForm::class), $akun, $admin, 'Camp Uji', $nomor, [['Ani', '081234567890']])
            ->set('status', $status)
            ->call('save')
            ->assertHasNoErrors();
    }

    expect(PemesananRsc::count())->toBe(2)
        ->and(\App\Models\CashFlow::where('sourceable_type', PemesananRsc::class)->where('type', 'income')->count())->toBe(0);
});

it('mengubah status ke habis tidak menghapus pemasukan batch', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Uji', '3', [['Ani', '+6281234567890']]);
    $rows = PemesananRsc::all();

    // Disimpan sebagai baru → tercatat.
    Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
        ->call('save');

    Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
        ->set('status', 'habis')
        ->call('save')
        ->assertHasNoErrors();

    expect(\App\Models\CashFlow::where('sourceable_type', PemesananRsc::class)->where('type', 'income')->sum('amount'))->toEqual(15000);
});

it('batch baru tidak bisa berstatus perpanjang, batch lama tetap bisa disimpan', function () {
    $admin = adminRsc();
    $akun = akunRsc();

    $lw = isiFormRsc(Livewire::actingAs($admin)->test(PemesananrscForm::class), $akun, $admin, 'Camp Uji', '1', [['Ani', '081234567890']]);
    expect($lw->instance()->pilihanStatus())->toBe(['baru', 'pengganti', 'habis']);
    $lw->set('status', 'perpanjang')->call('save')->assertHasErrors(['status']);

    batchRsc($admin, $akun, 'Camp Lama', '1', [['Budi', '+6281298765432']], ['status' => 'perpanjang']);
    $rows = PemesananRsc::where('nama_camp', 'Camp Lama')->get();
    $edit = Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows]);
    expect($edit->instance()->pilihanStatus())->toContain('perpanjang');
    $edit->call('save')->assertHasNoErrors();
});

it('tombol wa memakai api.whatsapp.com dengan pesan akses atau masa habis', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Aktif', '1', [['Ani', '081234567890']], ['tanggal_berakhir' => today()->addMonth()]);
    batchRsc($admin, $akun, 'Camp Selesai', '1', [['Budi', '+6281298765432']], ['tanggal_berakhir' => today()->subDay()]);

    $aktif = Livewire::actingAs($admin)->test(\App\Livewire\Pages\Admin\PemesananRSC\PemesananrscDetail::class, ['nama_camp' => 'Camp Aktif', 'batch_camp' => '1']);
    $wa = collect($aktif->viewData('waPeserta'))->first();
    parse_str(parse_url($wa['url'], PHP_URL_QUERY), $q);
    expect($wa['url'])->toStartWith('https://api.whatsapp.com/send?phone=6281234567890&text=')
        ->and($wa['jenis'])->toBe('akses')
        ->and($q['text'])->toContain('Halo Ani,')
        ->and($q['text'])->toContain('• Password: *rahasia-uji*')
        ->and($q['text'])->toContain('Camp Aktif Batch 1');

    $selesai = Livewire::actingAs($admin)->test(\App\Livewire\Pages\Admin\PemesananRSC\PemesananrscDetail::class, ['nama_camp' => 'Camp Selesai', 'batch_camp' => '1']);
    $wa = collect($selesai->viewData('waPeserta'))->first();
    parse_str(parse_url($wa['url'], PHP_URL_QUERY), $q);
    expect($wa['jenis'])->toBe('habis')
        ->and($q['text'])->toContain('SUDAH HABIS MASA AKTIFNYA')
        ->and($q['text'])->not->toContain('rahasia-uji');
});

it('pemasukan tercatat sekali saat baru dan bertahan walau status diganti', function () {
    $admin = adminRsc();
    $akun = akunRsc();
    batchRsc($admin, $akun, 'Camp Uji', '5', [['Ani', '+6281234567890'], ['Budi', '+6281298765432'], ['Caca', '+6281200000000']]);

    $pemasukan = fn () => \App\Models\CashFlow::where('sourceable_type', PemesananRsc::class)->where('type', 'income');

    foreach (['baru', 'habis', 'baru', 'habis'] as $status) {
        $rows = PemesananRsc::all();
        Livewire::actingAs($admin)
            ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
            ->set('status', $status)
            ->call('save')
            ->assertHasNoErrors();

        expect($pemasukan()->count())->toBe(1)
            ->and((int) $pemasukan()->sum('amount'))->toBe(45000);
    }

    // Diganti jadi pengganti sesudah tercatat: pemasukannya tetap satu,
    // tidak dilepas dan tidak digandakan.
    $rows = PemesananRsc::all();
    Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
        ->set('status', 'pengganti')
        ->call('save')
        ->assertHasNoErrors();
    expect($pemasukan()->count())->toBe(1)
        ->and((int) $pemasukan()->sum('amount'))->toBe(45000);

    // Peserta dihapus sesudahnya: tetap satu baris, nilainya menyesuaikan.
    $rows = PemesananRsc::orderBy('created_at')->orderBy('id')->get();
    Livewire::actingAs($admin)
        ->test(PemesananrscForm::class, ['pemesananrsc' => $rows->first(), 'pemesananBatch' => $rows])
        ->call('removePeserta', $rows->first()->id)
        ->call('save')
        ->assertHasNoErrors();
    expect($pemasukan()->count())->toBe(1)
        ->and((int) $pemasukan()->sum('amount'))->toBe(30000);
});
