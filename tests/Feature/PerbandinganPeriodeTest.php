<?php

use App\Models\CashFlow;
use App\Support\PerbandinganPeriode;
use App\Support\PeriodeGaji;
use Illuminate\Support\Str;

/**
 * Pembanding periode untuk kartu Saldo Bersih, Total Pemasukan, dan Total
 * Pengeluaran di dasbor.
 *
 * Yang dijaga di sini adalah hal-hal yang membuat pembandingnya BOHONG tanpa
 * terlihat salah: rentang periode yang meleset sehari, dan persentase yang
 * dihitung dari pembanding nol atau minus.
 */
function arusKas(string $jenis, float $nominal, string $tanggal): CashFlow
{
    // sourceable_* NOT NULL: ledger ini polimorfik, tiap baris selalu berasal
    // dari satu catatan (pesanan, pengeluaran, gaji, ...).
    return CashFlow::create([
        'id' => Str::uuid(),
        'sourceable_type' => \App\Models\Order::class,
        'sourceable_id' => (string) Str::uuid(),
        'type' => $jenis,
        'amount' => $nominal,
        'description' => 'uji banding',
        'transaction_date' => $tanggal,
        'category' => 'lainnya',
    ]);
}

beforeEach(function () {
    CashFlow::query()->delete();

    // Acuan dikunci: periode berjalan 21 Agu – 20 Sep 2026, sebelumnya
    // 21 Jul – 20 Agu 2026. Tanpa acuan tetap, tesnya berubah arti tiap hari.
    $this->acuan = \Carbon\Carbon::create(2026, 9, 16, 10);
    $this->ini = PeriodeGaji::mulai(9, 2026);          // 21 Agu 2026
    $this->lalu = PeriodeGaji::mulai(8, 2026);         // 21 Jul 2026
});

it('membandingkan periode berjalan dengan periode sebelumnya', function () {
    arusKas('income', 1_000_000, $this->lalu->copy()->addDays(3)->toDateString());
    arusKas('expense', 400_000, $this->lalu->copy()->addDays(4)->toDateString());

    arusKas('income', 1_500_000, $this->ini->copy()->addDays(2)->toDateString());
    arusKas('expense', 300_000, $this->ini->copy()->addDays(5)->toDateString());

    $b = PerbandinganPeriode::ringkas($this->acuan);

    expect($b['pemasukan']['sekarang'])->toBe(1_500_000.0)
        ->and($b['pemasukan']['sebelumnya'])->toBe(1_000_000.0)
        ->and($b['pemasukan']['persen'])->toBe(50.0)
        ->and($b['pemasukan']['arah'])->toBe(1)
        ->and($b['pengeluaran']['persen'])->toBe(-25.0)
        ->and($b['pengeluaran']['arah'])->toBe(-1)
        // Saldo: (1,5jt − 300rb) vs (1jt − 400rb) = 1,2jt vs 600rb.
        ->and($b['saldo']['sekarang'])->toBe(1_200_000.0)
        ->and($b['saldo']['sebelumnya'])->toBe(600_000.0)
        ->and($b['saldo']['persen'])->toBe(100.0);
});

it('hari terakhir periode lalu tidak bocor ke periode berjalan', function () {
    // Batas 20/21 adalah tempat kesalahan sehari paling mudah lolos: angkanya
    // tetap "masuk akal", hanya saja dihitung di periode yang salah.
    $akhirLalu = PeriodeGaji::akhir(8, 2026);          // 20 Agu 2026

    arusKas('income', 700_000, $akhirLalu->toDateString());
    arusKas('income', 200_000, $this->ini->toDateString());

    $b = PerbandinganPeriode::ringkas($this->acuan);

    expect($b['pemasukan']['sebelumnya'])->toBe(700_000.0)
        ->and($b['pemasukan']['sekarang'])->toBe(200_000.0);
});

it('persentase kosong saat periode lalu nol — bukan naik 100%', function () {
    arusKas('income', 500_000, $this->ini->copy()->addDay()->toDateString());

    $b = PerbandinganPeriode::ringkas($this->acuan);

    expect($b['pemasukan']['persen'])->toBeNull()
        ->and($b['pemasukan']['arah'])->toBe(1);
});

it('saldo yang membaik dari MINUS tidak dilaporkan sebagai penurunan', function () {
    // Persen dari pembanding minus tandanya terbalik: −100rb → +50rb akan
    // terhitung "−150%", padahal keadaannya justru membaik.
    arusKas('expense', 100_000, $this->lalu->copy()->addDay()->toDateString());
    arusKas('income', 50_000, $this->ini->copy()->addDay()->toDateString());

    $b = PerbandinganPeriode::ringkas($this->acuan);

    expect($b['saldo']['sebelumnya'])->toBe(-100_000.0)
        ->and($b['saldo']['sekarang'])->toBe(50_000.0)
        ->and($b['saldo']['persen'])->toBeNull()
        ->and($b['saldo']['arah'])->toBe(1);
});

it('naik turunnya pengeluaran diberi warna terbalik di tampilan', function () {
    // Dijaga di SUMBER komponennya: kalau pengeluaran yang naik ikut hijau,
    // satu-satunya kartu yang memberi peringatan malah terbaca sebagai
    // pencapaian.
    $sumber = file_get_contents(resource_path('views/components/banding-periode.blade.php'));

    // Saat arahnya NAIK: pemasukan hijau (is-baik), pengeluaran merah (is-buruk).
    expect($sumber)->toContain("\$rupa = \$biaya ? 'is-buruk' : 'is-baik';")
        ->and(file_get_contents(resource_path('views/components/banding-gaya.blade.php')))
        ->toContain('.bnd-pil.is-buruk { background: #fee2e2; color: #b91c1c; }')
        ->and(file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php')))
        ->toContain(":data=\"\$bandingPeriode['pengeluaran']\"");
});
