<?php

use App\Exports\OrchaPendaftaranExport;
use Maatwebsite\Excel\Excel as JenisExcel;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Perataan lembar Excel pendaftaran.
 *
 * Tanpa perataan yang ditetapkan, Excel memakai bawaannya: teks rata kiri,
 * angka rata kanan. Satu lembar yang sama jadi memuat dua perataan yang
 * berganti-ganti mengikuti isi selnya, dan tepi kiri yang seharusnya menuntun
 * mata jadi patah-patah.
 */
function lembarPendaftaran(): PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
{
    $pendaftaran = [
        'kode' => 'OT-1608-FXYK', 'nama' => 'Suparjiman', 'whatsapp' => '081234567890',
        'email' => 'suparjiman@contoh.id', 'status_label' => 'Lunas', 'catatan' => null,
        'dibuat_pada' => '2026-08-16T09:09:00+07:00',
        'jumlah_peserta' => 2, 'titik_jemput' => 'Klaten, Jogja',
        'tanggal_berangkat' => '2026-10-19',
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
        'peserta' => [
            ['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten'],
            ['nama' => 'Huhu', 'titik_jemput' => 'Jogja'],
        ],
        'tagihan' => ['total' => 2860000, 'sudah' => 2860000, 'sisa' => 0, 'lunas' => true],
        'pembayaran' => [[
            'tanggal_transfer' => '2026-08-16', 'jenis_label' => 'Pelunasan', 'nominal' => 2860000,
            'bank_pengirim' => 'BCA', 'atas_nama_pengirim' => 'Sofyan',
            'status_label' => 'Diterima', 'catatan_admin' => null,
            'dibuat_pada' => '2026-08-16T10:00:00+07:00',
        ]],
        'pembatalan' => null,
    ];

    $berkas = tempnam(sys_get_temp_dir(), 'uji-eksel').'.xlsx';

    file_put_contents($berkas, Excel::raw(
        new OrchaPendaftaranExport($pendaftaran, []),
        JenisExcel::XLSX,
    ));

    return IOFactory::load($berkas)->getActiveSheet();
}

test('seluruh sel rata kiri, termasuk yang berisi angka', function () {
    $lembar = lembarPendaftaran();

    $perataan = [];

    foreach ($lembar->getRowIterator(1, $lembar->getHighestRow()) as $baris) {
        foreach (['A', 'B', 'C'] as $kolom) {
            $sel = $kolom.$baris->getRowIndex();
            $perataan[] = $lembar->getStyle($sel)->getAlignment()->getHorizontal();
        }
    }

    // Satu perataan untuk seluruh lembar, bukan dua yang berganti-ganti
    // mengikuti isi selnya.
    expect(array_unique($perataan))->toBe([Alignment::HORIZONTAL_LEFT]);
});

test('sel berisi angka tetap tersimpan sebagai angka, hanya perataannya yang disamakan', function () {
    $lembar = lembarPendaftaran();

    // Jumlah peserta dan nominal harus tetap bisa dijumlahkan di Excel —
    // menyeragamkan tampilan tidak boleh mengubahnya jadi teks.
    $adaAngka = false;

    foreach ($lembar->getRowIterator(1, $lembar->getHighestRow()) as $baris) {
        foreach ($baris->getCellIterator() as $sel) {
            if (is_numeric($sel->getValue()) && $sel->getValue() !== null && $sel->getValue() !== '') {
                $adaAngka = true;
                expect($lembar->getStyle($sel->getCoordinate())->getAlignment()->getHorizontal())
                    ->toBe(Alignment::HORIZONTAL_LEFT);
            }
        }
    }

    expect($adaAngka)->toBeTrue();
});
