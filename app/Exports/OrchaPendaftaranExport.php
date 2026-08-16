<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Data lengkap satu pendaftaran untuk keperluan kantor.
 *
 * Datanya datang dari API Orcha, bukan dari basis data lemon — jadi yang
 * dioper ke sini sudah berupa larik, bukan model.
 */
class OrchaPendaftaranExport implements FromView
{
    public function __construct(
        private array $pendaftaran,
        private array $riwayat,
    ) {}

    public function view(): View
    {
        return view('exports.orcha-pendaftaran', [
            'pendaftaran' => $this->pendaftaran,
            'riwayat' => $this->riwayat,
        ]);
    }
}
