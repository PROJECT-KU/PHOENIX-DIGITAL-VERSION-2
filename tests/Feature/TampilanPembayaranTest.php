<?php

use App\Livewire\Pages\Public\ShopPage\PaymentPage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Tampilan /payment. QRIS-nya TIDAK memanggil API mana pun di uji ini: mount()
 * memakai ulang QRIS lama yang masih berlaku, dan gambarnya digambar lokal oleh
 * endroid/qr-code. Pembuatan baru cuma lewat wire:init, yang tidak dijalankan
 * sendiri oleh Livewire::test.
 */
function pesananMenunggu(array $barang = [], int $total = 155000): Order
{
    $customer = Customer::create([
        'nama' => 'Pembeli Bayar',
        'no_hp' => '0812'.random_int(10000000, 99999999),
        'email' => 'bayar'.uniqid().'@contoh.test',
    ]);

    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-BAYAR-'.Str::random(6),
        'customer_id' => $customer->id,
        'subtotal' => $total,
        'total' => $total,
        'unique_code' => 0,
        'status' => 'pending',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    foreach ($barang ?: [['NotebookLM', null]] as [$nama, $gambar]) {
        OrderItem::create([
            'order_id' => $order->id,
            // FK-nya sudah dilepas (lihat migrasi lepas_fk_product_id_di_order_items)
            // tetapi kolomnya masih NOT NULL, jadi tetap harus diisi.
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

    Payment::create([
        'order_id' => $order->id,
        'payment_gateway' => 'qris',
        'transaction_id' => 'TRX-'.Str::random(10),
        'payment_method' => 'qris_dinamis',
        'amount' => $total,
        'status' => 'pending',
        'expired_at' => now()->addMinutes(25),
        'gateway_response' => ['data' => [
            'qris_content' => '00020101021126'.Str::random(20),
            'qris_invoiceid' => '43921123',
            'qris_nmid' => 'ID1026483411678',
        ]],
    ]);

    return $order->fresh();
}

it('kabel yang menggerakkan pembayaran tidak berubah', function () {
    /*
     | Yang dijaga di sini adalah hal-hal yang kalau hilang TIDAK menimbulkan
     | galat apa pun — hanya berhenti bekerja diam-diam:
     |
     | - #countdown + data-expired digerakkan public/niceshop/assets/js/custom.js;
     |   saat waktunya habis ia memanggil checkPaymentStatus lewat komponen
     |   terdekat, yang membatalkan pesanan kedaluwarsa.
     | - wire:ignore menahan Livewire menimpa angka yang sedang berjalan.
     | - #ph-qris-img dibaca phDownloadQris untuk menggambar kartu unduhan.
     | - wire:poll adalah satu-satunya cara status QRIS terdeteksi di layar
     |   (QRIS dinamis tidak punya callback).
     */
    $order = pesananMenunggu();

    Livewire::test(PaymentPage::class, ['order' => $order])
        ->assertSeeHtml('id="countdown"')
        ->assertSeeHtml('wire:ignore')
        ->assertSeeHtml('data-expired=')
        ->assertSeeHtml('id="ph-qris-img"')
        ->assertSeeHtml('wire:poll.15s="checkPaymentStatus"')
        ->assertSeeHtml('wire:click="checkPaymentStatus"')
        ->assertSeeHtml('phDownloadQris(this)');
});

it('jalur langkah menandai Bayar sebagai yang sedang dikerjakan', function () {
    $order = pesananMenunggu();

    Livewire::test(PaymentPage::class, ['order' => $order])
        ->assertSeeHtml('<div class="byr-henti is-kini">')
        ->assertSee('Keranjang')
        ->assertSee('Data & Promo')
        ->assertSee('Bayar')
        ->assertSee('Terima');
});

it('tiap bagian berkepala ubin ikon berwarna', function () {
    $order = pesananMenunggu();

    Livewire::test(PaymentPage::class, ['order' => $order])
        ->assertSeeHtml('<div class="byr-kartu" style="--c: #f26522">')   // QRIS
        ->assertSeeHtml('<div class="byr-kartu" style="--c: #2563eb">')   // Detail Pesanan
        ->assertSeeHtml('<div class="byr-kartu" style="--c: #7c3aed">')   // Informasi Pelanggan
        ->assertSeeHtml('<span class="byr-ubin"><i class="bi bi-qr-code-scan"></i></span>');
});

it('detail pesanan memasang logo produk bila berkasnya ada', function () {
    $nama = 'Product_uji_'.uniqid().'.png';
    $tujuan = public_path('storage/img/Product/'.$nama);
    @mkdir(dirname($tujuan), 0777, true);
    file_put_contents($tujuan, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

    $order = pesananMenunggu([['Grammarly Premium', $nama]]);

    try {
        Livewire::test(PaymentPage::class, ['order' => $order])
            ->assertSeeHtml('storage/img/Product/'.$nama)
            ->assertSeeHtml('alt="Grammarly Premium"');
    } finally {
        @unlink($tujuan);
    }
});

it('detail pesanan jatuh ke ikon kategori saat berkas logonya tidak ada', function () {
    // <img> TIDAK dipasang sama sekali, supaya teks alt tidak tampil sebagai
    // gambar rusak — pelajaran dari Keranjang dan Wishlist.
    $order = pesananMenunggu([['NotebookLM', 'Product_64229.webp']]);

    Livewire::test(PaymentPage::class, ['order' => $order])
        ->assertSeeHtml('<div class="byr-item" style="--c: #7c3aed">')
        ->assertSeeHtml('<i class="bi bi-robot"></i>')
        ->assertDontSeeHtml('storage/img/Product/');
});

it('peringatan penipuan & cara pembayaran tetap terbaca', function () {
    // Peringatan ini satu-satunya pengaman pembeli terhadap QRIS palsu;
    // jangan sampai hilang saat halamannya ditata ulang.
    $order = pesananMenunggu();

    Livewire::test(PaymentPage::class, ['order' => $order])
        ->assertSee('Hati-hati penipuan!')
        ->assertSee('Phoenix Digital Warehouse')
        ->assertSee('Cara Pembayaran')
        ->assertSee('Scan QRIS')
        ->assertSee('sama persis')
        ->assertSee('NMID: ID1026483411678');
});

it('QRIS yang habis waktunya di layar menawarkan pembuatan ulang', function () {
    /*
     | Cabang ini TIDAK tercapai dengan membuat pembayaran yang sudah lewat:
     | mount() menandainya 'expire' lalu menyiapkan QRIS baru. Ia hanya muncul
     | pada keadaan yang sesungguhnya — halaman sudah terbuka dengan QRIS yang
     | sah, lalu waktunya habis sementara pembeli masih menatapnya, dan
     | wire:poll menggambar ulang.
     */
    $order = pesananMenunggu();

    $layar = Livewire::test(PaymentPage::class, ['order' => $order])
        ->assertSeeHtml('id="countdown"');

    $this->travel(30)->minutes();

    $layar->call('$refresh')
        ->assertSeeHtml('wire:click="generateNewQris"')
        ->assertSee('QRIS sudah kadaluarsa')
        ->assertDontSeeHtml('id="countdown"');

    $this->travelBack();
});
