<?php

namespace App\Actions\Finance;

use App\Models\DataAkun;
use App\Models\PemesananRsc;
use App\Models\RscBatchAkun;
use Illuminate\Support\Carbon;

/**
 * Catat biaya MODAL akun PRIVATE untuk satu batch Rumah Scopus (RSC) sebagai
 * expense di cash flow — sejajar dengan SyncOrderPrivateCostAction untuk Order.
 *
 * ===================== JUMLAH AKUN (kunci hitungan) =====================
 * Modal = JUMLAH AKUN PRIVATE yang benar-benar disediakan × harga modal katalog.
 * Jumlah akunnya beda menurut metode harga:
 *
 *   per_peserta : 1 peserta = 1 akun. Jumlah akun = JUMLAH PESERTA (baris batch),
 *                 semuanya memakai produk akun UTAMA.
 *   per_akun    : jumlah akun = akun UTAMA + akun TAMBAHAN (RscBatchAkun).
 *                 TIAP akun dinilai sesuai produknya sendiri (batch boleh campur
 *                 private & sharing); yang sharing dilewati.
 *
 * SHARING tidak pernah dicatat di sini — modalnya lewat Pengeluaran (pembelian
 * akun), mencatat ulang = dobel. Yang dicatat cuma akun PRIVATE.
 *
 * Harga modal dari katalog Harga Modal (ProductModalPrice) memakai DURASI camp
 * (kolom jumlah_pemesanan = jumlah bulan) & harga yang BERLAKU pada tanggal
 * pemesanan — satu sumber & pola sama dgn modal private Order.
 * ========================================================================
 *
 * Baris cash flow-nya terpisah dari pemasukan RSC (kategori beda), dikelola
 * lewat relasi cashFlowModal(); idempoten (aman dipanggil berulang).
 */
class SyncRscPrivateCostAction
{
    /** Kategori cash flow yg SAMA dgn modal private Order — supaya fitur Modal ikut membacanya. */
    private const KATEGORI = 'Modal Akun Private';

    /**
     * Catat/segarkan baris modal untuk batch (pakai baris representatif).
     */
    public function execute(PemesananRsc $rep): void
    {
        $total = array_sum($this->modalPerProduk($rep));

        if ($total <= 0) {
            $rep->cashFlowModal()->delete();

            return;
        }

        $rep->cashFlowModal()->updateOrCreate(
            ['sourceable_id' => $rep->id, 'sourceable_type' => PemesananRsc::class],
            [
                'amount' => $total,
                'type' => 'expense',
                'transaction_date' => $rep->tanggal_pemesanan,
                'category' => self::KATEGORI,
                'description' => 'Modal RSC - '.$rep->nama_camp.' Batch '.$rep->batch_camp
                    .' ('.($rep->metode_harga === 'per_akun' ? 'per akun' : 'per peserta').')',
            ]
        );
    }

    /** Hapus baris modal batch ini (dipakai saat batch/peserta dihapus). */
    public function delete(PemesananRsc $rep): void
    {
        $rep->cashFlowModal()->delete();
    }

    /**
     * Modal akun PRIVATE batch ini, DIPECAH per produk.
     *
     * Dipakai bersama:
     *  - execute()                       → dijumlah jadi satu baris cash flow
     *  - CashFlowList::modalRscPrivate() → diakumulasi per produk untuk omset
     *
     * Return kosong bila status bukan 'baru' atau tidak ada akun private.
     *
     * @param  PemesananRsc  $rep  baris representatif batch
     * @return array<string,float>  product_id => total modal
     */
    public function modalPerProduk(PemesananRsc $rep): array
    {
        $peta = [];
        foreach ($this->rincianModal($rep) as $r) {
            $peta[$r['product_id']] = ($peta[$r['product_id']] ?? 0) + $r['total'];
        }

        return $peta;
    }

    /**
     * RINCIAN modal private batch — dipecah per (produk, durasi, harga satuan),
     * lengkap dengan jumlah akun. Dipakai fitur Modal untuk mengisi kolom
     * Durasi / Modal Satuan / Order pada tabel Rincian (bukan cuma totalnya).
     *
     * modalPerProduk() adalah ringkasan dari sini, jadi angkanya pasti sejalan.
     *
     * @param  PemesananRsc  $rep  baris representatif batch
     * @return array<int, array{product_id:string,durasi_value:int,durasi_type:string,satuan:float,jumlah:int,total:float}>
     */
    public function rincianModal(PemesananRsc $rep): array
    {
        if ($rep->status !== 'baru') {
            return [];
        }

        // Durasi dari jumlah_pemesanan (jumlah bulan camp). Fallback ke selisih
        // tanggal bila kosong, supaya tetap dapat angka yang masuk akal.
        $bulan = (int) $rep->jumlah_pemesanan;
        if ($bulan < 1) {
            $mulai = Carbon::parse($rep->tanggal_pemesanan);
            $akhir = $rep->tanggal_berakhir ? Carbon::parse($rep->tanggal_berakhir) : $mulai->copy();
            $bulan = max(1, (int) round($mulai->diffInDays($akhir) / 30));
        }
        $asOf = Carbon::parse($rep->tanggal_pemesanan)->toDateString();

        $rincian = [];

        // Tambahkan N akun private dari satu Data Akun ke rincian.
        // Dikelompokkan per (produk|durasi|satuan) supaya kolom Durasi & Modal
        // Satuan punya arti; kalau harga katalog beda, barisnya juga terpisah.
        $tambahAkun = function (?string $akunId, int $qty) use (&$rincian, $bulan, $asOf) {
            $akun = $akunId ? DataAkun::with('product')->find($akunId) : null;
            $product = optional($akun)->product;
            if (! $product || $product->tipe_akun !== 'private' || $qty < 1) {
                return;
            }

            $satuan = (float) $product->modalSatuan($bulan, 'bulan', $asOf);
            if ($satuan <= 0) {
                return;
            }

            $k = $product->id.'|'.$bulan.'|'.$satuan;
            if (! isset($rincian[$k])) {
                $rincian[$k] = [
                    'product_id' => (string) $product->id,
                    'durasi_value' => $bulan,
                    'durasi_type' => 'bulan',
                    'satuan' => $satuan,
                    'jumlah' => 0,
                    'total' => 0.0,
                ];
            }
            $rincian[$k]['jumlah'] += $qty;
            $rincian[$k]['total'] += $satuan * $qty;
        };

        if ($rep->metode_harga === 'per_akun') {
            // Akun utama + tiap akun tambahan, dinilai per produknya sendiri.
            //
            // SENGAJA TIDAK di-dedup: sisi PENJUALAN (sumHargaAkun() di form RSC)
            // juga menjumlah per ENTRI akun, bukan per akun unik. Kalau modal
            // di-dedup sementara penjualan tidak, batch yang menjual 2 entri akun
            // hanya dihitung modal 1 akun → untung terlihat lebih besar dari
            // kenyataan. Jadi modal mengikuti cara penjualan menghitung.
            $akunIds = RscBatchAkun::where('nama_camp', $rep->nama_camp)
                ->where('batch_camp', $rep->batch_camp)
                ->pluck('akun_id')
                ->prepend($rep->akun)
                ->filter();

            foreach ($akunIds as $akunId) {
                $tambahAkun($akunId, 1);
            }
        } else {
            // per_peserta: hitungan PER KEPALA. Modal = harga katalog akun UTAMA
            // × jumlah peserta. Akun TAMBAHAN SENGAJA DIABAIKAN (baik private
            // maupun sharing) — karena biayanya dihitung per orang, bukan per
            // akun; berapa pun akun tambahan tidak menambah modal. (Sisi PENJUALAN
            // per_peserta juga tak terpengaruh akun tambahan: "tidak memengaruhi
            // harga".) Untuk memodalkan tiap akun fisik, pakai mode per_akun.
            $jumlahPeserta = PemesananRsc::where('nama_camp', $rep->nama_camp)
                ->where('batch_camp', $rep->batch_camp)
                ->count();
            $tambahAkun($rep->akun, max(1, $jumlahPeserta));
        }

        return array_values($rincian);
    }
}
