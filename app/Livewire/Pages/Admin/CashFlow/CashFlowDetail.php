<?php

namespace App\Livewire\Pages\Admin\CashFlow;

use App\Models\CashFlow;
use App\Models\GajiKaryawans;
use App\Models\Loan;
use App\Models\Order;
use App\Models\PemesananRsc;
use App\Models\Pengembalian;
use App\Models\Spending;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class CashFlowDetail extends Component
{
    public ?CashFlow $cashFlow = null;

    public $isOpen = false;

    protected $listeners = ['openDetail' => 'loadReport'];

    public function loadReport($id)
    {
        $this->cashFlow = CashFlow::with($this->relationsToLoad($id))->find($id);
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
        $this->cashFlow = null;
    }

    /**
     * Unduh detail transaksi sebagai invoice PDF.
     */
    public function downloadInvoice()
    {
        if (! $this->cashFlow) {
            return null;
        }

        $detail = $this->buildDetail();

        $pdf = Pdf::loadView('livewire.pages.admin.cash-flow.invoice-pdf', [
            'cashFlow' => $this->cashFlow,
            'detail' => $detail,
        ])->setPaper('a4', 'portrait');

        $kode = $this->cashFlow->sourceable->order_number
            ?? $this->cashFlow->sourceable->id_transaksi
            ?? substr($this->cashFlow->id, 0, 8);

        $filename = 'invoice-'.str($kode)->slug().'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.pages.admin.cash-flow.cash-flow-detail', [
            'detail' => $this->isOpen && $this->cashFlow ? $this->buildDetail() : null,
        ])->layout('livewire.layout.templateindex');
    }

    /**
     * Tentukan relasi yang perlu di-eager load sesuai tipe sumber.
     */
    protected function relationsToLoad($id): array
    {
        $type = CashFlow::whereKey($id)->value('sourceable_type');

        return match ($type) {
            Order::class => ['sourceable.customer', 'sourceable.items'],
            GajiKaryawans::class => ['sourceable.karyawan'],
            Spending::class => ['sourceable.penginput', 'sourceable.picPembeli'],
            Loan::class, Pengembalian::class => ['sourceable.penginput'],
            default => ['sourceable'],
        };
    }

    /**
     * Susun data detail terstruktur agar blade & PDF tetap bersih dari logika.
     */
    protected function buildDetail(): array
    {
        $s = $this->cashFlow->sourceable;

        $jenis = 'Transaksi Manual';
        $rows = [];
        $items = [];
        $totals = [];
        $itemsNote = null;

        if ($s instanceof Order) {
            $jenis = 'Pesanan Toko';
            $rows = [
                'Nomor Pesanan' => $s->order_number,
                'Customer' => $s->customer->nama ?? '-',
                'No. HP' => $s->customer->no_hp ?? '-',
                'Status' => ucfirst($s->status),
                'Metode Bayar' => $s->payment_method ?: '-',
                'Tanggal Bayar' => $s->paid_at?->format('d M Y H:i') ?? '-',
            ];

            foreach ($s->items as $it) {
                $items[] = [
                    'nama' => $it->product_name,
                    'durasi' => trim($it->duration_value.' '.$it->duration_type),
                    'qty' => $it->quantity,
                    'harga' => $this->rupiah($it->price),
                    'subtotal' => $this->rupiah($it->subtotal),
                ];
            }

            // Diskon sudah didistribusikan ke tiap item, sehingga subtotal item
            // bersifat NET (setelah diskon). Agar alur angka mudah dipahami orang awam,
            // breakdown disusun: Harga Normal - Diskon = Subtotal (= jumlah item) + Kode Unik = Total.
            $subtotalSetelahDiskon = $s->subtotal - $s->total_discount;

            $totals = [];
            if ($s->total_discount > 0) {
                $totals['Harga Normal'] = $this->rupiah($s->subtotal);
                $totals['Diskon'] = '- '.$this->rupiah($s->total_discount);
            }
            $totals['Subtotal'] = $this->rupiah($subtotalSetelahDiskon);
            if ($s->unique_code > 0) {
                $totals['Kode Unik'] = '+ '.$this->rupiah($s->unique_code);
            }
            $totals['Total Bayar'] = $this->rupiah($s->total);

            if ($s->total_discount > 0) {
                $itemsNote = 'Harga & subtotal tiap item sudah termasuk potongan diskon Rp '
                    .number_format($s->total_discount, 0, ',', '.').' (dari harga normal Rp '
                    .number_format($s->subtotal, 0, ',', '.').').';
            }
        } elseif ($s instanceof GajiKaryawans) {
            $jenis = 'Gaji Karyawan';
            $rows = [
                'ID Transaksi' => $s->id_transaksi,
                'Karyawan' => $s->karyawan->name ?? '-',
                'Bank' => $s->bank,
                'No. Rekening' => $s->no_rek,
                'Tanggal' => optional($s->tanggal_transaksi)->format('d M Y') ?? '-',
                'Status' => ucfirst($s->status ?? '-'),
            ];
            $totals = [
                'Gaji Pokok' => $this->rupiah($s->gaji_pokok),
                'Bonus Kinerja' => $this->rupiah($s->bonus_kinerja),
                'Tunjangan' => $this->rupiah($s->tunjangan_kesehatan + $s->tunjangan_thr + $s->tunjangan_ketenagakerjaan + $s->tunjangan_lainnya),
                'Potongan & PPh21' => '- '.$this->rupiah($s->potongan + $s->pph21),
                'Total Diterima' => $this->rupiah($s->total),
            ];
        } elseif ($s instanceof Loan) {
            $jenis = 'Pinjaman';
            $rows = [
                'ID Transaksi' => $s->id_transaksi,
                'Peminjam' => $s->nama_peminjam,
                'Tanggal' => optional($s->tanggal_peminjam)->format('d M Y') ?? '-',
                'Status' => ucfirst($s->status ?? '-'),
                'Penginput' => $s->penginput->name ?? '-',
            ];
            $totals = ['Nominal Pinjaman' => $this->rupiah($s->nominal)];
        } elseif ($s instanceof Pengembalian) {
            $jenis = 'Pengembalian';
            $rows = [
                'ID Transaksi' => $s->id_transaksi,
                'Nama' => $s->nama_pengembalian,
                'Tanggal' => optional($s->tanggal_pengembalian)->format('d M Y') ?? '-',
                'Status' => ucfirst($s->status ?? '-'),
                'Penginput' => $s->penginput->name ?? '-',
            ];
            $totals = ['Nominal Pengembalian' => $this->rupiah($s->nominal)];
        } elseif ($s instanceof Spending) {
            $isPembelianAkun = $s->jenis_pengeluaran === 'pembelian_akun';
            $jenis = $isPembelianAkun ? 'Pengeluaran Pembelian Akun' : 'Pengeluaran Lainnya';

            $rows = [
                'ID Transaksi' => $s->id_transaksi,
                'Jenis Pengeluaran' => $isPembelianAkun ? 'Pembelian Akun' : 'Lainnya',
                'Tanggal' => optional($s->tanggal_transaksi)->format('d M Y') ?? '-',
                'Status' => ucfirst($s->status ?? '-'),
            ];

            // PIC pembeli hanya ditampilkan untuk pengeluaran pembelian akun
            if ($isPembelianAkun) {
                $rows['PIC Pembeli'] = $s->picPembeli->name ?? '-';
            }
            $rows['PIC Penginput'] = $s->penginput->name ?? '-';

            $totals = ['Nominal Pengeluaran' => $this->rupiah($s->nominal)];
        } elseif ($s instanceof PemesananRsc) {
            $jenis = 'Pesanan Rumah Scopus';
            $rows = [
                'ID Transaksi' => $s->id_transaksi,
                'Camp' => $s->nama_camp.' (Batch '.$s->batch_camp.')',
                'Pembeli' => $s->nama_pembeli,
                'Telp' => $s->telp_pembeli,
                'Jumlah' => $s->jumlah_pemesanan,
                'Tanggal Pesan' => optional($s->tanggal_pemesanan)->format('d M Y') ?? '-',
                'PIC' => $s->pic,
                'Status' => ucfirst($s->status ?? '-'),
            ];
            $totals = [
                'Harga Satuan' => $this->rupiah($s->harga_satuan),
                'Total' => $this->rupiah($s->total),
            ];
        }

        return [
            'jenis' => $jenis,
            'rows' => $rows,
            'items' => $items,
            'totals' => $totals,
            'itemsNote' => $itemsNote,
        ];
    }

    protected function rupiah($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
