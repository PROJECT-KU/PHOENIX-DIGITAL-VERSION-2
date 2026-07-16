<?php

namespace App\Actions\Finance;

use App\Models\DataAkun;
use App\Models\PemesananRsc;
use Illuminate\Support\Carbon;

/**
 * Catat biaya MODAL akun PRIVATE untuk satu batch Rumah Scopus (RSC) sebagai
 * expense di cash flow — sejajar dengan SyncOrderPrivateCostAction untuk Order.
 *
 * Rumah Scopus = model patungan: SATU akun dipakai banyak peserta dalam satu
 * batch. Jadi modal dicatat PER BATCH (satu akun = satu modal), memakai baris
 * REPRESENTATIF batch (sama seperti pemasukannya).
 *
 * Harga modal dari katalog Harga Modal (ProductModalPrice) memakai durasi camp
 * dan harga yang BERLAKU pada tanggal pemesanan — satu sumber & pola sama dgn
 * modal private Order.
 *
 * Baris cash flow-nya terpisah dari pemasukan RSC (kategori beda), dikelola
 * lewat relasi cashFlowModal() yang sudah dibatasi ke 'Modal Akun Private',
 * jadi tidak saling menimpa dengan sync pemasukan.
 *
 * Idempoten: dipanggil ulang aman. Bila tak layak dicatat (akun bukan private,
 * status bukan 'baru', atau harga katalog belum ada) → baris modalnya dihapus.
 */
class SyncRscPrivateCostAction
{
    /** Kategori cash flow yg SAMA dengan modal private Order — supaya fitur Modal ikut membacanya. */
    private const KATEGORI = 'Modal Akun Private';

    /**
     * @param  PemesananRsc  $rep  baris representatif batch (yang memegang cash flow)
     */
    public function execute(PemesananRsc $rep): void
    {
        $modal = $this->hitungModal($rep);

        if ($modal <= 0) {
            $rep->cashFlowModal()->delete();

            return;
        }

        $akun = DataAkun::with('product')->find($rep->akun);
        $namaProduk = optional(optional($akun)->product)->nama_akun ?? 'Akun Private';

        $rep->cashFlowModal()->updateOrCreate(
            ['sourceable_id' => $rep->id, 'sourceable_type' => PemesananRsc::class],
            [
                'amount' => $modal,
                'type' => 'expense',
                'transaction_date' => $rep->tanggal_pemesanan,
                'category' => self::KATEGORI,
                'description' => 'Modal RSC '.$namaProduk.' - '.$rep->nama_camp.' Batch '.$rep->batch_camp,
            ]
        );
    }

    /** Hapus baris modal batch ini (dipakai saat batch/peserta dihapus). */
    public function delete(PemesananRsc $rep): void
    {
        $rep->cashFlowModal()->delete();
    }

    /**
     * Modal batch = harga modal satuan (katalog) untuk durasi camp.
     * 0 bila: akun tak tertaut produk, produk bukan private, status bukan 'baru',
     * atau durasi tidak ada di katalog.
     */
    private function hitungModal(PemesananRsc $rep): float
    {
        if ($rep->status !== 'baru') {
            return 0;
        }

        $akun = DataAkun::with('product')->find($rep->akun);
        $product = optional($akun)->product;

        if (! $product || $product->tipe_akun !== 'private') {
            return 0;
        }

        $mulai = Carbon::parse($rep->tanggal_pemesanan);
        $akhir = $rep->tanggal_berakhir ? Carbon::parse($rep->tanggal_berakhir) : $mulai->copy();
        $bulan = max(1, (int) round($mulai->diffInDays($akhir) / 30));

        return (float) $product->modalSatuan($bulan, 'bulan', $mulai->toDateString());
    }
}
