<?php

namespace App\Console\Commands;

use App\Models\CustomerMessage;
use Illuminate\Console\Command;

/**
 * Tutup otomatis tiket yang sudah lama berstatus "Selesai".
 *
 * Selesai berarti jawabannya sudah dikirim tapi pintunya masih terbuka kalau
 * pelanggan membalas lagi. Setelah sekian hari tanpa kelanjutan, tiketnya
 * ditutup supaya papan antrean memperlihatkan pekerjaan yang benar-benar hidup.
 */
class TutupTiketSelesai extends Command
{
    protected $signature = 'helpdesk:tutup-selesai
        {--hari= : Tutup yang berstatus Selesai lebih lama dari sekian hari}
        {--kering : Tampilkan daftar saja, jangan tutup}';

    protected $description = 'Tutup tiket helpdesk yang sudah lama selesai';

    public function handle(): int
    {
        $hari = (int) ($this->option('hari') ?: config('helpdesk.tutup_setelah_hari', 7));
        $kering = (bool) $this->option('kering');

        $daftar = CustomerMessage::where('status', 'resolved')
            ->where('updated_at', '<', now()->subDays($hari))
            ->get();

        if ($daftar->isEmpty()) {
            $this->info("Tidak ada tiket selesai yang lebih tua dari {$hari} hari.");

            return self::SUCCESS;
        }

        foreach ($daftar as $pesan) {
            $this->line(($kering ? '[kering] ' : '').'Tutup '.$pesan->ticket);

            if (! $kering) {
                $pesan->update(['status' => 'closed']);
                $pesan->catat('status', 'Selesai → Ditutup (otomatis setelah '.$hari.' hari)');
            }
        }

        $this->info(($kering ? 'Akan ditutup: ' : 'Ditutup: ').$daftar->count().' tiket.');

        return self::SUCCESS;
    }
}
