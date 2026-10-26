<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Daftar vendor beserta nomornya, untuk dibawa ke luar aplikasi.
 *
 * Barisnya datang dari API Orcha, bukan dari basis data Phoenix — vendor
 * memang tidak hidup di sini. Jadi yang dikirim ke kelas ini sudah berupa
 * larik balasan, bukan kueri.
 */
class OrchaVendorExport implements FromView
{
    public function __construct(private array $baris) {}

    public function view(): View
    {
        return view('exports.orcha-vendor', ['baris' => $this->baris]);
    }
}
