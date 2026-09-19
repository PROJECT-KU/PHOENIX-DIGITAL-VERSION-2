<?php

namespace App\Console\Commands;

use App\Models\ProductReview;
use Illuminate\Console\Command;

/**
 * Buang permanen ulasan produk yang sudah lama berada di Arsip.
 *
 * Arsip adalah jaring pengaman untuk penghapusan yang keliru, bukan gudang
 * permanen: tanpa pembersih ini isinya tumbuh diam-diam.
 */
class BersihkanArsipUlasan extends Command
{
    protected $signature = 'ulasan:bersihkan-arsip
        {--hari=90 : Buang yang sudah diarsipkan lebih lama dari sekian hari}
        {--kering : Tampilkan daftar saja, jangan hapus}';

    protected $description = 'Buang permanen ulasan produk yang lama diarsipkan';

    public function handle(): int
    {
        $hari = max(1, (int) $this->option('hari'));
        $kering = (bool) $this->option('kering');

        $daftar = ProductReview::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($hari))
            ->get();

        if ($daftar->isEmpty()) {
            $this->info("Tidak ada arsip ulasan yang lebih tua dari {$hari} hari.");

            return self::SUCCESS;
        }

        foreach ($daftar as $u) {
            // Nama & isi ulasan TIDAK ditulis ke keluaran: perintah ini bisa
            // berjalan terjadwal dan keluarannya tersimpan di berkas cron.
            $this->line(($kering ? '[kering] ' : '').'Buang ulasan '.$u->getKey().' (diarsipkan '.$u->deleted_at?->format('d/m/Y').')');

            if (! $kering) {
                $u->forceDelete();
            }
        }

        $this->info(($kering ? 'Akan dibuang: ' : 'Dibuang: ').$daftar->count().' ulasan.');

        return self::SUCCESS;
    }
}
