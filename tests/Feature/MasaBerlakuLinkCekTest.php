<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Masa berlaku link /cek.
 *
 * Hitungan mundurnya dulu dimulai dari unggahan TERAKHIR PELANGGAN. Itu keliru
 * dengan akibat yang berat, dan sudah terjadi sungguhan pada INV-20260907-0008:
 * pelanggan mengirim dokumen terakhirnya 7 September 15.05, hasilnya baru
 * diunggah admin 8 September 22.00 — tujuh jam setelah linknya mati. Ia
 * membayar, hasilnya jadi, dan tidak pernah bisa mengunduhnya.
 *
 * Link ini ada supaya pelanggan mengambil hasilnya, jadi jamnya harus mulai
 * berjalan ketika hasil itu ada.
 */
function pesananSatuKuota(): Order
{
    $produk = Product::create([
        'nama_akun' => 'Jasa Cek Plagiasi',
        'butuh_file' => true,
        'jasa_mode' => 'halaman',
        'harga_perbulan' => 100000,
    ]);

    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-LINK-'.random_int(1000, 9999),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli', 'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'link'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 100000, 'total' => 100000, 'unique_code' => 0,
        'status' => 'paid', 'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    OrderItem::create([
        'id' => Str::uuid(), 'order_id' => $order->id, 'product_id' => $produk->id,
        'product_name' => $produk->nama_akun, 'duration_type' => 'bulan', 'duration_value' => 1,
        'price' => 100000, 'quantity' => 1, 'subtotal' => 100000, 'addons' => [],
    ]);

    return $order->fresh(['items', 'uploads']);
}

/**
 * Membuat satu baris unggahan dengan waktu yang ditentukan.
 *
 * created_at TIDAK fillable, jadi menyebutkannya di create() diam-diam
 * diabaikan dan Eloquent mengisinya dengan waktu sekarang — tesnya lalu lulus
 * atau gagal tergantung jam berapa ia dijalankan.
 */
function unggahanJasa(Order $order, string $dibuat, ?string $selesai, string $status = 'selesai'): OrderUpload
{
    $up = OrderUpload::create([
        'order_id' => $order->id, 'jenis' => 'plagiasi', 'path' => 'a.pdf',
        'nama_asli' => 'a.pdf', 'status' => $status,
        'hasil_path' => $status === 'selesai' ? 'hasil.pdf' : null,
        'selesai_at' => $selesai,
    ]);

    $up->forceFill(['created_at' => $dibuat])->saveQuietly();

    return $up;
}

it('hitungan mundur dimulai dari hasil terakhir, bukan unggahan pelanggan', function () {
    $order = pesananSatuKuota();

    // Persis urutan INV-20260907-0008.
    unggahanJasa($order, '2026-09-07 15:05:00', '2026-09-08 22:00:00');

    $order = $order->fresh(['items', 'uploads']);

    expect($order->sisaKuota())->toBe(0)
        ->and($order->kuotaHabisAt()->format('Y-m-d H:i'))->toBe('2026-09-08 22:00')
        ->and($order->cekLinkKadaluarsaAt()->format('Y-m-d H:i'))->toBe('2026-09-09 22:00');
});

it('link INV-20260907-0008 masih hidup pada 9 September pagi', function () {
    Carbon::setTestNow('2026-09-09 09:00:00');

    $order = pesananSatuKuota();

    unggahanJasa($order, '2026-09-07 15:05:00', '2026-09-08 22:00:00');

    // Dengan hitungan lama link ini sudah mati sejak 8 September 15.05.
    expect($order->fresh(['items', 'uploads'])->cekLinkKadaluarsa())->toBeFalse();

    Carbon::setTestNow();
});

it('link mati tepat 24 jam setelah hasil terakhir', function () {
    $order = pesananSatuKuota();

    unggahanJasa($order, '2026-09-07 15:05:00', '2026-09-08 22:00:00');

    $order = $order->fresh(['items', 'uploads']);

    Carbon::setTestNow('2026-09-09 21:59:00');
    expect($order->cekLinkKadaluarsa())->toBeFalse();

    Carbon::setTestNow('2026-09-09 22:01:00');
    expect($order->cekLinkKadaluarsa())->toBeTrue();

    Carbon::setTestNow();
});

it('unggahan pelanggan tetap dipakai bila hasilnya justru lebih dulu', function () {
    // Bisa terjadi pada pesanan berkuota lebih dari satu: hasil pertama sudah
    // diserahkan sementara dokumen terakhir baru dikirim belakangan. Yang
    // dipakai selalu kejadian TERAKHIR di antara keduanya.
    $order = pesananSatuKuota();

    unggahanJasa($order, '2026-09-10 08:00:00', '2026-09-09 08:00:00');

    expect($order->fresh(['items', 'uploads'])->kuotaHabisAt()->format('Y-m-d H:i'))
        ->toBe('2026-09-10 08:00');
});

it('baris lama tanpa selesai_at tetap memakai waktu unggahan', function () {
    // Data lama dibuat sebelum selesai_at dicatat; tanpa penanganan ini,
    // pesanan lama akan kehilangan masa berlakunya sama sekali.
    $order = pesananSatuKuota();

    unggahanJasa($order, '2026-09-01 10:00:00', null);

    expect($order->fresh(['items', 'uploads'])->kuotaHabisAt()->format('Y-m-d H:i'))
        ->toBe('2026-09-01 10:00');
});

it('kuota belum habis berarti belum ada hitungan mundur sama sekali', function () {
    $order = pesananSatuKuota();

    expect($order->kuotaHabisAt())->toBeNull()
        ->and($order->cekLinkKadaluarsaAt())->toBeNull()
        ->and($order->cekLinkKadaluarsa())->toBeFalse();
});

it('unggahan yang dibatalkan tidak ikut menghitung', function () {
    $order = pesananSatuKuota();

    unggahanJasa($order, '2026-09-20 10:00:00', '2026-09-20 10:00:00', 'dibatalkan');

    unggahanJasa($order, '2026-09-07 15:05:00', '2026-09-08 22:00:00');

    expect($order->fresh(['items', 'uploads'])->kuotaHabisAt()->format('Y-m-d H:i'))
        ->toBe('2026-09-08 22:00');
});
