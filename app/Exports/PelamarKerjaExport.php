<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Ekspor Pelamar Kerja — koleksi yang SUDAH disaring di layar, supaya berkas
 * yang terunduh persis sama dengan yang admin lihat. Berkas (CV dsb.) tidak
 * ikut: hanya data teks.
 */
class PelamarKerjaExport implements FromView
{
    public function __construct(protected Collection $pelamar) {}

    public function view(): View
    {
        return view('exports.pelamar-kerja', ['pelamar' => $this->pelamar]);
    }
}
