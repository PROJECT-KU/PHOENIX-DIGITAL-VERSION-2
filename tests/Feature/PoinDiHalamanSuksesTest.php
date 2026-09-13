<?php

use App\Livewire\Pages\Public\ShopPage\OrderSuccessPage;
use App\Models\Customer;
use App\Models\Order;
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

    return Order::create([
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
}

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
