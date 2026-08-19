<?php

use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductBundlings;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(function () {
    HargaPaket::lupakan();
});

function paketDiKeranjang(): ProductBundlings
{
    $paket = ProductBundlings::create([
        'nama_paket' => 'Paket Checkout',
        'harga_awal' => 125000,
        'harga_bundling' => 99000,
        'status' => 'active',
    ]);

    session()->put('cart', ["bundling_{$paket->id}" => [
        'product_id' => $paket->id,
        'product_name' => $paket->nama_paket,
        'product_image' => null,
        // Paket tidak punya durasi tunggal — durasinya melekat pada tiap
        // produk di dalamnya. Inilah yang dulu membuat checkout gagal.
        'duration_type' => null,
        'duration_value' => null,
        'harga_awal' => 125000,
        'type' => 'bundling',
        'price' => 99000,
        'quantity' => 1,
        'subtotal' => 99000,
    ]]);

    return $paket;
}

function isiFormulir(string $nomor)
{
    // Urutan penting: mengisi no_hp memicu pencarian pelanggan yang menimpa
    // nama dan email.
    return Livewire::test(CheckoutPage::class)
        ->set('no_hp', $nomor)
        ->set('nama', 'Pembeli Paket')
        ->set('email', 'pembeli.paket@contoh.test');
}

it('checkout paket membuat pesanan dan mengarah ke halaman pembayaran', function () {
    $paket = paketDiKeranjang();

    $hasil = isiFormulir('081200000001')->call('checkout');

    $order = Order::latest('created_at')->first();

    expect($order)->not->toBeNull();

    $hasil->assertRedirect(route('payment', ['order' => $order->id]));
});

it('baris pesanan paket tersimpan tanpa durasi', function () {
    $paket = paketDiKeranjang();

    isiFormulir('081200000002')->call('checkout');

    $item = OrderItem::where('product_id', $paket->id)->first();

    expect($item)->not->toBeNull()
        ->and($item->duration_type)->toBeNull()
        ->and($item->duration_value)->toBeNull()
        ->and((int) $item->subtotal)->toBe(99000);
});

it('checkout paket tidak meninggalkan pesanan gagal', function () {
    paketDiKeranjang();

    isiFormulir('081200000003')->call('checkout');

    // Sebelum perbaikan, pesanan dibuat lalu dibatalkan diam-diam saat baris
    // itemnya gagal disimpan, sehingga pembeli tidak pernah sampai ke QRIS.
    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->items()->count())->toBe(1)
        ->and($order->payment_method)->toBe('qris_dinamis');
});
