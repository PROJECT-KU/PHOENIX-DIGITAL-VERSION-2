<?php

use App\Livewire\Pages\Admin\Order\OrderDetail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Tombol unduh di detail pesanan jasa.
 *
 * Berkas pelanggan dihapus otomatis 30 hari setelah pekerjaannya rampung, tapi
 * tombol "File Customer" dulu ditampilkan tanpa memeriksa berkasnya masih ada —
 * yang menekannya hanya mendapat 404. Dan saat semua berkas sudah hilang, layar
 * ini cuma memperlihatkan tombol yang TIDAK ADA, sehingga admin menyimpulkan
 * halamannya rusak (INV-20260828-0006, 14 Sep 2026).
 */
beforeEach(fn () => Storage::fake('local'));

function adminBerkas(): User
{
    $peran = Role::create(['name' => 'uji-berkas-'.uniqid(), 'description' => 'Peran uji berkas']);
    foreach (['view_pemesanantoko', 'edit_pemesanantoko'] as $nama) {
        $peran->permissions()->attach(Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'pemesanan', 'description' => 'uji']
        )->id);
    }

    return User::factory()->create(['role_id' => $peran->id])->fresh();
}

function pesananBerkas(array $isianUpload = []): Order
{
    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-BERKAS-'.Str::upper(Str::random(5)),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli Jasa',
            'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'berkas'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 15000,
        'total' => 15000,
        'unique_code' => 0,
        'status' => 'completed',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ]);

    OrderItem::create([
        'id' => Str::uuid(),
        'order_id' => $order->id,
        'product_id' => Product::create(['nama_akun' => 'Jasa Parafrase Manual', 'butuh_file' => true, 'jasa_mode' => 'halaman'])->id,
        'product_name' => 'Jasa Parafrase Manual',
        'duration_type' => 'halaman',
        'duration_value' => 1,
        'price' => 15000,
        'quantity' => 1,
        'subtotal' => 15000,
    ]);

    OrderUpload::create(array_merge([
        'order_id' => $order->id,
        'jenis' => 'parafrase',
        'nama_asli' => 'naskah.docx',
        'status' => 'selesai',
        'selesai_at' => now(),
    ], $isianUpload));

    return $order->fresh(['items.product', 'uploads', 'customer']);
}

it('tombol File Customer TIDAK muncul bila naskahnya sudah dihapus', function () {
    $order = pesananBerkas(['path' => null, 'hasil_docx_path' => 'hasil/parafrase.docx']);

    $up = $order->uploads->first();

    Livewire::actingAs(adminBerkas())
        ->test(OrderDetail::class, ['order' => $order])
        // Diperiksa lewat ALAMAT unduhannya, bukan teks tombol: kalimat
        // "File Customer" juga muncul di komentar CSS halaman ini.
        ->assertDontSeeHtml(route('admin.jasa.berkas', $up))
        ->assertSee('Naskah customer sudah dihapus otomatis')
        // Hasil yang masih ada tetap bisa diunduh.
        ->assertSeeHtml(route('admin.jasa.hasil-docx', $up));
});

it('tombol File Customer muncul selama naskahnya masih ada', function () {
    $order = pesananBerkas(['path' => 'masuk/naskah.docx']);

    Livewire::actingAs(adminBerkas())
        ->test(OrderDetail::class, ['order' => $order])
        ->assertSeeHtml(route('admin.jasa.berkas', $order->uploads->first()))
        ->assertDontSee('Naskah customer sudah dihapus otomatis');
});

it('menjelaskan kenapa tak ada tombol hasil, bukan sekadar menyembunyikannya', function () {
    // Persis keadaan INV-20260828-0006: selesai, tapi semua berkas lenyap.
    $order = pesananBerkas(['path' => null]);

    $up = $order->uploads->first();

    Livewire::actingAs(adminBerkas())
        ->test(OrderDetail::class, ['order' => $order])
        ->assertSee('Berkas hasil sudah tidak tersimpan')
        ->assertSee('Ganti Hasil')       // jalan keluarnya disebutkan
        ->assertDontSeeHtml(route('admin.jasa.hasil', $up))
        ->assertDontSeeHtml(route('admin.jasa.hasil-docx', $up));
});

it('pengecekan yang berkasnya lengkap tidak diberi keterangan apa pun', function () {
    $order = pesananBerkas([
        'path' => 'masuk/naskah.docx',
        'hasil_path' => 'hasil/plagiasi.pdf',
        'hasil_ai_path' => 'hasil/ai.pdf',
        'hasil_docx_path' => 'hasil/parafrase.docx',
    ]);

    $up = $order->uploads->first();

    Livewire::actingAs(adminBerkas())
        ->test(OrderDetail::class, ['order' => $order])
        ->assertSeeHtml(route('admin.jasa.hasil', $up))
        ->assertSeeHtml(route('admin.jasa.hasil-ai', $up))
        ->assertSeeHtml(route('admin.jasa.hasil-docx', $up))
        ->assertSeeHtml(route('admin.jasa.berkas', $up))
        // Keterangan hanya muncul saat memang ada yang hilang.
        ->assertDontSee('Naskah customer sudah dihapus otomatis')
        ->assertDontSee('Berkas hasil sudah tidak tersimpan');
});
