<?php

use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsForm;
use App\Models\Product;
use App\Models\ProductBundlings;
use Livewire\Livewire;

/*
 | Untuk produk JASA, kolom "Durasi" di form bundling BUKAN durasi.
 |
 | Order::kuotaDasarPerJenis() menghitungnya sebagai duration_value x quantity,
 | jadi "1" berarti SATU KALI pengecekan — bukan "aktif sebulan". Dengan label
 | "Durasi" dan satuan "Bulan/Tahun", admin yang menulis "1 Tahun" mengira
 | memberi layanan setahun, padahal yang diberikan satu kali cek. Tidak ada
 | galat untuk salah paham ini: paketnya tersimpan, pesanannya jadi, dan yang
 | ketahuan belakangan adalah pelanggan kehabisan kuota.
 */

function produkJasaAi(): Product
{
    return Product::create([
        'nama_akun' => 'Cek Plagiasi AI',
        'butuh_file' => 1,
        'cek_ai' => 1,
        'jasa_mode' => 'paket',
    ]);
}

test('slot produk jasa memakai label "Jumlah pengecekan", slot akun tetap "Durasi"', function () {
    $akun = Product::create(['nama_akun' => 'Scopus Lisensi', 'harga_perbulan' => 30000]);
    $jasa = produkJasaAi();

    $html = Livewire::test(ProductBundlingsForm::class)
        ->set('product_1', $akun->id)
        ->set('product_2', $jasa->id)
        ->html();

    /*
     | Dicocokkan lengkap dengan tag <label>-nya. Sebagai tulisan telanjang,
     | "Jumlah pengecekan" juga cocok dengan keterangan di kepala kartu yang
     | menjelaskan aturan ini — ujinya lulus/gagal karena kalimat yang bukan
     | label.
     */
    expect(substr_count($html, '<label class="slot-label">Jumlah pengecekan</label>'))->toBe(1);

    /*
     | Pemilih satuan DISEMBUNYIKAN di slot jasa, bukan sekadar diberi pilihan
     | lain: apa pun isinya tidak mengubah kuota, jadi pilihan yang tetap ada
     | di layar hanya mengundang salah isi.
     */
    expect($html)->not->toContain('durations.product_2.type');

    // Empat slot sisanya (akun + tiga slot kosong) tidak ikut berubah.
    expect($html)->toContain('durations.product_1.type')
        ->and(substr_count($html, '<option value="tahun">Tahun</option>'))->toBe(4);
});

test('satuan slot jasa dipaksa "bulan" saat disimpan, berapa pun yang tersisa di layar', function () {
    /*
     | Admin bisa memilih produk akun dulu, menyetel "Tahun", lalu menggantinya
     | dengan produk jasa — nilai 'tahun' tertinggal di properti walaupun
     | pemilihnya sudah tidak tergambar. Satuan itu tidak berpengaruh pada
     | kuota, tetapi OrderItem::calculateEndDate() memakainya: baris jasa itu
     | akan memajang "masa aktif" bertahun-tahun yang tidak berarti apa-apa,
     | sebab unggahannya memang tidak pernah dibatasi tanggal.
     */
    $jasa = produkJasaAi();

    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Uji Jasa',
        'harga_awal' => 'Rp 100.000',
        'harga_bundling' => 'Rp 80.000',
        'status' => 'active',
        'product_1' => $jasa->id,
        'durations' => ['product_1' => ['value' => 1, 'type' => 'tahun']],
    ]);

    Livewire::test(ProductBundlingsForm::class, ['product_bundlings' => $paket])
        ->set('durations.product_1.value', 3)
        ->set('durations.product_1.type', 'tahun')
        ->call('save');

    $tersimpan = $paket->fresh()->durations['product_1'];

    expect($tersimpan['value'])->toBe(3)
        ->and($tersimpan['type'])->toBe('bulan');
});

test('kuota pengecekan mengikuti angka yang diisi admin, bukan satuannya', function () {
    /*
     | Penjaga artinya, bukan tampilannya: angka 3 di kolom itu harus benar-benar
     | menghasilkan 3 kali pengecekan pada pesanan.
     */
    $jasa = produkJasaAi();

    $pesanan = \App\Models\Order::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'order_number' => 'INV-UJI-KUOTA-1',
        'customer_id' => \App\Models\Customer::create([
            'nama' => 'Pembeli Uji',
            'no_hp' => '081200000199',
            'email' => 'uji-kuota@contoh.test',
        ])->id,
        'subtotal' => 80000,
        'total' => 80000,
        'unique_code' => 0,
        'status' => 'paid',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    \App\Models\OrderItem::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'order_id' => $pesanan->id,
        'product_id' => $jasa->id,
        'product_name' => 'Paket Uji Jasa - Cek Plagiasi AI',
        'duration_type' => 'bulan',
        'duration_value' => 3,
        'price' => 80000,
        'quantity' => 1,
        'subtotal' => 80000,
    ]);

    expect($pesanan->fresh()->load('items.product')->kuotaDasarPerJenis())
        ->toBe(['ai' => 3]);
});
