<?php


function bladeDaftarGaji(): string
{
    return file_get_contents(
        resource_path('views/livewire/pages/admin/gaji-karyawans/gaji-karyawans-list.blade.php')
    );
}

function bladeFilterGaji(): string
{
    return file_get_contents(
        resource_path('views/livewire/pages/admin/gaji-karyawans/partials/filter.blade.php')
    );
}

it('tombol slip menyasar barisnya sendiri, bukan seluruh tabel', function () {
    $blade = bladeDaftarGaji();

    // Tanpa argumen, satu klik menonaktifkan tombol slip di semua baris.
    expect($blade)->toContain('wire:target="downloadSlip(\'{{ $item->id }}\')"')
        ->and($blade)->not->toContain('wire:target="downloadSlip"');
});

it('tombol slip menampilkan spinner di dalam tombolnya', function () {
    $blade = bladeDaftarGaji();

    expect($blade)->toContain('spinner-border spinner-border-sm');
});

it('tombol export pdf berganti spinner, bukan menjadi kosong', function () {
    $blade = bladeFilterGaji();

    // Sebelumnya isinya hanya disembunyikan tanpa pengganti, sehingga tombolnya
    // tampak kosong selama proses berjalan.
    expect($blade)->toContain('<span wire:loading wire:target="downloadPdf"')
        ->and($blade)->toContain('Menyiapkan');
});

it('tombol generate gaji juga berganti spinner', function () {
    $blade = bladeFilterGaji();

    expect($blade)->toContain('<span wire:loading wire:target="generateGaji"')
        ->and($blade)->toContain('Membuat');
});

it('kedua blade tetap bisa dikompilasi', function () {
    // Merender halamannya butuh pengguna berizin — di luar cakupan perubahan ini.
    // Yang perlu dijaga: markup barunya tidak merusak blade.
    foreach ([bladeDaftarGaji(), bladeFilterGaji()] as $blade) {
        expect(fn () => Illuminate\Support\Facades\Blade::compileString($blade))
            ->not->toThrow(Exception::class);
    }
});
