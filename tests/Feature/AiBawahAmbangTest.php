<?php

use App\Models\OrderUpload;

/**
 * Turnitin menulis "*% detected as AI" bila skornya di bawah 20%, dengan
 * alasannya sendiri: skor sekecil itu terlalu sering keliru. Bintang itu BUKAN
 * angka yang tersembunyi, melainkan penolakan menyebut angka.
 *
 * Memaksanya jadi bilangan mengarang ketelitian yang tidak ada — dan pelanggan
 * yang membuka PDF-nya akan melihat angka kita berbeda dari laporan aslinya.
 */
it('menampilkan keadaan di bawah ambang, bukan angka', function () {
    $up = new OrderUpload(['ai_bawah_ambang' => true]);

    expect($up->labelPersenAi())->toBe('di bawah 20%');
});

it('keadaan di bawah ambang mengalahkan angka yang tersisa', function () {
    // Sisa angka dari unggahan sebelumnya tidak boleh ikut tampil.
    $up = new OrderUpload(['ai_bawah_ambang' => true, 'persentase_ai' => 8]);

    expect($up->labelPersenAi())->toBe('di bawah 20%');
});

it('angka biasa tetap tampil apa adanya', function () {
    $up = new OrderUpload(['ai_bawah_ambang' => false, 'persentase_ai' => 14]);

    expect($up->labelPersenAi())->toBe('14%');
});

it('tanpa hasil AI sama sekali, tidak ada yang ditampilkan', function () {
    $up = new OrderUpload(['ai_bawah_ambang' => false, 'persentase_ai' => null]);

    expect($up->labelPersenAi())->toBeNull();
});

it('nol persen tetap angka, bukan dianggap kosong', function () {
    // 0% adalah hasil yang sah dan berbeda artinya dari "belum ada hasil".
    $up = new OrderUpload(['ai_bawah_ambang' => false, 'persentase_ai' => 0]);

    expect($up->labelPersenAi())->toBe('0%');
});

it('ambangnya satu tetapan, bukan angka telanjang yang tersebar', function () {
    expect(OrderUpload::AMBANG_AI)->toBe(20)
        ->and((new OrderUpload(['ai_bawah_ambang' => true]))->labelPersenAi())
        ->toContain((string) OrderUpload::AMBANG_AI);
});
