<?php

/**
 * Alat perbaikan skema MySQL.
 *
 * Yang dijaga: alat ini tidak boleh diam-diam mengubah apa pun. Bawaannya
 * melapor saja, dan pada koneksi selain MySQL ia harus berhenti — bukan
 * mencoba menjalankan ALTER TABLE bergaya MySQL di tempat yang salah.
 */
it('menolak jalan di luar MySQL', function () {
    expect(DB::connection()->getDriverName())->toBe('sqlite');

    $this->artisan('db:perbaiki-kunci')
        ->expectsOutputToContain('hanya untuk MySQL')
        ->assertFailed();
});

it('tidak mengubah apa pun tanpa --terapkan', function () {
    // Bila suatu saat perintahnya dijalankan pada MySQL, --terapkan adalah
    // satu-satunya pintu menuju perubahan. Ditegaskan lewat tanda tangannya
    // supaya opsi ini tidak pernah tanpa sengaja dijadikan bawaan.
    $definisi = (new App\Console\Commands\PerbaikiKunciUtama)->getDefinition();

    expect($definisi->hasOption('terapkan'))->toBeTrue()
        ->and($definisi->getOption('terapkan')->acceptValue())->toBeFalse();
});
