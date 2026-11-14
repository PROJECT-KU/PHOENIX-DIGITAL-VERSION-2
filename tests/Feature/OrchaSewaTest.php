<?php

use Illuminate\Support\Facades\Http;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';

function sewaUji(array $ubah = []): array
{
    return array_merge([
        'id' => 5, 'kode' => 'SW-0001', 'nama' => 'Budi', 'whatsapp' => '0812', 'email' => null,
        'kendaraan' => ['id' => 1, 'nama' => 'Avanza', 'transmisi' => 'Matic', 'sebutan' => 'Mobil',
            'kapasitas' => 6, 'kursi_total' => 7],
        'sopir_label' => 'Lepas kunci', 'operasional_label' => '-', 'satuan' => 'hari',
        'satuan_label' => 'Hari', 'durasi' => 2, 'durasi_label' => '2 hari',
        'tanggal_mulai' => '2026-09-01', 'jam_mulai' => '08:00', 'dengan_sopir' => false,
        'lokasi_antar' => 'Jogja', 'lokasi_kembali' => 'Jogja', 'tujuan' => null, 'luar_kota' => false,
        'tanggal_selesai' => '2026-09-03', 'jam_selesai' => '08:00', 'jadwal_selesai' => null,
        'terlambat' => false, 'terlambat_menit' => 0,
        'denda_keterlambatan_usulan' => 0, 'denda_kerusakan_usulan' => 0,
        'rincian_denda_kerusakan' => [], 'rincian_denda' => [],
        'diserahkan_pada' => null, 'dikembalikan_pada' => null,
        'kilometer_awal' => null, 'kilometer_akhir' => null,
        'bahan_bakar_awal' => null, 'bahan_bakar_akhir' => null,
        'jaminan' => null, 'berkas_jaminan' => null,
        'kondisi_awal' => [], 'kondisi_akhir' => [], 'kerusakan_baru' => [],
        'kondisi_unit_terkini' => [], 'estimasi_biaya' => 500000,
        'denda_keterlambatan' => 0, 'denda_kerusakan' => 0, 'denda_lain' => 0,
        'catatan_denda' => null, 'total_denda' => 0, 'total_tagihan' => 500000,
        'catatan' => null, 'status' => 'baru', 'status_label' => 'Baru',
        'dibuat_pada' => '2026-08-25T09:00:00+07:00',
    ], $ubah);
}

test('tombol serah terima adalah tautan ke halamannya sendiri', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Lembarnya kini halaman tersendiri. Sebagai jendela, ia dibuka lewat
     | tombol Livewire — dan tombol itu tidak merespons di tempat admin.
     | Tautan biasa tidak menunggu apa pun hidup lebih dulu: alamatnya sudah
     | ada di HTML, dan peramban tahu cara membukanya tanpa JavaScript.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    expect($isi)
        ->toContain('/admin/orcha/penyewaan/5/serah-terima')
        ->not->toContain('wire:click="buka(')
        ->not->toContain('buka({"');
});

test('halaman serah terima mengisi lembarnya dari nomor di alamat', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => sewaUji(['kilometer_awal' => 12345])]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/5/serah-terima')
        ->assertOk()
        ->assertSee('Serah Terima Kendaraan')
        ->assertSee('SW-0001')
        ->assertSee('12345');
});

test('nomor yang sudah tidak ada memberi jalan kembali, bukan lembar kosong', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => null]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Sewa bisa hilang di antara halaman digambar dan tautan ditekan — dihapus
    // admin lain, atau alamatnya disalin dari catatan lama.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan/999/serah-terima')
        ->assertOk()
        ->assertSee('tidak bisa dibuka')
        ->assertSee('Kembali ke daftar');
});

test('daftar sewa tidak lagi memikul isi lembar serah terima', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Dulu seluruh lembar — pemeriksaan bodi per bagian, rincian denda, kamera
    // — ikut tergambar di setiap pemuatan daftar meski tak seorang pun
    // membukanya, dan setiap penekanan tombol menyeret keadaan sebesar itu
    // bolak-balik ke server.
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    expect($isi)
        ->not->toContain('Simpan Serah Terima')
        ->not->toContain('orcha-kamera-video');
});

test('judul kolom daftar sewa ditengahkan, termasuk yang isinya rata kanan', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    /*
     | Layar ini dipindahkan ke bahasa rupa dasbor (.dsb-tabel), yang sudah
     | mengurus perataan judul kolomnya sendiri. Yang dijaga sekarang: tabelnya
     | memang .dsb-tabel, dan kolom yang isinya rata kanan menyatakan
     | perataannya di judulnya juga — kalau hanya selnya yang dirata-kanan,
     | judul dan angkanya berdiri di dua garis yang berbeda.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    $kepala = substr($isi, strpos($isi, '<thead'), strpos($isi, '</thead>') - strpos($isi, '<thead'));

    expect($isi)->toContain('class="dsb-tabel sw-tabel"')
        // Tagihan & Aksi rata kanan, judulnya ikut.
        ->and(substr_count($kepala, 'text-align: right;'))->toBe(2);
});

test('dua tombol di kolom aksi sama tingginya', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji()], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Terukur sebelumnya: ikon detail 58x38 dan tombol serah terima 127x29 —
    // dua benda bersebelahan dengan tinggi berbeda sembilan piksel, dan garis
    // bawahnya tidak pernah sejajar.
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')->assertOk()->getContent();

    /*
     | Diiris dari TABELNYA, bukan dari '<tbody' pertama di halaman.
     |
     | Halaman admin memuat tabel lain lebih dulu (bilah samping), jadi irisan
     | yang dimulai dari '<tbody' pertama justru membentang melewati blok
     | <style> — dan nama kelas yang muncul di aturan CSS ikut terhitung.
     | Terukur: 6 kemunculan untuk satu baris data.
     */
    $mulaiTabel = strpos($isi, 'class="dsb-tabel sw-tabel"');
    $tabel = substr($isi, $mulaiTabel, strpos($isi, '</table>', $mulaiTabel) - $mulaiTabel);

    // Dua kendali di satu sel: tombol ikon detail, dan tombol berlabel.
    expect(substr_count($tabel, 'dsb-tabel-btn'))->toBe(1)
        ->and(substr_count($tabel, 'sw-lanjut'))->toBe(1);

    /*
     | Tingginya DIPATOK sama, bukan diperiksa lewat nama kelasnya: nama yang
     | sama tidak menjamin angka yang sama, dan selisih sembilan piksel itulah
     | yang dulu membuat garis bawah keduanya tidak pernah sejajar.
     |
     | .dsb-tabel-btn setinggi 34px (partials/dasbor-gaya); yang berlabel
     | mengikuti angka itu, bukan 44px bawaan .dsb-tombol yang terlalu jangkung
     | untuk duduk di dalam baris tabel.
     */
    $gayaDasbor = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));
    preg_match('/\.dsb-tabel-btn\s*\{[^}]*?height:\s*(\d+)px/s', $gayaDasbor, $ikon);

    $mulai = strpos($isi, '.sw-lanjut {');
    preg_match('/height:\s*(\d+)px/', substr($isi, $mulai, strpos($isi, '}', $mulai) - $mulai), $label);

    expect($ikon[1] ?? null)->not->toBeNull('Tinggi .dsb-tabel-btn tidak ditemukan.')
        ->and($label[1] ?? null)->toBe($ikon[1], 'Tombol berlabel tidak setinggi tombol ikon di sebelahnya.');
});

test('status di daftar sewa ringkas dan berwarna menurut keadaannya', function () {
    Http::fake([
        '*/penyewaan*' => Http::response(['data' => [sewaUji(['status' => 'berjalan'])], 'meta' => []]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Layout memaksa 48px pada SETIAP .form-select — terlalu besar untuk satu
    // sel tabel, dan daftar sepuluh sewa jadi memakan layar untuk dua puluh.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/penyewaan')
        ->assertOk()
        ->assertSee('orcha-status-ringkas', false)
        ->assertSee('data-status="berjalan"', false);
});
