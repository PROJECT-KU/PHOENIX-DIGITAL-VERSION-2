<?php

use App\Http\Middleware\CegahAlihPermintaanLivewire;
use Illuminate\Http\Request;

function lewatMiddleware(Request $request, $respons)
{
    return (new CegahAlihPermintaanLivewire)->handle($request, fn () => $respons);
}

it('pengalihan pada permintaan latar Livewire diubah jadi 419', function () {
    $req = Request::create('/livewire/update', 'POST');
    $req->headers->set('X-Livewire', 'true');

    $hasil = lewatMiddleware($req, redirect('/login'));

    // 419 dikenali Livewire sebagai "halaman kedaluwarsa" lalu memuat ulang
    // halaman yang sedang dibuka — bukan melompat ke /livewire/update.
    expect($hasil->getStatusCode())->toBe(419)
        ->and($hasil->isRedirection())->toBeFalse();
});

it('pengalihan pada kunjungan halaman biasa TIDAK diganggu', function () {
    $req = Request::create('/admin/dashboard', 'GET');

    $hasil = lewatMiddleware($req, redirect('/login'));

    // Tanpa header X-Livewire, ini kunjungan biasa: pengalihan harus tetap jalan.
    expect($hasil->isRedirection())->toBeTrue()
        ->and($hasil->getStatusCode())->toBe(302);
});

it('respons sukses Livewire diteruskan apa adanya', function () {
    $req = Request::create('/livewire/update', 'POST');
    $req->headers->set('X-Livewire', 'true');

    $hasil = lewatMiddleware($req, response('{"ok":true}', 200));

    expect($hasil->getStatusCode())->toBe(200)
        ->and($hasil->getContent())->toBe('{"ok":true}');
});

it('galat pada permintaan Livewire tidak ikut diubah', function () {
    $req = Request::create('/livewire/update', 'POST');
    $req->headers->set('X-Livewire', 'true');

    $hasil = lewatMiddleware($req, response('galat', 500));

    // Hanya 3xx yang disasar; galat lain harus tetap terlihat apa adanya.
    expect($hasil->getStatusCode())->toBe(500);
});

it('middleware terpasang di grup web', function () {
    $isi = file_get_contents(base_path('bootstrap/app.php'));

    expect($isi)->toContain('CegahAlihPermintaanLivewire::class');
});
