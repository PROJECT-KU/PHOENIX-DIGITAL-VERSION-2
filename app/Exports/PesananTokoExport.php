<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Ekspor daftar Pesanan Toko — mengikuti tab & saringan yang sedang tampil.
 * Salah satu dari $orders (satu baris = satu pesanan) atau $items (tab
 * Segera Habis / Akun Habis, satu baris = satu akun).
 */
class PesananTokoExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected ?Collection $orders = null,
        protected ?Collection $items = null,
    ) {}

    public function view(): View
    {
        return $this->items
            ? view('exports.pesanan-toko-akun', ['items' => $this->items])
            : view('exports.pesanan-toko', ['orders' => $this->orders ?? collect()]);
    }
}
