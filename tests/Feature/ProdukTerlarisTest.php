<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\ProdukTerlaris;
use Illuminate\Support\Str;

/**
 * Peringkat produk terlaris dihitung dari pesanan yang benar-benar dibayar.
 *
 * Sebelumnya daftar ini ditulis TETAP di dalam Blade: empat kartu dengan nama
 * dan gambar yang dipatok, semuanya menaut ke /shop. Daftar itu tidak pernah
 * berubah meski yang laku sudah berganti berbulan-bulan — dan tidak ada yang
 * tahu ia bohong, karena tampilannya tetap meyakinkan.
 */
function produkToko(string $nama, array $sifat = []): Product
{
    return Product::create(array_merge([
        'nama_akun' => $nama,
        'harga_perbulan' => 15000,
    ], $sifat));
}

function pesanan(string $status, ?string $tanggal = null): Order
{
    $o = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-LARIS-'.Str::random(8),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli', 'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'laris'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 15000, 'total' => 15000, 'unique_code' => 0,
        'status' => $status, 'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    if ($tanggal) {
        // created_at tidak fillable; harus dipaksa setelah barisnya dibuat.
        $o->forceFill(['created_at' => $tanggal])->saveQuietly();
    }

    return $o;
}

function beli(Order $order, Product $produk, int $jumlah = 1): OrderItem
{
    return OrderItem::create([
        'id' => Str::uuid(), 'order_id' => $order->id, 'product_id' => $produk->id,
        'product_name' => $produk->nama_akun, 'duration_type' => 'bulan', 'duration_value' => 1,
        'price' => 15000, 'quantity' => $jumlah, 'subtotal' => 15000 * $jumlah, 'addons' => [],
    ]);
}

it('mengurut menurut jumlah PESANAN, bukan jumlah unit', function () {
    $borongan = produkToko('Dibeli sekali borongan');
    $populer = produkToko('Dibeli banyak orang');

    // Satu orang memborong 10.
    beli(pesanan('completed'), $borongan, 10);

    // Tiga orang berbeda, masing-masing satu.
    foreach (range(1, 3) as $i) {
        beli(pesanan('completed'), $populer, 1);
    }

    $urutan = ProdukTerlaris::ambil(2)->pluck('nama_akun')->all();

    // "Paling sering dibeli" berarti paling banyak ORANG memesannya.
    expect($urutan[0])->toBe('Dibeli banyak orang')
        ->and($urutan[1])->toBe('Dibeli sekali borongan');
});

it('hanya menghitung pesanan yang uangnya sudah masuk', function () {
    $dibayar = produkToko('Sudah dibayar');
    $belum = produkToko('Belum dibayar');

    beli(pesanan('completed'), $dibayar);
    foreach (['pending', 'cancelled', 'processing'] as $status) {
        beli(pesanan($status), $belum);
    }

    $hasil = ProdukTerlaris::ambil(5);

    expect($hasil->firstWhere('nama_akun', 'Sudah dibayar')->pesanan)->toBe(1)
        // Boleh ikut tampil sebagai penambal, tapi bukan karena penjualan.
        ->and($hasil->firstWhere('nama_akun', 'Belum dibayar')?->pesanan ?? 0)->toBe(0);
});

it('produk yang sudah dihapus tidak muncul sebagai kartu tanpa nama', function () {
    $hidup = produkToko('Masih ada');
    $mati = produkToko('Akan dihapus');

    foreach (range(1, 5) as $i) {
        beli(pesanan('completed'), $mati);
    }
    beli(pesanan('completed'), $hidup);

    // order_items menyimpan product_id apa adanya; barisnya tetap tertinggal.
    $mati->delete();

    $nama = ProdukTerlaris::ambil(5)->pluck('nama_akun')->all();

    expect($nama)->toContain('Masih ada')
        ->and($nama)->not->toContain('Akan dihapus');
});

it('produk yang sedang dijeda tidak diunggulkan', function () {
    // Produk dijeda tidak bisa di-checkout; mengunggulkannya di beranda hanya
    // mengantar orang ke jalan buntu.
    $dijeda = produkToko('Sedang dijeda', ['dijeda' => true]);
    $biasa = produkToko('Bisa dibeli');

    foreach (range(1, 5) as $i) {
        beli(pesanan('completed'), $dijeda);
    }
    beli(pesanan('completed'), $biasa);

    expect(ProdukTerlaris::ambil(5)->pluck('nama_akun')->all())
        ->not->toContain('Sedang dijeda');
});

it('hanya menghitung pesanan di dalam jendela waktunya', function () {
    $baru = produkToko('Laku bulan ini');
    $lama = produkToko('Laku tahun lalu');

    beli(pesanan('completed'), $baru);
    foreach (range(1, 9) as $i) {
        beli(pesanan('completed', now()->subDays(200)->toDateTimeString()), $lama);
    }

    // Dengan jendela 30 hari, yang lama kalah meski total penjualannya jauh
    // lebih banyak — itulah yang membuat daftarnya berganti mengikuti waktu.
    expect(ProdukTerlaris::ambil(1)->first()->nama_akun)->toBe('Laku bulan ini');
});

it('tidak pernah kosong meski belum ada penjualan sama sekali', function () {
    // Bagian beranda yang kosong lebih buruk daripada daftar yang belum
    // mencerminkan penjualan: pengunjung melihat toko yang seolah tak berisi.
    foreach (range(1, 6) as $i) {
        produkToko('Produk '.$i);
    }

    $hasil = ProdukTerlaris::ambil(5);

    expect($hasil)->toHaveCount(5)
        ->and($hasil->every(fn ($p) => $p->pesanan === 0))->toBeTrue();
});

it('jendela yang sepi ditambal penjualan sepanjang masa', function () {
    $lama = produkToko('Laku lama');
    foreach (range(1, 3) as $i) {
        beli(pesanan('completed', now()->subDays(120)->toDateTimeString()), $lama);
    }

    $hasil = ProdukTerlaris::ambil(3);

    // Muncul lewat jaring pengaman, lengkap dengan jumlah pesanannya.
    expect($hasil->firstWhere('nama_akun', 'Laku lama')->pesanan)->toBe(3);
});

it('tidak pernah memuat produk kembar', function () {
    $p = produkToko('Satu-satunya');
    foreach (range(1, 4) as $i) {
        beli(pesanan('completed', now()->subDays(120)->toDateTimeString()), $p);
    }

    $hasil = ProdukTerlaris::ambil(5)->pluck('id');

    expect($hasil->unique()->count())->toBe($hasil->count());
});
