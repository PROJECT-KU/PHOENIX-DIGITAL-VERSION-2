<?php

namespace App\Exports;

use App\Models\Loan;
use App\Models\Pengembalian;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PengembalianExport implements FromView
{
    public function view(): View
    {
        $pengembalian = Pengembalian::with('penginput')
            ->orderBy('tanggal_pengembalian', 'desc')
            ->get();
        $statusMap = Loan::statusMap();

        return view('exports.pengembalian', compact('pengembalian', 'statusMap'));
    }
}
