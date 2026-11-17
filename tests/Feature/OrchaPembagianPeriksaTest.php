<?php

/**
 * Aturan pemeriksaan pembagian, sisi lemon.
 *
 * Kasus di berkas ini SENGAJA sama dengan PembagianKamarKursiTest di Orcha.
 * Aturannya hidup di dua tempat — yang di Orcha memeriksa data TERSIMPAN dan
 * berwenang, yang di sini memeriksa yang BELUM tersimpan supaya peringatan
 * tidak terlambat satu langkah. Yang mengubah aturan di satu sisi akan melihat
 * sisi lain merah, dan itu satu-satunya hal yang menjaga keduanya tidak
 * menyimpang.
 *
 * Lihat App\Support\PembagianPeriksa untuk alasan panjangnya.
 */

use App\Support\PembagianPeriksa;

function barisPeriksa(string $nama, array $ubah = []): array
{
    return array_merge([
        'nama' => $nama,
        'jenis_kelamin' => null,
        'bus' => null,
        'kursi' => null,
        'kamar' => null,
    ], $ubah);
}

test('kamar yang dicampur laki-laki dan perempuan ditandai, bukan dilarang', function () {
    $peringatan = PembagianPeriksa::periksa([
        barisPeriksa('Budi', ['jenis_kelamin' => 'Laki-laki', 'kamar' => '201']),
        barisPeriksa('Siti', ['jenis_kelamin' => 'Perempuan', 'kamar' => '201']),
    ]);

    expect(collect($peringatan)->pluck('jenis')->all())->toContain('kamar_campur');
    // Kalimatnya menyebut kemungkinan yang sah: pasangan suami-istri memang
    // satu kamar, dan peringatan yang terbaca sebagai larangan akan diabaikan
    // seluruhnya setelah dua kali salah.
    expect($peringatan[0]['pesan'])->toContain('pasangan');
});

test('kamar berisi peserta tanpa jenis kelamin ditandai', function () {
    $peringatan = PembagianPeriksa::periksa([
        barisPeriksa('Sri', ['kamar' => '201']),
    ]);

    expect(collect($peringatan)->pluck('jenis')->all())->toContain('kamar_tanpa_kelamin');
});

test('kamar berisi satu jenis kelamin saja tidak memunculkan apa pun', function () {
    $peringatan = PembagianPeriksa::periksa([
        barisPeriksa('Budi', ['jenis_kelamin' => 'Laki-laki', 'kamar' => '201']),
        barisPeriksa('Agus', ['jenis_kelamin' => 'Laki-laki', 'kamar' => '201']),
    ]);

    expect($peringatan)->toBe([]);
});

test('dua orang di satu kursi selalu jadi peringatan', function () {
    $peringatan = PembagianPeriksa::periksa([
        barisPeriksa('Budi', ['bus' => 'Bus 1', 'kursi' => '5']),
        barisPeriksa('Agus', ['bus' => 'Bus 1', 'kursi' => '5']),
    ]);

    expect(collect($peringatan)->pluck('jenis')->all())->toContain('kursi_kembar');
    expect($peringatan[0]['pesan'])->toContain('Budi, Agus');
});

test('kursi bernomor sama di bus berbeda bukan bentrok', function () {
    $peringatan = PembagianPeriksa::periksa([
        barisPeriksa('Budi', ['bus' => 'Bus 1', 'kursi' => '5']),
        barisPeriksa('Agus', ['bus' => 'Bus 2', 'kursi' => '5']),
    ]);

    expect($peringatan)->toBe([]);
});

test('yang belum ditempatkan tidak diperiksa sama sekali', function () {
    /*
     | Kosong berarti "belum dibagi", bukan "salah". Pembagian baru dikerjakan
     | beberapa hari sebelum berangkat, jauh setelah namanya masuk —
     | memperlakukan yang kosong sebagai kesalahan membuat layar penuh
     | peringatan merah selama berminggu-minggu, dan peringatan yang selalu ada
     | akhirnya tidak dibaca.
     */
    $peringatan = PembagianPeriksa::periksa([
        barisPeriksa('Budi'),
        barisPeriksa('Siti'),
        barisPeriksa('Sri'),
    ]);

    expect($peringatan)->toBe([]);
});

test('kelas lemon dan kelas Orcha memulangkan jenis peringatan yang sama', function () {
    /*
     | Penjaga penyimpangan. Daftar jenis yang dikenali kedua sisi harus sama;
     | jenis baru yang hanya ada di satu sisi berarti peringatan yang muncul
     | sebelum disimpan lalu hilang sesudahnya — atau sebaliknya.
     */
    $jenisLemon = collect(PembagianPeriksa::periksa([
        barisPeriksa('Budi', ['jenis_kelamin' => 'Laki-laki', 'kamar' => '201', 'bus' => 'Bus 1', 'kursi' => '1']),
        barisPeriksa('Siti', ['jenis_kelamin' => 'Perempuan', 'kamar' => '201', 'bus' => 'Bus 1', 'kursi' => '1']),
        barisPeriksa('Sri', ['kamar' => '202']),
    ]))->pluck('jenis')->unique()->sort()->values()->all();

    expect($jenisLemon)->toBe(['kamar_campur', 'kamar_tanpa_kelamin', 'kursi_kembar']);
});
