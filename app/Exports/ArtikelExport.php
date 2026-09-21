<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Ekspor daftar artikel — memakai koleksi yang SUDAH disaring di layar,
 * supaya berkas yang terunduh persis sama dengan yang admin lihat.
 */
class ArtikelExport implements FromView
{
    public function __construct(protected Collection $artikel, protected bool $ikutIsi = false) {}

    public function view(): View
    {
        return view('exports.artikel', ['artikel' => $this->artikel, 'ikutIsi' => $this->ikutIsi]);
    }
}
