<?php

namespace App\Livewire\Pages\Admin\CashFlow;

use App\Actions\Finance\SyncRscPrivateCostAction;
use App\Models\CashFlow;
use App\Models\GajiKaryawans;
use App\Models\Loan;
use App\Models\Order;
use App\Models\PemesananRsc;
use App\Models\Pengembalian;
use App\Models\Product;
use App\Models\Spending;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
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
                'Metode Bayar' => $this->labelMetode($s->payment_method),
                'Tanggal Bayar' => $this->tanggalBayar($s),
            ];

            foreach ($s->items as $it) {
                // Add-on yang dibeli pada item ini (mis. cek plagiasi pada cek AI,
                // atau target parafrase) — ditampilkan di bawah nama produk.
                $addons = collect($it->addons ?? [])->map(fn ($a) => [
                    'nama' => $a['nama'] ?? '-',
                    'harga' => $this->rupiah((int) ($a['harga'] ?? 0)),
                ])->all();

                /*
                 * order_items.subtotal SUDAH termasuk harga add-on. Kalau baris
                 * induk ditampilkan apa adanya lalu add-on ditulis lagi di
                 * bawahnya, admin melihat 13.000 + 500 tapi Subtotal 13.000 —
                 * seolah ada yang hilang. Jadi baris induk menampilkan porsinya
                 * SENDIRI (subtotal - add-on), sehingga menjumlah tepat.
                 */
                $addonsTotal = (int) ($it->addons_total ?? 0);
                $subtotalInduk = (int) $it->subtotal - $addonsTotal;

                $items[] = [
                    'nama' => $it->product_name,
                    'durasi' => trim($it->duration_value.' '.$it->duration_type),
                    'qty' => $it->quantity,
                    'harga' => $this->rupiah($it->price),
                    'subtotal' => $this->rupiah($subtotalInduk),
                    'addons' => $addons,
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
                'Status' => ucfirst(Loan::statusMap()[$s->nama_peminjam] ?? 'pending'),
                'Penginput' => $s->penginput->name ?? '-',
            ];
            $totals = ['Nominal Pinjaman' => $this->rupiah($s->nominal)];
        } elseif ($s instanceof Pengembalian) {
            $jenis = 'Pengembalian';
            $rows = [
                'ID Transaksi' => $s->id_transaksi,
                'Nama' => $s->nama_pengembalian,
                'Tanggal' => optional($s->tanggal_pengembalian)->format('d M Y') ?? '-',
                'Status' => ucfirst(Loan::statusMap()[$s->nama_pengembalian] ?? 'pending'),
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
            // Satu baris peserta bisa punya DUA cash flow: pemasukan penjualan dan
            // modal akun private. Keduanya sampai ke sini, jadi angkanya harus
            // dibedakan — kalau tidak, baris modal ikut memamerkan angka penjualan.
            $isModal = $this->cashFlow->category === 'Modal Akun Private';
            $jenis = $isModal ? 'Modal Akun Private (Rumah Scopus)' : 'Pesanan Rumah Scopus';

            // Cash flow RSC dicatat per BATCH (bukan per peserta), jadi rinciannya
            // menggambarkan batch: berapa peserta & berapa akun yang dipakai.
            $jumlahPeserta = PemesananRsc::where('nama_camp', $s->nama_camp)
                ->where('batch_camp', $s->batch_camp)
                ->count();
            $jumlahAkun = 1 + \App\Models\RscBatchAkun::where('nama_camp', $s->nama_camp)
                ->where('batch_camp', $s->batch_camp)
                ->count();

            $rows = [
                'ID Transaksi' => $s->id_transaksi,
                'Camp' => $s->nama_camp.' (Batch '.$s->batch_camp.')',
                // Baris ini mewakili SATU peserta; batch bisa berisi banyak.
                'Pembeli' => $jumlahPeserta > 1
                    ? $s->nama_pembeli.' (+'.($jumlahPeserta - 1).' peserta lain)'
                    : $s->nama_pembeli,
                'Telp' => $s->telp_pembeli,
                'Metode Harga' => $s->metode_harga === 'per_akun' ? 'Per akun' : 'Per peserta',
                // jumlah_pemesanan sebenarnya JUMLAH BULAN camp (dipakai addMonths()
                // saat menghitung tanggal berakhir) — dulu dilabeli "Jumlah" begitu
                // saja sehingga terbaca seperti kuantitas.
                'Durasi' => $s->jumlah_pemesanan.' bulan',
                'Jumlah Peserta' => $jumlahPeserta.' orang',
                'Jumlah Akun' => $jumlahAkun.' akun (utama + tambahan)',
                // tanggal_pemesanan TIDAK di-cast date di model PemesananRsc (masih
                // string mentah dari DB), jadi optional(...)->format() memulangkan
                // null → tampil "-". Diparse dulu supaya tanggal aslinya muncul.
                'Tanggal Pesan' => $s->tanggal_pemesanan
                    ? Carbon::parse($s->tanggal_pemesanan)->translatedFormat('d M Y')
                    : '-',
                // Kolom `pic` menyimpan ID user, bukan nama — ambil lewat relasi.
                'PIC' => optional($s->users)->name ?? '-',
                'Status' => ucfirst($s->status ?? '-'),
            ];
            // Baris cash flow ini milik SATU BATCH, jadi angkanya harus angka
            // batch — bukan angka baris peserta yang kebetulan jadi representatif.
            // SUM(total) sebatch inilah yang dipakai saat baris cash flow ditulis
            // (lihat syncRscBatchCashFlow), sehingga totalnya pasti sama.
            $totalBatch = (int) PemesananRsc::where('nama_camp', $s->nama_camp)
                ->where('batch_camp', $s->batch_camp)
                ->sum('total');

            if ($isModal) {
                [$items, $totals, $itemsNote] = $this->rincianModalRsc($s);
            } elseif ($s->metode_harga === 'per_akun') {
                // Mode per akun: grand total = durasi x harga SEMUA akun, lalu
                // dibagi rata ke tiap peserta. Jadi total & harga_satuan baris
                // peserta cuma pecahan — tak bermakna sendirian. Yang bermakna:
                // harga gabungan semua akun per bulannya.
                $bulan = max(1, (int) $s->jumlah_pemesanan);
                $totals = [
                    'Harga Semua Akun (per bulan)' => $this->rupiah(intdiv($totalBatch, $bulan)),
                    'Durasi' => $bulan.' bulan',
                    'Total Pemasukan' => $this->rupiah($totalBatch),
                ];
            } else {
                // Mode per peserta: tiap peserta bayar harga_satuan x durasi.
                $totals = [
                    'Harga Satuan (per bulan)' => $this->rupiah($s->harga_satuan),
                    'Total per Peserta' => $this->rupiah($s->total),
                    'Total Pemasukan ('.$jumlahPeserta.' peserta)' => $this->rupiah($totalBatch),
                ];
            }
        }

        return [
            'jenis' => $jenis,
            'rows' => $rows,
            'items' => $items,
            'totals' => $totals,
            'itemsNote' => $itemsNote,
        ];
    }

    /**
     * Rincian baris MODAL akun private sebuah batch RSC.
     *
     * Sengaja memakai action yang sama dengan penulis baris cash flow-nya
     * (SyncRscPrivateCostAction), bukan menghitung ulang di sini — supaya
     * rincian yang ditampilkan tidak mungkin berbeda dari nominal barisnya.
     *
     * @return array{0: array, 1: array, 2: ?string} [items, totals, itemsNote]
     */
    protected function rincianModalRsc(PemesananRsc $s): array
    {
        $rincian = app(SyncRscPrivateCostAction::class)->rincianModal($s);

        $nama = Product::whereIn('id', array_column($rincian, 'product_id'))
            ->pluck('nama_akun', 'id');

        $items = [];
        foreach ($rincian as $r) {
            $items[] = [
                'nama' => ($nama[$r['product_id']] ?? 'Produk').' (private)',
                'durasi' => $r['durasi_value'].' '.$r['durasi_type'],
                'qty' => $r['jumlah'],
                'harga' => $this->rupiah($r['satuan']),
                'subtotal' => $this->rupiah($r['total']),
            ];
        }

        $totals = ['Total Modal' => $this->rupiah($this->cashFlow->amount)];

        $itemsNote = $s->metode_harga === 'per_akun'
            ? 'Modal dihitung per AKUN private yang dipakai batch ini (utama + tambahan), memakai harga katalog modal saat tanggal pemesanan.'
            : 'Modal dihitung per PESERTA (1 peserta = 1 akun), memakai harga katalog modal saat tanggal pemesanan.';

        return [$items, $totals, $itemsNote];
    }

    protected function rupiah($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    /** Label metode bayar yang rapi (tanpa underscore). */
    protected function labelMetode(?string $m): string
    {
        return match ($m) {
            'transfer' => 'Transfer Bank',
            'qris_statis' => 'QRIS Statis',
            'qris_dinamis' => 'QRIS Dinamis',
            default => $m ? ucwords(str_replace('_', ' ', $m)) : '-',
        };
    }

    /**
     * Tanggal bayar = saat pesanan dibayar (paid_at). Bila kosong, pakai tanggal
     * transaksi cash flow (tanggal uang tercatat masuk / pesanan completed).
     */
    protected function tanggalBayar(Order $order): string
    {
        $tgl = $order->paid_at ?: $this->cashFlow->transaction_date;

        return $tgl ? \Illuminate\Support\Carbon::parse($tgl)->format('d M Y H:i') : '-';
    }
}
