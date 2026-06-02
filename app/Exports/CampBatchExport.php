<?php

namespace App\Exports;

use App\Models\PemesananRsc;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class CampBatchExport implements FromView, ShouldAutoSize, WithEvents
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('E'.$row)->getValue();

                    if (! empty($cellValue) && strpos($cellValue, '+') !== 0) {
                        $cellValue = '+'.$cellValue;
                    }

                    $sheet->getCell('E'.$row)
                        ->setValueExplicit(
                            $cellValue,
                            DataType::TYPE_STRING
                        );
                }
            },
        ];
    }
}
