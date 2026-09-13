<?php

use App\Livewire\Pages\Public\ShopPage\TrackOrder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(fn () => RateLimiter::clear('track-order:'.request()->ip()));

function pesananLacak(string $status, string $hp = '081200000777'): Order
{
    $customer = Customer::create([
        'nama' => 'Pembeli Lacak',
        'no_hp' => $hp,
        'email' => 'lacak'.uniqid().'@contoh.test',
    ]);

    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-LACAK-'.Str::upper(Str::random(6)),
        'customer_id' => $customer->id,
        'subtotal' => 155000,
        'total' => 155000,
        'unique_code' => 0,
        'status' => $status,
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
        'share_token' => Str::upper(Str::random(10)),
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        // FK-nya sudah dilepas tetapi kolomnya masih NOT NULL.
        'product_id' => (string) Str::uuid(),
        'product_name' => 'NotebookLM',
        'product_image' => null,
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => 155000,
        'quantity' => 1,
        'subtotal' => 155000,
    ]);

    return $order->fresh();
}

it('tidak lagi meminjam kerangka dari CSS yang beku di server', function () {
    // public/build masuk .gitignore, jadi gaya dari public-custom-styles.css
    // tidak ikut `git pull` dan beku di server sampai ada rsync. Halaman yang
    // meminjam kelas dari sana tampil rusak di produksi walau sempurna di lokal
    // — tanpa satu pun galat di log.
    $sumber = file_get_contents(resource_path('views/livewire/pages/public/shop-page/track-order.blade.php'));

    foreach (['co-card', 'co-card-head', 'co-card-body', 'co-section', 'co-field', 'co-err',
        'co-btn', 'pay-card', 'pay-item', 'pay-sum', 'pay-total', 'pay-info-row',
        'ph-empty', 'ph-empty-btn', 'cart-summary-note'] as $beku) {
        expect($sumber)->not->toContain('class="'.$beku.'"');
        expect($sumber)->not->toContain('class="'.$beku.' ');
    }
});

it('kabel pencarian tetap utuh', function () {
    $sumber = file_get_contents(resource_path('views/livewire/pages/public/shop-page/track-order.blade.php'));

    expect($sumber)->toContain('wire:submit="track"')
        ->and($sumber)->toContain('wire:model="orderNumber"')
        ->and($sumber)->toContain('wire:model="phone"')
        ->and($sumber)->toContain('wire:target="track"');
});

it('jalur perhentian menjawab "pesanan saya sampai mana"', function () {
    /*
     | Inilah pertanyaan yang membuat orang datang ke halaman ini. Statusnya
     | diterjemahkan jadi posisi pada jalur yang sama dengan /checkout & /payment.
     */
    $hp = '081200000881';

    // Belum dibayar: berhenti di "Bayar".
    Livewire::test(TrackOrder::class)
        ->set('orderNumber', pesananLacak('pending', $hp)->order_number)
        ->set('phone', $hp)
        ->call('track')
        ->assertSeeHtml('<div class="lcp-henti is-kini">')
        ->assertDontSeeHtml('<div class="lcp-henti is-tuntas">')
        ->assertDontSeeHtml('<div class="lcp-henti is-gagal">');
});

it('pesanan selesai menuntaskan seluruh jalur', function () {
    $hp = '081200000882';

    Livewire::test(TrackOrder::class)
        ->set('orderNumber', pesananLacak('completed', $hp)->order_number)
        ->set('phone', $hp)
        ->call('track')
        ->assertSeeHtml('<div class="lcp-henti is-tuntas">')
        ->assertDontSeeHtml('<div class="lcp-henti is-gagal">');
});

it('pesanan batal ditandai MERAH di perhentian Bayar', function () {
    // Kelabu seperti langkah yang belum dijalani akan menyembunyikan satu-satunya
    // hal yang perlu diketahui: di sinilah pesanannya berhenti.
    $hp = '081200000883';

    Livewire::test(TrackOrder::class)
        ->set('orderNumber', pesananLacak('cancelled', $hp)->order_number)
        ->set('phone', $hp)
        ->call('track')
        ->assertSeeHtml('<div class="lcp-henti is-gagal">')
        ->assertSee('Total yang batal');
});

it('warna total mengikuti nasib uangnya', function () {
    /*
     | Sama seperti di halaman sukses dan halaman kedaluwarsa: hijau bila
     | uangnya benar-benar masuk, kelabu dicoret bila tidak jadi berpindah.
     | Merayakan total pesanan yang batal dengan gradasi jingga itu menyesatkan.
     */
    $hp1 = '081200000884';
    Livewire::test(TrackOrder::class)
        ->set('orderNumber', pesananLacak('completed', $hp1)->order_number)
        ->set('phone', $hp1)
        ->call('track')
        ->assertSeeHtml('class="lcp-total is-lunas"');

    $hp2 = '081200000885';
    Livewire::test(TrackOrder::class)
        ->set('orderNumber', pesananLacak('cancelled', $hp2)->order_number)
        ->set('phone', $hp2)
        ->call('track')
        ->assertSeeHtml('class="lcp-total is-batal"');
});

it('barang ditampilkan dengan warna kategorinya, ikon cadangan bila logo tak ada', function () {
    $hp = '081200000886';

    Livewire::test(TrackOrder::class)
        ->set('orderNumber', pesananLacak('paid', $hp)->order_number)
        ->set('phone', $hp)
        ->call('track')
        // NotebookLM = AI Tools (#7c3aed) — taksonomi yang sama dengan Shop.
        ->assertSeeHtml('<div class="lcp-item" style="--k: #7c3aed">')
        ->assertSeeHtml('<i class="bi bi-robot"></i>')
        // Berkasnya tidak ada: <img> TIDAK dipasang, supaya teks alt tidak
        // tampil sebagai gambar rusak.
        ->assertDontSeeHtml('storage/img/Product/');
});

it('pesanan yang tidak cocok tidak membocorkan apa pun', function () {
    // Nomor order benar tetapi HP berbeda: halaman harus berlaku seolah
    // pesanannya tidak ada, bukan menampilkan sebagiannya.
    $order = pesananLacak('paid', '081200000887');

    Livewire::test(TrackOrder::class)
        ->set('orderNumber', $order->order_number)
        ->set('phone', '081999999999')
        ->call('track')
        ->assertSee('Pesanan tidak ditemukan')
        ->assertDontSee($order->order_number)
        ->assertDontSee('NotebookLM');
});
