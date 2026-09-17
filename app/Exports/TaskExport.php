<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Rekap task yang SEDANG TERLIHAT di layar Task Saya.
 *
 * Menerima koleksi jadi, bukan menyusun kuerinya sendiri: kuerinya milik
 * layar (saringan, periode, urutan), dan kueri kedua yang "kebetulan mirip"
 * adalah cara paling gampang membuat berkas unduhan berbeda isi dengan apa
 * yang barusan dilihat orangnya.
 *
 * TANPA nilai rupiah. Berkas ini berpindah tangan lewat surel dan grup chat,
 * sedangkan besaran bonus hanya untuk pemegang view_all_gajikaryawan.
 */
class TaskExport implements FromView
{
    public function __construct(protected Collection $tasks) {}

    public function view(): View
    {
        return view('exports.task', ['tasks' => $this->tasks]);
    }
}
