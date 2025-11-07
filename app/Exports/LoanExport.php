<?php

namespace App\Exports;

use App\Models\Loan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LoanExport implements FromView
{
    public function view(): View
    {
        // $loans = Loan::with('penginput')->orderBy('tanggal_pinjam', 'desc')->get();
        $loans = Loan::with('penginput')->orderBy('tanggal_peminjam', 'desc')->get();

        return view('exports.loan', compact('loans'));
    }
}