<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\RagamJasa;
use Illuminate\Support\Str;

/**
 * Halaman /cek berbicara dengan kosakata jasanya sendiri.
 *
 * Dulu seluruh halaman berbicara sebagai "pengecekan". Untuk pesanan PARAFRASE
 * itu keliru: tidak ada yang diperiksa di sana, naskahnya ditulis ulang — dan
 * janji "No Repository" serta "100% Turnitin" yang ikut tampil adalah janji
 * yang tidak pernah kami buat untuk layanan itu.
 */
function produkJasa(string $nama, array $sifat): Product
{
    return Product::create(array_merge([
        'nama_akun' => $nama,
        'butuh_file' => true,
        'harga_perbulan' => 100000,
    ], $sifat));
}

function pesananDenganProduk(Product ...$produk): Order
{
    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-RAGAM-'.random_int(1000, 9999),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli', 'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'ragam'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 100000, 'total' => 100000, 'unique_code' => 0,
        'status' => 'paid', 'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    foreach ($produk as $p) {
        OrderItem::create([
            'id' => Str::uuid(), 'order_id' => $order->id, 'product_id' => $p->id,
            'product_name' => $p->nama_akun, 'duration_type' => 'bulan', 'duration_value' => 1,
            'price' => 100000, 'quantity' => 1, 'subtotal' => 100000, 'addons' => [],
        ]);
    }

    return $order->fresh(['items']);
}

it('pesanan parafrase memakai kosakata pengerjaan, bukan pengecekan', function () {
    $order = pesananDenganProduk(produkJasa('Jasa Parafrase Manual', ['jasa_mode' => 'halaman']));

    $r = RagamJasa::untuk($order);

    expect(RagamJasa::jenisPesanan($order))->toBe('parafrase')
        ->and($r['judul'])->toBe('Halaman Pengerjaan Anda')
        ->and($r['tombol'])->toBe('Kirim untuk Dikerjakan')
        ->and($r['satuan'])->toBe('naskah');

    // Tidak boleh ada satu pun kata "pengecekan"/"diperiksa" di kosakatanya.
    $semua = strtolower(json_encode($r, JSON_UNESCAPED_UNICODE));
    expect($semua)->not->toContain('pengecekan')
        ->and($semua)->not->toContain('diperiksa');
});

it('janji Turnitin tidak pernah tampil pada pesanan parafrase', function () {
    $order = pesananDenganProduk(produkJasa('Jasa Parafrase Manual', ['jasa_mode' => 'halaman']));

    $jaminan = json_encode(RagamJasa::untuk($order)['jaminan'], JSON_UNESCAPED_UNICODE);

    expect($jaminan)->not->toContain('Turnitin')
        ->and($jaminan)->not->toContain('No Repository');
});

it('pesanan cek plagiasi tetap memakai kosakata pengecekan', function () {
    $order = pesananDenganProduk(produkJasa('Cek Plagiasi Turnitin', ['pakai_exclude' => true]));

    $r = RagamJasa::untuk($order);

    expect(RagamJasa::jenisPesanan($order))->toBe('plagiasi')
        ->and($r['judul'])->toBe('Halaman Pengecekan Anda')
        ->and($r['tombol'])->toBe('Kirim untuk Diperiksa')
        ->and(json_encode($r['jaminan']))->toContain('Turnitin');
});

it('pesanan deteksi AI punya kosakata dan peringatan bahasanya sendiri', function () {
    $order = pesananDenganProduk(produkJasa('Cek Plagiasi AI', ['cek_ai' => true]));

    $r = RagamJasa::untuk($order);

    expect(RagamJasa::jenisPesanan($order))->toBe('ai')
        ->and($r['judul'])->toBe('Halaman Deteksi AI Anda')
        ->and($r['satuan'])->toBe('deteksi')
        // Syarat bahasa Inggris wajib disebut: tanpa itu pelanggan mengirim
        // naskah Indonesia dan hasilnya nihil tanpa ia tahu sebabnya.
        ->and(json_encode($r['jaminan'], JSON_UNESCAPED_UNICODE))->toContain('bahasa Inggris');
});

it('ketiga jasa berbeda judul, tombol, dan label jatahnya', function () {
    /*
     | 'warna' SENGAJA tidak lagi ada di daftar ini.
     |
     | Dulu ada, dan itu memaksa deteksi AI memakai warna karangan sendiri
     | (indigo) hanya supaya berbeda — padahal Shop menaruh produk deteksi AI
     | di kategori 'Cek Plagiasi' yang biru. Barang yang sama karena itu
     | berganti warna di tengah jalan: biru di Shop, indigo di /cek.
     |
     | Yang membedakan kedua halaman bagi pelanggan adalah KATA-KATANYA —
     | judulnya "Halaman Pengecekan Anda" lawan "Halaman Deteksi AI Anda",
     | tombolnya "Kirim untuk Diperiksa" lawan "Kirim untuk Dideteksi". Itulah
     | yang tetap wajib unik. Warnanya mengikuti Shop, dan dijaga uji di bawah.
     */
    $j = ['plagiasi', 'ai', 'parafrase'];

    foreach (['judul', 'tombol', 'riwayat', 'jatahLabel'] as $kunci) {
        $nilai = array_map(fn ($x) => RagamJasa::dariJenis($x)[$kunci], $j);

        expect($nilai)->toHaveCount(count(array_unique($nilai)), "kunci '$kunci' tidak unik antar jasa");
    }
});

it('add-on tidak mengubah identitas halaman', function () {
    // Pesanan parafrase yang menambah hasil plagiasi & AI tetap sebuah pesanan
    // parafrase — add-on adalah berkas balasan, bukan layanan yang dipesan.
    $parafrase = produkJasa('Jasa Parafrase Manual', ['jasa_mode' => 'halaman']);

    $order = pesananDenganProduk($parafrase);
    $order->items->first()->update(['addons' => [
        ['nama' => 'Cek Plagiasi Turnitin', 'harga' => 0],
        ['nama' => 'Cek Plagiasi AI', 'harga' => 0],
    ]]);

    expect(RagamJasa::jenisPesanan($order->fresh('items')))->toBe('parafrase');
});

it('pesanan campuran memakai kosakata netral', function () {
    // Menonjolkan salah satu jasa berarti menyembunyikan yang lain.
    $order = pesananDenganProduk(
        produkJasa('Jasa Parafrase Manual', ['jasa_mode' => 'halaman']),
        produkJasa('Cek Plagiasi Turnitin', ['pakai_exclude' => true]),
    );

    expect(RagamJasa::jenisPesanan($order))->toBe(RagamJasa::BAWAAN);
});

it('setiap jenis punya seluruh kunci yang dipakai halaman', function () {
    // Satu kunci yang lupa diisi akan tampil sebagai halaman kosong tanpa
    // pesan galat — jenis kesalahan yang paling lama tidak ketahuan.
    $wajib = array_keys(RagamJasa::DAFTAR[RagamJasa::BAWAAN]);

    foreach (RagamJasa::DAFTAR as $jenis => $isi) {
        expect(array_keys($isi))->toBe($wajib, "jenis '$jenis' kuncinya tidak lengkap");
    }
});

it('warna & ikon jasa sama dengan yang dilihat pelanggan sejak halaman Shop', function () {
    /*
     | Satu layanan tidak boleh berganti warna tergantung halamannya. Jasa
     | Parafrase sempat jingga-amber di Shop/keranjang/checkout lalu mendadak
     | ungu di /cek — dan ungunya justru warna kategori 'AI Tools', jadi bukan
     | sekadar beda tapi meminjam warna kategori lain.
     |
     | Yang dibandingkan warna KATEGORI produknya, karena itulah yang sudah
     | dilihat pelanggan sebelum ia sampai ke /cek.
     */
    $pasangan = [
        'Cek Plagiasi Turnitin' => 'plagiasi',
        // Produk deteksi AI juga: kata kunci kategori 'Cek Plagiasi' di
        // KategoriBeranda adalah plagiasi/plagiarism/turnitin, jadi keduanya
        // memang satu kategori — dan karenanya satu warna.
        'Cek Plagiasi AI' => 'ai',
        'Cek AI Turnitin' => 'ai',
        'Jasa Parafrase Manual' => 'parafrase',
    ];

    foreach ($pasangan as $namaProduk => $jenis) {
        $kategori = \App\Support\KategoriBeranda::untukProduk($namaProduk);

        $ragam = RagamJasa::dariJenis($jenis);

        expect($kategori)->not->toBeNull("produk '$namaProduk' tidak dikenali KategoriBeranda")
            ->and($ragam['warna'])
            ->toBe($kategori['warna'], "warna jasa '$jenis' beda dengan kategori Shop untuk '$namaProduk'")
            // KategoriBeranda menyimpan ikonnya berawalan 'bi-', RagamJasa
            // tanpa awalan (Blade-nya menuliskan sendiri). Dibandingkan setelah
            // disamakan bentuknya.
            ->and('bi-'.$ragam['ikon'])
            ->toBe($kategori['ikon'], "ikon jasa '$jenis' beda dengan kategori Shop untuk '$namaProduk'");
    }
});

it('deteksi AI dan cek plagiasi memang sewarna, karena Shop menyatukannya', function () {
    /*
     | Ditulis sebagai uji tersendiri supaya kesamaan ini terbaca sebagai
     | PILIHAN, bukan kelalaian yang lolos. Siapa pun yang kelak merasa keduanya
     | "seharusnya beda" akan menemukan alasannya di sini lebih dulu.
     */
    expect(RagamJasa::dariJenis('ai')['warna'])
        ->toBe(RagamJasa::dariJenis('plagiasi')['warna']);

    // Yang membedakan keduanya di layar adalah kata-katanya.
    expect(RagamJasa::dariJenis('ai')['judul'])
        ->not->toBe(RagamJasa::dariJenis('plagiasi')['judul'])
        ->and(RagamJasa::dariJenis('ai')['tombol'])
        ->not->toBe(RagamJasa::dariJenis('plagiasi')['tombol']);
});

it('ikon fungsional tetap milik halaman ini, tidak diikat ke Shop', function () {
    // Yang diikat ke Shop hanya IDENTITAS layanan (warna & ikon). 'jatahIkon'
    // dan 'unggahIkon' menandai PERBUATAN di halaman /cek — melihat sisa jatah,
    // mengirim berkas — dan tidak punya padanan di Shop. Keduanya harus tetap
    // bercerita tentang jasanya masing-masing.
    expect(RagamJasa::dariJenis('parafrase')['unggahIkon'])
        ->not->toBe(RagamJasa::dariJenis('plagiasi')['unggahIkon'])
        ->and(RagamJasa::dariJenis('ai')['jatahIkon'])
        ->not->toBe(RagamJasa::dariJenis('plagiasi')['jatahIkon']);
});
