<?php

/**
 * Perkakas di layar daftar pendaftaran: urutan, pilih beberapa sekaligus,
 * dan ekspor rekap.
 *
 * Sebelum ini urutannya terkunci (terbaru mendaftar lebih dulu), mengabari
 * dua puluh orang berarti membuka dua puluh halaman, dan satu-satunya ekspor
 * berbentuk daftar adalah manifes tour leader — sehingga rekap kantor disalin
 * tangan dari layar.
 */

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function adminAlatDaftar(): User
{
    $role = Role::create(['name' => 'uji-alat-'.uniqid(), 'description' => 'Peran uji alat daftar']);

    foreach (['akses_orcha', 'view_orcha_kesehatan'] as $nama) {
        $izin = Permission::firstOrCreate(['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']);
        $role->permissions()->attach($izin->id);
    }

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function barisDaftarAlat(int $id, array $ubah = []): array
{
    return array_merge([
        'id' => $id, 'kode' => 'OT-1711-A'.$id, 'nama' => 'Pemesan '.$id,
        'whatsapp' => '08120000000'.$id, 'email' => null,
        'jumlah_peserta' => 2, 'pendamping_gratis' => 0, 'peserta_dibayar' => 2,
        'peserta' => [], 'jemput_per_titik' => [],
        'bus_per_kelompok' => [], 'kamar_per_kelompok' => [],
        'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => [],
        'paket' => ['id' => 5, 'nama' => 'Open Trip Bromo', 'titik_jemput' => []],
        'keberangkatan_id' => 11,
        'keberangkatan' => ['id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026', 'aktif' => true],
        'tanggal_berangkat' => now()->addDays(20)->toDateString(),
        'hari_ke_berangkat' => 20, 'pengingat_pelunasan_pada' => null,
        'titik_jemput' => 'Terminal', 'catatan' => null, 'catatan_internal' => null,
        'status' => 'baru', 'status_label' => 'Baru',
        'riwayat_penggantian' => [], 'surat_penggantian' => null, 'surat_penggantian_pada' => null,
        'tautan_kesehatan' => 'https://orchajourney.com/riwayat-kesehatan?kode=OT-1711-A'.$id,
        'dibuat_pada' => now()->toIso8601String(),
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 0, 'sudah_teks' => 'Rp 0',
            'sisa' => 4000000, 'sisa_teks' => 'Rp 4.000.000',
            'dp' => 1000000, 'dp_teks' => 'Rp 1.000.000', 'dp_persen' => 25,
            'lunas' => false, 'jenis_disarankan' => 'dp',
        ],
        'pembayaran' => [], 'pembatalan' => null, 'angsuran' => null,
    ], $ubah);
}

/** Parameter yang benar-benar sampai ke Orcha pada permintaan daftar terakhir. */
function parameterDaftarTerakhir(): array
{
    $permintaan = collect(Http::recorded())
        ->map(fn ($pasang) => $pasang[0])
        ->filter(fn ($p) => str_contains($p->url(), '/pendaftaran')
            && ! str_contains($p->url(), '/rujukan'))
        ->last();

    if (! $permintaan) {
        return [];
    }

    parse_str(parse_url($permintaan->url(), PHP_URL_QUERY) ?? '', $hasil);

    return $hasil;
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake(function ($permintaan) {
        if (str_contains($permintaan->url(), '/rujukan')) {
            return Http::response(['data' => [
                'status_pendaftaran' => ['baru' => 'Baru', 'dp_masuk' => 'DP Masuk', 'batal' => 'Batal'],
                'jenis_pembayaran' => ['dp' => 'DP'],
                'pembayaran' => ['pelunasan_hari_sebelum' => 5],
                'paket_wisata' => [[
                    'id' => 5, 'nama' => 'Open Trip Bromo', 'tanggal_berangkat' => null,
                    'keberangkatan' => [
                        ['id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026', 'aktif' => true,
                            'penuh' => false, 'sisa_kursi' => 10],
                        ['id' => 12, 'rentang_tanggal' => '11 – 13 Des 2026', 'aktif' => true,
                            'penuh' => false, 'sisa_kursi' => 4],
                    ],
                ]],
            ]]);
        }

        return Http::response([
            'data' => [
                barisDaftarAlat(1),
                // Pendaftaran lama: belum terhubung ke rombongan mana pun.
                barisDaftarAlat(2, ['keberangkatan_id' => null, 'keberangkatan' => null]),
            ],
            'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 2,
                'jumlah_tab' => ['semua' => 2]],
        ]);
    });
});

test('pemilih urutan ada dan pilihannya sampai ke Orcha', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('Urutkan')
        ->assertSee('Berangkat paling dekat')
        ->set('urut', 'berangkat_dekat');

    expect(parameterDaftarTerakhir()['urut'] ?? null)->toBe('berangkat_dekat');
});

test('urutan bawaan tidak ikut dikirim', function () {
    /*
     | "terbaru" sudah urutan bawaan Orcha. Parameter yang selalu menempel
     | membuat alamat halaman berbeda tanpa ada yang berbeda — dan alamat yang
     | disalin admin lalu memuat pilihan yang tidak pernah ia buat.
     */
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class);

    expect(parameterDaftarTerakhir())->not->toHaveKey('urut');
});

test('kepala kolom berangkat bergantian naik-turun', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->call('urutkanKolom', 'berangkat_dekat', 'berangkat_jauh')
        ->assertSet('urut', 'berangkat_dekat')
        ->call('urutkanKolom', 'berangkat_dekat', 'berangkat_jauh')
        ->assertSet('urut', 'berangkat_jauh')
        ->call('urutkanKolom', 'berangkat_dekat', 'berangkat_jauh')
        ->assertSet('urut', 'berangkat_dekat');
});

test('kunci urut yang tidak dikenal dipulangkan ke bawaannya', function () {
    /*
     | Orcha mengabaikan kunci yang tidak dikenal tanpa galat. Dibiarkan, layar
     | menampilkan pilihan yang tidak berlaku — keterangan yang salah, bukan
     | sekadar tidak berguna.
     */
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('urut', 'tidak-ada-urutan-ini')
        ->assertSet('urut', 'terbaru');
});

test('centang semua yang tampil mencentang dua baris, lalu mengosongkannya', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->call('pilihSemuaTampil', ['1', '2'])
        ->assertSet('dipilih', ['1', '2'])
        // Ditekan lagi: mengosongkan, bukan mencentang ulang.
        ->call('pilihSemuaTampil', ['1', '2'])
        ->assertSet('dipilih', []);
});

test('panel pesan massal menolak dibuka tanpa centangan', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->call('bukaKirimMassal')
        ->assertDispatched('toast-error')
        ->assertSet('bukaPesanMassal', false);
});

test('panel pesan massal menyiapkan tautan per orang', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('dipilih', ['1', '2'])
        ->call('bukaKirimMassal')
        ->assertSet('bukaPesanMassal', true)
        ->assertSee('2 pendaftaran dicentang')
        // Nomornya sudah berawalan 62, siap dibuka WhatsApp.
        ->assertSee('phone=6281200000001', false);
});

test('centangan di halaman lain disebut selisihnya, bukan didiamkan', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        // Baris 9 tidak ada di halaman ini.
        ->set('dipilih', ['1', '9'])
        ->call('bukaKirimMassal')
        ->assertSee('1 lagi di halaman lain');
});

test('membersihkan saringan ikut mengosongkan centangan', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('dipilih', ['1'])
        ->set('cari', 'budi')
        ->call('bersihkanSaringan')
        ->assertSet('dipilih', [])
        ->assertSet('bukaPesanMassal', false);
});

test('pendaftaran tanpa rombongan ditandai di daftar', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('belum punya rombongan');
});

test('berkas unduhan dikelompokkan per PEMBACANYA, bukan per nama berkas', function () {
    /*
     | Tiga tombol unduh berjajar berarti tiga hal yang harus dibaca sebelum
     | menekan satu — dan ketiganya berbunyi mirip. Kalimatnya menyebut untuk
     | SIAPA berkasnya, bukan nama berkasnya.
     |
     | Manifes PDF dan Excel harus berpasangan: yang satu dibawa berdiri di
     | parkiran, yang satu disaring di laptop, dan isinya sama persis.
     */
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('Untuk tour leader di lapangan')
        ->assertSee('Unduh Daftar Peserta untuk tour leader (PDF)')
        ->assertSee('Unduh Daftar Peserta untuk tour leader (Excel)')
        ->assertSee('isinya sama persis')
        ->assertSee('Untuk kantor')
        ->assertSee('Rekap uang per pemesanan (Excel)')
        ->assertSee('Daftar Tunggu');
});

test('manifes PDF dan Excel membawa saringan yang sama', function () {
    /*
     | Dua berkas yang menjawab pertanyaan yang sama tetapi berbeda isinya
     | adalah asal perselisihan di parkiran.
     */
    $html = Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '5')
        ->set('filterKeberangkatan', '12')
        ->html();

    expect(substr_count($html, 'keberangkatan_id=12'))->toBeGreaterThanOrEqual(2);
});

test('tautan berkas ikut membawa saringan rombongan dan urutan', function () {
    /*
     | Inilah yang dulu meleset: manifes tour leader hanya membaca cari,
     | status, dan paket_id. Tour leader yang menyaring "4–6 Des" lalu menekan
     | Unduh mendapat ketiga rombongan sekaligus — lembarnya terbit, isinya
     | banyak, dan kelebihannya baru ketahuan di parkiran.
     */
    $layar = Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '5')
        ->set('filterKeberangkatan', '12')
        ->set('urut', 'berangkat_dekat');

    $saringan = $layar->instance()->saringanTampil();

    expect($saringan['keberangkatan_id'])->toBe('12');
    expect($saringan['urut'])->toBe('berangkat_dekat');
    expect($saringan['paket_id'])->toBe('5');
});

/*
|--------------------------------------------------------------------------
| Keadaan massal & gabung
|--------------------------------------------------------------------------
*/

test('sisa tagihan sekarang ada di daftar urutan', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->assertSee('Sisa tagihan terbesar')
        ->set('urut', 'sisa_besar');

    expect(parameterDaftarTerakhir()['urut'] ?? null)->toBe('sisa_besar');
});

test('menandai keadaan beberapa sekaligus menolak tanpa centangan', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('keadaanMassal', 'dihubungi')
        ->call('ubahKeadaanMassal')
        ->assertDispatched('toast-error');
});

test('menandai keadaan beberapa sekaligus mengirim id yang dicentang', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('dipilih', ['1', '2'])
        ->set('keadaanMassal', 'dihubungi')
        ->call('ubahKeadaanMassal')
        ->assertSet('dipilih', [])
        ->assertDispatched('order-updated');

    $terkirim = collect(Http::recorded())
        ->map(fn ($pasang) => $pasang[0])
        ->first(fn ($p) => str_contains($p->url(), 'status-massal'));

    expect($terkirim->data()['id'])->toBe([1, 2]);
    expect($terkirim->data()['status'])->toBe('dihubungi');
});

test('batal tidak pernah ditawarkan sebagai keadaan massal', function () {
    /*
     | Membatalkan melepas kursi dan mengabari daftar tunggu. Dua puluh
     | pembatalan dari satu salah klik tidak bisa dibatalkan, dan tidak ada
     | layar yang bisa menjelaskan apa yang baru saja terjadi.
     */
    $layar = Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class);

    expect(array_keys($layar->instance()->pilihanKeadaanMassal()))
        ->not->toContain('batal');
});

test('gabung menuntut tepat dua centangan', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('dipilih', ['1'])
        ->call('bukaFormulirGabung')
        ->assertDispatched('toast-error')
        ->assertSet('bukaGabung', false);
});

test('panel gabung menawarkan dua kode, dan yang ditekan jadi yang bertahan', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('dipilih', ['1', '2'])
        ->call('bukaFormulirGabung')
        ->assertSet('bukaGabung', true)
        ->assertSee('Pilih pendaftaran yang BERTAHAN')
        ->assertSee('OT-1711-A1')
        ->assertSee('OT-1711-A2');
});

test('gabung mengirim yang satunya sebagai sumber', function () {
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('dipilih', ['1', '2'])
        ->call('bukaFormulirGabung')
        // Yang dipilih bertahan adalah 2, jadi yang dikirim sebagai sumber 1.
        ->call('gabungkan', 2)
        ->assertSet('dipilih', [])
        ->assertDispatched('order-updated');

    $terkirim = collect(Http::recorded())
        ->map(fn ($pasang) => $pasang[0])
        ->first(fn ($p) => str_contains($p->url(), 'gabungkan'));

    expect($terkirim->url())->toContain('/pendaftaran/1/gabungkan');
    expect($terkirim->data()['ke'])->toBe(2);
});

test('tautan daftar tunggu membawa tanggal rombongan saat ada yang disaring', function () {
    /*
     | Saat yang orang teringat daftar tunggu adalah saat ia baru membatalkan
     | satu pendaftaran dan satu kursi menganggur — dan yang ia butuhkan pada
     | saat itu bukan daftar antrean, melainkan kalimat yang siap dikirim
     | lengkap dengan tanggalnya.
     */
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '5')
        ->set('filterKeberangkatan', '12')
        ->assertSee('Tawarkan ke Daftar Tunggu')
        // Tanggalnya ikut di kueri. Potongannya pendek dengan sengaja:
        // route() menyandikan spasi sebagai %20, dan memaku sandi lengkapnya
        // membuat uji ini merah pada perubahan yang tidak ada hubungannya.
        ->assertSee('tawarkan=11', false);
});

test('tiap baris punya tombol bagi kursi & kamar, tanpa membuka detailnya dulu', function () {
    /*
     | Pembagiannya milik rombongan, bukan milik satu pemesanan — tetapi admin
     | selalu tiba di sini lewat orangnya, bukan lewat tanggalnya. Sebelum
     | tombol ini ada, ia harus membuka detail satu pendaftaran dulu hanya
     | untuk sampai ke papan pembagian rombongannya.
     */
    Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        // Baris 1 punya rombongan 11.
        ->assertSee('/rombongan/11/pembagian', false);
});

test('baris tanpa rombongan tidak diberi tombol yang pasti gagal', function () {
    /*
     | Baris 2 adalah pendaftaran lama tanpa keberangkatan_id — tidak punya
     | satuan untuk dibagi. Tombol yang membuka halaman kosong lebih buruk
     | daripada tombol yang tidak ada; penandanya sudah ada di kolom Berangkat.
     */
    $html = Livewire::actingAs(adminAlatDaftar())
        ->test(OrchaPendaftaranList::class)
        ->html();

    // Hanya SATU tautan pembagian dari badan tabel, bukan dua.
    expect(substr_count($html, '/pembagian'))->toBe(1);
});
