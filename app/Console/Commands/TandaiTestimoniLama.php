<?php

namespace App\Console\Commands;

use App\Models\Testimoni;
use App\Models\TestimoniRiwayat;
use Illuminate\Console\Command;

/**
 * Sekali jalan: beri jejak pada testimoni yang sudah disetujui/ditolak SEBELUM
 * pencatatan jejak ada (19 Sep 2026).
 *
 * Waktunya PERKIRAAN dari updated_at — karena itu kolom ditinjau_at sengaja
 * dibiarkan kosong, supaya jendela detail tetap jujur menulis "Tidak tercatat"
 * alih-alih memajang jam yang sebenarnya tidak pernah kita ketahui.
 */
class TandaiTestimoniLama extends Command
{
    protected $signature = 'testimoni:tandai-lama
        {--kering : Tampilkan jumlahnya saja, jangan tulis}';

    protected $description = 'Beri jejak perkiraan pada testimoni yang diputuskan sebelum jejak moderasi ada';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');

        $daftar = Testimoni::withTrashed()
            ->whereIn('status', ['active', 'non-active'])
            ->whereNull('ditinjau_at')
            ->whereDoesntHave('riwayat')
            ->get();

        if ($daftar->isEmpty()) {
            $this->info('Tidak ada testimoni lama yang perlu ditandai.');

            return self::SUCCESS;
        }

        $this->info(($kering ? 'Akan ditandai: ' : 'Ditandai: ').$daftar->count().' testimoni.');

        if ($kering) {
            return self::SUCCESS;
        }

        foreach ($daftar as $t) {
            TestimoniRiwayat::create([
                'testimoni_id' => $t->getKey(),
                'user_id' => null,
                'aksi' => $t->status === 'active' ? 'disetujui' : 'ditolak',
                'keterangan' => 'Perkiraan dari waktu perubahan terakhir — diputuskan sebelum jejak dicatat',
                'created_at' => $t->updated_at ?? $t->created_at,
            ]);
        }

        return self::SUCCESS;
    }
}
