<?php

namespace App\Exports;

use App\Models\GajiKaryawans;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class GajiKaryawansExport implements FromView
{
    protected $status;
    protected $start;
    protected $end;

    public function __construct($status = null, $start = null, $end = null)
    {
        $this->status = $status;
        $this->start = $start;
        $this->end = $end;
    }

    public function view(): View
    {
        $query = GajiKaryawans::with(['karyawan']);

        if ($this->start && $this->end) {
            $query->whereBetween('tanggal_transaksi', [$this->start, $this->end]);
        }

        if($this->status){
            $query->when('status', $this->status);
        }

        return view('exports.gaji-karyawan', [
            'items' => $query->orderBy('tanggal_transaksi', 'desc')->get()
        ]);
    }

}
