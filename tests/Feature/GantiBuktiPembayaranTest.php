<?php

use App\Livewire\Pages\Admin\Order\BuktiPembayaran;
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

it('detail order menautkan ke halaman unggah bukti, bukan popup', function () {
    $order = orderBayar('transfer');

    $html = Livewire::test(OrderDetail::class, ['order' => $order])->html();

    // Satu pekerjaan, satu tampilan: memakai halaman yang sama dengan alur draft.
    expect($html)->toContain('/unggah-bukti')
        ->and($html)->not->toContain('bp-overlay');
});

it('halaman unggah bukti melayani pesanan yang sudah aktif, bukan hanya draft', function () {
    $order = orderBayar('transfer', 'processing');

    $t = Livewire::test(BuktiPembayaran::class, ['order' => $order]);

    expect($t->get('gantiSaja'))->toBeTrue();
});

it('pesanan draft ditandai sebagai unggah pertama, bukan ganti', function () {
    $order = orderBayar('transfer', 'draft');

    $t = Livewire::test(BuktiPembayaran::class, ['order' => $order]);

    expect($t->get('gantiSaja'))->toBeFalse();
});

it('mengganti bukti pesanan aktif TIDAK mengubah statusnya', function () {
    Storage::fake('local');

    $order = orderBayar('transfer', 'processing');
    $lama = UploadedFile::fake()->image('lama.jpg')->store('bukti_pembayaran', 'local');
    $order->update(['bukti_pembayaran' => $lama]);

    Livewire::test(BuktiPembayaran::class, ['order' => $order])
        ->set('bukti', UploadedFile::fake()->image('baru.jpg'))
        ->call('simpan');

    $segar = $order->fresh();

    expect($segar->status)->toBe('processing')
        ->and($segar->bukti_pembayaran)->not->toBe($lama)
        // Berkas lama tidak ditinggalkan menumpuk di disk.
        ->and(Storage::disk('local')->exists($lama))->toBeFalse();
});

it('unggah bukti pesanan draft mengaktifkan pesanan', function () {
    Storage::fake('local');

    $order = orderBayar('transfer', 'draft');

    Livewire::test(BuktiPembayaran::class, ['order' => $order])
        ->set('bukti', UploadedFile::fake()->image('bukti.jpg'))
        ->call('simpan');

    expect($order->fresh()->status)->toBe('pending');
});

it('qris dinamis tidak bisa membuka halaman unggah bukti', function () {
    $order = orderBayar('qris_dinamis');

    Livewire::test(BuktiPembayaran::class, ['order' => $order])->assertStatus(404);
});

it('menolak berkas yang bukan gambar', function () {
    Storage::fake('local');

    $order = orderBayar('transfer');

    Livewire::test(BuktiPembayaran::class, ['order' => $order])
        ->set('bukti', UploadedFile::fake()->create('dokumen.pdf', 100))
        ->call('simpan')
        ->assertHasErrors(['bukti' => 'image']);
});

it('pratinjau dibaca di browser, tidak meminta ke server', function () {
    $blade = file_get_contents(
        resource_path('views/livewire/pages/admin/order/bukti-pembayaran.blade.php')
    );

    // temporaryUrl() menghasilkan alamat berakhiran .jpg/.jpeg/.png yang
    // dicegat pengoptimal gambar hosting sebelum sampai ke aplikasi.
    // Disasar ke PEMAKAIANNYA; kata "temporaryUrl" masih muncul di komentar
    // yang menjelaskan kenapa cara itu ditinggalkan.
    expect($blade)->toContain('URL.createObjectURL')
        ->and($blade)->not->toContain('$bukti->temporaryUrl');
});
