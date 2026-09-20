<?php

namespace App\Console\Commands;

use App\Models\CustomerMessage;
use App\Models\User;
use App\Notifications\TiketLewatBatas;
use Illuminate\Console\Command;

/**
 * Pengingat harian untuk tiket helpdesk yang lewat batas waktu membalas.
 *
 * Penanda merah di layar hanya terlihat kalau layarnya dibuka; ini yang
 * membuat tiket menggantung tetap ketahuan.
 */
class IngatkanTiketLewatBatas extends Command
{
    protected $signature = 'helpdesk:ingatkan-lewat-batas {--kering : Tampilkan saja, jangan kirim}';

    protected $description = 'Kirim pengingat tiket helpdesk yang lewat batas waktu membalas';

    public function handle(): int
    {
        $tiket = CustomerMessage::query()->bukanSpam()->lewatBatas()
            ->orderBy('created_at')
            ->get(['id', 'ticket', 'name', 'priority', 'kategori', 'assigned_to', 'created_at']);

        if ($tiket->isEmpty()) {
            $this->info('Tidak ada tiket yang lewat batas. Antrean bersih.');

            return self::SUCCESS;
        }

        // Yang memegang tiket dapat rekap miliknya sendiri; sisanya (tiket tanpa
        // petugas) jadi tanggung jawab semua yang boleh menangani helpdesk.
        $perPetugas = $tiket->whereNotNull('assigned_to')->groupBy('assigned_to');
        $tanpaPetugas = $tiket->whereNull('assigned_to');

        foreach ($perPetugas as $userId => $miliknya) {
            $petugas = User::find($userId);
            $this->line('Petugas '.($petugas?->name ?? $userId).': '.$miliknya->count().' tiket');

            if ($petugas && ! $this->option('kering')) {
                $petugas->notify(new TiketLewatBatas($miliknya));
            }
        }

        if ($tanpaPetugas->isNotEmpty()) {
            // Dikelompokkan per TOPIK: komplain pembayaran tidak perlu
            // membanjiri semua orang, cukup pemegang izin yang relevan.
            $rute = config('helpdesk.penanggung_topik', []);

            foreach ($tanpaPetugas->groupBy(fn ($t) => $rute[$t->kategori] ?? 'edit_customer_message') as $izin => $baris) {
                $penanggung = User::query()
                    ->where('status', 'active')
                    ->whereHas('role.permissions', fn ($q) => $q->where('name', $izin))
                    ->get();

                // Tidak ada pemegang izin khusus itu? Jangan sampai tiketnya
                // hilang dari radar — kembalikan ke penanggung umum helpdesk.
                if ($penanggung->isEmpty() && $izin !== 'edit_customer_message') {
                    $penanggung = User::query()
                        ->where('status', 'active')
                        ->whereHas('role.permissions', fn ($q) => $q->where('name', 'edit_customer_message'))
                        ->get();
                }

                $this->line('Tanpa petugas ('.$izin.'): '.$baris->count().' tiket → '.$penanggung->count().' penerima');

                if (! $this->option('kering')) {
                    foreach ($penanggung as $orang) {
                        $orang->notify(new TiketLewatBatas($baris));
                    }
                }
            }
        }

        $this->info(($this->option('kering') ? 'Akan diingatkan: ' : 'Diingatkan: ').$tiket->count().' tiket lewat batas.');

        return self::SUCCESS;
    }
}
