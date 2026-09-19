<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Ekspor Moderasi Ulasan Produk — memakai koleksi yang SUDAH disaring di
 * layar, supaya berkas yang terunduh persis sama dengan yang admin lihat.
 */
class UlasanProdukExport implements FromView
{
    public function __construct(protected Collection $ulasan) {}

    public function view(): View
    {
        return view('exports.ulasan-produk', ['ulasan' => $this->ulasan]);
    }
}
