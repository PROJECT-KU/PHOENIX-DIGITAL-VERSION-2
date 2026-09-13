<?php

use App\Livewire\Pages\Public\ShopPage\PaymentExpired;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Tampilan /order/expired: halaman terakhir yang dilihat pembeli yang gagal
 * membayar. Tugasnya dua — menerangkan bahwa tidak ada uang yang terpotong,
 * dan mengingatkan apa isi pesanannya supaya ia bisa memesan ulang.
 */
function pesananBatal(array $barang = [], int $total = 155000): Order
{
    $customer = Customer::create([
        'nama' => 'Pembeli Batal',
        'no_hp' => '0812'.random_int(10000000, 99999999),
        'email' => 'batal'.uniqid().'@contoh.test',
    ]);

    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-BATAL-'.Str::random(6),
        'customer_id' => $customer->id,
        'subtotal' => $total,
        'total' => $total,
        'unique_code' => 0,
        'status' => 'cancelled',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->subHour(),
    ]);

    foreach ($barang ?: [['NotebookLM', null]] as [$nama, $gambar]) {
        OrderItem::create([
            'order_id' => $order->id,
            // FK-nya sudah dilepas tetapi kolomnya masih NOT NULL.
            'product_id' => (string) Str::uuid(),
            'product_name' => $nama,
            'product_image' => $gambar,
            'duration_type' => 'bulan',
            'duration_value' => 1,
            'price' => $total,
            'quantity' => 1,
            'subtotal' => $total,
        ]);
    }

    return $order->fresh();
}

it('jalur langkah menandai Bayar sebagai yang GAGAL, bukan yang belum dijalani', function () {
    // Kelabu seperti langkah yang belum dijalani akan menyembunyikan
    // satu-satunya hal yang perlu diketahui pembeli: di sinilah ia berhenti.
    $order = pesananBatal();

    Livewire::test(PaymentExpired::class, ['order' => $order])
        ->assertSeeHtml('<div class="kdl-henti is-gagal">')
        ->assertSeeHtml('<span class="kdl-henti-bulat"><i class="bi bi-x-lg"></i></span>')
        ->assertSee('Bayar')
        ->assertSee('Keranjang')
        ->assertSee('Terima');
});

it('menerangkan pembatalan dan menegaskan tidak ada uang terpotong', function () {
    $order = pesananBatal();

    Livewire::test(PaymentExpired::class, ['order' => $order])
        ->assertSee('Waktu Pembayaran Habis')
        ->assertSee($order->order_number)
        ->assertSee('dibatalkan')
        ->assertSee('Tidak ada uang yang terpotong')
        ->assertSeeHtml('href="'.route('shop.index').'"');
});

it('menampilkan isi pesanan yang batal supaya tak perlu diingat sendiri', function () {
    $order = pesananBatal([
        ['NotebookLM', null],
        ['Canva Premium', null],
    ]);

    Livewire::test(PaymentExpired::class, ['order' => $order])
        ->assertSee('Isi Pesanan Ini')
        ->assertSee('NotebookLM')
        ->assertSee('Canva Premium')
        // Warna kategori sama dengan Keranjang, Checkout, dan Pembayaran.
        ->assertSeeHtml('<div class="kdl-item" style="--c: #7c3aed">')
        ->assertSeeHtml('<div class="kdl-item" style="--c: #db2777">')
        ->assertSee('Total yang batal');
});

it('memasang logo produk bila berkasnya ada, ikon kategori bila tidak', function () {
    $nama = 'Product_uji_'.uniqid().'.png';
    $tujuan = public_path('storage/img/Product/'.$nama);
    @mkdir(dirname($tujuan), 0777, true);
    file_put_contents($tujuan, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

    try {
        Livewire::test(PaymentExpired::class, ['order' => pesananBatal([['Grammarly Premium', $nama]])])
            ->assertSeeHtml('storage/img/Product/'.$nama);
    } finally {
        @unlink($tujuan);
    }

    // Berkasnya tidak ada: <img> TIDAK dipasang sama sekali, supaya teks alt
    // tidak tampil sebagai gambar rusak.
    Livewire::test(PaymentExpired::class, ['order' => pesananBatal([['NotebookLM', 'Product_64229.webp']])])
        ->assertSeeHtml('<i class="bi bi-robot"></i>')
        ->assertDontSeeHtml('storage/img/Product/');
});

it('pesanan yang BELUM dibatalkan tidak boleh memakai halaman ini', function () {
    // Kalau tidak dijaga, pesanan yang masih bisa dibayar akan terbaca sudah
    // hangus oleh pembelinya sendiri.
    $order = pesananBatal();
    $order->forceFill(['status' => 'pending'])->saveQuietly();

    Livewire::test(PaymentExpired::class, ['order' => $order->fresh()])
        ->assertRedirect(route('payment', $order));
});
