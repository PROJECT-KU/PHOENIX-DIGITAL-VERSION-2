<?php

/**
 * Tidak boleh ada DUA berkas uji yang mendeklarasikan fungsi bernama sama.
 *
 * Pest memuat seluruh berkas uji ke dalam SATU proses PHP. Dua deklarasi
 * bernama sama membuat rangkaiannya mati dengan "Cannot redeclare" — dan
 * kegagalannya tidak menunjuk fitur mana pun, hanya berkas yang kebetulan
 * dimuat belakangan.
 *
 * Yang paling berbahaya: tiap berkasnya HIJAU saat dijalankan sendirian.
 * Sudah terjadi tiga kali — lowonganUji(), adminBerkas(), rabDieng() — dan
 * tiap kali ketahuannya baru saat rangkaian penuh dijalankan menjelang
 * commit.
 */
it('tidak ada nama fungsi pembantu yang dipakai dua berkas uji', function () {
    $milik = [];

    foreach (glob(base_path('tests/**/*.php')) + glob(base_path('tests/**/**/*.php')) as $berkas) {
        preg_match_all('/^function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/m', file_get_contents($berkas), $cocok);

        foreach ($cocok[1] as $nama) {
            $milik[$nama][] = str_replace(base_path('tests/'), '', $berkas);
        }
    }

    $bentrok = array_filter($milik, fn ($berkas) => count($berkas) > 1);

    $pesan = implode("\n", array_map(
        fn ($nama, $berkas) => "  {$nama}() → ".implode(' & ', $berkas),
        array_keys($bentrok),
        $bentrok,
    ));

    expect($bentrok)->toBe([], "Nama pembantu uji dipakai lebih dari sekali:\n".$pesan);
});
