<?php

use App\Livewire\Pages\Public\ShopPage\OrderSuccessPage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Halaman sukses menutup pesanan dengan satu kartu: poin bagi member, ajakan
 * bagi yang belum. Di sinilah tempat ajakannya — pembeli sudah membayar, jadi
 * tidak ada corong yang bisa bocor seperti bila dipasang di checkout.
 */
function pesananSukses(array $pelanggan = [], int $total = 155000): Order
{
    $customer = Customer::create(array_merge([
        'nama' => 'Pembeli Uji',
        'no_hp' => '0812'.random_int(10000000, 99999999),
        'email' => 'sukses'.uniqid().'@contoh.test',
    ], $pelanggan));

    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-SUKSES-'.Str::random(6),
        'customer_id' => $customer->id,
        'subtotal' => $total,
        'total' => $total,
        'unique_code' => 0,
        'status' => 'paid',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        // FK-nya sudah dilepas tetapi kolomnya masih NOT NULL.
        'product_id' => (string) Str::uuid(),
        'product_name' => 'NotebookLM',
        'product_image' => null,
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => $total,
        'quantity' => 1,
        'subtotal' => $total,
    ]);

    return $order->fresh();
}

it('jalur langkah menutup rangkaian: tiga hijau, "Terima" masih berjalan', function () {
    // Selama pesanannya belum 'completed', akun memang belum di tangan
    // pembeli. Menandai "Terima" hijau lebih cepat dari kenyataan membuat
    // pembeli mengira akunnya sudah dikirim.
    $order = pesananSukses();

    Livewire::test(OrderSuccessPage::class, ['order' => $order])
        ->assertSeeHtml('<div class="sks-henti is-lewat">')
        ->assertSeeHtml('<div class="sks-henti is-kini">')
        ->assertDontSeeHtml('<div class="sks-henti is-tuntas">')
        ->assertSee('Terima');

    $order->forceFill(['status' => 'completed'])->saveQuietly();

    Livewire::test(OrderSuccessPage::class, ['order' => $order->fresh()])
        ->assertSeeHtml('<div class="sks-henti is-tuntas">');
});

it('ringkasan memasang logo produk bila berkasnya ada, ikon kategori bila tidak', function () {
    $nama = 'Product_uji_'.uniqid().'.png';
    $tujuan = public_path('storage/img/Product/'.$nama);
    @mkdir(dirname($tujuan), 0777, true);
    file_put_contents($tujuan, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

    $order = pesananSukses();
    $order->items()->first()->forceFill([
        'product_name' => 'Grammarly Premium',
        'product_image' => $nama,
    ])->saveQuietly();

    try {
        Livewire::test(OrderSuccessPage::class, ['order' => $order->fresh()])
            ->assertSeeHtml('storage/img/Product/'.$nama)
            ->assertSeeHtml('alt="Grammarly Premium"');
    } finally {
        @unlink($tujuan);
    }

    // Berkasnya tidak ada: <img> TIDAK dipasang sama sekali, supaya teks alt
    // tidak tampil sebagai gambar rusak.
    $order->items()->first()->forceFill([
        'product_name' => 'NotebookLM',
        'product_image' => 'Product_64229.webp',
    ])->saveQuietly();

    Livewire::test(OrderSuccessPage::class, ['order' => $order->fresh()])
        ->assertSeeHtml('<i class="bi bi-robot"></i>')
        ->assertDontSeeHtml('storage/img/Product/');
});

it('id tautan pengecekan tetap utuh untuk tombol salinnya', function () {
    /*
     | suSalinCek() membaca elemen lewat id su-cek-link. Kalau idnya berubah
     | saat halaman ditata ulang, tombol "Salin" berhenti bekerja TANPA galat
     | apa pun — hanya diam. Diperiksa di sumber karena blok jasanya baru
     | muncul bila pesanannya memuat produk berkas.
     */
    $sumber = file_get_contents(resource_path('views/livewire/pages/public/shop-page/order-success-page.blade.php'));

    expect($sumber)->toContain('id="su-cek-link"')
        ->and($sumber)->toContain('onclick="suSalinCek()"')
        ->and($sumber)->toContain("getElementById('su-cek-link')");
});

it('menyebut poin yang bisa didapat dari belanja ini bagi yang belum member', function () {
    // Rp 155.000 : Rp 50.000 = 3 poin, senilai 3 × Rp 500 = Rp 1.500.
    $order = pesananSukses([], 155000);

    Livewire::test(OrderSuccessPage::class, ['order' => $order])
        ->assertSee('Kamu Belum Jadi Member')
        ->assertSee('Rp 155.000')
        ->assertSee('3 poin')
        ->assertSee('Rp 1.500')
        ->assertSeeHtml('href="'.route('member.info').'"');
});

it('tidak pernah menjanjikan "0 poin" saat belanjanya belum cukup', function () {
    // Rp 20.000 belum cukup untuk satu poin pun. Menyebut "bisa jadi 0 poin"
    // justru mematahkan ajakannya sendiri.
    $order = pesananSukses([], 20000);

    Livewire::test(OrderSuccessPage::class, ['order' => $order])
        ->assertSee('Kamu Belum Jadi Member')
        ->assertDontSee('0 poin')
        ->assertSee('Rp 50.000')
        ->assertSee('1 poin');
});

it('menampilkan saldo poin bagi member aktif, bukan ajakan', function () {
    $order = pesananSukses([
        'status_member' => 'active',
        'point' => 7,
        'point_balance' => 30000,
        'points_year' => now()->year,
    ]);

    Livewire::test(OrderSuccessPage::class, ['order' => $order])
        ->assertSee('Poin Member')
        ->assertDontSee('Kamu Belum Jadi Member')
        ->assertSee('7')
        // 7 × Rp 500
        ->assertSee('Rp 3.500')
        // Rp 50.000 − Rp 30.000 tersisa menuju poin berikutnya
        ->assertSee('Rp 20.000');
});

it('poin milik tahun lalu tidak ditampilkan sebagai saldo berjalan', function () {
    $order = pesananSukses([
        'status_member' => 'active',
        'point' => 9,
        'point_balance' => 40000,
        'points_year' => now()->year - 1,
    ]);

    Livewire::test(OrderSuccessPage::class, ['order' => $order])
        ->assertSee('Poin Member')
        ->assertDontSee('Rp 4.500');   // 9 × Rp 500, angka yang sudah kadaluarsa
});

it('membuka halaman sukses TIDAK mengubah data poin pelanggan', function () {
    /*
     | Customer::applyYearlyExpiry() menyimpan ke database. Kalau halaman ini
     | memanggilnya, sekadar memuat ulang layar — atau perayap yang lewat —
     | sudah cukup untuk menolkan poin seseorang. Penolakannya harus terjadi di
     | titik pakai, bukan di layar yang cuma menggambar hasil.
     */
    $order = pesananSukses([
        'status_member' => 'active',
        'point' => 9,
        'point_balance' => 40000,
        'points_year' => now()->year - 1,
    ]);

    $sebelum = $order->customer->only(['point', 'point_balance', 'points_year']);

    Livewire::test(OrderSuccessPage::class, ['order' => $order]);

    expect($order->customer->fresh()->only(['point', 'point_balance', 'points_year']))
        ->toBe($sebelum);
});

it('pembagi poin di layar sama dengan pembagi yang menghitungnya', function () {
    // Satu sumber angka. Kalau keduanya bisa berbeda, layar akan menjanjikan
    // poin yang tak pernah datang.
    expect(Customer::RUPIAH_PER_POIN)->toBe(50000)
        ->and(Customer::NILAI_PER_POIN)->toBe(500);

    $c = Customer::create([
        'nama' => 'Hitung', 'no_hp' => '0812'.random_int(10000000, 99999999),
        'email' => 'hitung'.uniqid().'@contoh.test',
        'status_member' => 'active', 'point_balance' => 0, 'points_year' => now()->year,
    ]);

    // Pembagi yang dipakai layar (intdiv) harus sepakat dengan model.
    expect(intdiv(155000, Customer::RUPIAH_PER_POIN))->toBe(3)
        ->and($c->calculateYearlyPoints()['points'])->toBe((float) 0);
});
