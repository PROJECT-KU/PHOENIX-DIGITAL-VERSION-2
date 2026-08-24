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
 * Data lengkap satu pendaftaran untuk keperluan kantor.
 *
 * Datanya datang dari API Orcha, bukan dari basis data lemon — jadi yang
 * dioper ke sini sudah berupa larik, bukan model.
 */
class OrchaPendaftaranExport implements FromView, ShouldAutoSize, WithEvents, WithTitle
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

    /** Nama lembarnya memakai kode pendaftaran supaya beberapa berkas yang
     *  dibuka bersamaan tetap bisa dibedakan. */
    public function title(): string
    {
        return substr($this->pendaftaran['kode'] ?? 'Pendaftaran', 0, 31);
    }

    /**
     * Kolom sempit dibiarkan mengikuti isi (ShouldAutoSize), tetapi kolom yang
     * memuat kalimat panjang perlu batas — kalau tidak, satu catatan panjang
     * membuat satu kolom selebar layar dan sisanya terdorong keluar.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $kejadian) {
                $lembar = $kejadian->sheet->getDelegate();

                foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H'] as $kolom) {
                    $lembar->getColumnDimension($kolom)->setAutoSize(false)->setWidth(24);
                }

                $lembar->getColumnDimension('A')->setAutoSize(false)->setWidth(28);

                // Baris judul tetap terlihat saat digulung ke bawah.
                $lembar->freezePane('A4');

                // Isi sel yang panjang dibungkus, bukan memanjang ke samping.
                //
                // Perataannya dipaksa rata kiri untuk SEMUA sel, termasuk yang
                // berisi angka. Tanpa itu Excel memakai bawaannya — teks ke
                // kiri, angka ke kanan — sehingga satu lembar yang sama memuat
                // dua perataan yang berganti-ganti mengikuti isi selnya: "Jumlah
                // peserta" dan "Nominal" melompat ke kanan sementara tetangganya
                // tetap di kiri. Lembar begini dibaca menurun kolom demi kolom,
                // dan tepi kiri yang lurus itulah yang menuntun mata.
                $lembar->getStyle('A1:H'.$lembar->getHighestRow())
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical('top');
            },
        ];
    }
}
