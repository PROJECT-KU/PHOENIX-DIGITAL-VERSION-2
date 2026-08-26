<?php

use App\Services\OrchaClient;

/*
 | Dasbor Orcha di lemon sempat kosong dengan pesan "IP server ini belum
 | diizinkan oleh Orcha". Kuncinya benar — kalau salah, Orcha menjawab 401,
 | bukan 403. Yang tidak cocok alamat pemanggilnya.
 |
 | Server ini punya dua alamat keluar. IPv4-nya tetap (46.202.186.249) dan
 | sudah terdaftar di Orcha; IPv6-nya BERGANTI sendiri — tercatat blok
 | 2a02:4780:6:1234:: pada 24 Agu 2026, lalu 2a02:4780:6:1509:: pada 26 Agu
 | 2026. curl memilih IPv6 lebih dulu, jadi yang sampai ke Orcha justru alamat
 | yang tidak terdaftar, dan menambahkan alamat baru itu hanya menunda
 | kejadian yang sama sampai ia berganti lagi.
 |
 | Diperiksa lewat refleksi pada PendingRequest, bukan lewat Http::assertSent.
 | Alasannya bukan selera: assertSent di versi ini hanya menyerahkan Request
 | dan Response ke penutupnya — opsi permintaan TIDAK ikut. Pernyataan yang
 | menyebut $options di situ akan lulus tanpa memeriksa apa pun, yang justru
 | lebih buruk daripada tidak punya tes sama sekali. Yang dibaca di sini objek
 | yang benar-benar diserahkan ke curl.
 */

function opsiPermintaanOrcha(): array
{
    $klien = app(OrchaClient::class);

    $metode = new ReflectionMethod($klien, 'permintaan');
    $metode->setAccessible(true);
    $pending = $metode->invoke($klien);

    $properti = new ReflectionProperty($pending, 'options');
    $properti->setAccessible(true);

    return $properti->getValue($pending);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.uji');
    config()->set('orcha.kunci', 'kunci-uji');
});

it('memaksa panggilan Orcha lewat IPv4', function () {
    config()->set('orcha.paksa_ipv4', true);

    expect(opsiPermintaanOrcha())
        ->toHaveKey('force_ip_resolve')
        ->and(opsiPermintaanOrcha()['force_ip_resolve'])->toBe('v4');
});

it('tidak memaksa IPv4 saat sakelarnya dimatikan', function () {
    config()->set('orcha.paksa_ipv4', false);

    expect(opsiPermintaanOrcha())->not->toHaveKey('force_ip_resolve');
});

it('sakelarnya menyala secara bawaan', function () {
    // Bawaan yang salah membuat penjaga di atas lulus sementara produksi tetap
    // memanggil lewat IPv6 — setelan itulah yang menentukan, bukan tesnya.
    expect(filter_var(env('ORCHA_API_PAKSA_IPV4', true), FILTER_VALIDATE_BOOLEAN))->toBeTrue();
});
