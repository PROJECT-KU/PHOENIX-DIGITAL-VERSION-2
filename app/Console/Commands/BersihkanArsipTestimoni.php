<?php

namespace App\Console\Commands;

use App\Models\Testimoni;
use Illuminate\Console\Command;

/**
 * Buang permanen testimoni yang sudah lama berada di Arsip.
 *
 * Arsip adalah jaring pengaman untuk penghapusan yang keliru, bukan gudang
 * permanen: tanpa pembersih ini isinya tumbuh diam-diam dan foto-fotonya ikut
 * menumpuk di disk. Berkas fotonya dihapus di sini — pada penghapusan biasa
 * foto sengaja DIPERTAHANKAN supaya pemulihan tidak kehilangan gambar.
 */
class BersihkanArsipTestimoni extends Command
{
    protected $signature = 'testimoni:bersihkan-arsip
        {--hari=90 : Buang yang sudah diarsipkan lebih lama dari sekian hari}
        {--kering : Tampilkan daftar saja, jangan hapus}';

    protected $description = 'Buang permanen testimoni yang lama diarsipkan, berikut fotonya';

    public function handle(): int
    {
        $hari = max(1, (int) $this->option('hari'));
        $kering = (bool) $this->option('kering');

        $daftar = Testimoni::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($hari))
            ->get();

        if ($daftar->isEmpty()) {
            $this->info("Tidak ada arsip testimoni yang lebih tua dari {$hari} hari.");

            return self::SUCCESS;
        }

        foreach ($daftar as $t) {
            // Nama pengirim TIDAK ditulis ke keluaran/log: perintah ini bisa
            // berjalan terjadwal dan keluarannya tersimpan di berkas cron.
            $this->line(($kering ? '[kering] ' : '').'Buang testimoni '.$t->getKey().' (diarsipkan '.$t->deleted_at?->format('d/m/Y').')');

            if ($kering) {
                continue;
            }

            if ($t->foto) {
                $berkas = storage_path('app/public/img/testimoni/'.$t->foto);
                if (file_exists($berkas)) {
                    unlink($berkas);
                }
            }

            $t->forceDelete();
        }

        $this->info(($kering ? 'Akan dibuang: ' : 'Dibuang: ').$daftar->count().' testimoni.');

        return self::SUCCESS;
    }
}
