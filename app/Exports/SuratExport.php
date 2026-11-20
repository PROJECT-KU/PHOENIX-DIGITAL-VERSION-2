<?php

namespace App\Exports;

use App\Models\User;
use App\Support\Surat\SaringanSurat;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Buku agenda surat sebagai Excel.
 *
 * Saringannya DITERUSKAN apa adanya ke SaringanSurat — bukan disalin ulang
 * di sini. Berkas yang memuat surat yang sengaja disaring keluar jauh lebih
 * berbahaya daripada tombol yang gagal, dan tidak ada gejalanya: berkasnya
 * tetap terunduh dan tetap berisi surat sungguhan.
 *
 * Penggunanya ikut diteruskan supaya surat rahasia tidak bisa keluar lewat
 * pintu ekspor — izin yang dijaga di layar tetapi bocor di berkas unduhan
 * sama saja dengan tidak dijaga.
 */
class SuratExport implements FromView
{
    public function __construct(
        private ?User $pengguna,
        private array $saringan,
    ) {}

    public function view(): View
    {
        $surat = SaringanSurat::urut(
            SaringanSurat::kueri($this->pengguna, $this->saringan)->with(['klasifikasi', 'pencatat', 'induk', 'penanggungJawab']),
            $this->saringan['urut'] ?? 'terbaru',
        )->get();

        return view('exports.surat', [
            'surat' => $surat,
            'ringkasan' => SaringanSurat::ringkasan($this->saringan),
        ]);
    }
}
