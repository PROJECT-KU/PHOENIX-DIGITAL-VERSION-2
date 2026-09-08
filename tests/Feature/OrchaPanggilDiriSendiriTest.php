<?php

use App\Services\OrchaClient;

/**
 * ORCHA_API_URL yang menunjuk lemon sendiri membuat lemon memanggil dirinya
 * sendiri. Server bawaan PHP melayani satu permintaan pada satu waktu, jadi
 * permintaan dalam tak pernah dilayani dan setiap halaman admin menggantung
 * sampai batas waktu — gejalanya tak pernah menyebut port, hanya
 * "Maximum execution time exceeded" di dalam Guzzle.
 */
it('host & port yang sama dikenali sebagai memanggil diri sendiri', function () {
    expect(OrchaClient::alamatSama('http://127.0.0.1:8001/api/v1', '127.0.0.1', 8001))->toBeTrue();
});

it('port berbeda di host yang sama adalah susunan yang benar', function () {
    // Dua server terpisah di satu komputer — justru yang diharapkan.
    expect(OrchaClient::alamatSama('http://127.0.0.1:8000/api/v1', '127.0.0.1', 8001))->toBeFalse();
});

it('localhost dan 127.0.0.1 dianggap host yang sama', function () {
    expect(OrchaClient::alamatSama('http://127.0.0.1:8001/api/v1', 'localhost', 8001))->toBeTrue()
        ->and(OrchaClient::alamatSama('http://localhost:8001/api/v1', '127.0.0.1', 8001))->toBeTrue();
});

it('host berbeda tidak pernah dianggap diri sendiri', function () {
    expect(OrchaClient::alamatSama('https://orcha.example.com:8001/api/v1', 'phoenixdigitalwarehouse.com', 8001))->toBeFalse();
});

it('URL tanpa port tidak pernah dianggap diri sendiri', function () {
    // Di produksi keduanya bisa satu domain dengan jalur berbeda; menolaknya
    // di sana akan mematikan sambungan yang sah.
    expect(OrchaClient::alamatSama('https://phoenixdigitalwarehouse.com/api/v1', 'phoenixdigitalwarehouse.com', 443))->toBeFalse();
});

it('tanpa host atau port yang diketahui, penjaga ini diam', function () {
    expect(OrchaClient::alamatSama('http://127.0.0.1:8001/api/v1', null, null))->toBeFalse()
        ->and(OrchaClient::alamatSama('', '127.0.0.1', 8001))->toBeFalse();
});

it('di luar konteks HTTP sambungan tetap dianggap siap', function () {
    // Artisan, antrean, dan uji memakai permintaan bikinan yang mewarisi
    // APP_URL — menebak di sana pernah menuduh 83 uji Orcha yang sebenarnya sah.
    config(['orcha.url' => 'http://127.0.0.1:8000/api/v1', 'orcha.kunci' => 'kunci-uji']);

    expect(app(OrchaClient::class)->siap())->toBeTrue();
});

it('sambungan tanpa kunci tetap dianggap belum siap', function () {
    config(['orcha.url' => 'http://127.0.0.1:8000/api/v1', 'orcha.kunci' => '']);

    expect(app(OrchaClient::class)->siap())->toBeFalse();
});
