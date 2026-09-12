<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Tampilan /order/history: kartu pesanan berstatus warna, isi pesanan
 * berikon menurut masa aktifnya, dan ringkasan biaya bergaya struk.
 */
function pesananRiwayat(string $status, array $lain = [], array $isi = []): Order
{
    $pelanggan = Customer::create([
        'nama' => 'Pembeli Uji',
        'no_hp' => $lain['no_hp'] ?? '08123456789',
        'email' => 'uji'.uniqid().'@contoh.test',
    ]);

    $order = Order::create(array_merge([
        'id' => Str::uuid(),
        'order_number' => 'INV-UJI-'.random_int(1000, 9999),
        'customer_id' => $pelanggan->id,
        'subtotal' => 100000, 'total' => 100000, 'unique_code' => 0,
        'status' => $status, 'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ], $lain));

    $produk = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    OrderItem::create(array_merge([
        'id' => Str::uuid(), 'order_id' => $order->id, 'product_id' => $produk->id,
        'product_name' => 'Canva Premium', 'duration_type' => 'bulan', 'duration_value' => 1,
        'price' => 100000, 'quantity' => 1, 'subtotal' => 100000,
    ], $isi));

    return $order->fresh(['items']);
}

/** Halaman riwayat, dipulihkan lewat nomor HP (cookie yang dibaca komponen). */
function bukaRiwayat(string $hp = '08123456789')
{
    // withCookie (bukan withUnencryptedCookie): aplikasi mengurai cookie
    // terenkripsi, jadi nilai mentah tidak akan terbaca.
    return test()->withCookie('history_phone', $hp)->get(route('order.history'));
}

it('pesanan lunas tampil sebagai kartu hijau berlabel Lunas', function () {
    pesananRiwayat('paid');

    bukaRiwayat()
        ->assertOk()
        ->assertSee('<details class="rw-order" style="--c: #16a34a">', false)
        ->assertSeeText('Lunas')
        ->assertSeeText('Canva Premium')
        ->assertSeeText('1 pesanan');
});

it('status pesanan lain punya warna dan sebutannya sendiri', function () {
    pesananRiwayat('pending', ['no_hp' => '08129990001']);
    bukaRiwayat('08129990001')
        ->assertSee('style="--c: #d97706"', false)
        ->assertSeeText('Menunggu Pembayaran');

    pesananRiwayat('cancelled', ['no_hp' => '08129990002']);
    bukaRiwayat('08129990002')
        ->assertSee('style="--c: #e11d48"', false)
        ->assertSeeText('Dibatalkan');
});

it('ringkasan biaya memuat diskon dan kode unik bila ada', function () {
    pesananRiwayat('paid', ['total_discount' => 15000, 'unique_code' => 137, 'total' => 85137]);

    bukaRiwayat()
        ->assertSeeText('Subtotal')
        ->assertSeeText('Diskon')
        ->assertSeeText('Kode Unik')
        ->assertSeeText('Total Bayar')
        ->assertSee('Rp 85.137');
});

it('item yang belum aktif ditandai menunggu aktivasi', function () {
    // Tanpa end_date: akun belum diserahkan/diaktifkan.
    pesananRiwayat('paid');

    bukaRiwayat()
        ->assertSeeText('Menunggu aktivasi')
        ->assertSee('<i class="bi bi-hourglass-split"></i>', false);
});

it('tanpa riwayat, halaman menawarkan jalan keluar', function () {
    // Tanpa cookie sama sekali: paginator kosong.
    $this->get(route('order.history'))
        ->assertOk()
        ->assertSeeText('Belum ada riwayat pesanan')
        ->assertSeeText('Mulai Belanja')
        ->assertSeeText('Pulihkan Riwayat');
});

it('penanda yang dipakai jendela pemulihan tetap ada', function () {
    // Skrip halaman mencari id ini untuk menutup jendela setelah berhasil.
    bukaRiwayat()
        ->assertSee('id="restoreModal"', false)
        ->assertSee('data-bs-target="#restoreModal"', false)
        ->assertSee('wire:submit.prevent="restoreSession"', false);
});
