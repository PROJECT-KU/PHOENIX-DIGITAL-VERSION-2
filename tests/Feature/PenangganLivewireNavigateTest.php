<?php

/**
 * Penangan Livewire tidak boleh bergantung HANYA pada `livewire:initialized`.
 *
 * Peristiwa itu dikirim SEKALI, di dalam start2() Livewire, dan tidak pernah
 * diulang. Seluruh menu lemon memakai wire:navigate, yang menukar body tanpa
 * muat ulang — dan Livewire menjalankan ulang tiap <script> di body baru.
 * Skrip yang baru mendaftar saat itu akan menunggu peristiwa yang sudah lewat.
 *
 * Akibatnya seluruh pemberitahuan mati tanpa satu pun galat, dan F5
 * memperbaikinya sendiri — jadi pengujian manual selalu melihatnya bekerja.
 * Kena di layar Orcha 7 Nov 2026: Simpan di Vendor, Master Harga, dan RAB
 * tidak memunculkan toast dan tidak berpindah, padahal datanya tersimpan.
 */
function berkasTampilanDenganLivewireOn(): array
{
    $hasil = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('views'), FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $berkas) {
        if (! str_ends_with($berkas->getFilename(), '.blade.php')) {
            continue;
        }

        $isi = file_get_contents($berkas->getPathname());

        if (str_contains($isi, 'Livewire.on(')) {
            $hasil[str_replace(resource_path('views').'/', '', $berkas->getPathname())] = $isi;
        }
    }

    return $hasil;
}

it('tidak ada penangan Livewire yang hanya menunggu livewire:initialized', function () {
    $pelanggar = [];

    foreach (berkasTampilanDenganLivewireOn() as $jalur => $isi) {
        $pakaiInit = str_contains($isi, "livewire:initialized") || str_contains($isi, "livewire:init'")
            || str_contains($isi, 'livewire:init"');

        if (! $pakaiInit) {
            // Mendaftar tanpa menunggu peristiwa apa pun — aman: skripnya
            // jalan sesudah Livewire dimuat di body yang sama.
            continue;
        }

        /*
         | Yang menunggu peristiwa WAJIB punya jalan kedua: memasang langsung
         | bila Livewire sudah hidup. Tanpa itu, halaman yang dicapai lewat
         | wire:navigate tidak pernah memasang penangannya.
         */
        if (! str_contains($isi, 'window.Livewire')) {
            $pelanggar[] = $jalur;
        }
    }

    expect($pelanggar)->toBe([], implode("\n", array_merge(
        ['Tampilan berikut mendaftar penangan Livewire hanya lewat peristiwa init:'],
        $pelanggar,
        ['', 'Peristiwa itu dikirim SEKALI per muat halaman penuh. Halaman yang',
            'dicapai lewat wire:navigate tidak akan pernah memasang penangannya.',
            '', 'Pakai pola: if (window.Livewire) { pasang(); } else { addEventListener(...) }'],
    )));
});

it('partial skrip orcha memasang penangannya tanpa menunggu init', function () {
    $isi = file_get_contents(
        resource_path('views/livewire/pages/admin/orcha/partials/skrip.blade.php')
    );

    // Jalur langsung untuk halaman yang dicapai lewat wire:navigate.
    expect($isi)->toContain('if (window.Livewire)');
    // Jalur lambat tetap ada untuk muat halaman penuh.
    expect($isi)->toContain("addEventListener('livewire:initialized'");
    // Dan penjaganya tetap mencegah pemasangan ganda.
    expect($isi)->toContain('window.__orchaNotifBound');
});

it('tiap layar orcha memuat partial skrip', function () {
    /*
     | Penangan `orcha-sukses-pindah` dan `toast-error` hidup di partial itu.
     | Layar yang melewatkannya mengirim eventnya ke ruang kosong: tidak ada
     | toast, tidak ada perpindahan, dan tidak ada galat yang bisa dicurigai.
     */
    $tanpa = [];

    foreach (glob(resource_path('views/livewire/pages/admin/orcha/*/*.blade.php')) as $jalur) {
        if (str_contains($jalur, '/partials/')) {
            continue;
        }

        if (! str_contains(file_get_contents($jalur), 'partials.skrip')) {
            $tanpa[] = str_replace(resource_path('views/livewire/pages/admin/orcha/'), '', $jalur);
        }
    }

    expect($tanpa)->toBe([], 'Layar Orcha tanpa partial skrip: '.implode(', ', $tanpa));
});
