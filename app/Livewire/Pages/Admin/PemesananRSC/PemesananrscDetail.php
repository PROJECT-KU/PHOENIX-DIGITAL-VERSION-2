<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Exports\CampBatchExport;
use App\Models\PemesananRsc;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
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

        $waPeserta = [];
        if ($batchData) {
            foreach ($pesertaList as $peserta) {
                $waPeserta[$peserta->id] = self::tautanWa($peserta, $batchData, $extraAkuns);
            }
        }

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-detail', [
            'waPeserta' => $waPeserta,
            'batchData' => $batchData,
            'pesertaList' => $pesertaList,
            'extraAkuns' => $extraAkuns,
            'metode_harga' => $metodeHarga,
            'akunBreakdown' => $akunBreakdown,
            'sumHargaAkun' => collect($akunBreakdown)->sum('harga'),
        ]);
    }

    /**
     * Tautan api.whatsapp.com lengkap dengan pesannya — pola yang sama dengan
     * Pesanan Toko (order-detail), supaya pelanggan menerima pesan yang
     * seragam dari dua jalur.
     *
     * Jenis pesan mengikuti masa akun:
     *  - "akses": akun masih aktif → kirim username/password/link semua akun.
     *  - "habis": status Habis atau tanggal berakhir sudah lewat → beri tahu
     *    masa aktifnya habis dan arahkan perpanjangan ke Pemesanan Toko.
     *
     * @return array{url: string|null, jenis: string}
     */
    public static function tautanWa(PemesananRsc $peserta, $batch, $extraAkuns): array
    {
        $digit = preg_replace('/\D/', '', (string) $peserta->telp_pembeli);
        if (str_starts_with($digit, '0')) {
            $digit = '62'.substr($digit, 1);
        }

        $berakhir = $batch->tanggal_berakhir ? Carbon::parse($batch->tanggal_berakhir)->startOfDay() : null;
        $habis = $batch->status === 'habis' || ($berakhir && $berakhir->lt(today()));
        $tgl = fn ($d) => $d ? Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : '-';

        $produk = $batch->dataakun?->product;
        $namaAkun = $produk?->nama_akun ?: ($batch->dataakun->nama_akun ?? 'akun');
        $kepala = 'ID Transaksi: '.$peserta->id_transaksi."\n\nHalo ".$peserta->nama_pembeli.',';
        $penutup = "Jika ada kendala, jangan ragu untuk menghubungi kami.\n"
            ."Terima kasih telah menggunakan layanan kami.\n\n"
            ."Salam hangat,\nPhoenix Digital Warehouse\n"
            ."Instagram: phoenixdigital_warehouse\n"
            .'Website: https://phoenixdigitalwarehouse.com/';

        if ($habis) {
            $tautanBeli = $produk
                ? route('shop.detail-product', $produk->id)
                : 'https://phoenixdigitalwarehouse.com/';

            $pesan = $kepala."\n"
                .'Akun *'.$namaAkun.'* dari *'.$batch->nama_camp.' Batch '.$batch->batch_camp.'* '
                .'yang aktif sejak *'.$tgl($batch->tanggal_pemesanan).'* sampai *'.$tgl($batch->tanggal_berakhir).'* '
                ."*SUDAH HABIS MASA AKTIFNYA*.\n\n"
                ."Jika ingin memperpanjang akun *{$namaAkun}*, silakan order langsung melalui website kami:\n"
                .$tautanBeli."\n\n"
                .$penutup;
        } else {
            $blok = fn ($nama, $user, $pass, $link) => '*'.$nama."*\n"
                .'• Username: '.($user ?: '-')."\n"
                .'• Password: *'.($pass ?: '-')."*\n"
                .'• Link Login: '.($link ?: '-');

            $akun = [$blok($namaAkun, $batch->username, $batch->password, $batch->link_akses)];
            foreach ($extraAkuns as $ea) {
                $nama = $ea->dataakun?->product?->nama_akun ?: ($ea->nama_akun ?: 'Akun');
                $akun[] = $blok($nama, $ea->username, $ea->password, $ea->link_akses);
            }

            $pesan = $kepala."\n"
                .'Terima kasih telah mengikuti *'.$batch->nama_camp.' Batch '.$batch->batch_camp.'*. '
                .'Akun Anda aktif mulai *'.$tgl($batch->tanggal_pemesanan).'* sampai *'.$tgl($batch->tanggal_berakhir).'*. '
                .'Detail akunnya sebagai berikut:'."\n\n"
                .implode("\n\n", $akun)."\n\n"
                .$penutup;
        }

        return [
            'url' => strlen($digit) >= 9
                ? 'https://api.whatsapp.com/send?phone='.$digit.'&text='.rawurlencode($pesan)
                : null,
            'jenis' => $habis ? 'habis' : 'akses',
        ];
    }
}
