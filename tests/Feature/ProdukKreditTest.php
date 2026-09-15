<?php

use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Livewire\Pages\Public\ShopPage\Index;
use App\Livewire\Pages\Public\ShopPage\ProductDetail;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

/**
 * Produk KREDIT (mis. kredit Gamma AI).
 *
 * Kredit menumpang kolom durasi yang sama dengan bulan/tahun — satuannya
 * 'kredit'. Dua hal membedakannya dan keduanya dijaga di sini:
 *
 *  1. TIDAK punya masa aktif. Kode lama memperlakukan satuan apa pun selain
 *     'tahun' sebagai BULAN, jadi "1500 kredit" akan berakhir 125 tahun lagi —
 *     dan pembelinya kelak dikirimi peringatan "akun akan habis".
 *  2. Angkanya besar. Tanpa pemisah ribuan kartunya mencetak "1500 kredit",
 *     yang di antara harga rupiah terbaca seperti salah ketik.
 *
 * Produk non-kredit tidak boleh ikut berubah — itu alur yang sudah berjalan.
 */
beforeEach(function () {
    Cache::flush();
    Product::query()->delete();
});

function produkKredit(array $paket = [1500 => 25000, 6000 => 85000]): Product
{
    $p = Product::create(['nama_akun' => 'Kredit Gamma AI', 'tipe_akun' => 'private']);

    foreach ($paket as $jumlah => $harga) {
        ProductPrice::create([
            'product_id' => $p->id,
            'durasi_value' => $jumlah,
            'durasi_type' => 'kredit',
            'harga' => $harga,
        ]);
    }

    return $p->fresh('prices');
}

it('kartu /shop memakai paket kredit termurah, bukan harga per bulan yang kosong', function () {
    produkKredit();

    Livewire::test(Index::class)
        ->assertSeeHtml('<b><span class="sk-rp">Rp</span>25.000</b>')
        ->assertSeeHtml('<small>/1.500 kredit</small>')
        // "Mulai" karena paketnya lebih dari satu dan yang tercetak yang termurah.
        ->assertSeeHtml('<small>Mulai</small>')
        ->assertDontSeeHtml('<small>/bln</small>');
});

it('produk berlangganan biasa tetap tampil per bulan', function () {
    Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 20000]);

    Livewire::test(Index::class)
        ->assertSeeHtml('<small>/bln</small>')
        ->assertDontSeeHtml('<small>Mulai</small>');
});

it('kartu paket di halaman produk memisah ribuan dan tidak menghitung harga per bulan', function () {
    $p = produkKredit();

    Livewire::test(ProductDetail::class, ['id' => $p->id])
        ->assertSeeHtml('1.500 Kredit')
        ->assertSeeHtml('6.000 Kredit')
        // "≈ Rp…/bulan" tidak punya arti untuk kredit.
        ->assertDontSee('/bulan');
});

it('kredit tidak punya tanggal berakhir, dan satuan waktu tetap dihitung seperti dulu', function () {
    $kredit = new OrderItem(['duration_type' => 'kredit', 'duration_value' => 1500, 'start_date' => '2026-09-01']);
    $bulanan = new OrderItem(['duration_type' => 'bulan', 'duration_value' => 3, 'start_date' => '2026-09-01']);
    $tahunan = new OrderItem(['duration_type' => 'tahun', 'duration_value' => 1, 'start_date' => '2026-09-01']);

    expect($kredit->calculateEndDate())->toBeNull()
        ->and($kredit->pakaiKredit())->toBeTrue()
        ->and($bulanan->calculateEndDate()->format('Y-m-d'))->toBe('2026-12-01')
        ->and($tahunan->calculateEndDate()->format('Y-m-d'))->toBe('2027-09-01')
        ->and($bulanan->pakaiKredit())->toBeFalse();
});

it('label durasi kredit dipisah ribuan', function () {
    $item = new OrderItem(['duration_type' => 'kredit', 'duration_value' => 15000]);

    expect($item->getDurationLabel())->toBe('15.000 kredit')
        ->and((new OrderItem(['duration_type' => 'bulan', 'duration_value' => 3]))->getDurationLabel())->toBe('3 bulan');
});

it('pesanan kredit wajib menyebut akun tujuan di catatan', function () {
    $p = produkKredit();

    session()->put('cart', ["{$p->id}_kredit_1500" => [
        'product_id' => $p->id,
        'product_name' => $p->nama_akun,
        'product_image' => null,
        'duration_type' => 'kredit',
        'duration_value' => 1500,
        'price' => 25000,
        'quantity' => 1,
        'subtotal' => 25000,
    ]]);

    $form = fn () => Livewire::test(CheckoutPage::class)
        ->set('no_hp', '081200000077')
        ->set('nama', 'Pembeli Kredit')
        ->set('email', 'pembeli.kredit@contoh.test');

    // Tanpa catatan: admin tidak tahu akun mana yang diisi.
    $form()->call('checkout')->assertHasErrors('customer_notes');

    $form()->set('customer_notes', 'akun saya: pembeli.kredit@contoh.test')
        ->call('checkout')
        ->assertHasNoErrors('customer_notes');
});

it('pesanan biasa catatannya tetap opsional', function () {
    $p = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 20000]);

    session()->put('cart', ["{$p->id}_bulan_1" => [
        'product_id' => $p->id,
        'product_name' => $p->nama_akun,
        'product_image' => null,
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => 20000,
        'quantity' => 1,
        'subtotal' => 20000,
    ]]);

    Livewire::test(CheckoutPage::class)
        ->set('no_hp', '081200000078')
        ->set('nama', 'Pembeli Bulanan')
        ->set('email', 'pembeli.bulanan@contoh.test')
        ->call('checkout')
        ->assertHasNoErrors('customer_notes');
});

it('satuan kredit diizinkan kolom MySQL dan kedua formulir admin', function () {
    // Dijaga di SUMBER: pengujian memakai SQLite yang kolom satuannya sudah
    // dilonggarkan jadi string, jadi ENUM MySQL yang ketinggalan tidak akan
    // pernah membuat tes merah — ia baru terlihat di server, sebagai pesanan
    // yang batal diam-diam.
    $migrasi = glob(database_path('migrations/*izinkan_satuan_kredit*.php'));
    expect($migrasi)->not->toBeEmpty();

    $isi = file_get_contents($migrasi[0]);
    expect($isi)->toContain("'bulan','tahun','sekali','kali','halaman','kredit'")
        ->and($isi)->toContain('product_prices')
        ->and($isi)->toContain('product_modal_prices')
        ->and($isi)->toContain('order_items');

    // Harga jual & harga modal: keduanya harus menerima satuan ini.
    expect(file_get_contents(app_path('Livewire/Pages/Admin/Product/ProductForm.php')))
        ->toContain('in:bulan,tahun,sekali,kali,halaman,kredit')
        ->and(file_get_contents(app_path('Livewire/Pages/Admin/HargaModal/HargaModalList.php')))
        ->toContain('in:bulan,tahun,kali,halaman,kredit');
});
