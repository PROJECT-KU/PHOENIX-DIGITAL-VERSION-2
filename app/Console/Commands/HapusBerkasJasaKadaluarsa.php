<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus BERKAS jasa pengecekan (unggahan customer + hasil admin) 7 hari SETELAH
 * link /cek kedaluwarsa — yakni: kuota habis → +24 jam link mati → +7 hari berkas
 * dihapus. Tujuannya hemat storage; dokumen pribadi (skripsi/naskah) tidak
 * disimpan lebih lama dari perlu.
 *
 * Yang dihapus HANYA berkas fisiknya. Baris pengecekan (persentase, metadata)
 * TETAP disimpan untuk riwayat; kolom path di-null-kan supaya UI tak lagi
 * menawarkan unduhan yang berkasnya sudah tiada.
 *
 * Aditif: tidak mengubah kuota, expiry, atau alur pesanan mana pun. Pesanan yang
 * kuotanya belum habis (mis. beli 5×, sisa 3) tak tersentuh — berkasHarusDihapus()
 * baru true setelah semua kuota terpakai lalu lewat 24 jam + 7 hari.
 */
class HapusBerkasJasaKadaluarsa extends Command
{
    protected $signature = 'jasa:hapus-berkas-kadaluarsa
                            {--dry-run : Tampilkan saja, jangan hapus}';

    protected $description = 'Hapus berkas jasa (unggahan customer + hasil admin) 7 hari setelah link /cek kedaluwarsa';

    /** Kolom path berkas fisik di OrderUpload yang harus dibersihkan. */
    private const PATH_COLUMNS = ['path', 'pdf_path', 'hasil_path', 'hasil_ai_path', 'hasil_docx_path'];

    public function handle(): int
    {
        $simulasi = (bool) $this->option('dry-run');
        $disk = Storage::disk('local');

        // Kandidat: pesanan yang punya unggahan (non-batal) MASIH berberkas, dan
        // unggahan itu sudah > 8 hari (24 jam + 7 hari — batas paling awal berkas
        // bisa dihapus). Penentu akhir tetap berkasHarusDihapus() di PHP: kuota
        // benar-benar habis DAN sudah lewat 24 jam + 7 hari.
        $batasKandidat = now()->subDays(8);

        $orders = Order::whereHas('uploads', function ($q) use ($batasKandidat) {
            $q->where('status', '!=', 'dibatalkan')
                ->where('created_at', '<', $batasKandidat)
                ->where(function ($x) {
                    foreach (self::PATH_COLUMNS as $col) {
                        $x->orWhereNotNull($col);
                    }
                });
        })
            ->with(['uploads', 'items.product'])
            ->get();

        $orderTerproses = 0;
        $berkasTerhapus = 0;

        foreach ($orders as $order) {
            if (! $order->berkasHarusDihapus()) {
                continue;
            }

            $adaBerkas = false;
            foreach ($order->uploads as $up) {
                $ubah = [];
                foreach (self::PATH_COLUMNS as $col) {
                    $path = $up->{$col};
                    if (! $path) {
                        continue;
                    }
                    $adaBerkas = true;

                    if ($simulasi) {
                        $berkasTerhapus++;

                        continue;
                    }

                    if ($disk->exists($path)) {
                        $disk->delete($path);
                    }
                    $berkasTerhapus++;
                    $ubah[$col] = null;
                }

                if (! $simulasi && $ubah) {
                    $up->forceFill($ubah)->save();
                }
            }

            if ($adaBerkas) {
                $orderTerproses++;
            }
        }

        $kata = $simulasi ? 'AKAN dihapus' : 'dihapus';
        $this->info("{$orderTerproses} pesanan diproses, {$berkasTerhapus} berkas {$kata} (7 hari setelah link /cek kedaluwarsa).");

        return self::SUCCESS;
    }
}
