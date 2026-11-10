<?php

/**
 * Jendela sembul (.ts-modal) harus tetap berada DI ATAS bilah samping.
 *
 * Sudah dua kali gagal di layar Pelamar, dan dua-duanya tidak terlihat
 * sebagai galat: jendelanya muncul, hanya separuh kirinya tertutup bilah
 * samping dan latar gelapnya tidak menggelapkan bilah itu.
 *
 * Yang bikin repot: menaikkan z-index tidak menyembuhkannya. Begitu salah
 * satu pembungkus halaman (#app / #main / #page-content) memiliki properti
 * yang membuat KONTEKS PENUMPUKAN, semua isi di dalamnya terkurung — dan
 * z-index 1056 di dalam kurungan yang ber-z-index auto tetap dibandingkan
 * sebagai 0 di tingkat akar, jadi kalah dari .sidebar-wrapper (z-index: 10).
 *
 * Pelakunya dulu `#page-content { will-change: opacity }` di layout.
 */

/** Properti yang membuat konteks penumpukan baru pada elemen biasa. */
const PROPERTI_MENGURUNG = [
    'will-change',
    'transform',
    'filter',
    'backdrop-filter',
    'perspective',
    'isolation',
    'contain',
    'mix-blend-mode',
];

/** Pembungkus yang membungkus SEMUA jendela sembul admin. */
const PEMBUNGKUS_HALAMAN = ['#app', '#main', '#page-content'];

/**
 * Isi <style> milik sebuah tampilan, TANPA komentarnya.
 *
 * Komentarnya wajib dibuang: keterangan kenapa properti ini dilarang justru
 * menyebut nama propertinya, dan tanpa langkah ini uji ini merah oleh
 * tulisan yang menjelaskan dirinya sendiri.
 */
function gayaTanpaKomentar(string $berkas): string
{
    $isi = file_get_contents($berkas);

    preg_match_all('/<style\b[^>]*>(.*?)<\/style>/si', $isi, $cocok);

    return preg_replace('#/\*.*?\*/#s', '', implode("\n", $cocok[1]));
}

test('pembungkus halaman admin tidak mengurung jendela sembul', function () {
    $berkas = [
        resource_path('views/layouts/app.blade.php'),
        resource_path('views/livewire/layout/templateindex.blade.php'),
    ];

    foreach ($berkas as $jalur) {
        $gaya = gayaTanpaKomentar($jalur);

        // Tiap aturan CSS: "pemilih { deklarasi }".
        preg_match_all('/([^{}]+)\{([^{}]*)\}/s', $gaya, $aturan, PREG_SET_ORDER);

        foreach ($aturan as [, $pemilih, $deklarasi]) {
            $kena = collect(PEMBUNGKUS_HALAMAN)
                ->first(fn ($p) => str_contains($pemilih, $p));

            if (! $kena) {
                continue;
            }

            foreach (PROPERTI_MENGURUNG as $properti) {
                expect(preg_match('/(^|[;{\s])'.preg_quote($properti, '/').'\s*:/i', $deklarasi))
                    ->toBe(0, sprintf(
                        '%s memberi "%s" pada "%s". Properti itu membuat konteks penumpukan, '
                        .'dan jendela .ts-modal di dalamnya tidak akan bisa naik di atas bilah '
                        .'samping berapa pun z-index-nya. Kalau memang dibutuhkan saat animasi, '
                        .'nyalakan sesaat lewat JS lalu cabut lagi.',
                        basename($jalur), $properti, trim($pemilih)
                    ));
            }
        }
    }
});

test('jendela sembul memakai z-index di atas bilah samping', function () {
    /*
     | .sidebar-wrapper milik Mazer ber-z-index 10 dan position: fixed. Syarat
     | kedua (sesudah tidak ada yang mengurung) adalah angkanya memang lebih
     | besar — ini yang menjaga kalau sistem desainnya disetel ulang.
     */
    $gaya = gayaTanpaKomentar(
        resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php')
    );

    preg_match('/\.ts-modal\s*\{([^}]*)\}/s', $gaya, $jendela);
    preg_match('/\.ts-modal-back\s*\{([^}]*)\}/s', $gaya, $latar);

    expect($jendela)->not->toBeEmpty('Kerangka .ts-modal hilang dari sistem desain.');

    preg_match('/z-index\s*:\s*(\d+)/', $jendela[1], $angkaJendela);
    preg_match('/z-index\s*:\s*(\d+)/', $latar[1] ?? '', $angkaLatar);

    expect((int) ($angkaJendela[1] ?? 0))->toBeGreaterThan(10);
    expect((int) ($angkaLatar[1] ?? 0))->toBeGreaterThan(10);
    // Jendelanya di atas latarnya sendiri, bukan sebaliknya.
    expect((int) $angkaJendela[1])->toBeGreaterThan((int) $angkaLatar[1]);
});
