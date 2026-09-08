<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Support\HasilPengecekan;
use Illuminate\Support\Str;

/**
 * Menyimpan hasil yang belum lengkap dulunya berhasil tanpa tanda apa pun:
 * admin mengira sudah mengirim tiga berkas, pelanggan hanya menerima dua.
 * Terjadi pada pesanan parafrase 5 Sep 2026 dan baru ketahuan tiga hari
 * kemudian karena pelanggan tidak melihat hasil AI-nya.
 */
function pesananJasa(array $addon): Order
{
    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-JASA-'.random_int(1000, 9999),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli Jasa',
            'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'jasa'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 100000,
        'total' => 100000,
        'unique_code' => 0,
        'status' => 'paid',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    OrderItem::create([
        'id' => Str::uuid(),
        'order_id' => $order->id,
        'product_id' => \App\Models\Product::create(['nama_akun' => 'Jasa Parafrase', 'butuh_file' => true, 'jasa_mode' => 'halaman'])->id,
        'product_name' => 'Jasa Parafrase',
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => 100000,
        'quantity' => 1,
        'subtotal' => 100000,
        'addons' => array_map(fn ($n) => ['nama' => $n, 'harga' => 0], $addon),
    ]);

    return $order->fresh('items');
}

function unggahan(Order $order, array $isi = []): OrderUpload
{
    return OrderUpload::create(array_merge([
        'order_id' => $order->id,
        'jenis' => 'parafrase',
        'path' => 'contoh/dokumen.docx',
        'nama_asli' => 'dokumen.docx',
        'status' => 'selesai',
    ], $isi));
}

it('menyebut hasil AI yang belum diunggah bila pelanggan membelinya', function () {
    $order = pesananJasa(['Cek Plagiasi AI', 'Cek Plagiasi Turnitin']);
    $up = unggahan($order, ['hasil_path' => 'a.pdf', 'hasil_docx_path' => 'b.docx']);

    expect(HasilPengecekan::yangKurang($order, $up))->toBe(['Hasil Cek AI']);
});

it('menyebut hasil plagiasi yang belum diunggah', function () {
    $order = pesananJasa(['Cek Plagiasi Turnitin']);
    $up = unggahan($order, ['hasil_docx_path' => 'b.docx']);

    expect(HasilPengecekan::yangKurang($order, $up))->toBe(['Hasil Cek Plagiasi']);
});

it('menyebut dokumen parafrase yang belum diunggah', function () {
    $order = pesananJasa([]);
    $up = unggahan($order);

    expect(HasilPengecekan::yangKurang($order, $up))->toBe(['Dokumen Hasil (Parafrase)']);
});

it('menyebut semua yang kurang sekaligus', function () {
    $order = pesananJasa(['Cek Plagiasi AI', 'Cek Plagiasi Turnitin']);
    $up = unggahan($order);

    expect(HasilPengecekan::yangKurang($order, $up))->toBe([
        'Dokumen Hasil (Parafrase)', 'Hasil Cek Plagiasi', 'Hasil Cek AI',
    ]);
});

it('tidak menuduh kurang bila semuanya sudah ada', function () {
    $order = pesananJasa(['Cek Plagiasi AI', 'Cek Plagiasi Turnitin']);
    $up = unggahan($order, [
        'hasil_path' => 'a.pdf',
        'hasil_ai_path' => 'ai.pdf',
        'hasil_docx_path' => 'b.docx',
    ]);

    expect(HasilPengecekan::yangKurang($order, $up))->toBe([]);
});

it('pesanan tanpa add-on AI tidak dituduh kurang hasil AI', function () {
    $order = pesananJasa(['Cek Plagiasi Turnitin']);
    $up = unggahan($order, ['hasil_path' => 'a.pdf', 'hasil_docx_path' => 'b.docx']);

    // Yang tidak dibeli tidak boleh dituntut.
    expect(HasilPengecekan::yangKurang($order, $up))->toBe([]);
});

it('jenis pengecekan sendiri sudah cukup jadi acuan tanpa add-on', function () {
    $order = pesananJasa([]);
    $up = unggahan($order, ['jenis' => 'ai', 'hasil_docx_path' => 'b.docx']);

    expect(HasilPengecekan::yangKurang($order, $up))->toBe(['Hasil Cek AI']);
});
