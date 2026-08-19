<?php

function bladeSlip(): string
{
    return file_get_contents(
        resource_path('views/livewire/pages/admin/gaji-karyawans/slip-gaji-pdf.blade.php')
    );
}

it('slip memuat baris gaji bersih diterima', function () {
    expect(bladeSlip())->toContain('GAJI BERSIH DITERIMA');
});

it('blok gaji bersih memakai warna solid, bukan gradasi', function () {
    $blade = bladeSlip();

    // dompdf tidak menggambar linear-gradient. Dengan color:#fff di atasnya,
    // latarnya hilang dan tulisannya jadi putih di atas kertas putih —
    // bloknya memakan ruang tapi tidak terlihat sama sekali.
    // Diambil dari ".takehome {" sampai aturan berikutnya. Regex berbasis "}"
    // tidak bisa dipakai: tanda itu juga milik sintaks Blade {{ ... }}.
    $mulai = strpos($blade, '.takehome {');
    $aturan = substr($blade, $mulai, strpos($blade, '.takehome table', $mulai) - $mulai);

    expect($aturan)->not->toContain('linear-gradient')
        ->and($aturan)->toContain('background: {{ $accent }}');
});

it('tidak ada gradasi yang dipakai sebagai gaya di templat PDF ini', function () {
    // Kata "linear-gradient" boleh muncul di komentar penjelas, tetapi tidak
    // boleh lagi dipakai sebagai nilai properti background.
    expect(bladeSlip())->not->toMatch('/background:\s*linear-gradient/');
});
