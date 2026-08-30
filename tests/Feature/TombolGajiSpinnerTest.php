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

it('tombol export pdf berganti spinner tanpa teks', function () {
    $blade = bladeFilterGaji();

    expect($blade)->toContain('<span wire:loading wire:target="downloadPdf" class="spinner-border')
        // Tanpa keterangan tambahan: yang diminta hanya spinner.
        ->and($blade)->not->toContain('Menyiapkan');
});

it('tombol generate gaji dibiarkan seperti semula', function () {
    $blade = bladeFilterGaji();

    expect($blade)->not->toContain('<span wire:loading wire:target="generateGaji"');
});

it('tidak ada komentar Blade yang rusak di seluruh tampilan', function () {
    // "{-- ... --}" (kurung tunggal) BUKAN komentar Blade — isinya ikut
    // tercetak ke layar sebagai teks biasa. Pernah terjadi dan terlihat
    // pengguna, jadi dijaga menyeluruh.
    $bocor = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('views'))
    );

    foreach ($iterator as $berkas) {
        if (! str_ends_with($berkas->getFilename(), '.blade.php')) {
            continue;
        }

        if (preg_match('/^\s*\{--[^{]/m', file_get_contents($berkas->getPathname()))) {
            $bocor[] = $berkas->getFilename();
        }
    }

    expect($bocor)->toBe([]);
});

it('kedua blade tetap bisa dikompilasi', function () {
    // Merender halamannya butuh pengguna berizin — di luar cakupan perubahan ini.
    // Yang perlu dijaga: markup barunya tidak merusak blade.
    foreach ([bladeDaftarGaji(), bladeFilterGaji()] as $blade) {
        expect(fn () => Illuminate\Support\Facades\Blade::compileString($blade))
            ->not->toThrow(Exception::class);
    }
});
