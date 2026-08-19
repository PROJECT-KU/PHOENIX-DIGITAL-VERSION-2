<?php

use App\Livewire\Pages\Admin\Order\BuktiPembayaran;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Str;
use Livewire\Livewire;

function orderDraft(string $metode = 'transfer'): Order
{
    $customer = Customer::create([
        'nama' => 'Pembeli Draft',
        'no_hp' => '0812'.rand(10000000, 99999999),
        'email' => 'draft'.uniqid().'@contoh.test',
    ]);

    return Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-DRAFT-'.rand(1000, 9999),
        'customer_id' => $customer->id,
        'subtotal' => 99000,
        'total' => 99000,
        'unique_code' => 0,
        'status' => 'draft',
        'payment_method' => $metode,
        'expired_at' => now()->addHours(24),
    ]);
}

it('halaman unggah bukti draft terbuka tanpa galat', function () {
    $order = orderDraft('transfer');

    // Sebelumnya halaman ini 500 karena breadcrumb dikirim dengan kunci
    // 'label', sedangkan komponennya membaca 'name'.
    Livewire::test(BuktiPembayaran::class, ['order' => $order])
        ->assertStatus(200);
});

it('remah roti unggah bukti memakai kunci name yang benar', function () {
    $order = orderDraft('transfer');

    $html = Livewire::test(BuktiPembayaran::class, ['order' => $order])->html();

    expect($html)->toContain('Data Pemesanan')
        ->and($html)->toContain('Unggah Bukti');
});

it('qris statis juga bisa membuka halaman unggah bukti', function () {
    $order = orderDraft('qris_statis');

    Livewire::test(BuktiPembayaran::class, ['order' => $order])
        ->assertStatus(200);
});

it('pesanan yang bukan draft ditolak', function () {
    $order = orderDraft('transfer');
    $order->update(['status' => 'pending']);

    Livewire::test(BuktiPembayaran::class, ['order' => $order])
        ->assertStatus(404);
});

it('ikon disejajarkan dengan teks', function () {
    $blade = file_get_contents(
        resource_path('views/livewire/pages/admin/order/bukti-pembayaran.blade.php')
    );

    // Ikon Bootstrap duduk mengikuti baseline huruf; line-height dinolkan dan
    // ::before dijadikan block supaya tingginya persis kotak ikonnya.
    expect($blade)->toContain('i.bi::before')
        ->and($blade)->toContain('line-height: 1;');
});

it('memakai sistem desain admin, bukan gaya karangan sendiri', function () {
    $order = orderDraft('transfer');

    $html = Livewire::test(BuktiPembayaran::class, ['order' => $order])->html();

    // Kepala halaman mengikuti pola halaman Tambah Pesanan, dan keping ikon
    // memakai gradasi milik layout admin.
    foreach (['fixed-header-card', 'gradient-text', 'breadcrumb-custom', 'bg-gradient-purple', 'bg-gradient-blue'] as $kelas) {
        expect($html)->toContain($kelas);
    }
});
