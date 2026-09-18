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

it('detail menampilkan bukti bila berkasnya ada', function () {
    Storage::fake('local');
    Storage::fake('public');

    $order = orderBayar('transfer', 'processing');
    $path = UploadedFile::fake()->image('bukti.jpg')->store('bukti_pembayaran', 'local');
    $order->update(['bukti_pembayaran' => $path]);

    $t = Livewire::test(OrderDetail::class, ['order' => $order]);

    expect($t->instance()->buktiTersedia())->toBeTrue()
        ->and($t->html())->toContain('alt="Bukti pembayaran"')
        ->and($t->html())->not->toContain('class="pt-bukti is-gagal');
});

it('detail tidak memasang gambar rusak bila berkas bukti hilang', function () {
    Storage::fake('local');
    Storage::fake('public');

    // Kolomnya terisi, berkasnya tidak ada (mis. database disalin tanpa unggahan).
    $order = orderBayar('transfer', 'processing');
    $order->update(['bukti_pembayaran' => 'bukti_pembayaran/tidak-ada.jpg']);

    $t = Livewire::test(OrderDetail::class, ['order' => $order]);

    expect($t->instance()->buktiTersedia())->toBeFalse()
        ->and($t->html())->not->toContain('alt="Bukti pembayaran"')
        ->and($t->html())->toContain('pt-bukti is-gagal')
        ->and($t->html())->toContain('tidak ditemukan di server');
});

it('detail menandai bukti yang belum diunggah', function () {
    $order = orderBayar('transfer', 'pending');

    $html = Livewire::test(OrderDetail::class, ['order' => $order])->html();

    expect($html)->toContain('is-kosong')
        ->and($html)->toContain('Belum ada bukti yang diunggah')
        ->and($html)->not->toContain('alt="Bukti pembayaran"');
});

it('kartu pembeli menampilkan status member, poin, jumlah pesanan, dan tombol whatsapp', function () {
    $order = orderBayar('transfer', 'processing');
    $order->customer->update(['no_hp' => '0812 3456 7890', 'status_member' => 'active', 'point' => 1500]);

    $html = Livewire::test(OrderDetail::class, ['order' => $order->fresh()])->html();

    expect($html)->toContain('Member aktif')
        ->and($html)->toContain('1.500 poin')
        ->and($html)->toContain('1 pesanan')
        ->and($html)->toContain('https://api.whatsapp.com/send?phone=6281234567890');
});

it('pesanan qris dinamis menampilkan referensi dan id transaksi qris', function () {
    $order = orderBayar('qris_dinamis', 'paid');
    $order->update(['payment_reference' => 'INV-REF-UJI-ABCD', 'qris_trx_id' => '43809182']);

    $html = Livewire::test(OrderDetail::class, ['order' => $order->fresh()])->html();

    expect($html)->toContain('Referensi Pembayaran')
        ->and($html)->toContain('INV-REF-UJI-ABCD')
        ->and($html)->toContain('43809182');
});

it('tabel item menampilkan harga satuan dan rincian harga paket', function () {
    $order = orderBayar('transfer', 'processing');
    $produk = \App\Models\Product::factory()->create(['harga_perbulan' => 20000, 'harga_5_perbulan' => 60000]);
    \App\Models\OrderItem::create([
        'order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'Produk Uji',
        'quantity' => 1, 'price' => 60000, 'subtotal' => 60000, 'duration_value' => 5, 'duration_type' => 'bulan',
    ]);

    $html = Livewire::test(OrderDetail::class, ['order' => $order->fresh()])->html();

    expect($html)->toContain('Harga Satuan')
        ->and($html)->toContain('Rp 20.000')
        ->and($html)->toContain('per bulan')
        ->and($html)->toContain('Harga paket 5 bulan')
        ->and($html)->toContain('Rp 100.000')
        ->and($html)->toContain('hemat Rp 40.000');
});

it('blok jasa memakai nama jasa, angka kuota, dan tetap memuat aksi berkas yang menunggu', function () {
    $order = orderBayar('qris_dinamis', 'paid');
    $produk = \App\Models\Product::factory()->create(['nama_akun' => 'Cek Plagiasi Turnitin', 'butuh_file' => 1, 'harga_perbulan' => 5000]);
    \App\Models\OrderItem::create([
        'order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'Cek Plagiasi Turnitin',
        'quantity' => 1, 'price' => 5000, 'subtotal' => 5000, 'duration_value' => 1, 'duration_type' => 'kali',
    ]);
    \App\Models\OrderUpload::create([
        'order_id' => $order->id, 'jenis' => 'plagiasi', 'nama_asli' => 'naskah.docx', 'status' => 'menunggu',
    ]);

    $html = Livewire::test(OrderDetail::class, ['order' => $order->fresh()])->html();

    expect($html)->toContain('Jasa pengecekan')
        ->and($html)->toContain('<h5 class="fw-bold mb-0">Cek Plagiasi Turnitin</h5>')
        ->and($html)->toContain('pcek-kuota-angka')
        ->and($html)->toContain('Mulai Proses')
        ->and($html)->toContain('data-action="batalkanPengecekan"')
        ->and($html)->toContain('pcek-daftar is-tunggal');
});

it('form unggah hasil tampil sebagai jendela terpisah, bukan di dalam kartu berkas', function () {
    $order = orderBayar('qris_dinamis', 'paid');
    $produk = \App\Models\Product::factory()->create(['nama_akun' => 'Cek Plagiasi Turnitin', 'butuh_file' => 1, 'harga_perbulan' => 5000]);
    \App\Models\OrderItem::create([
        'order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'Cek Plagiasi Turnitin',
        'quantity' => 1, 'price' => 5000, 'subtotal' => 5000, 'duration_value' => 1, 'duration_type' => 'kali',
    ]);
    $up = \App\Models\OrderUpload::create([
        'order_id' => $order->id, 'jenis' => 'plagiasi', 'nama_asli' => 'naskah-selesai.pdf', 'status' => 'selesai',
    ]);

    $t = Livewire::test(OrderDetail::class, ['order' => $order->fresh()]);
    expect($t->html())->not->toContain('pcek-jendela');

    $html = $t->call('bukaUploadHasil', $up->id)->html();
    expect($html)->toContain('class="ts-modal-card dsb is-datar pcek pcek-jendela"')
        ->and($html)->toContain('Ganti Hasil Pengecekan')
        ->and($html)->toContain('menggantikan');

    expect($t->call('tutupUploadHasil')->html())->not->toContain('pcek-jendela');
});
