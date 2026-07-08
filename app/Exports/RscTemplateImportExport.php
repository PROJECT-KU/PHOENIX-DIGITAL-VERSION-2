<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Template Excel untuk import data peserta Pemesanan RSC.
 * Kolom mengikuti format yang dibaca PemesananrscForm::updatedFileExcel():
 *   A = Nama Camp, B = Batch Camp, C = Nama Pembeli, D = No Telp
 * Baris 1 = header (diabaikan saat import).
 */
class RscTemplateImportExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return ['Nama Camp', 'Batch Camp', 'Nama Pembeli', 'No Telp'];
    }

    public function array(): array
    {
        // Baris contoh — silakan hapus/ganti saat mengisi data asli.
        return [
            ['Scopus Camp', '1', 'Contoh: Budi Santoso', '08123456789'],
            ['Scopus Camp', '1', 'Contoh: Siti Aminah', '08129876543'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Style header (baris 1)
                $sheet->getStyle('A1:D1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F59E0B'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Border seluruh tabel
                $sheet->getStyle('A1:D'.$highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E2E8F0'],
                        ],
                    ],
                ]);

                // Paksa kolom No Telp (D) sebagai teks agar nol di depan tidak hilang
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getCell('D'.$row)->setValueExplicit(
                        (string) $sheet->getCell('D'.$row)->getValue(),
                        DataType::TYPE_STRING
                    );
                }
            },
        ];
    }
}
