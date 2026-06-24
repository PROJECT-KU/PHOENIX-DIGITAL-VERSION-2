<?php

namespace App\Exports;

use App\Models\GajiKaryawans;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GajiKaryawansExport implements FromView
{
    protected $bulan;

    protected $tahun;

    public function __construct($bulan = null, $tahun = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        $query = GajiKaryawans::with(['karyawan']);

        // Filter berdasarkan PERIODE GAJI
        if ($this->bulan) {
            $query->where('periode_bulan', $this->bulan);
        }
        if ($this->tahun) {
            $query->where('periode_tahun', $this->tahun);
        }

        return view('exports.gaji-karyawan', [
            'items' => $query->orderBy('tanggal_transaksi', 'desc')->get(),
        ]);
    }
}
