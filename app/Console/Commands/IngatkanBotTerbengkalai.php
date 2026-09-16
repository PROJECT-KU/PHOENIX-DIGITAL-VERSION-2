<?php

namespace App\Console\Commands;

use App\Notifications\BotTurnitinTerbengkalai;
use App\Support\BotTurnitin;
use Illuminate\Console\Command;

/**
 * Ingatkan admin bila ada pengecekan bot yang menunggu tangan admin lebih dari
 * sehari (BotTurnitin::TERBENGKALAI_JAM).
 *
 * Panel di dasbor sudah menampilkannya, tetapi panel hanya bekerja bila ada
 * yang membukanya. Perintah ini yang membuat pekerjaan terlupakan tetap
 * mengetuk — dan mengetuk LAGI tiap hari selama belum dikerjakan, karena satu
 * pemberitahuan yang terlewat sama saja dengan tidak ada.
 */
class IngatkanBotTerbengkalai extends Command
{
    protected $signature = 'bot:ingatkan-terbengkalai {--dry-run : Tampilkan saja, jangan kirim}';

    protected $description = 'Kirim pengingat ke admin untuk pengecekan bot yang menunggu tindakan lebih dari sehari';

    public function handle(): int
    {
        // Kolom bot bisa belum ada (jeda antara kode terdeploy dan migrasi
        // dijalankan). Tanpa penjaga ini, penjadwal menuliskan galat tiap hari.
        if (! BotTurnitin::skemaSiap()) {
            $this->info('Kolom bot belum ada — dilewati.');

            return self::SUCCESS;
        }

        $terbengkalai = BotTurnitin::terbengkalai();

        if ($terbengkalai->isEmpty()) {
            $this->info('Tidak ada pengecekan yang terbengkalai.');

            return self::SUCCESS;
        }

        // perluAdmin() sudah terurut dari yang paling tua.
        $tertua = $terbengkalai->first();
        $lama = BotTurnitin::menungguSejak($tertua)?->locale('id')->diffForHumans(null, true) ?? 'beberapa waktu';
        $nomor = optional($tertua->order)->order_number;

        if ($this->option('dry-run')) {
            $this->info("AKAN mengirim: {$terbengkalai->count()} terbengkalai, terlama {$lama} ({$nomor}).");

            return self::SUCCESS;
        }

        $penerima = BotTurnitinTerbengkalai::kirim($terbengkalai->count(), $lama, $nomor);

        $this->info("{$terbengkalai->count()} terbengkalai (terlama {$lama}) — pengingat dikirim ke {$penerima} admin.");

        return self::SUCCESS;
    }
}
