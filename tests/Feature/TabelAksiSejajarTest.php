<?php

/**
 * Garis pemisah baris harus lurus sampai ke kolom Aksi.
 *
 * `.dsb-tabel-aksi` adalah pembungkus di DALAM sel — gayanya
 * `display: inline-flex`. Dipasang langsung di `<td>`, sel itu berhenti
 * menjadi sel tabel: tingginya menyusut sampai sebesar kotak tombolnya saja,
 * dan garis bawahnya ikut digambar di situ. Hasilnya garis di kolom Aksi
 * berhenti 27px lebih tinggi daripada garis di sebelahnya — diukur 22 Nov
 * 2026 di layar Arsip Surat.
 *
 * Tidak ada galat, tidak ada peringatan; halamannya tetap 200 dan tabelnya
 * tetap terbaca. Satu-satunya gejalanya adalah garis yang tidak sejajar,
 * dan itu baru terlihat kalau ada yang memperhatikan tepi kanan tabel.
 */
function bladeAdmin(): array
{
    $pola = resource_path('views/livewire/pages/admin');

    $berkas = [];

    foreach (['*', '*/*', '*/*/*', '*/*/*/*'] as $tingkat) {
        $berkas = array_merge($berkas, glob($pola.'/'.$tingkat.'.blade.php') ?: []);
    }

    return $berkas;
}

it('tidak memasang kelas pembungkus aksi langsung di sel tabel', function () {
    $salah = [];

    foreach (bladeAdmin() as $berkas) {
        $isi = file_get_contents($berkas);

        if (preg_match_all('/<td[^>]*class="[^"]*\bdsb-tabel-aksi\b[^"]*"/', $isi, $cocok)) {
            $salah[] = str_replace(resource_path('views/'), '', $berkas)
                .' ('.count($cocok[0]).'×)';
        }
    }

    /*
     | Bentuk yang benar: <td style="text-align: right;"><span class="dsb-tabel-aksi">…
     | Sel tetap sel; yang dibariskan adalah pembungkus di dalamnya.
     */
    expect($salah)->toBe([], "kelas .dsb-tabel-aksi dipasang di <td> pada: \n  ".implode("\n  ", $salah));
});

it('membungkus tombol aksi arsip surat di dalam selnya', function () {
    foreach (['daftar', 'klasifikasi'] as $layar) {
        $isi = file_get_contents(resource_path("views/livewire/pages/admin/surat/{$layar}.blade.php"));

        expect($isi)->toContain('<span class="dsb-tabel-aksi">');
    }
});
