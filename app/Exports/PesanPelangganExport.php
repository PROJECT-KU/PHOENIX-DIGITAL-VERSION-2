<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Ekspor Pesan Pelanggan — memakai koleksi yang SUDAH disaring di layar,
 * supaya berkas yang terunduh persis sama dengan yang admin lihat.
 */
class PesanPelangganExport implements FromView
{
    public function __construct(protected Collection $pesan) {}

    public function view(): View
    {
        return view('exports.pesan-pelanggan', ['pesan' => $this->pesan]);
    }
}
