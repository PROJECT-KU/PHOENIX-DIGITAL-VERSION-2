<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Manifes rombongan untuk tour leader, versi Excel.
 *
 * ISINYA SAMA PERSIS dengan manifes PDF — baris yang sama, urutan yang sama,
 * kolom yang sama. Dua berkas yang menjawab pertanyaan yang sama tetapi
 * berbeda isinya adalah asal perselisihan di parkiran: yang satu dipegang
 * tour leader, yang satu dibuka panitia di laptop, dan tidak ada yang bisa
 * menjelaskan mengapa jumlahnya beda.
 *
 * Yang berbeda cuma bentuknya, dan itu memang gunanya: PDF dibawa berdiri,
 * Excel disaring dan dihitung.
 */
class OrchaManifesExport implements FromView, ShouldAutoSize, WithEvents, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $daftar  pendaftaran dari Orcha
     * @param  array<int, array<string, mixed>>  $kesehatan  riwayat per pendaftaran
     * @param  array<string, mixed>  $saringan
     */
    public function __construct(
        private array $daftar,
        private array $kesehatan,
        private array $saringan,
    ) {}

    public function view(): View
    {
        return view('exports.orcha-manifes-excel', [
            'daftar' => $this->daftar,
            'kesehatan' => $this->kesehatan,
            'saringan' => $this->saringan,
        ]);
    }

    public function title(): string
    {
        return 'Manifes Rombongan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $kejadian) {
                $lembar = $kejadian->sheet->getDelegate();

                // Judul kolom tetap terlihat saat digulung — manifes rombongan
                // besar ratusan baris, dan kolom tanpa judul harus dihitung
                // dengan jari dari tepi kiri.
                $lembar->freezePane('A4');

                /*
                 | Perataan dipaksa rata kiri untuk SEMUA sel, termasuk angka.
                 |
                 | Alasannya sama dengan ekspor Orcha yang lain: lembar yang
                 | perataannya berganti-ganti mengikuti isi sel dibaca menurun
                 | kolom demi kolom, dan tepi kiri yang lurus itulah yang
                 | menuntun mata.
                 */
                $lembar->getStyle('A1:J'.$lembar->getHighestRow())
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical('top');
            },
        ];
    }
}
