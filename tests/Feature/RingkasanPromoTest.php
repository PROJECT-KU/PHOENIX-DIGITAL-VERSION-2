<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Promo;
use App\Support\RingkasanPromo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ringkasan "promo yang terpakai" di dasbor.
 *
 * Angkanya dipakai untuk menilai promo mana yang layak diteruskan, jadi yang
 * dijaga di sini adalah hal-hal yang membuatnya BOHONG tanpa kelihatan salah:
 * promo pada pesanan batal, promo di luar periode, dan kode rujukan yang
 * memang tidak tercatat di tabel pivot promo.
 */
function pelangganPromo(string $nama): Customer
{
    return Customer::create([
        'nama' => $nama,
        'no_hp' => '0812'.random_int(10000000, 99999999),
        'email' => Str::slug($nama).uniqid().'@contoh.test',
    ]);
}

function pesananPromo(string $status, ?Carbon\Carbon $dibayar = null, array $lain = []): Order
{
    return Order::create(array_merge([
        'id' => Str::uuid(),
        'order_number' => 'INV-PROMO-'.Str::upper(Str::random(6)),
        'customer_id' => pelangganPromo('Pembeli Promo')->id,
        'subtotal' => 100000, 'total' => 100000, 'unique_code' => 0,
        'status' => $status,
        'paid_at' => $dibayar,
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ], $lain));
}

function tempelPromo(Order $order, Promo $promo, int $diskon): void
{
    DB::table('order_promo')->insert([
        'id' => (string) Str::uuid(),
        'order_id' => $order->id,
        'promo_id' => $promo->id,
        'kode_promo' => $promo->kode_promo,
        'tipe_diskon' => 'nominal',
        'nilai_diskon' => $diskon,
        'jumlah_diskon' => $diskon,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function promoJenis(string $tipe): Promo
{
    return Promo::create([
        'nama_promo' => 'Uji '.$tipe,
        'kode_promo' => Str::upper(Str::random(6)),
        'tipe_promo' => $tipe,
        'tipe_diskon' => 'nominal',
        'diskon_member_nominal' => 5000,
        'diskon_non_member_nominal' => 5000,
        'mulai_promo' => now()->subMonth(),
        'selesai_promo' => now()->addMonth(),
        'is_active' => true,
    ]);
}

beforeEach(function () {
    $this->mulai = now()->startOfDay()->subDays(5);
    $this->akhir = now()->addDay()->startOfDay();
});

it('menghitung pemakaian tiap jenis promo beserta rupiah yang dilepas', function () {
    tempelPromo(pesananPromo('paid', now()), promoJenis('flash_sale'), 5000);
    tempelPromo(pesananPromo('completed', now()), promoJenis('kode_promo'), 7000);
    tempelPromo(pesananPromo('processing', now()), promoJenis('kode_promo'), 3000);

    $hasil = RingkasanPromo::periode($this->mulai, $this->akhir);

    expect($hasil['flash_sale'])->toBe(['jumlah' => 1, 'nilai' => 5000.0])
        ->and($hasil['kode_promo'])->toBe(['jumlah' => 2, 'nilai' => 10000.0])
        ->and($hasil['total_jumlah'])->toBe(3)
        ->and($hasil['total_nilai'])->toBe(15000.0);
});

it('promo pada pesanan batal atau belum dibayar tidak ikut dihitung', function () {
    // Diskonnya tidak pernah benar-benar dilepas; menghitungnya membuat angka
    // "rupiah yang dilepas" tidak akan pernah cocok dengan pendapatan.
    tempelPromo(pesananPromo('cancelled', null), promoJenis('flash_sale'), 9000);
    tempelPromo(pesananPromo('pending', null), promoJenis('kode_promo'), 9000);

    $hasil = RingkasanPromo::periode($this->mulai, $this->akhir);

    expect($hasil['total_jumlah'])->toBe(0)->and($hasil['total_nilai'])->toBe(0.0);
});

it('promo di luar periode tidak ikut dihitung', function () {
    tempelPromo(pesananPromo('paid', now()->subDays(40)), promoJenis('flash_sale'), 4000);

    $hasil = RingkasanPromo::periode($this->mulai, $this->akhir);

    expect($hasil['flash_sale']['jumlah'])->toBe(0);
});

it('kode rujukan dihitung dari pesanannya sendiri, bukan dari tabel promo', function () {
    // Rujukan tidak pernah punya baris di order_promo; kalau ikut dicari di
    // sana, angkanya selamanya nol tanpa ada yang menyadarinya.
    pesananPromo('paid', now(), ['referral_code' => 'RUJUK-A1', 'referral_discount' => 7500]);
    pesananPromo('completed', now(), ['referral_code' => 'RUJUK-B2', 'referral_discount' => 2500]);
    pesananPromo('cancelled', null, ['referral_code' => 'RUJUK-C3', 'referral_discount' => 5000]);

    $hasil = RingkasanPromo::periode($this->mulai, $this->akhir);

    expect($hasil['referral'])->toBe(['jumlah' => 2, 'nilai' => 10000.0]);
});

it('tanpa data apa pun, seluruh angkanya nol dan bukan null', function () {
    $hasil = RingkasanPromo::periode($this->mulai, $this->akhir);

    foreach (['flash_sale', 'kode_promo', 'auto_promo', 'referral'] as $jenis) {
        expect($hasil[$jenis])->toBe(['jumlah' => 0, 'nilai' => 0.0]);
    }
});
