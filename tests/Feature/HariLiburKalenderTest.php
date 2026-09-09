<?php

use App\Support\HariLibur;
use Illuminate\Support\Carbon;

/**
 * Penanda hari libur & peringatan nasional di kalender.
 *
 * Yang dijaga paling keras di sini bukan tampilannya, melainkan BATAS
 * PENGETAHUANNYA: sistem hanya boleh menandai tanggal yang benar-benar pasti.
 * Libur yang tanggalnya berpindah ditetapkan pemerintah lewat SKB, dan
 * menghitungnya sendiri lewat konversi kalender menghasilkan tanggal yang
 * meyakinkan tapi bisa meleset sehari. Orang mengatur cuti dan tenggat dari
 * kalender ini; tanggal libur karangan bukan kesalahan kecil.
 */
it('menandai libur nasional yang tanggalnya tetap', function () {
    $h = HariLibur::untukRentang(Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($h)->toHaveKey('2026-08-17')
        ->and($h['2026-08-17']['nama'])->toBe('HUT Kemerdekaan RI')
        ->and($h['2026-08-17']['libur'])->toBeTrue();
});

it('membedakan hari peringatan dari hari libur', function () {
    // Hari Pelanggan Nasional tetap HARI KERJA. Menandainya merah akan membuat
    // orang mengira tokonya tutup.
    $h = HariLibur::untukRentang(Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30'));

    expect($h['2026-09-04']['nama'])->toBe('Hari Pelanggan Nasional')
        ->and($h['2026-09-04']['libur'])->toBeFalse();
});

it('tanggal tetap berlaku untuk tahun mana pun', function () {
    foreach ([2026, 2027, 2030] as $tahun) {
        $h = HariLibur::untukRentang(Carbon::parse("$tahun-01-01"), Carbon::parse("$tahun-01-05"));

        expect($h["$tahun-01-01"]['nama'])->toBe('Tahun Baru Masehi');
    }
});

it('TIDAK menebak libur yang tanggalnya berpindah', function () {
    // Sepanjang 2026 tidak boleh ada penanda selain yang bertanggal tetap.
    $h = HariLibur::untukRentang(Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));

    $kunciTetap = array_keys(HariLibur::TETAP);
    foreach (array_keys($h) as $tanggal) {
        expect(in_array(substr($tanggal, 5), $kunciTetap, true))->toBeTrue(
            "tanggal $tanggal muncul padahal bukan tanggal tetap"
        );
    }

    expect(HariLibur::bergerakTerisi(2026))->toBeFalse();
});

it('rentang yang tidak memuat penanda apa pun mengembalikan kosong', function () {
    // Awal Maret tidak punya tanggal tetap; hasilnya harus benar-benar kosong,
    // bukan sederet null yang harus diperiksa lagi oleh tampilan.
    expect(HariLibur::untukRentang(Carbon::parse('2026-03-03'), Carbon::parse('2026-03-08')))
        ->toBe([]);
});

it('rentang yang melewati pergantian tahun tetap benar', function () {
    $h = HariLibur::untukRentang(Carbon::parse('2026-12-28'), Carbon::parse('2027-01-03'));

    expect($h)->toHaveKey('2027-01-01')
        ->and($h)->not->toHaveKey('2026-01-01');
});
