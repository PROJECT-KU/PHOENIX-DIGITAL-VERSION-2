<?php

use App\Models\Customer;
use App\Models\DataAkun;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Models\Product;
use App\Models\Task;
use App\Support\RingkasanOperasional as Ringkas;
use Illuminate\Support\Str;

/**
 * Angka operasional dasbor: pekerjaan dan uang yang MENUNGGU tindakan.
 *
 * Yang dijaga di sini adalah hal-hal yang membuat angkanya menyesatkan tanpa
 * terlihat salah — langganan batal ikut terhitung, akun yang langganannya
 * sudah habis dianggap masih terpakai, dan pesanan batal masuk "terlaris".
 */
function pelangganOps(string $nama = 'Pembeli Ops'): Customer
{
    return Customer::create([
        'nama' => $nama,
        'no_hp' => '0812'.random_int(10000000, 99999999),
        'email' => Str::slug($nama).uniqid().'@contoh.test',
    ]);
}

function pesananOps(string $status = 'paid', array $lain = []): Order
{
    return Order::create(array_merge([
        'id' => Str::uuid(),
        'order_number' => 'INV-OPS-'.Str::upper(Str::random(6)),
        'customer_id' => pelangganOps()->id,
        'subtotal' => 100000, 'total' => 100000, 'unique_code' => 0,
        'status' => $status,
        'paid_at' => $status === 'pending' ? null : now(),
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ], $lain));
}

function itemOps(Order $order, array $lain = []): OrderItem
{
    // product_id NOT NULL: tiap baris pesanan selalu menunjuk produknya.
    $produk = Product::firstOrCreate(
        ['nama_akun' => $lain['product_name'] ?? 'Chat Gpt Plus Sharing'],
        ['tipe_akun' => 'sharing', 'harga_perbulan' => 100000]
    );

    return OrderItem::create(array_merge([
        'id' => Str::uuid(),
        'order_id' => $order->id,
        'product_id' => $produk->id,
        'product_name' => 'Chat Gpt Plus Sharing',
        'duration_type' => 'bulan', 'duration_value' => 1,
        'price' => 100000, 'quantity' => 1, 'subtotal' => 100000,
        'delivery_status' => 'delivered',
    ], $lain));
}

it('menghitung langganan yang segera habis beserta nilainya', function () {
    itemOps(pesananOps(), ['end_date' => today()->addDays(3), 'subtotal' => 150000]);
    itemOps(pesananOps(), ['end_date' => today()->addDays(30)]);   // masih jauh
    itemOps(pesananOps(), ['end_date' => null]);                    // belum diaktifkan

    $l = Ringkas::langganan();

    expect($l['segera'])->toBe(1)->and($l['nilai_segera'])->toBe(150000.0);
});

it('memisahkan langganan habis yang BELUM pernah dikabari', function () {
    // Inilah yang benar-benar hilang: pemiliknya tidak tahu langganannya habis,
    // jadi tidak akan memperpanjang — dan tak ada layar yang menghitungnya.
    itemOps(pesananOps(), ['end_date' => today()->subDays(3), 'habis_notified_at' => now()]);
    itemOps(pesananOps(), ['end_date' => today()->subDays(5)]);
    itemOps(pesananOps(), ['end_date' => today()->subDays(9)]);

    $l = Ringkas::langganan();

    expect($l['habis'])->toBe(3)->and($l['belum_dikabari'])->toBe(2);
});

it('memisahkan antrean jasa yang dikerjakan bot dan yang manual', function () {
    // Bot hanya menangani plagiasi Turnitin; cek AI & parafrase selalu manual,
    // dan beban itu tidak terlihat di mana pun sebelum ini.
    $o = pesananOps();
    OrderUpload::create(['order_id' => $o->id, 'jenis' => 'plagiasi', 'nama_asli' => 'a.docx', 'status' => 'menunggu']);
    OrderUpload::create(['order_id' => $o->id, 'jenis' => 'ai', 'nama_asli' => 'b.docx', 'status' => 'menunggu']);
    OrderUpload::create(['order_id' => $o->id, 'jenis' => 'parafrase', 'nama_asli' => 'c.docx', 'status' => 'menunggu']);
    OrderUpload::create(['order_id' => $o->id, 'jenis' => 'ai', 'nama_asli' => 'd.docx', 'status' => 'diproses']);

    $a = Ringkas::antreanJasa();

    expect($a['bot'])->toBe(1)->and($a['manual'])->toBe(2)->and($a['dikerjakan'])->toBe(1)
        ->and($a['manual_terlama'])->not->toBeNull();
});

it('menghitung pesanan yang belum dibayar beserta yang hampir kedaluwarsa', function () {
    pesananOps('pending', ['total' => 75000, 'expired_at' => now()->addMinutes(30)]);
    pesananOps('pending', ['total' => 25000, 'expired_at' => now()->addHours(5)]);
    pesananOps('paid');

    $p = Ringkas::pesananMenunggu();

    expect($p['jumlah'])->toBe(2)
        ->and($p['nilai'])->toBe(100000.0)
        ->and($p['segera_kedaluwarsa'])->toBe(1);
});

it('menghitung task yang lewat tenggat, bukan yang sudah selesai', function () {
    $karyawan = \App\Models\User::factory()->create();
    $buat = fn (string $tenggat, string $progress) => Task::create([
        'nama' => 'Uji '.uniqid(),
        'user_id' => $karyawan->id,
        'periode_bulan' => (int) today()->format('n'),
        'periode_tahun' => (int) today()->format('Y'),
        'bobot' => 'sedang',
        'deadline_mulai' => $tenggat,
        'deadline_selesai' => $tenggat,
        'progress' => $progress,
    ]);

    $buat(today()->subDays(4)->toDateString(), 'belum');
    $buat(today()->subDay()->toDateString(), 'dikerjakan');
    $buat(today()->subDays(9)->toDateString(), 'selesai');
    $buat(today()->addDay()->toDateString(), 'belum');

    $t = Ringkas::taskTerlambat();

    expect($t['jumlah'])->toBe(2)
        ->and($t['terlama']->toDateString())->toBe(today()->subDays(4)->toDateString());
});

it('akun yang langganannya sudah habis dihitung bebas lagi', function () {
    // Kalau tidak, stok terlihat kosong terus dan peringatannya jadi kebisingan
    // yang diabaikan.
    $produk = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 20000]);

    $dipakai = DataAkun::create(['nama_akun' => 'Canva 1', 'product_id' => $produk->id, 'username_akun' => 'a', 'password_akun' => 'x', 'status' => 'active']);
    $bebasLagi = DataAkun::create(['nama_akun' => 'Canva 2', 'product_id' => $produk->id, 'username_akun' => 'b', 'password_akun' => 'x', 'status' => 'active']);
    DataAkun::create(['nama_akun' => 'Canva 3', 'product_id' => $produk->id, 'username_akun' => 'c', 'password_akun' => 'x', 'status' => 'non-active']);

    itemOps(pesananOps(), ['data_akun_id' => $dipakai->id, 'end_date' => today()->addMonth()]);
    itemOps(pesananOps(), ['data_akun_id' => $bebasLagi->id, 'end_date' => today()->subDay()]);

    $stok = collect(Ringkas::stokAkun())->firstWhere('produk', 'Canva Premium');

    expect($stok)->not->toBeNull()->and($stok['sisa'])->toBe(1);
});

it('produk terlaris hanya menghitung pesanan yang dibayar', function () {
    $mulai = now()->startOfDay()->subDays(3);
    $akhir = now()->addDay()->startOfDay();

    itemOps(pesananOps('paid'), ['product_name' => 'Chat Gpt Plus Private', 'subtotal' => 500000]);
    itemOps(pesananOps('completed'), ['product_name' => 'Chat Gpt Plus Private', 'subtotal' => 300000]);
    itemOps(pesananOps('paid'), ['product_name' => 'Canva Premium', 'subtotal' => 200000]);
    itemOps(pesananOps('cancelled'), ['product_name' => 'Canva Premium', 'subtotal' => 900000]);

    $laris = Ringkas::produkTerlaris($mulai, $akhir);

    expect($laris[0]['produk'])->toBe('Chat Gpt Plus Private')
        ->and($laris[0]['jumlah'])->toBe(2)
        ->and($laris[0]['nilai'])->toBe(800000.0)
        ->and($laris[1]['produk'])->toBe('Canva Premium')
        ->and($laris[1]['nilai'])->toBe(200000.0);
});

it('grafik harian menampilkan hari sepi sebagai nol, bukan melompatinya', function () {
    // Garis yang melompati hari sepi membuat tren terbaca lebih ramai daripada
    // kenyataannya.
    $mulai = now()->startOfDay()->subDays(3);
    $akhir = now()->addDay()->startOfDay();

    pesananOps('paid', ['total' => 250000, 'paid_at' => now()->subDays(3)]);
    pesananOps('paid', ['total' => 100000, 'paid_at' => now()]);

    $g = Ringkas::pemasukanHarian($mulai, $akhir);

    expect($g['nilai'])->toHaveCount(4)
        ->and($g['nilai'][0])->toBe(250000.0)
        ->and($g['nilai'][1])->toBe(0.0)
        ->and($g['nilai'][2])->toBe(0.0)
        ->and($g['nilai'][3])->toBe(100000.0)
        ->and($g['tanggal'])->toHaveCount(4);
});

it('task telat hanya menghitung tenggat di periode yang dipilih', function () {
    // Task dari periode lalu yang tak pernah ditutup dulu ikut menumpuk di
    // kartu dasbor. Kini hanya yang tenggatnya jatuh di periode 21–20 itu.
    \Illuminate\Support\Carbon::setTestNow('2026-10-05 10:00:00');

    $karyawan = \App\Models\User::factory()->create(['status' => 'active']);
    $buat = fn (string $tenggat, string $progress = 'belum') => Task::create([
        'nama' => 'Task '.Str::random(4),
        'user_id' => $karyawan->id,
        'periode_bulan' => 10,
        'periode_tahun' => 2026,
        'bobot' => 'sedang',
        'deadline_mulai' => $tenggat,
        'deadline_selesai' => $tenggat,
        'progress' => $progress,
    ]);

    $buat('2026-08-04');                 // periode lalu-lalu — diabaikan
    $buat('2026-09-18');                 // periode lalu (21 Agt–20 Sep) — diabaikan
    $buat('2026-09-22');                 // periode ini, telat
    $buat('2026-10-01');                 // periode ini, telat
    $buat('2026-10-02', 'selesai');      // periode ini, tapi sudah selesai
    $buat('2026-10-10');                 // periode ini, belum lewat tenggat

    $mulai = \App\Support\PeriodeGaji::mulai(10, 2026);
    $akhirEks = \App\Support\PeriodeGaji::akhir(10, 2026)->copy()->addDay()->startOfDay();

    $t = Ringkas::taskTerlambat($mulai, $akhirEks);

    expect($mulai->toDateString())->toBe('2026-09-21')
        ->and($t['jumlah'])->toBe(2)
        ->and($t['terlama']->toDateString())->toBe('2026-09-22');

    // Tanpa periode: perilaku lama (sepanjang masa) tetap tersedia.
    expect(Ringkas::taskTerlambat()['jumlah'])->toBe(4);

    \Illuminate\Support\Carbon::setTestNow();
});
