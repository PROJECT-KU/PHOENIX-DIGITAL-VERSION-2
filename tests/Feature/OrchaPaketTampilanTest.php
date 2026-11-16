<?php

use Illuminate\Support\Facades\Http;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';

/**
 * Layar daftar Paket Wisata sesudah dipindahkan ke bahasa rupa dasbor.
 *
 * Yang dijaga di sini bukan "cantik atau tidak", melainkan empat cara
 * tampilan ini pernah rusak TANPA satu pun galat di layar lain:
 * kolom yang disembunyikan lewat nomor, keterangan yang disamarkan sampai
 * hilang di desktop, judul kolom yang tidak ikut rata kanan, dan garis
 * kemajuan yang menghitung kuota kosong sebagai nol.
 */
function paketUji(array $ubah = []): array
{
    return array_merge([
        'id' => 7,
        'uuid' => '0d37d7a7-a189-40c5-ba1c-197bfc0d29f7',
        'nama' => 'Open Trip Kawah Ijen',
        'kategori' => 'open_trip',
        'kategori_label' => 'Open Trip',
        'status' => 'tayang',
        'status_tayang' => 'tayang',
        'status_tayang_label' => 'Tayang',
        'tayang_mulai' => null,
        'durasi' => '3 hari 2 malam',
        'jadwal_label' => '27 – 29 November 2026',
        'batas_pelunasan' => '2026-11-20',
        'minimal_peserta' => 4,
        'kuota' => 20,
        'kursi_terpakai' => 5,
        'sisa_kursi' => 15,
        'harga' => 1300000,
        'harga_asli' => 1500000,
        'margin_per_orang' => 300000,
        'margin_per_orang_teks' => 'Rp 300.000',
        'diskon_persen' => 13,
        'pilihan_terbaik' => false,
        'jumlah_pendaftar' => 3,
        'tautan_publik' => 'https://orchajourney.com/paket/0d37d7a7-a189-40c5-ba1c-197bfc0d29f7',
    ], $ubah);
}

/** @return string HTML halaman daftar paket. */
function halamanPaket(array $baris): string
{
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['kategori_paket' => ['open_trip' => 'Open Trip']]]),
        '*/paket-wisata*' => Http::response(['data' => $baris, 'meta' => ['total' => count($baris)]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');

    return test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata')->assertOk()->getContent();
}

/**
 * Halaman tanpa satu pun blok <style>.
 *
 * Gaya Orcha dimuat SEBARIS, jadi seluruh aturan CSS-nya — termasuk definisi
 * kelas lama .orcha-tabel yang dipakai 13 layar lain — ikut jadi isi halaman
 * ini. Menguji "kelas lama sudah tidak ada" atas seluruh respons karena itu
 * selalu merah, padahal markupnya sudah bersih.
 */
function markahPaket(string $isi): string
{
    return preg_replace('/<style\b[^>]*>.*?<\/style>/s', '', $isi);
}

/**
 * Blok gaya layar ini saja, komentarnya dibuang.
 *
 * Komentar CSS di dalam <style> sebaris ikut terkirim sebagai isi halaman.
 * Blok ini MENYEBUT ":nth-child" untuk menjelaskan kenapa ia tidak dipakai —
 * dan kalimat itu sendiri yang dulu membuat penjaganya merah.
 */
function gayaPaket(string $isi): string
{
    $mulai = strpos($isi, '.pw-saring');

    expect($mulai)->not->toBeFalse('Blok gaya layar paket tidak ditemukan.');

    $blok = substr($isi, $mulai, strpos($isi, '</style>', $mulai) - $mulai);

    return preg_replace('/\/\*.*?\*\//s', '', $blok);
}

/** Iris tabelnya sendiri, bukan '<tbody' pertama di halaman. */
function tabelPaket(string $isi): string
{
    $mulai = strpos($isi, 'class="dsb-tabel pw-tabel"');

    expect($mulai)->not->toBeFalse('Tabel .dsb-tabel pw-tabel tidak ditemukan.');

    return substr($isi, $mulai, strpos($isi, '</table>', $mulai) - $mulai);
}

test('daftar paket memakai bahasa rupa dasbor, bukan kartu lama', function () {
    $isi = halamanPaket([paketUji()]);

    /*
     | Patokannya layar Pesanan Toko: kepala hero, kartu dsb, dan tabel dsb.
     | Kelas lama orcha-tabel/orcha-aksi tidak boleh tertinggal — satu tabel
     | yang masih memakainya berdiri dengan tinggi baris dan tepi yang berbeda
     | di tengah halaman yang sudah pindah.
     */
    $markah = markahPaket($isi);

    expect($markah)
        ->toContain('class="dsb-tabel pw-tabel"')
        ->toContain('dsb-hero')
        ->toContain('dsb-kartu')
        ->not->toContain('orcha-tabel')
        ->not->toContain('orcha-aksi-ubah')
        ->not->toContain('orcha-gulung');
});

test('judul kolom yang isinya rata kanan ikut rata kanan', function () {
    $isi = halamanPaket([paketUji()]);

    // Kalau hanya selnya yang dirata-kanan, judul dan angkanya berdiri di dua
    // garis yang berbeda — terbaca sebagai kolom yang melenceng.
    $kepala = substr($isi, strpos($isi, '<thead'), strpos($isi, '</thead>') - strpos($isi, '<thead'));

    // Harga & Aksi rata kanan, judulnya ikut.
    expect(substr_count($kepala, 'text-align: right;'))->toBe(2);
});

test('kolom tidak disembunyikan lewat nomor urutnya', function () {
    $isi = halamanPaket([paketUji()]);

    /*
     | Nomor kolom adalah patokan yang diam-diam berubah arti: begitu satu
     | kolom dilebur atau disisipkan, nomornya bergeser dan aturan yang dulu
     | menyembunyikan "Jadwal" berpindah menyembunyikan "Aksi" — tanpa galat
     | dan tanpa uji yang merah. Master Harga pernah kehilangan tombol ubah &
     | hapusnya persis begitu.
     */
    expect(gayaPaket($isi))->not->toContain('nth-child');

    // Yang dipakai kelas kolomnya, dan bahasa rupa dasbor sudah mengurusnya.
    expect(tabelPaket($isi))->toContain('class="k-sedang"');
});

test('keterangan yang tidak punya kolom lain tidak disamarkan', function () {
    $isi = halamanPaket([paketUji([
        'status_tayang' => 'terjadwal',
        'status_tayang_label' => 'Terjadwal',
        'tayang_mulai' => '2026-12-03T08:00',
    ])]);

    /*
     | .dsb-tabel-samar sengaja DISEMBUNYIKAN di layar lebar — tempatnya memang
     | untuk salinan kolom yang sudah tampil di sebelahnya. "mulai 3 Des",
     | "pelunasan sd …", dan "3 pendaftar · min 4" tidak punya kolom lain, jadi
     | menyamarkannya berarti ketiganya hilang sama sekali di desktop, padahal
     | di sanalah layar ini dipakai. Sewa Kendaraan pernah kehilangan nomor
     | WhatsApp-nya persis begitu.
     */
    $tabel = tabelPaket($isi);

    expect($tabel)
        ->toContain('mulai 03 Des 2026, 08:00')
        ->toContain('pelunasan sd 20 Nov 2026')
        ->toContain('3 pendaftar · min 4');

    foreach (['mulai 03 Des 2026, 08:00', 'pelunasan sd 20 Nov 2026', '3 pendaftar · min 4'] as $teks) {
        $potongan = substr($tabel, max(0, strpos($tabel, $teks) - 160), 160);

        expect($potongan)->toContain('pw-ket')
            ->and($potongan)->not->toContain('dsb-tabel-samar');
    }

    // Yang MEMANG salinan kolom tetap boleh samar: Jadwal hilang di layar
    // menengah, jadi ia perlu kembar samarnya di kolom Paket.
    expect($tabel)->toContain('dsb-tabel-samar');
});

test('kuota kosong tidak digambar sebagai garis penuh', function () {
    $isi = halamanPaket([paketUji([
        'kuota' => null,
        'kursi_terpakai' => 0,
        'sisa_kursi' => null,
        'jumlah_pendaftar' => 0,
    ])]);

    /*
     | Kuota kosong berarti BELUM DITETAPKAN, bukan nol — paket tanpa kuota
     | tidak pernah penuh. Membaginya sebagai nol akan menggambar garis penuh
     | dan lencana "penuh" untuk paket yang justru masih kosong melompong,
     | dan admin membacanya sebagai trip yang sudah tidak menerima pendaftar.
     */
    $tabel = tabelPaket($isi);

    expect($tabel)
        ->not->toContain('dsb-kemajuan')
        ->not->toContain('>penuh')
        ->not->toContain('/ 0');
});

test('kursi penuh berwarna hijau, bukan merah', function () {
    $isi = halamanPaket([paketUji(['kursi_terpakai' => 20, 'sisa_kursi' => 0])]);

    // Penuh itu kabar BAIK bagi admin — tripnya terjual habis. Merah di layar
    // ini dipakai untuk yang salah, bukan untuk yang berhasil.
    $tabel = tabelPaket($isi);

    expect($tabel)
        ->toContain('20 / 20')
        ->toContain('penuh')
        ->toContain('--c: #16a34a')
        ->and(substr_count($tabel, 'width: 100%'))->toBe(1);
});

test('paket tanpa modal ditandai di baris dan dihitung di kepala', function () {
    $isi = halamanPaket([
        paketUji(),
        paketUji(['id' => 8, 'nama' => 'Private Trip Baluran', 'margin_per_orang' => null, 'margin_per_orang_teks' => null]),
    ]);

    /*
     | Inilah yang diam-diam merugikan: harganya sudah tayang dan orang sudah
     | bisa mendaftar, tetapi tidak ada seorang pun yang tahu paket itu untung
     | atau tidak. Angkanya disebut di kepala halaman, bukan ditemukan dengan
     | membaca kolom harga satu per satu.
     */
    expect($isi)->toContain('1 modal belum diisi');

    $tabel = tabelPaket($isi);

    // Satu baris ditandai, bukan keduanya.
    expect(substr_count($tabel, 'is-tanda'))->toBe(1)
        ->and(substr_count($tabel, 'modal belum diisi'))->toBe(1);
});

test('cip kepala hanya menyebut yang memang ada', function () {
    // Cip "0 sedang tayang" yang selalu berdiri membuat mata berhenti
    // membacanya, dan pada hari yang benar-benar ada masalahnya ia tidak lagi
    // menarik perhatian siapa pun.
    $isi = halamanPaket([paketUji(['status_tayang' => 'arsip', 'status_tayang_label' => 'Arsip'])]);

    expect($isi)
        ->toContain('1 paket')
        ->not->toContain('sedang tayang')
        ->not->toContain('modal belum diisi');
});

test('daftar kosong menyebut sebabnya: belum ada, atau tersaring', function () {
    $isi = halamanPaket([]);

    expect($isi)
        ->toContain('Belum ada paket wisata')
        ->not->toContain('Tidak ada paket yang cocok');
});
