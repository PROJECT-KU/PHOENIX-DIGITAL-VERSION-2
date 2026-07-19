<?php

namespace App\Exports;

use App\Models\PemesananRsc;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CampBatchExport implements FromView, WithColumnWidths, WithDrawings, WithEvents
{
    use Exportable;

    protected $selectedBathes;

    public function __construct(array $selectedBatches)
    {
        $this->selectedBathes = $selectedBatches;
    }

    public function view(): View
    {
        $conditions = collect($this->selectedBathes)->map(function ($item) {
            [$nama, $batch] = explode('|', $item);

            return ['nama_camp' => $nama, 'batch_camp' => $batch];
        });
        $data = PemesananRsc::query()
            ->where(function ($query) use ($conditions) {
                foreach ($conditions as $condition) {
                    $query->orWhere(function ($q) use ($condition) {
                        $q->where('nama_camp', $condition['nama_camp'])
                            ->where('batch_camp', $condition['batch_camp']);
                    });
                }
            })
            ->orderBy('nama_camp')
            ->orderBy('batch_camp')
            ->orderBy('nama_pembeli')
            ->get()
            ->groupby(function ($item) {
                return $item->nama_camp.'|'.$item->batch_camp;
            });

        return view('exports.camp-batch', [
            'groupedData' => $data,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 28,
            'C' => 26,
            'D' => 30,
            'E' => 20,
        ];
    }

    /**
     * Logo Phoenix Digital di pojok kiri atas.
     */
    public function drawings()
    {
        $logoPath = storage_path('app/public/img/archive/phoenix.png');
        if (! is_file($logoPath)) {
            return [];
        }

        $drawing = new Drawing;
        $drawing->setName('Phoenix Digital');
        $drawing->setDescription('Phoenix Digital');
        $drawing->setPath($logoPath);
        $drawing->setHeight(46);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(6);
        $drawing->setOffsetY(4);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Beri tinggi memadai untuk area logo/identitas.
                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->getRowDimension(2)->setRowHeight(15);

                // Paksa kolom No WA (E) menjadi teks; tambah '+' hanya bila diawali angka.
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = (string) $sheet->getCell('E'.$row)->getValue();

                    if ($cellValue === '') {
                        continue;
                    }

                    // Hanya sentuh nilai yang tampak seperti nomor telepon.
                    if (ctype_digit($cellValue[0])) {
                        $cellValue = '+'.$cellValue;
                        $sheet->getCell('E'.$row)->setValueExplicit($cellValue, DataType::TYPE_STRING);
                    } elseif ($cellValue[0] === '+') {
                        $sheet->getCell('E'.$row)->setValueExplicit($cellValue, DataType::TYPE_STRING);
                    }
                }
            },
        ];
    }
}
