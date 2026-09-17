<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Exports\CampBatchExport;
use App\Models\PemesananRsc;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class PemesananrscDetail extends Component
{
    public $nama_camp;

    public $batch_camp;

    /*
     * Data batch, peserta, dan akun TIDAK lagi disimpan sebagai properti
     * publik.
     *
     * Properti publik Livewire dikirim utuh ke peramban di dalam snapshot
     * halaman — termasuk username & password akun, walau di layar
     * passwordnya disamarkan titik-titik. Sekarang semuanya dihitung di
     * render(). Password tetap ada di HTML (tersembunyi di balik tombol
     * mata, untuk disalin admin), tetapi tidak lagi ikut dalam snapshot
     * yang dikirim bolak-balik pada setiap permintaan Livewire.
     */

    public function mount($nama_camp, $batch_camp)
    {
        $this->nama_camp = urldecode($nama_camp);
        $this->batch_camp = urldecode($batch_camp);

        if (! $this->kueriBatch()->exists()) {
            session()->flash('error', 'Data batch tidak ditemukan!');

            return redirect()->route('admin.pesananrsc.index');
        }
    }

    protected function kueriBatch()
    {
        return PemesananRsc::query()
            ->where('nama_camp', $this->nama_camp)
            ->where('batch_camp', $this->batch_camp);
    }

    /** Kunci batch dalam bentuk yang dipakai ekspor & invoice: "nama|batch". */
    protected function kunci(): string
    {
        return $this->nama_camp.'|'.$this->batch_camp;
    }

    /**
     * Unduh invoice PDF untuk batch INI saja.
     *
     * Sebelumnya halaman detail tidak punya satu pun tombol: untuk mengunduh
     * invoice batch yang sedang dibuka, orang harus kembali ke daftar,
     * membuka jendela unduh, lalu mencari dan mencentang batch yang sama.
     */
    public function unduhInvoice()
    {
        $items = PemesananRsc::invoiceItemsFor([$this->kunci()]);

        $pdf = Pdf::loadView('exports.invoice-pdf', [
            'invoiceNumber' => 'INV-'.date('Y').'-'.rand(1000, 9999),
            'date' => now()->locale('id')->translatedFormat('d F Y'),
            'items' => $items,
            'grandTotal' => $items->sum('total_harga'),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'Invoice_'.str($this->nama_camp)->slug().'_batch-'.$this->batch_camp.'.pdf'
        );
    }

    /** Unduh daftar peserta batch INI sebagai Excel. */
    public function unduhExcel()
    {
        return Excel::download(
            new CampBatchExport([$this->kunci()]),
            'Peserta_'.str($this->nama_camp)->slug().'_batch-'.$this->batch_camp.'.xlsx'
        );
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $batchData = $this->kueriBatch()
            ->select([
                'nama_camp',
                'batch_camp',
                'tanggal_mulai_camp',
                'tanggal_akhir_camp',
                'tanggal_pemesanan',
                'tanggal_berakhir',
                'jumlah_pemesanan',
                'metode_harga',
                'akun',
                'pic',
                'status',
                'username',
                'password',
                'link_akses',
                'deskripsi',
                DB::raw('COUNT(*) as total_peserta'),
                DB::raw('SUM(CAST(total as DECIMAL(15,2))) as total_harga'),
                DB::raw('AVG(CAST(harga_satuan as DECIMAL(15,2))) as harga_satuan'),
            ])
            ->with(['dataakun', 'users'])
            ->groupBy([
                'nama_camp',
                'batch_camp',
                'tanggal_mulai_camp',
                'tanggal_akhir_camp',
                'tanggal_pemesanan',
                'tanggal_berakhir',
                'jumlah_pemesanan',
                'metode_harga',
                'akun',
                'pic',
                'status',
                'username',
                'password',
                'link_akses',
                'deskripsi',
            ])
            ->first();

        // Semua peserta dalam batch ini.
        $pesertaList = $this->kueriBatch()->orderBy('nama_pembeli')->get();

        // Akun tambahan (kredensial saja).
        $extraAkuns = \App\Models\RscBatchAkun::with('dataakun')
            ->where('nama_camp', $this->nama_camp)
            ->where('batch_camp', $this->batch_camp)
            ->orderBy('id')
            ->get();

        $metodeHarga = $batchData->metode_harga ?? 'per_peserta';

        // Rincian harga tiap akun (untuk mode per_akun).
        $parse = fn ($v) => (int) preg_replace('/[^0-9]/', '', (string) $v);

        $akunBreakdown = [];
        if ($batchData) {
            $akunBreakdown[] = [
                'nama' => optional($batchData->dataakun)->nama_akun ?? 'Akun Utama',
                'harga' => (int) round($batchData->harga_satuan),
                'utama' => true,
            ];
            foreach ($extraAkuns as $ea) {
                $akunBreakdown[] = [
                    'nama' => $ea->nama_akun ?? optional($ea->dataakun)->nama_akun ?? 'Akun',
                    'harga' => $ea->akun_id ? $parse(optional($ea->dataakun)->harga_satuan) : 0,
                    'utama' => false,
                ];
            }
        }

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-detail', [
            'batchData' => $batchData,
            'pesertaList' => $pesertaList,
            'extraAkuns' => $extraAkuns,
            'metode_harga' => $metodeHarga,
            'akunBreakdown' => $akunBreakdown,
            'sumHargaAkun' => collect($akunBreakdown)->sum('harga'),
        ]);
    }
}
