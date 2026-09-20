<?php

namespace App\Console\Commands;

use App\Models\CustomerMessage;
use Illuminate\Console\Command;

/**
 * Buang permanen tiket helpdesk yang sudah lama berada di Arsip.
 *
 * Arsip adalah jaring pengaman untuk penghapusan yang keliru, bukan gudang
 * permanen. Spam dibuang lebih cepat daripada tiket biasa: tidak ada gunanya
 * menyimpan iklan judi selama setengah tahun.
 */
class BersihkanArsipHelpdesk extends Command
{
    protected $signature = 'helpdesk:bersihkan-arsip
        {--hari=180 : Buang tiket arsip biasa yang lebih tua dari sekian hari}
        {--hari-spam=30 : Buang tiket spam yang lebih tua dari sekian hari}
        {--kering : Tampilkan daftar saja, jangan hapus}';

    protected $description = 'Buang permanen tiket helpdesk yang lama diarsipkan';

    public function handle(): int
    {
        $hari = max(1, (int) $this->option('hari'));
        $hariSpam = max(1, (int) $this->option('hari-spam'));
        $kering = (bool) $this->option('kering');

        $daftar = CustomerMessage::onlyTrashed()
            ->with('lampiran')
            ->where(function ($q) use ($hari, $hariSpam) {
                $q->where(fn ($s) => $s->where('is_spam', false)->where('deleted_at', '<', now()->subDays($hari)))
                    ->orWhere(fn ($s) => $s->where('is_spam', true)->where('deleted_at', '<', now()->subDays($hariSpam)));
            })
            ->get();

        if ($daftar->isEmpty()) {
            $this->info("Tidak ada arsip helpdesk yang perlu dibuang (biasa > {$hari} hari, spam > {$hariSpam} hari).");

            return self::SUCCESS;
        }

        foreach ($daftar as $pesan) {
            // Nama & isi pesan TIDAK ditulis ke keluaran: perintah ini berjalan
            // terjadwal dan keluarannya tersimpan di berkas cron.
            $this->line(($kering ? '[kering] ' : '').'Buang '.$pesan->ticket
                .($pesan->is_spam ? ' (spam)' : '')
                .' — diarsipkan '.$pesan->deleted_at?->format('d/m/Y'));

            if (! $kering) {
                // Berkas fisiknya ikut dibuang; barisnya hilang lewat cascade.
                $pesan->lampiran->each->hapusBerkas();
                $pesan->forceDelete();
            }
        }

        $this->info(($kering ? 'Akan dibuang: ' : 'Dibuang: ').$daftar->count().' tiket.');

        return self::SUCCESS;
    }
}
