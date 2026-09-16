<?php

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Models\Product;
use App\Support\KecepatanJasa;
use App\Support\PeriodeGaji;
use App\Support\RingkasanLaba;
use App\Support\RingkasanPenjualan;
use Illuminate\Support\Str;

/**
 * Laba, jumlah transaksi, pelanggan baru vs kembali, dan kecepatan jasa.
 *
 * Yang dijaga di sini adalah hal-hal yang membuat angkanya menyesatkan tanpa
 * terlihat salah: modal yang tidak terpisah dari gaji, pelanggan lama yang
 * terhitung baru tiap periode, dan pesanan batal yang ikut masuk hitungan.
 */
function rentangLaba(): array
{
    $p = PeriodeGaji::dariTanggal(now());

    return [
        PeriodeGaji::mulai($p['bulan'], $p['tahun']),
        PeriodeGaji::akhir($p['bulan'], $p['tahun'])->copy()->addDay()->startOfDay(),
    ];
}

function pelangganLaba(string $nama = 'Pembeli Laba'): Customer
{
    return Customer::create([
        'nama' => $nama,
        'no_hp' => '0813'.random_int(10000000, 99999999),
        'email' => Str::slug($nama).uniqid().'@contoh.test',
    ]);
}

function pesananLaba(float $total, ?Customer $pelanggan = null, array $lain = []): Order
{
    return Order::create(array_merge([
        'id' => Str::uuid(),
        'order_number' => 'INV-LABA-'.Str::upper(Str::random(6)),
        'customer_id' => ($pelanggan ?? pelangganLaba())->id,
        'subtotal' => $total, 'total' => $total, 'unique_code' => 0,
        'status' => 'paid',
        'paid_at' => now(),
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ], $lain));
}

/** Satu baris modal, persis seperti yang ditulis SyncOrderPrivateCostAction. */
function modalLaba(Order $order, float $nominal): OrderItem
{
    $produk = Product::firstOrCreate(
        ['nama_akun' => 'Produk Laba'],
        ['tipe_akun' => 'private', 'harga_perbulan' => 100000]
    );

    $item = OrderItem::create([
        'id' => Str::uuid(),
        'order_id' => $order->id,
        'product_id' => $produk->id,
        'product_name' => 'Produk Laba',
        'duration_type' => 'bulan', 'duration_value' => 1,
        'price' => 100000, 'quantity' => 1, 'subtotal' => 100000,
        'delivery_status' => 'delivered',
    ]);

    CashFlow::create([
        'sourceable_id' => $item->id,
        'sourceable_type' => OrderItem::class,
        'amount' => $nominal,
        'type' => 'expense',
        'transaction_date' => now(),
        'category' => 'Modal Akun Private',
        'description' => 'Modal uji',
    ]);

    return $item;
}

/** Kolom berkas pada order_uploads NOT NULL — tiap unggahan selalu punya berkasnya. */
function unggahanKecepatan(Order $order, string $jenis, string $status, $masuk, $selesai): OrderUpload
{
    $unggahan = OrderUpload::create([
        'order_id' => $order->id,
        'jenis' => $jenis,
        'status' => $status,
        'path' => 'uji/naskah.pdf',
        'nama_asli' => 'naskah.pdf',
        'ukuran' => 1024,
        'mime' => 'application/pdf',
    ]);

    // created_at diisi Eloquent sendiri saat create(), jadi waktunya ditulis
    // ulang lewat forceFill — tanpa ini seluruh unggahan uji berumur nol
    // detik dan pengujian kecepatannya tidak menguji apa pun.
    return $unggahan->forceFill(['created_at' => $masuk, 'selesai_at' => $selesai])->save()
        ? $unggahan->refresh()
        : $unggahan;
}

it('laba kotor adalah omset dikurangi modal, bukan omset dikurangi seluruh pengeluaran', function () {
    [$mulai, $akhir] = rentangLaba();

    modalLaba(pesananLaba(300000), 100000);

    // Pengeluaran yang BUKAN modal: gaji. Ia tidak boleh mengurangi laba
    // kotor — kalau ikut, "laba kotor" berubah arti jadi laba bersih dan
    // margin produknya tidak bisa lagi dibaca.
    CashFlow::create([
        'sourceable_id' => (string) Str::uuid(),
        'sourceable_type' => \App\Models\Spending::class,
        'amount' => 250000, 'type' => 'expense',
        'transaction_date' => now(), 'category' => 'Gaji', 'description' => 'Gaji uji',
    ]);

    $l = RingkasanLaba::periode($mulai, $akhir);

    expect($l['omset'])->toBe(300000.0)
        ->and($l['modal'])->toBe(100000.0)
        ->and($l['laba_kotor'])->toBe(200000.0)
        ->and($l['margin'])->toBe(66.7)
        ->and($l['biaya_operasional'])->toBe(250000.0);
});

it('margin kosong saat belum ada omset, bukan nol atau pembagian nol', function () {
    [$mulai, $akhir] = rentangLaba();

    expect(RingkasanLaba::periode($mulai, $akhir)['margin'])->toBeNull();
});

it('pesanan batal tidak ikut omset maupun modalnya', function () {
    [$mulai, $akhir] = rentangLaba();

    modalLaba(pesananLaba(500000, null, ['status' => 'cancelled', 'paid_at' => null]), 200000);

    $l = RingkasanLaba::periode($mulai, $akhir);

    expect($l['omset'])->toBe(0.0)->and($l['modal'])->toBe(0.0);
});

it('menghitung jumlah pesanan dan rata-rata nilainya', function () {
    [$mulai, $akhir] = rentangLaba();

    pesananLaba(100000);
    pesananLaba(200000);
    pesananLaba(300000, null, ['status' => 'pending', 'paid_at' => null]); // belum dibayar

    $j = RingkasanPenjualan::periode($mulai, $akhir);

    expect($j['pesanan'])->toBe(2)
        ->and($j['nilai'])->toBe(300000.0)
        ->and($j['rata'])->toBe(150000.0);
});

it('pelanggan lama tidak terhitung baru hanya karena membeli lagi', function () {
    [$mulai, $akhir] = rentangLaba();

    $lama = pelangganLaba('Pelanggan Lama');
    // Pesanan pertamanya jauh sebelum periode ini.
    pesananLaba(100000, $lama, ['paid_at' => now()->subMonths(4), 'created_at' => now()->subMonths(4)]);
    pesananLaba(100000, $lama); // beli lagi periode ini

    pesananLaba(100000, pelangganLaba('Pelanggan Baru'));

    $j = RingkasanPenjualan::periode($mulai, $akhir);

    expect($j['pelanggan'])->toBe(2)
        ->and($j['baru'])->toBe(1)
        ->and($j['kembali'])->toBe(1);
});

it('menghitung kecepatan jasa dan yang lewat sehari', function () {
    [$mulai, $akhir] = rentangLaba();

    $pesanan = pesananLaba(100000);

    // Dua jam: wajar. Tiga hari: lewat ambang.
    unggahanKecepatan($pesanan, 'plagiasi', 'selesai', now()->subHours(2), now());
    unggahanKecepatan($pesanan, 'ai', 'selesai', now()->subDays(3), now());
    // Belum selesai: tidak punya selesai_at, jadi tidak boleh ikut dihitung —
    // memasukkannya membuat rata-rata terbaca lebih cepat dari kenyataan.
    unggahanKecepatan($pesanan, 'plagiasi', 'menunggu', now()->subDays(9), null);

    $k = KecepatanJasa::periode($mulai, $akhir);

    expect($k['selesai'])->toBe(2)
        ->and($k['lewat_sehari'])->toBe(1)
        ->and($k['rata_jam'])->toBe(37.0)
        ->and($k['tepat_persen'])->toBe(50.0);
});

it('menulis lama pengerjaan dalam satuan yang terbaca', function () {
    expect(KecepatanJasa::labelJam(0.5))->toBe('30 menit')
        ->and(KecepatanJasa::labelJam(7.2))->toBe('7,2 jam')
        ->and(KecepatanJasa::labelJam(192.4))->toBe('8,0 hari')
        ->and(KecepatanJasa::labelJam(null))->toBe('—');
});
