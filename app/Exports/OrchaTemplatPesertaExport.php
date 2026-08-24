<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Templat kosong untuk panitia mengisi daftar peserta.
 *
 * Dibuat per pendaftaran, bukan satu berkas umum: barisnya sebanyak peserta
 * yang tercatat, dan kolom titik jemputnya berisi pilihan milik paket itu
 * sendiri. Panitia memilih alih-alih mengetik — mengetik bebas menghasilkan
 * ejaan berbeda-beda untuk tempat yang sama, dan manifes lalu mencetak dua
 * kelompok bernama sama.
 */
class OrchaTemplatPesertaExport implements FromView, ShouldAutoSize, WithEvents, WithTitle
{
    /**
     * @param  array<int, string>  $titikJemput
     */
    public function __construct(
        private string $kode,
        private int $jumlahPeserta,
        private array $titikJemput,
    ) {}

    public function view(): View
    {
        return view('exports.orcha-templat-peserta', [
            'kode' => $this->kode,
            'baris' => max(1, $this->jumlahPeserta),
            'titikJemput' => $this->titikJemput,
        ]);
    }

    public function title(): string
    {
        return substr('Peserta '.$this->kode, 0, 31);
    }

    /**
     * Baris terakhir yang ikut diberi daftar pilihan.
     *
     * Nama kolom di baris 1, isian mulai baris 2.
     */
    private function barisAkhir(): int
    {
        return 1 + max(1, $this->jumlahPeserta);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $kejadian) {
                $lembar = $kejadian->sheet->getDelegate();

                $lembar->getColumnDimension('A')->setAutoSize(false)->setWidth(34);
                $lembar->getColumnDimension('B')->setAutoSize(false)->setWidth(26);
                $lembar->getColumnDimension('C')->setAutoSize(false)->setWidth(28);

                $lembar->getStyle('A1:C'.$lembar->getHighestRow())
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical('top');

                $lembar->freezePane('A2');

                /*
                 | Daftar pilihan ditulis langsung di dalam berkasnya (inline
                 | list), bukan menunjuk ke sel lain — supaya tetap berlaku
                 | walau panitia menyalin sheet-nya ke berkas lain.
                 |
                 | Batasnya dua: daftar inline tidak boleh melebihi 255 huruf,
                 | dan pemisahnya koma sehingga nama titik yang mengandung koma
                 | akan memecah pilihannya sendiri. Bila salah satunya kena,
                 | validasinya dilewati — kolomnya tetap bisa diisi tangan, dan
                 | daftar yang sah sudah tercetak di baris keterangan.
                 */
                $daftar = implode(',', $this->titikJemput);

                $bolehValidasi = $this->titikJemput !== []
                    && mb_strlen($daftar) <= 250
                    && ! collect($this->titikJemput)->contains(fn ($titik) => str_contains($titik, ','));

                if (! $bolehValidasi) {
                    return;
                }

                for ($nomor = 2; $nomor <= $this->barisAkhir(); $nomor++) {
                    $validasi = $lembar->getCell('B'.$nomor)->getDataValidation();
                    $validasi->setType(DataValidation::TYPE_LIST);
                    $validasi->setErrorStyle(DataValidation::STYLE_WARNING);
                    $validasi->setAllowBlank(true);
                    $validasi->setShowDropDown(true);
                    $validasi->setShowErrorMessage(true);
                    $validasi->setErrorTitle('Titik jemput tidak dikenal');
                    $validasi->setError('Pilih dari daftar supaya sama dengan titik jemput paket.');
                    $validasi->setFormula1('"'.$daftar.'"');
                }
            },
        ];
    }
}
