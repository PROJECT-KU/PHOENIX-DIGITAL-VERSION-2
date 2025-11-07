<?php

namespace App\Exports;

use App\Models\Spending;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SpendingExport implements FromView
{
    protected $jenis;
    protected $start;
    protected $end;

    public function __construct($jenis = null, $start = null, $end = null)
    {
        $this->jenis = $jenis;
        $this->start = $start;
        $this->end = $end;
    }

    public function view(): View
    {
        $query = Spending::with(['penginput', 'picPembeli']);

        if ($this->jenis) {
            $query->where('jenis_pengeluaran', $this->jenis);
        }
        if ($this->start && $this->end) {
            $query->whereBetween('tanggal_transaksi', [$this->start, $this->end]);
        }

        $spendings = $query->orderBy('tanggal_transaksi', 'desc')->get();

        return view('exports.spanding', [
            'spendings' => $spendings
        ]);
    }
}