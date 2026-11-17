<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Rekap DAFTAR pendaftaran — satu baris per pemesanan.
 *
 * Pembacanya kantor, bukan tour leader. Sebelum ini satu-satunya ekspor
 * berbentuk daftar adalah manifes tour leader, yang isinya nama peserta dan
 * titik jemput; rekap bulanan karenanya disalin tangan dari layar, satu baris
 * demi satu baris — dan angka yang disalin tangan dari dua puluh baris
 * hampir selalu meleset di satu tempat yang tidak bisa ditemukan lagi.
 *
 * TIDAK memuat data kesehatan. Bukan karena lupa: kolom medis menuntut izin
 * tersendiri, dan rekap keuangan yang ikut memuatnya berarti lembar yang tidak
 * boleh dibuka siapa pun yang mengerjakan rekap.
 */
class OrchaPendaftaranRekapExport implements FromView, ShouldAutoSize, WithEvents, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $baris
     * @param  int  $total  jumlah SELURUH hasil saringan, bukan yang terbawa
     * @param  array<string, mixed>  $saringan
     */
    public function __construct(
        private array $baris,
        private int $total,
        private array $saringan,
    ) {}

    public function view(): View
    {
        return view('exports.orcha-pendaftaran-rekap', [
            'baris' => $this->baris,
            'total' => $this->total,
            'terbawa' => count($this->baris),
            'saringan' => $this->saringan,
        ]);
    }

    public function title(): string
    {
        return 'Rekap Pendaftaran';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $kejadian) {
                $lembar = $kejadian->sheet->getDelegate();

                // Judul kolom tetap terlihat saat digulung — rekap ini biasanya
                // ratusan baris, dan kolom tanpa judul harus dihitung dengan
                // jari dari tepi kiri.
                $lembar->freezePane('A4');

                /*
                 | Perataan dipaksa rata kiri untuk SEMUA sel, termasuk angka.
                 |
                 | Alasannya sama dengan OrchaPendaftaranExport: lembar yang
                 | perataannya berganti-ganti mengikuti isi sel dibaca menurun
                 | kolom demi kolom, dan tepi kiri yang lurus itulah yang
                 | menuntun mata.
                 */
                $lembar->getStyle('A1:J'.$lembar->getHighestRow())
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical('top');
            },
        ];
    }
}
