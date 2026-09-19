<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Ekspor Data Testimoni — memakai koleksi yang SUDAH disaring di layar,
 * supaya berkas yang terunduh persis sama dengan yang admin lihat.
 *
 * Nomor WhatsApp sengaja TIDAK ikut: berkasnya sering dibagikan, sedangkan
 * nomor pengirim adalah data pribadi yang hanya perlu dilihat saat memoderasi.
 */
class TestimoniExport implements FromView
{
    public function __construct(protected Collection $testimoni) {}

    public function view(): View
    {
        return view('exports.testimoni', ['testimoni' => $this->testimoni]);
    }
}
