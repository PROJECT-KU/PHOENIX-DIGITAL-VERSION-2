<?php

use App\Livewire\Pages\Admin\Order\OrderDetail;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

function orderBayar(string $metode, string $status = 'pending'): Order
{
    $customer = Customer::create([
        'nama' => 'Pembeli',
        'no_hp' => '0812'.rand(10000000, 99999999),
        'email' => 'bayar'.uniqid().'@contoh.test',
    ]);

    return Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-BUKTI-'.rand(1000, 9999),
        'customer_id' => $customer->id,
        'subtotal' => 99000,
        'total' => 99000,
        'unique_code' => 0,
        'status' => $status,
        'payment_method' => $metode,
        'expired_at' => now()->addHours(24),
    ]);
}

it('transfer dan qris statis boleh mengganti bukti', function (string $metode) {
    $order = orderBayar($metode);

    $t = Livewire::test(OrderDetail::class, ['order' => $order]);

    expect($t->instance()->bolehGantiBukti())->toBeTrue();
})->with(['transfer', 'qris_statis']);

it('qris dinamis tidak boleh mengganti bukti', function () {
    $order = orderBayar('qris_dinamis');

    $t = Livewire::test(OrderDetail::class, ['order' => $order]);

    expect($t->instance()->bolehGantiBukti())->toBeFalse();
});

it('bukti lama diganti dan berkasnya dibersihkan', function () {
    Storage::fake('local');

    $order = orderBayar('transfer');
    $lama = UploadedFile::fake()->image('lama.jpg')->store('bukti_pembayaran', 'local');
    $order->update(['bukti_pembayaran' => $lama]);

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->set('buktiBaru', UploadedFile::fake()->image('baru.jpg'))
        ->call('gantiBukti');

    $baru = $order->fresh()->bukti_pembayaran;

    expect($baru)->not->toBe($lama)
        ->and(Storage::disk('local')->exists($baru))->toBeTrue()
        // Berkas lama tidak ditinggalkan menumpuk di disk.
        ->and(Storage::disk('local')->exists($lama))->toBeFalse();
});

it('mengganti bukti TIDAK mengubah status pesanan', function () {
    Storage::fake('local');

    $order = orderBayar('transfer', 'processing');
    $order->update(['bukti_pembayaran' => UploadedFile::fake()->image('lama.jpg')->store('bukti_pembayaran', 'local')]);

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->set('buktiBaru', UploadedFile::fake()->image('baru.jpg'))
        ->call('gantiBukti');

    // Ini koreksi berkas, bukan perpindahan tahap pembayaran.
    expect($order->fresh()->status)->toBe('processing');
});

it('menolak berkas yang bukan gambar', function () {
    Storage::fake('local');

    $order = orderBayar('transfer');

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->set('buktiBaru', UploadedFile::fake()->create('dokumen.pdf', 100))
        ->call('gantiBukti')
        ->assertHasErrors(['buktiBaru' => 'image']);
});

it('menolak gambar lebih dari 4 MB', function () {
    Storage::fake('local');

    $order = orderBayar('transfer');

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->set('buktiBaru', UploadedFile::fake()->image('besar.jpg')->size(5000))
        ->call('gantiBukti')
        ->assertHasErrors(['buktiBaru' => 'max']);
});
