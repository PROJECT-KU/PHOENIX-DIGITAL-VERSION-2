<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Models\Product;
use App\Models\ProductAddon;
use Illuminate\Support\Str;

/**
 * Kuota yang dilihat admin adalah PEKERJAAN yang harus diserahkan, sedangkan
 * jatah unggah pelanggan adalah berapa DOKUMEN boleh dikirim. Dulu keduanya
 * memakai angka yang sama, sehingga:
 *
 *  - add-on jaminan "Plagiasi di bawah 20%" menambah kuota hantu yang tak
 *    pernah bisa dipakai (INV-20260831-0005), dan
 *  - hasil yang sudah diserahkan tidak pernah menghabiskan kuota.
 */
function pesananParafrase(array $namaAddon = []): Order
{
    $produk = Product::create([
        'nama_akun' => 'Jasa Parafrase Manual',
        'butuh_file' => true,
        'jasa_mode' => 'halaman',
        'harga_perbulan' => 100000,
    ]);

    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-KUOTA-'.random_int(1000, 9999),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli', 'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'kuota'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 100000, 'total' => 100000, 'unique_code' => 0,
        'status' => 'paid', 'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    $addons = [];
    foreach ($namaAddon as $nama) {
        $katalog = ProductAddon::where('nama', $nama)->first();
        $addons[] = ['id' => $katalog?->id, 'nama' => $nama, 'harga' => 0];
    }

    OrderItem::create([
        'id' => Str::uuid(), 'order_id' => $order->id, 'product_id' => $produk->id,
        'product_name' => $produk->nama_akun, 'duration_type' => 'bulan', 'duration_value' => 1,
        'price' => 100000, 'quantity' => 1, 'subtotal' => 100000,
        'addons' => $addons,
    ]);

    return $order->fresh(['items', 'uploads']);
}

function katalogAddon(string $nama, string $jenis): ProductAddon
{
    // Add-on melekat pada satu produk jasa; product_id wajib terisi.
    $induk = Product::create([
        'nama_akun' => 'Induk '.uniqid(),
        'butuh_file' => true,
        'jasa_mode' => 'halaman',
    ]);

    return ProductAddon::create([
        'product_id' => $induk->id,
        'nama' => $nama, 'harga' => 0, 'pakai_exclude' => false,
        'cek_ai' => false, 'jenis_layanan' => $jenis,
    ]);
}

it('add-on jaminan tidak menambah pekerjaan apa pun', function () {
    katalogAddon('Plagiasi di bawah 20% maksimal 5%', '');
    $order = pesananParafrase(['Plagiasi di bawah 20% maksimal 5%']);

    // Jaminan mutu, bukan berkas yang diserahkan.
    expect($order->kuotaPengecekan())->toBe(1);
});

it('add-on hasil plagiasi dan AI masing-masing menambah satu pekerjaan', function () {
    katalogAddon('Cek Plagiasi Turnitin', 'plagiasi');
    katalogAddon('Cek Plagiasi AI', 'ai');
    $order = pesananParafrase(['Cek Plagiasi Turnitin', 'Cek Plagiasi AI']);

    expect($order->kuotaPengecekan())->toBe(3);
});

it('pelanggan tetap hanya boleh mengirim SATU dokumen', function () {
    katalogAddon('Cek Plagiasi Turnitin', 'plagiasi');
    katalogAddon('Cek Plagiasi AI', 'ai');
    $order = pesananParafrase(['Cek Plagiasi Turnitin', 'Cek Plagiasi AI']);

    // Add-on adalah berkas balasan, bukan undangan mengirim dokumen lagi.
    expect(array_sum($order->kuotaUnggahanPerJenis()))->toBe(1)
        ->and($order->jenisTersisa())->toBe(['parafrase']);
});

it('kuota habis setelah ketiga berkas hasil diunggah', function () {
    katalogAddon('Cek Plagiasi Turnitin', 'plagiasi');
    katalogAddon('Cek Plagiasi AI', 'ai');
    $order = pesananParafrase(['Cek Plagiasi Turnitin', 'Cek Plagiasi AI']);

    OrderUpload::create([
        'order_id' => $order->id, 'jenis' => 'parafrase', 'path' => 'a.docx',
        'nama_asli' => 'a.docx', 'status' => 'selesai',
        'hasil_docx_path' => 'hasil.docx',
        'hasil_path' => 'plagiasi.pdf',
        'hasil_ai_path' => 'ai.pdf',
    ]);

    $order = $order->fresh(['items', 'uploads']);

    expect($order->pekerjaanTerserah())->toBe(3)
        ->and($order->sisaKuota())->toBe(0)
        ->and($order->jasaTuntas())->toBeTrue();
});

it('kuota belum habis bila satu hasil belum diunggah', function () {
    katalogAddon('Cek Plagiasi Turnitin', 'plagiasi');
    katalogAddon('Cek Plagiasi AI', 'ai');
    $order = pesananParafrase(['Cek Plagiasi Turnitin', 'Cek Plagiasi AI']);

    // Persis keadaan INV-20260831-0005 sebelum hasil AI diunggah.
    OrderUpload::create([
        'order_id' => $order->id, 'jenis' => 'parafrase', 'path' => 'a.docx',
        'nama_asli' => 'a.docx', 'status' => 'selesai',
        'hasil_docx_path' => 'hasil.docx',
        'hasil_path' => 'plagiasi.pdf',
    ]);

    $order = $order->fresh(['items', 'uploads']);

    expect($order->pekerjaanTerserah())->toBe(2)
        ->and($order->sisaKuota())->toBe(1)
        ->and($order->jasaTuntas())->toBeFalse();
});

it('pelanggan tak bisa mengirim dokumen kedua meski pekerjaan belum tuntas', function () {
    katalogAddon('Cek Plagiasi Turnitin', 'plagiasi');
    $order = pesananParafrase(['Cek Plagiasi Turnitin']);

    OrderUpload::create([
        'order_id' => $order->id, 'jenis' => 'parafrase', 'path' => 'a.docx',
        'nama_asli' => 'a.docx', 'status' => 'diproses',
    ]);

    $order = $order->fresh(['items', 'uploads']);

    // Jatah kirimnya sudah terpakai walau hasilnya belum diserahkan.
    expect($order->bisaUploadJenis('parafrase'))->toBeFalse()
        ->and($order->sisaKuota())->toBeGreaterThan(0);
});

it('unggahan yang dibatalkan tidak dihitung sebagai pekerjaan terserah', function () {
    $order = pesananParafrase();

    OrderUpload::create([
        'order_id' => $order->id, 'jenis' => 'parafrase', 'path' => 'a.docx',
        'nama_asli' => 'a.docx', 'status' => 'dibatalkan',
        'hasil_docx_path' => 'hasil.docx',
    ]);

    expect($order->fresh(['items', 'uploads'])->pekerjaanTerserah())->toBe(0);
});

it('salinan add-on lama tidak lagi menebak jenis dari pakai_exclude', function () {
    katalogAddon('Plagiasi di bawah 20% maksimal 5%', '');
    $order = pesananParafrase([]);

    // Bentuk salinan pesanan LAMA: memuat penanda, tanpa jenis tegas.
    $order->items->first()->update(['addons' => [[
        'nama' => 'Plagiasi di bawah 20% maksimal 5%',
        'harga' => 100000,
        'pakai_exclude' => true,
        'cek_ai' => false,
    ]]]);

    // Dulu penanda itu dibaca sebagai "satu pengecekan plagiasi" dan melahirkan
    // kuota hantu; sekarang jenisnya diambil dari katalog.
    expect($order->fresh(['items', 'uploads'])->kuotaPengecekan())->toBe(1);
});

it('salinan yang menyebut jenisnya tegas tetap dipercaya', function () {
    $order = pesananParafrase([]);

    $order->items->first()->update(['addons' => [[
        'nama' => 'Cek Plagiasi AI', 'harga' => 0, 'jenis_layanan' => 'ai',
    ]]]);

    expect($order->fresh(['items', 'uploads'])->kuotaPengecekan())->toBe(2);
});
