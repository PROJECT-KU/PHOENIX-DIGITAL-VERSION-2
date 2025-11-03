<?php

namespace App\Exports;

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

        return view('exports.pengembalian', compact('pengembalian'));
    }
}